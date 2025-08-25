<?php
// app/Http/Requests/UpdateChartOfAccountRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Use the policy to check if user can update this specific account
        return true; // $this->user()->can('update', $this->route('account'));
    }

    public function rules(): array
    {
        $accountId = $this->route('account')->id;

        return [
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('chart_of_accounts', 'code')->ignore($accountId)
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'The account code must be unique.',
            'type.in' => 'The account type must be one of: asset, liability, equity, revenue, expense.',
        ];
    }
}
