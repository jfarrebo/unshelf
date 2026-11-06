<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Gènere literari. Taula de catàleg que omple el GenreSeeder.
 */
class Genre extends Model
{
    protected $primaryKey = 'id_genre';

    /**
     * La taula no té created_at ni updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * A les rutes el gènere s'identifica pel slug (p. ex. /api/genres/fantasia).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Llibres d'aquest gènere.
     *
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'id_genre');
    }
}
