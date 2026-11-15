<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loanDays' => ['required', 'integer', 'min:1', 'max:365'],
            'renewalDays' => ['required', 'integer', 'min:1', 'max:365'],
            'maxRenewals' => ['required', 'integer', 'min:1', 'max:10'],
            'dailyFineValue' => ['required', 'integer', 'min:1'],
            'clearanceCm' => ['required', 'numeric', 'gt:0', 'max:10'],
            'defaultThicknessCm' => ['required', 'numeric', 'gt:0', 'max:15'],
        ];
    }
}
