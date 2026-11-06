<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenreController extends Controller
{
    /**
     * GET /api/genres – llista per als filtres i el formulari de publicació.
     */
    public function index(): AnonymousResourceCollection
    {
        return GenreResource::collection(Genre::orderBy('name')->get());
    }
}
