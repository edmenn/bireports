<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if ($this->isMethod('post')) {
            return $user->can('applications.create');
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $user->can('applications.edit');
        }

        return false;
    }

    public function rules(): array
    {
        $applicationId = (int) optional($this->route('application'))->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('applications', 'name')->ignore($applicationId)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::unique('applications', 'slug')->ignore($applicationId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],

            'users' => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],

            'groups' => ['nullable', 'array'],
            'groups.*' => ['integer', 'exists:groups,id'],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],

            'reports' => ['nullable', 'array'],
            'reports.*' => ['string', 'exists:reports,id'],

            'sections' => ['nullable', 'array'],
            'sections.*' => ['nullable', 'string', 'max:255'],

            'sections_existing' => ['nullable', 'array'],
            'sections_existing.*' => ['nullable', 'string', 'max:255'],

            'sections_remove' => ['nullable', 'array'],
            'sections_remove.*' => ['integer', 'exists:application_sections,id'],
        ];
    }
}
