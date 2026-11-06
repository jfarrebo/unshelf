<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * POST /api/copies/{copy}/photo: foto de l'exemplar (multipart/form-data).
 */
class UploadCopyPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la propietat ja la comprova la policy (middleware can)
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', File::image()->types(['jpg', 'jpeg', 'png'])->max(10 * 1024)],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Selecciona una foto.',
            'photo.image' => 'La foto ha de ser JPG o PNG.',
            'photo.mimes' => 'La foto ha de ser JPG o PNG.',
            'photo.max' => 'La foto no pot superar els 10 MB.',
            'photo.uploaded' => "No s'ha pogut pujar la foto (màx. 10 MB).",
        ];
    }
}
