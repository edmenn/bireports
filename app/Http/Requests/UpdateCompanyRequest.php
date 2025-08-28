<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // o tu policy
    }

    public function rules(): array
    {
        return [
            // reglas para actualizar empresa
            'name'   => ['required','string','max:255'],
            'domain' => ['nullable','string','max:255'],
            // ...
        ];
    }
}
