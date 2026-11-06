<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PUT /api/copies/{copy}: dades de l'exemplar (no del llibre; la fitxa només la corregeix l'administrador).
 */
class UpdateCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la propietat ja la comprova la policy (middleware can)
    }

    public function rules(): array
    {
        return static::copyRules();
    }

    public function messages(): array
    {
        return static::copyMessages();
    }

    /**
     * Regles compartides amb StoreCopyRequest.
     */
    public static function copyRules(): array
    {
        return [
            'condition' => ['required', Rule::in(['com_nou', 'bon_estat', 'acceptable'])],
            'mode' => ['required', Rule::in(['prestec', 'donacio'])],
            // Només en préstec: 2, 4 o 8 setmanes
            'max_loan_weeks' => ['required_if:mode,prestec', 'prohibited_if:mode,donacio', 'nullable', 'integer', Rule::in([2, 4, 8])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public static function copyMessages(): array
    {
        return [
            'max_loan_weeks.required_if' => 'Indica la durada màxima del préstec.',
            'max_loan_weeks.prohibited_if' => 'Les donacions no tenen durada de préstec.',
            'max_loan_weeks.in' => 'La durada màxima ha de ser de 2, 4 o 8 setmanes.',
        ];
    }
}
