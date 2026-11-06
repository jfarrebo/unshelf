<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PATCH /api/copies/{copy}/status: el propietari pausa o reactiva l'exemplar.
 * La resta d'estats només els canvia el flux de sol·licituds.
 */
class UpdateCopyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la propietat ja la comprova la policy (middleware can)
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['disponible', 'pausat'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => "L'estat només pot ser «disponible» o «pausat».",
        ];
    }
}
