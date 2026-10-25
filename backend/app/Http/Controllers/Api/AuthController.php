<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register – F1.01
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // 'accepted_privacy' només es valida; no es desa a la taula
        $user = User::create($request->safe()->except('accepted_privacy'));

        $token = $user->createToken('unshelf')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * POST /api/login – F1.02
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            // Missatge genèric: no revela si el correu existeix
            throw ValidationException::withMessages([
                'email' => ['El correu o la contrasenya no són correctes.'],
            ]);
        }

        if ($user->is_blocked) {
            return response()->json(['message' => 'Aquest compte està bloquejat.'], 403);
        }

        $token = $user->createToken('unshelf')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/logout – F1.02
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * GET /api/me – F1.04
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * PUT /api/me – F1.04
     */
    public function updateMe(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }
}
