<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'accepted_privacy' => ['accepted'],   // RGPD: acceptació de la política de privacitat
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Aquest correu ja està registrat.',
            'accepted_privacy.accepted' => 'Cal acceptar la política de privacitat.',
        ];
    }
}
