@php
    $application = $application ?? null;
    $selectedUsers = collect(old('users', $application?->users?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedGroups = collect(old('groups', $application?->groups?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedReports = collect(old('reports', $application?->reports?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();

    $existingSections = ($application?->sections ?? collect());
    $existingSectionsSorted = $existingSections->sortBy('name');

    $assignedReports = ($application?->reports ?? collect())->sortBy('name');
    $reportSectionMap = collect(old('report_sections', $assignedReports->mapWithKeys(fn ($report) => [
        $report->id => optional($report->pivot)->section_id,
    ])->all()));
@endphp

<x-ui-card title="{{ __('applications.cards.details_title') }}" icon="fas fa-layer-group text-primary" theme="lightblue" theme-mode="outline">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.name') }}">
                {{ __('applications.fields.name') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <x-ui-input name="name" value="{{ old('name', $application->name ?? null) }}" required />
        </div>
        <div class="col-md-6">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.slug') }}">
                {{ __('applications.fields.slug') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <x-ui-input name="slug" value="{{ old('slug', $application->slug ?? null) }}" placeholder="{{ __('applications.placeholders.slug') }}" />
        </div>
        <div class="col-12">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.description') }}">
                {{ __('applications.fields.description') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <x-ui-textarea name="description" rows="3">{{ old('description', $application->description ?? null) }}</x-ui-textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.display_order') }}">
                {{ __('applications.fields.display_order') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <x-ui-input name="display_order" type="number" min="0" value="{{ old('display_order', $application->display_order ?? null) }}" />
        </div>
        <div class="col-md-4 d-flex align-items-center mt-4">
            <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.is_active') }}">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $application->is_active ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{ __('applications.fields.is_active') }}</label>
            </div>
        </div>
    </div>
</x-ui-card>

<x-ui-card title="{{ __('applications.cards.assignments_title') }}" icon="fas fa-users text-primary" theme="lightblue" theme-mode="outline">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.users') }}">
                {{ __('applications.fields.users') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <select class="form-select" name="users[]" multiple size="8">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, $selectedUsers, true) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.groups') }}">
                {{ __('applications.fields.groups') }}
                <i class="fas fa-question-circle ms-1 text-muted"></i>
            </label>
            <select class="form-select" name="groups[]" multiple size="8">
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}" {{ in_array($group->id, $selectedGroups, true) ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</x-ui-card>

<x-ui-card title="{{ __('applications.cards.reports_title') }}" icon="fas fa-chart-bar text-primary" theme="lightblue" theme-mode="outline">
    <label class="form-label d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.reports') }}">
        {{ __('applications.fields.reports') }}
        <i class="fas fa-question-circle ms-1 text-muted"></i>
    </label>
    <select class="form-select" name="reports[]" multiple size="12">
        @foreach ($reports as $report)
            <option value="{{ $report->id }}" {{ in_array($report->id, $selectedReports, true) ? 'selected' : '' }}>
                {{ $report->name }}
            </option>
        @endforeach
    </select>
</x-ui-card>

@if ($application && $application->exists && $assignedReports->isNotEmpty())
    <x-ui-card title="{{ __('applications.cards.report_sections_title') }}" icon="fas fa-stream text-primary" theme="lightblue" theme-mode="outline">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>{{ __('applications.fields.report_name') }}</th>
                        <th>{{ __('applications.fields.section_name') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignedReports as $report)
                        <tr>
                            <td>{{ $report->name }}</td>
                            <td>
                                <select name="report_sections[{{ $report->id }}]" class="form-select" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.report_sections') }}">
                                    <option value="">{{ __('applications.sections.none') }}</option>
                                    @foreach ($existingSectionsSorted as $section)
                                        <option value="{{ $section->id }}" {{ (int) $reportSectionMap->get($report->id) === (int) $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui-card>
@endif

<x-ui-card title="{{ __('applications.cards.sections_title') }}" icon="fas fa-sitemap text-primary" theme="lightblue" theme-mode="outline">
    @if ($existingSectionsSorted->isNotEmpty())
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>{{ __('applications.fields.section_name') }}</th>
                        <th>{{ __('applications.fields.section_slug') }}</th>
                        <th class="text-center">{{ __('applications.fields.section_delete') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($existingSectionsSorted as $section)
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="sections_existing[{{ $section->id }}]" value="{{ old('sections_existing.'.$section->id, $section->name) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.section_name') }}">
                            </td>
                            <td class="align-middle"><code>{{ $section->slug }}</code></td>
                            <td class="text-center">
                                <input type="checkbox" name="sections_remove[]" value="{{ $section->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.section_delete') }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="mb-2 fw-semibold" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.sections') }}">{{ __('applications.fields.new_sections') }}</div>
    <div id="new-sections-container" class="d-flex flex-column gap-2">
        @foreach (old('sections', []) as $value)
            <input type="text" class="form-control" name="sections[]" value="{{ $value }}" placeholder="{{ __('applications.placeholders.section_name') }}">
        @endforeach
        <input type="text" class="form-control" name="sections[]" placeholder="{{ __('applications.placeholders.section_name') }}">
    </div>
    <button class="btn btn-sm btn-outline-primary mt-3" type="button" id="add-section-input" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('applications.tooltips.add_section') }}">
        <i class="fas fa-plus"></i> {{ __('applications.actions.add_section') }}
    </button>
</x-ui-card>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.bootstrap && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                bootstrap.Tooltip.getInstance(tooltipTriggerEl) || new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        var addButton = document.getElementById('add-section-input');
        var container = document.getElementById('new-sections-container');
        if (!addButton || !container) {
            return;
        }

        addButton.addEventListener('click', function () {
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'sections[]';
            input.className = 'form-control';
            input.placeholder = '{{ __('applications.placeholders.section_name') }}';
            container.appendChild(input);
        });
    });
</script>
@endpush
