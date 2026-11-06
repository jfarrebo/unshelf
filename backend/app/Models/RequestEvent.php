<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Canvi d'estat d'una sol·licitud. Registre immutable: només té created_at.
 */
class RequestEvent extends Model
{
    protected $primaryKey = 'id_request_event';

    /**
     * La taula no té updated_at; Laravel només omplirà created_at.
     */
    const UPDATED_AT = null;

    /**
     * 'id_book_request' s'assigna des de la relació ($bookRequest->events()->create()).
     */
    protected $fillable = [
        'status',
        'is_automatic',
    ];

    protected function casts(): array
    {
        return [
            'is_automatic' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<BookRequest, $this>
     */
    public function bookRequest(): BelongsTo
    {
        return $this->belongsTo(BookRequest::class, 'id_book_request');
    }
}
