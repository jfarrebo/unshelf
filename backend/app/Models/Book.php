<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Llibre: fitxa única de cada obra o edició. Els usuaris no comparteixen
 * el llibre sinó els seus exemplars (Copy).
 */
class Book extends Model
{
    protected $primaryKey = 'id_book';

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
