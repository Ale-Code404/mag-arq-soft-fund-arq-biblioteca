<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['sometimes', 'string', 'max:50'],
            'password' => ['sometimes', 'string', 'min:8', 'max:100'],
            'name' => ['sometimes', 'string', 'max:150'],
            'position' => ['sometimes', 'string', 'max:100'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
