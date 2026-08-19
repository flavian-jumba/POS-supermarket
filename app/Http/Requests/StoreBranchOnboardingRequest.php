<?php

namespace App\Http\Requests;

use App\Support\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchOnboardingRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'code')->where('organization_id', $organization?->id),
            ],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'regex:/^(?:\\+?[0-9][0-9\\s().-]{7,18})$/'],
        ];
    }
}
