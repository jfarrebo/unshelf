<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Exemplar: la còpia concreta d'un llibre que ofereix un usuari.
 * Amb soft delete perquè les sol·licituds antigues el continuïn referenciant.
 */
class Copy extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_copy';

    /**
     * Valor per defecte (el mateix que la migració) perquè l'exemplar
     * acabat de crear ja tingui l'estat sense tornar-lo a llegir de la BD.
     */
    protected $attributes = [
        'status' => 'disponible',
    ];

    /**
     * 'id_user_owner' s'assigna des de l'usuari autenticat ($user->copies()->create()).
     * 'status' NO hi és: el canvien les accions del flux de sol·licituds.
     */
    protected $fillable = [
        'id_book',
        'condition',
        'mode',
        'max_loan_weeks',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'max_loan_weeks' => 'integer',
        ];
    }

    /**
     * El propietari només pot editar, pausar o retirar l'exemplar si no està
     * compromès amb cap sol·licitud (reservat, en préstec o cedit).
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['disponible', 'pausat'], true);
    }

    /**
     * Exemplars que es mostren al catàleg: no pausats ni cedits,
     * i d'usuaris que no estan bloquejats.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->whereIn('status', ['disponible', 'reservat', 'en_prestec'])
            ->whereHas('owner', fn (Builder $owner) => $owner->where('is_blocked', false));
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'id_book');
    }

    /**
     * Propietari de l'exemplar.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_owner');
    }

    /**
     * Sol·licituds rebudes per aquest exemplar.
     *
     * @return HasMany<BookRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(BookRequest::class, 'id_copy');
    }
}
