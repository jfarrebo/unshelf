<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Exemplar. El propietari es mostra amb les dades públiques (PublicUserResource).
 */
class CopyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_copy' => $this->id_copy,
            'condition' => $this->condition,
            'mode' => $this->mode,
            'max_loan_weeks' => $this->max_loan_weeks,
            'status' => $this->status,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'book' => new BookResource($this->whenLoaded('book')),
            'owner' => new PublicUserResource($this->whenLoaded('owner')),
            'pending_requests_count' => $this->whenCounted('pending_requests_count'),
            // Fitxa del llibre amb sessió: el controlador només carrega la sol·licitud activa
            // de l'usuari actual; si no n'hi ha, null. Sense sessió, el camp no apareix.
            'my_request' => $this->whenLoaded('requests', function () {
                $request = $this->requests->first();

                return $request ? [
                    'id_book_request' => $request->id_book_request,
                    'status' => $request->status,
                ] : null;
            }),
        ];
    }
}
