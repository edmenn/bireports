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

    protected function prepareForValidation(): void
    {
        $sections = $this->input('report_sections');

        if (is_array($sections)) {
            $normalized = [];
            foreach ($sections as $reportId => $sectionId) {
                $normalized[$reportId] = $sectionId === '' ? null : $sectionId;
            }

            $this->merge(['report_sections' => $normalized]);
        }
    }

    public function rules(): array
    {
        $applicationId = (int) optional($this->route('application'))->id;
        $sectionRule = Rule::exists('application_sections', 'id');

        if ($applicationId > 0) {
            $sectionRule = $sectionRule->where(fn ($query) => $query->where('application_id', $applicationId));
        }

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

            'report_sections' => ['nullable', 'array'],
            'report_sections.*' => ['nullable', 'integer', $sectionRule],

            'sections' => ['nullable', 'array'],
            'sections.*' => ['nullable', 'string', 'max:255'],

            'sections_existing' => ['nullable', 'array'],
            'sections_existing.*' => ['nullable', 'string', 'max:255'],

            'sections_remove' => ['nullable', 'array'],
            'sections_remove.*' => ['integer', 'exists:application_sections,id'],
        ];
    }
}
