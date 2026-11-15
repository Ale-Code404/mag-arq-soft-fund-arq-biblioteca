<?php

namespace App\Infrastructure\Http\Requests;

use App\Domain\Catalog\Genre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isbn' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:150'],
            'year' => ['required', 'integer', 'between:1000,9999'],
            'genre' => ['required', Rule::enum(Genre::class)],
            // measurement ranges are validated by the domain (R-02)
            'heightCm' => ['required', 'numeric'],
            'widthCm' => ['required', 'numeric'],
            'thicknessCm' => ['required', 'numeric'],
        ];
    }
}
