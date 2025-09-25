<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationSectionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $application = $this->route('application');
        if ($application) {
            $this->merge(['application_id' => (int) $application->id]);
        }
    }

    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $user->can('application_sections.manage');
    }

    public function rules(): array
    {
        $applicationId = (int) $this->input('application_id');
        $sectionId = (int) optional($this->route('section'))->id;

        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::unique('application_sections', 'slug')->ignore($sectionId)->where(fn ($query) => $query->where('application_id', $applicationId))],
            'parent_id' => ['nullable', 'integer', 'exists:application_sections,id'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}