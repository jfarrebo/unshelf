<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CopyController;
use App\Http\Controllers\Api\GenreController;
use Illuminate\Support\Facades\Route;

/*
| Rutes de l'API d'Unshelf (prefix /api)
| PAC 2: autenticació, perfil i control de rols.
| PAC 3: llibres i exemplars.
*/

// Públiques (màxim 6 intents per minut, contra atacs de força bruta)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Catàleg públic
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/lookup', [BookController::class, 'lookup']);   // abans de {book:slug}: si no, "lookup" es prendria com un slug
Route::get('/books/{book:slug}', [BookController::class, 'show']);

// Usuari amb la sessió iniciada i no bloquejat
Route::middleware(['auth:sanctum', 'not.blocked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);

    // Exemplars (F2.01). Middleware can: la CopyPolicy comprova que sigui el propietari (403)
    Route::get('/me/copies', [CopyController::class, 'mine']);
    Route::post('/copies', [CopyController::class, 'store']);
    Route::put('/copies/{copy}', [CopyController::class, 'update'])->can('update', 'copy');
    Route::patch('/copies/{copy}/status', [CopyController::class, 'updateStatus'])->can('update', 'copy');
    Route::post('/copies/{copy}/photo', [CopyController::class, 'uploadPhoto'])->can('update', 'copy');
    Route::delete('/copies/{copy}', [CopyController::class, 'destroy'])->can('delete', 'copy');

    // Administració
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Ruta temporal per comprovar el control de rols (PAC 2)
        Route::get('/ping', fn () => response()->json(['message' => "Accés d'administració correcte."]));
    });
});
