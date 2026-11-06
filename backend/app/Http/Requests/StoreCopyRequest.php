<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/copies – F2.01: publicar un exemplar.
 * El llibre pot ser un d'existent (id_book) o un de nou (objecte book).
 */
class StoreCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // ja passa pel middleware auth:sanctum
    }

    /**
     * Abans de validar, l'ISBN es normalitza (només dígits i X).
     */
    protected function prepareForValidation(): void
    {
        $book = $this->input('book');

        if (is_array($book) && array_key_exists('isbn', $book)) {
            $book['isbn'] = Book::normalizeIsbn($book['isbn']);
            $this->merge(['book' => $book]);
        }
    }

    public function rules(): array
    {
        return [
            ...UpdateCopyRequest::copyRules(),

            // Llibre existent…
            'id_book' => ['required_without:book', 'prohibits:book', 'integer', 'exists:books,id_book'],

            // …o llibre nou
            'book' => ['required_without:id_book', 'array'],
            'book.title' => ['required_with:book', 'string', 'max:255'],
            'book.author' => ['required_with:book', 'string', 'max:255'],
            'book.isbn' => ['nullable', 'string', 'regex:/^(\d{9}[\dX]|\d{13})$/'],
            'book.id_genre' => ['required_with:book', 'integer', 'exists:genres,id_genre'],
            'book.language' => ['required_with:book', Rule::in(['catala', 'castella', 'angles', 'frances'])],
            'book.synopsis' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            ...UpdateCopyRequest::copyMessages(),
            'id_book.required_without' => "Indica un llibre existent o les dades d'un llibre nou.",
            'book.required_without' => "Indica un llibre existent o les dades d'un llibre nou.",
            'id_book.prohibits' => 'Indica un llibre existent o un de nou, però no tots dos.',
            'book.isbn.regex' => "L'ISBN ha de tenir 10 o 13 caràcters (dígits i, al final, X).",
        ];
    }
}
