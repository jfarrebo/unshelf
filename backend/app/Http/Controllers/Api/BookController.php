<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Http\Requests\LookupBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * GET /api/books – catàleg: llibres amb algun exemplar visible que compleixi els filtres.
     *
     * Filtres de l'exemplar (estat, modalitat, zona del propietari): un llibre surt si en té
     *   almenys un que hi encaixa, i el recompte de disponibles només compta aquests.
     * Filtres del llibre: text (títol, autor o ISBN), gènere i idioma.
     * Ordre: rellevància (destacats i amb més disponibles primer), més recents o títol.
     */
    public function index(CatalogRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        // Condicions dels exemplars, reaprofitades al whereHas i al recompte
        $matchingCopies = function (Builder $copies) use ($filters) {
            $copies->visible()
                ->when($filters['condition'] ?? null, fn (Builder $q, array $values) => $q->whereIn('condition', $values))
                ->when($filters['mode'] ?? null, fn (Builder $q, array $values) => $q->whereIn('mode', $values))
                ->when($filters['location'] ?? null, fn (Builder $q, string $location) => $q->whereHas(
                    'owner',
                    fn (Builder $owner) => $this->whereLocation($owner, $location),
                ));
        };

        $books = Book::query()
            ->whereHas('copies', $matchingCopies)
            ->when($filters['q'] ?? null, fn (Builder $q, string $text) => $this->whereText($q, $text))
            ->when($filters['genre'] ?? null, fn (Builder $q, array $slugs) => $q->whereIn(
                'id_genre',
                Genre::whereIn('slug', $slugs)->select('id_genre'),
            ))
            ->when($filters['language'] ?? null, fn (Builder $q, array $values) => $q->whereIn('language', $values))
            ->with('genre')
            ->withCount(['copies as available_copies_count' => function (Builder $copies) use ($matchingCopies) {
                $matchingCopies($copies);
                $copies->where('status', 'disponible');
            }])
            ->withMax(['copies as latest_copy_at' => $matchingCopies], 'created_at');

        match ($filters['sort'] ?? 'rellevancia') {
            'recents' => $books->orderByDesc('latest_copy_at'),
            'titol' => $books->orderBy('title'),
            default => $books->orderByDesc('is_featured')
                ->orderByDesc('available_copies_count')
                ->orderByDesc('latest_copy_at'),
        };

        // Desempat estable perquè la paginació no repeteixi ni salti llibres
        $books->orderBy('id_book');

        return BookResource::collection($books->paginate(12)->withQueryString());
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
            $text = '%'.$this->escapeLike($request->validated('q')).'%';
            $query->where(fn (Builder $q) => $q->where('title', 'like', $text)->orWhere('author', 'like', $text))
                ->orderBy('title')
                ->limit(10);
        }

        return BookResource::collection($query->get());
    }

    /**
     * GET /api/books/{book:slug} – fitxa del llibre amb els exemplars visibles.
     *
     * La ruta és pública, però si arriba un token vàlid es pot saber qui és l'usuari
     * ($request->user('sanctum')). Aleshores, de cada exemplar es carrega només la seva
     * sol·licitud activa (pendent, acceptada o en préstec), que el resource retorna com a my_request.
     */
    public function show(Request $request, Book $book): BookResource
    {
        $user = $request->user('sanctum');

        $book->load([
            'genre',
            'copies' => fn ($copies) => $copies->visible()
                ->with('owner')
                ->when($user, fn ($q) => $q->with(['requests' => fn ($requests) => $requests
                    ->where('id_user_requester', $user->id_user)
                    ->whereIn('status', ['pendent', 'acceptada', 'en_prestec'])
                    ->latest()]))
                ->latest(),
        ]);

        return new BookResource($book);
    }

    /**
     * Text lliure: títol o autor que el contenen, o l'ISBN exacte (amb o sense guions).
     */
    private function whereText(Builder $query, string $text): void
    {
        $like = '%'.$this->escapeLike($text).'%';
        $isbn = Book::normalizeIsbn($text);

        $query->where(function (Builder $q) use ($like, $isbn) {
            $q->where('title', 'like', $like)->orWhere('author', 'like', $like);
            if ($isbn !== null && strlen($isbn) >= 10) {
                $q->orWhere('isbn', $isbn);
            }
        });
    }

    /**
     * Zona: si són dígits, es compara el principi del codi postal; si no, la ciutat el conté.
     *
     * D'un codi postal només es fan servir els 3 primers dígits, que agrupen la mateixa
     * població o zona (080xx = Barcelona ciutat, 170xx = Girona…): qui cerca 08001 vol
     * llibres a prop, no només del seu codi exacte. Amb 2 dígits (08) cobreix la província.
     * El codi postal només es fa servir per filtrar: mai es retorna.
     */
    private function whereLocation(Builder $owner, string $location): void
    {
        if (ctype_digit($location)) {
            $owner->where('postal_code', 'like', substr($location, 0, 3).'%');
        } else {
            $owner->where('city', 'like', '%'.$this->escapeLike($location).'%');
        }
    }

    /**
     * Escapa % i _ perquè l'usuari no pugui fer servir comodins de LIKE.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
