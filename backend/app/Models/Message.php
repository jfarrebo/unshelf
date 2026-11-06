<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Missatge del xat d'una sol·licitud entre el lector i el propietari.
 */
class Message extends Model
{
    protected $primaryKey = 'id_message';

    /**
     * La taula no té updated_at: els missatges no s'editen.
     */
    const UPDATED_AT = null;

    /**
     * 'id_book_request' s'assigna des de la relació ($bookRequest->messages()->create()).
     * 'read_at' NO hi és: el marca el sistema quan el destinatari llegeix el xat.
     */
    protected $fillable = [
        'id_user_sender',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<BookRequest, $this>
     */
    public function bookRequest(): BelongsTo
    {
        return $this->belongsTo(BookRequest::class, 'id_book_request');
    }

    /**
     * Usuari que envia el missatge.
     *
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_sender');
    }
}
