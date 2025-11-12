<?php
// app/Http/Requests/StoreChartOfAccountRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Use the policy to check if user can create accounts
        return true; // $this->user()->can('create', \App\Models\Accounting\ChartOfAccount::class);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'description' => ['nullable', 'string', 'max:500'],
            'organization_id' => ['required', 'exists:organizations,id'],
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
