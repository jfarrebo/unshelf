<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Dades de l'usuari que retorna l'API (mai la contrasenya ni els tokens).
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id_user,
            'name' => $this->name,
            'email' => $this->email,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'avatar_url' => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
            'role' => $this->role,
            'created_at' => $this->created_at,
        ];
    }
}
