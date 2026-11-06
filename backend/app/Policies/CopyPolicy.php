<?php

namespace App\Policies;

use App\Models\Copy;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Qui pot modificar un exemplar: només el seu propietari (si no, 403).
 * Si l'acció és possible segons l'estat de l'exemplar (409) ho decideix el controlador.
 */
class CopyPolicy
{
    public function update(User $user, Copy $copy): Response
    {
        return $copy->owner()->is($user)
            ? Response::allow()
            : Response::deny("No ets el propietari d'aquest exemplar.");
    }

    public function delete(User $user, Copy $copy): Response
    {
        return $this->update($user, $copy);
    }
}
