<?php

namespace App\Http\Requests;

use App\Support\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegisterOnboardingRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $organization = app(Workspace::class)->currentOrganization($this->user());
        $branch = $organization?->branches()->oldest()->first();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('registers', 'code')->where('branch_id', $branch?->id),
            ],
        ];
    }
}
