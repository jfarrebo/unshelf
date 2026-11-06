<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Fitxa d'un llibre. El gènere, el recompte i els exemplars només
 * apareixen si el controlador els ha carregat.
 */
class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_book' => $this->id_book,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'language' => $this->language,
            'synopsis' => $this->synopsis,
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'is_featured' => $this->is_featured,
            'genre' => new GenreResource($this->whenLoaded('genre')),
            'available_copies_count' => $this->whenCounted('available_copies_count'),
            'copies' => CopyResource::collection($this->whenLoaded('copies')),
        ];
    }
}
