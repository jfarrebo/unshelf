<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
| Rutes de l'API d'Unshelf (prefix /api)
| PAC 2: autenticació, perfil i control de rols.
*/

// Públiques (màxim 6 intents per minut, contra atacs de força bruta)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Usuari amb la sessió iniciada i no bloquejat
Route::middleware(['auth:sanctum', 'not.blocked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);

    // Administració
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Ruta temporal per comprovar el control de rols (PAC 2)
        Route::get('/ping', fn () => response()->json(['message' => "Accés d'administració correcte."]));
    });
});
