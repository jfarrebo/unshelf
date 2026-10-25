<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si un usuari és bloquejat mentre té la sessió iniciada, se li revoca el token
 * i no pot continuar fent servir l'API.
 */
class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_blocked) {
            $user->currentAccessToken()?->delete();

            return response()->json(['message' => 'Aquest compte està bloquejat.'], 403);
        }

        return $next($request);
    }
}
