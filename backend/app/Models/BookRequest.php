<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sol·licitud d'un exemplar (préstec o donació) d'un lector a un propietari.
 */
class BookRequest extends Model
{
    protected $primaryKey = 'id_book_request';

    /**
     * 'status' i 'due_date' NO hi són: els canvien les accions
     * de canvi d'estat (accept, reject, deliver, return, cancel).
     */
    protected $fillable = [
        'id_copy',
        'id_user_requester',
        'id_user_owner',
        'mode',
        'loan_weeks',
    ];

    protected function casts(): array
    {
        return [
            'loan_weeks' => 'integer',
            'due_date' => 'date',
        ];
    }

    /**
     * Exemplar sol·licitat. withTrashed(): l'historial s'ha de poder
     * consultar encara que el propietari hagi retirat l'exemplar.
     *
     * @return BelongsTo<Copy, $this>
     */
    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class, 'id_copy')->withTrashed();
    }

    /**
     * Lector que fa la sol·licitud.
     *
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_requester');
    }

    /**
     * Propietari de l'exemplar (es desa per no dependre de l'exemplar).
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_owner');
    }

    /**
     * Historial d'estats, del més antic al més recent (línia de temps).
     *
     * @return HasMany<RequestEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(RequestEvent::class, 'id_book_request')->oldest();
    }

    /**
     * Missatges del xat, en ordre cronològic.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_book_request')->oldest();
    }
}
