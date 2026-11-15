<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterShelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'levels' => ['required', 'array', 'min:1'],
            'levels.*.number' => ['required', 'integer', 'min:1', 'distinct'],
            'levels.*.lengthCm' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'levels.*.heightCm' => ['required', 'numeric', 'gt:0', 'max:1000'],
        ];
    }
}
