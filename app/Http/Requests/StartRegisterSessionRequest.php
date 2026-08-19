<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartRegisterSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'register_id' => ['required', 'integer'],
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
