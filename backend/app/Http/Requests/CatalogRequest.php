<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GET /api/books: cerca, filtres, ordre i pàgina del catàleg.
 * Les llistes arriben amb la sintaxi de Laravel: condition[]=com_nou&condition[]=bon_estat
 */
class CatalogRequest extends FormRequest
{
    public const CONDITIONS = ['com_nou', 'bon_estat', 'acceptable'];

    public const MODES = ['prestec', 'donacio'];

    public const LANGUAGES = ['catala', 'castella', 'angles', 'frances'];

    public const SORTS = ['rellevancia', 'recents', 'titol'];

    public function authorize(): bool
    {
        return true; // catàleg públic
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'condition' => ['sometimes', 'array'],
            'condition.*' => [Rule::in(self::CONDITIONS)],
            'mode' => ['sometimes', 'array'],
            'mode.*' => [Rule::in(self::MODES)],
            'language' => ['sometimes', 'array'],
            'language.*' => [Rule::in(self::LANGUAGES)],
            'genre' => ['sometimes', 'array'],
            'genre.*' => ['string', 'max:100'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.max' => 'La cerca pot tenir com a màxim 100 caràcters.',
            'location.max' => 'La zona pot tenir com a màxim 100 caràcters.',
            'condition.*.in' => "L'estat de l'exemplar no és vàlid.",
            'mode.*.in' => 'La modalitat no és vàlida.',
            'language.*.in' => "L'idioma no és vàlid.",
            'sort.in' => "L'ordre no és vàlid.",
            'page.*' => 'La pàgina no és vàlida.',
        ];
    }
}
