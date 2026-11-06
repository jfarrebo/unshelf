<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Llibre: fitxa única de cada obra o edició. Els usuaris no comparteixen
 * el llibre sinó els seus exemplars (Copy).
 */
class Book extends Model
{
    protected $primaryKey = 'id_book';

    /**
     * Valors per defecte (els mateixos que la migració) perquè el model
     * acabat de crear els tingui sense tornar-lo a llegir de la BD.
     */
    protected $attributes = [
        'is_featured' => false,
    ];

    /**
     * 'is_featured' NO hi és: només el pot marcar l'administrador.
     */
    protected $fillable = [
        'id_genre',
        'title',
        'slug',
        'author',
        'isbn',
        'language',
        'synopsis',
        'cover_path',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    /**
     * En crear un llibre, si no té slug se'n genera un d'únic a partir del títol.
     * El slug no canvia si després es corregeix el títol (URL estables).
     */
    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            $book->slug ??= static::uniqueSlug($book->title);
        });
    }

    /**
     * Slug a partir del títol; si ja existeix, s'hi afegeix -2, -3…
     */
    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'llibre';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Deixa l'ISBN només amb dígits i X (treu guions i espais): "978-84-1234-56-7" → "9788412345567".
     */
    public static function normalizeIsbn(?string $isbn): ?string
    {
        $normalized = preg_replace('/[^0-9X]/', '', strtoupper((string) $isbn));

        return $normalized === '' ? null : $normalized;
    }

    /**
     * A les rutes el llibre s'identifica pel slug (/llibres/:slug).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Genre, $this>
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'id_genre');
    }

    /**
     * Exemplars d'aquest llibre que ofereixen els usuaris.
     *
     * @return HasMany<Copy, $this>
     */
    public function copies(): HasMany
    {
        return $this->hasMany(Copy::class, 'id_book');
    }
}
