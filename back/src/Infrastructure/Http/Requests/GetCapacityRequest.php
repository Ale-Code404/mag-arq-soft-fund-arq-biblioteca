<?php

namespace App\Infrastructure\Http\Requests;

use App\Application\Inventory\GetCapacity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope' => ['required', Rule::in(GetCapacity::SCOPES)],
            'value' => ['nullable', 'required_unless:scope,total', 'string', 'max:30'],
        ];
    }
}
