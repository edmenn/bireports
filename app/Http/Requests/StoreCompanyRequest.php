<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // luego aplicamos policies/roles
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'subdomain'  => ['nullable', 'string', 'max:100'],
            'is_active'  => ['required', 'boolean'],
            'description'=> ['nullable', 'string'],
        ];
    }
}
