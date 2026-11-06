<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCopyRequest;
use App\Http\Requests\UpdateCopyRequest;
use App\Http\Requests\UpdateCopyStatusRequest;
use App\Http\Requests\UploadCopyPhotoRequest;
use App\Http\Resources\CopyResource;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Copy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CopyController extends Controller
{
    /**
     * GET /api/me/copies – exemplars de l'usuari, amb les sol·licituds pendents de cadascun.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $copies = $request->user()->copies()
            ->with('book.genre')
            ->withCount(['requests as pending_requests_count' => fn ($requests) => $requests->where('status', 'pendent')])
            ->latest()
            ->get();

        return CopyResource::collection($copies);
    }

    /**
     * POST /api/copies – F2.01: publicar un exemplar d'un llibre existent o d'un de nou.
     */
    public function store(StoreCopyRequest $request): JsonResponse
    {
        // Llibre i exemplar es creen junts: si un falla, no queda res a mitges
        $copy = DB::transaction(function () use ($request) {
            $book = $request->filled('id_book')
                ? Book::findOrFail($request->validated('id_book'))
                : $this->findOrCreateBook($request->validated('book'));

            return $request->user()->copies()->create([
                ...$this->copyData($request->validated()),
                'id_book' => $book->id_book,
            ]);
        });

        return (new CopyResource($copy->load('book.genre')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /api/copies/{copy} – modificar estat de conservació, modalitat, durada i notes.
     */
    public function update(UpdateCopyRequest $request, Copy $copy): CopyResource|JsonResponse
    {
        if (! $copy->isEditable()) {
            return $this->conflict('No pots modificar un exemplar reservat, en préstec o cedit.');
        }

        $copy->update($this->copyData($request->validated()));

        return new CopyResource($copy->load('book.genre'));
    }

    /**
     * PATCH /api/copies/{copy}/status – pausar o reactivar (disponible ↔ pausat).
     */
    public function updateStatus(UpdateCopyStatusRequest $request, Copy $copy): CopyResource|JsonResponse
    {
        if (! $copy->isEditable()) {
            return $this->conflict('No pots pausar ni reactivar un exemplar reservat, en préstec o cedit.');
        }

        $copy->status = $request->validated('status');
        $copy->save();

        return new CopyResource($copy->load('book.genre'));
    }

    /**
     * POST /api/copies/{copy}/photo – foto de l'exemplar. Si el llibre no té
     * portada, aquesta foto també passa a ser la portada.
     */
    public function uploadPhoto(UploadCopyPhotoRequest $request, Copy $copy): CopyResource
    {
        $disk = Storage::disk('public');

        if ($copy->photo_path) {
            $disk->delete($copy->photo_path);
        }

        $copy->photo_path = $request->file('photo')->store('copies', 'public');
        $copy->save();

        // Còpia independent del fitxer: si el propietari canvia o esborra
        // la foto de l'exemplar, la portada del llibre no es perd
        $book = $copy->book;
        if (! $book->cover_path) {
            $coverPath = 'covers/'.basename($copy->photo_path);
            $disk->copy($copy->photo_path, $coverPath);
            $book->cover_path = $coverPath;
            $book->save();
        }

        return new CopyResource($copy->load('book.genre'));
    }

    /**
     * DELETE /api/copies/{copy} – retirar l'exemplar (soft delete).
     * Les sol·licituds pendents es rebutgen automàticament.
     */
    public function destroy(Copy $copy): Response|JsonResponse
    {
        if (! $copy->isEditable()) {
            return $this->conflict('No pots retirar un exemplar reservat, en préstec o cedit.');
        }

        DB::transaction(function () use ($copy) {
            $copy->requests()->where('status', 'pendent')->get()
                ->each(function (BookRequest $bookRequest) {
                    $bookRequest->status = 'rebutjada';
                    $bookRequest->save();
                    $bookRequest->events()->create(['status' => 'rebutjada', 'is_automatic' => true]);
                });

            $copy->status = 'retirat';
            $copy->save();
            $copy->delete();
        });

        return response()->noContent();
    }

    /**
     * Si l'ISBN ja és al catàleg es reaprofita la fitxa; si no, es crea el llibre.
     */
    private function findOrCreateBook(array $data): Book
    {
        if (! empty($data['isbn'])) {
            $existing = Book::where('isbn', $data['isbn'])->first();

            if ($existing) {
                return $existing;
            }
        }

        return Book::create($data);
    }

    /**
     * Dades de l'exemplar. En donació, max_loan_weeks sempre queda a null.
     */
    private function copyData(array $validated): array
    {
        return [
            'condition' => $validated['condition'],
            'mode' => $validated['mode'],
            'max_loan_weeks' => $validated['mode'] === 'prestec' ? $validated['max_loan_weeks'] : null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /**
     * 409: l'acció no està permesa en l'estat actual de l'exemplar.
     */
    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
