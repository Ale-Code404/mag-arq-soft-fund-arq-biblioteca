<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'readerIdentification' => ['required', 'string', 'max:30'],
            'copyCode' => ['required', 'string', 'max:30'],
        ];
    }
}
