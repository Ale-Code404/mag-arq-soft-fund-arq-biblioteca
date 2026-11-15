<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shelf' => ['required', 'string', 'max:30'],
            'level' => ['required', 'integer', 'min:1'],
        ];
    }
}
