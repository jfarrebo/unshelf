<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;

/**
 * GET /api/books/lookup?isbn= o ?q=: comprovar si un llibre ja existeix abans de publicar-lo.
 */
class LookupBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('isbn')) {
            $this->merge(['isbn' => Book::normalizeIsbn($this->input('isbn'))]);
        }
    }

    public function rules(): array
    {
        return [
            'isbn' => ['required_without:q', 'nullable', 'string', 'regex:/^(\d{9}[\dX]|\d{13})$/'],
            'q' => ['required_without:isbn', 'nullable', 'string', 'min:2', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.required_without' => 'Indica un ISBN o un text de cerca.',
            'q.required_without' => 'Indica un ISBN o un text de cerca.',
            'isbn.regex' => "L'ISBN ha de tenir 10 o 13 caràcters (dígits i, al final, X).",
            'q.min' => 'La cerca ha de tenir almenys 2 caràcters.',
        ];
    }
}
