<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * GET /api/books – catàleg: llibres amb algun exemplar visible.
     * (Els filtres i la cerca s'afegeixen a la setmana 2.)
     */
    public function index(): AnonymousResourceCollection
    {
        $books = Book::query()
            ->whereHas('copies', fn (Builder $copies) => $copies->visible())
            ->with('genre')
            ->withCount(['copies as available_copies_count' => fn (Builder $copies) => $copies->visible()->where('status', 'disponible')])
            ->latest()
            ->paginate(12);

        return BookResource::collection($books);
    }

    /**
     * GET /api/books/lookup?isbn= o ?q= – F2.01: abans de publicar, comprovar
     * si el llibre ja existeix per reaprofitar-ne la fitxa.
     */
    public function lookup(LookupBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::with('genre');

        if ($request->filled('isbn')) {
            $query->where('isbn', $request->validated('isbn'));
        } else {
            $text = '%'.$request->validated('q').'%';
            $query->where(fn (Builder $q) => $q->where('title', 'like', $text)->orWhere('author', 'like', $text))
                ->orderBy('title')
                ->limit(10);
        }

        return BookResource::collection($query->get());
    }

    /**
     * GET /api/books/{book:slug} – fitxa del llibre amb els exemplars visibles.
     */
    public function show(Book $book): BookResource
    {
        $book->load([
            'genre',
            'copies' => fn ($copies) => $copies->visible()->with('owner')->latest(),
        ]);

        return new BookResource($book);
    }
}
