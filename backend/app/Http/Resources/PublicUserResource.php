<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Dades públiques d'un usuari (propietari d'un exemplar, lector…):
 * només nom, ciutat i foto. Mai el correu ni el codi postal.
 * L'id_user és un identificador intern perquè el front sàpiga si l'exemplar és seu.
 */
class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id_user,
            'name' => $this->name,
            'city' => $this->city,
            'avatar_url' => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
        ];
    }
}
