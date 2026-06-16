<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [$this->isMethod('post') ? 'required' : 'nullable', 'exists:accounts,id'], // Mandatory for new, optional for updates
            'code' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('accounts', 'code')->ignore($this->route('account'))],
            'name' => ['required', 'string', 'max:255'],
            'account_type_id' => ['required', 'exists:account_types,id'],
            'is_postable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'report_group' => ['nullable', 'string', 'max:100'],
            'depreciation_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}