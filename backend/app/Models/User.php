<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Clau primària segons la convenció del projecte (id_<entitat>).
     */
    protected $primaryKey = 'id_user';

    /**
     * Camps que es poden omplir des d'un formulari.
     * 'role' i 'is_blocked' NO hi són: només els pot canviar l'administrador.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'city',
        'postal_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Exemplars que ofereix l'usuari.
     *
     * @return HasMany<Copy, $this>
     */
    public function copies(): HasMany
    {
        return $this->hasMany(Copy::class, 'id_user_owner');
    }

    /**
     * Sol·licituds que l'usuari ha fet com a lector.
     *
     * @return HasMany<BookRequest, $this>
     */
    public function sentRequests(): HasMany
    {
        return $this->hasMany(BookRequest::class, 'id_user_requester');
    }

    /**
     * Sol·licituds que l'usuari ha rebut com a propietari.
     *
     * @return HasMany<BookRequest, $this>
     */
    public function receivedRequests(): HasMany
    {
        return $this->hasMany(BookRequest::class, 'id_user_owner');
    }

    /**
     * Missatges que l'usuari ha enviat.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_user_sender');
    }
}
