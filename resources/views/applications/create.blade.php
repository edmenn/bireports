@extends('layouts.app')

@section('title', __('applications.create.title'))

{{-- =========================
     HEADER
     ========================= --}}
@section('content_header')
    <x-page-header :title="__('applications.create.title')" icon="fas fa-plug">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('applications.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('applications.actions.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

{{-- =========================
     CONTENT
     ========================= --}}
@section('content')
    @php
        $application = $application ?? null;
        $selectedUsers = collect(old('users', $application?->users?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $selectedGroups = collect(old('groups', $application?->groups?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $selectedReports = collect(old('reports', $application?->reports?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
        $existingSections = $application?->sections ?? collect();
        $existingSectionsSorted = $existingSections->sortBy('name');
        $assignedReports = ($application?->reports ?? collect())->sortBy('name');
        $reportSectionMap = collect(old('report_sections', $assignedReports->mapWithKeys(fn ($r) => [$r->id => optional($r->pivot)->section_id])->all()));
    @endphp

    <form action="{{ route('applications.store') }}" method="POST" novalidate>
        @csrf

        {{-- CARD 1: Application details --}}
        <x-ui-card title="{{ __('applications.cards.details_title') }}" icon="fas fa-plug text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name --}}
                <div class="col-md-6 mb-3">
                    <x-ui-input name="name"
                        label="{{ __('applications.fields.name') }}"
                        placeholder="{{ __('applications.placeholders.name') }}"
                        value="{{ old('name', $application->name ?? null) }}"
                        required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Slug --}}
                <div class="col-md-6 mb-3">
                    <x-ui-input name="slug"
                        label="{{ __('applications.fields.slug') }}"
                        placeholder="{{ __('applications.placeholders.slug') }}"
                        value="{{ old('slug', $application->slug ?? null) }}"
                        class="@error('slug') is-invalid @enderror"/>
                    @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div class="col-12 mb-3">
                    <x-ui-textarea name="description"
                        label="{{ __('applications.fields.description') }}"
                        rows="2"
                        placeholder="{{ __('applications.placeholders.description') }}"
                        class="@error('description') is-invalid @enderror">{{ old('description', $application->description ?? null) }}</x-ui-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Display order --}}
                <div class="col-md-4 mb-3">
                    <x-ui-input type="number" name="display_order" min="0"
                        label="{{ __('applications.fields.display_order') }}"
                        value="{{ old('display_order', $application->display_order ?? null) }}"
                        class="@error('display_order') is-invalid @enderror"/>
                    @error('display_order')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Active --}}
                <div class="col-md-4 d-flex align-items-center mb-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $application->is_active ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">{{ __('applications.fields.is_active') }}</label>
                    </div>
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 2: Assignments --}}
        <x-ui-card title="{{ __('applications.cards.assignments_title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">
                {{-- Users --}}
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('applications.fields.users') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse ($users as $user)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="user_{{ $user->id }}"
                                       name="users[]" value="{{ $user->id }}"
                                       @checked(in_array((int)$user->id, $selectedUsers, true))>
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('applications.hints.users') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Groups --}}
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('applications.fields.groups') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse ($groups as $group)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="group_{{ $group->id }}"
                                       name="groups[]" value="{{ $group->id }}"
                                       @checked(in_array((int)$group->id, $selectedGroups, true))>
                                <label class="custom-control-label" for="group_{{ $group->id }}">{{ $group->name }}</label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('applications.hints.groups') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Reports --}}
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('applications.fields.reports') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse ($reports as $report)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="report_{{ $report->id }}"
                                       name="reports[]" value="{{ $report->id }}"
                                       @checked(in_array((string)$report->id, $selectedReports, true))>
                                <label class="custom-control-label" for="report_{{ $report->id }}">{{ $report->name }}</label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('applications.hints.reports') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 3: Sections --}}
        <x-ui-card title="{{ __('applications.cards.sections_title') }}" icon="fas fa-sitemap text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            @if ($existingSectionsSorted->isNotEmpty())
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle mb-0">
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
                                        <input type="text" class="form-control"
                                               name="sections_existing[{{ $section->id }}]"
                                               value="{{ old('sections_existing.'.$section->id, $section->name) }}">
                                    </td>
                                    <td class="align-middle"><code>{{ $section->slug }}</code></td>
                                    <td class="text-center">
                                        <input type="checkbox" name="sections_remove[]" value="{{ $section->id }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mb-2 font-weight-bold">{{ __('applications.fields.new_sections') }}</div>
            @php($oldSections = old('sections', []))
            <div id="new-sections-container" class="d-flex flex-column">
                @forelse ($oldSections as $value)
                    <input type="text" class="form-control mb-2" name="sections[]" value="{{ $value }}"
                           placeholder="{{ __('applications.placeholders.section_name') }}">
                @empty
                    <input type="text" class="form-control mb-2" name="sections[]"
                           placeholder="{{ __('applications.placeholders.section_name') }}">
                @endforelse
            </div>
            <div class="d-flex flex-wrap align-items-center mt-2" style="gap: .5rem;">
                <button class="btn btn-sm btn-outline-primary" type="button" id="add-section-input">
                    <i class="fas fa-plus mr-1"></i> {{ __('applications.actions.add_section') }}
                </button>
                <button class="btn btn-sm btn-outline-danger" type="button" id="remove-section-input">
                    <i class="fas fa-minus mr-1"></i> {{ __('applications.actions.delete') }}
                </button>
            </div>
        </x-ui-card>

        {{-- CARD 4: Actions --}}
        <x-ui-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="form-actions w-100">
                    <x-ui-button type="submit" theme="primary"
                        label="{{ __('applications.actions.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <button type="button" class="btn btn-secondary btn-eq" data-cancel-button
                        data-message="{{ __('applications.confirm.cancel') }}"
                        data-fallback="{{ route('applications.index') }}">
                        <i class="fas fa-times"></i>
                        <span>{{ __('applications.actions.cancel') }}</span>
                    </button>
                </div>
            </div>
        </x-ui-card>
    </form>
@stop

@push('css')
<style>
.header-actions {
    display:flex; flex-wrap:wrap; align-items:center; justify-content:flex-end; gap:.5rem;
}
.header-btn {
    display:inline-flex; align-items:center; justify-content:center;
    height:38px; line-height:1; padding:0 .9rem; font-size:.875rem;
}
.header-btn i { font-size:.9rem; margin-right:.4rem; }

input.form-control, textarea.form-control, select.form-control {
    border:1px solid #ced4da!important; border-radius:.375rem!important;
    padding:.5rem .75rem!important; height:42px!important; box-shadow:none!important;
}
textarea.form-control { resize:none!important; }

.checkbox-list-container {
    max-height:300px; overflow-y:auto; background-color:#f8f9fa;
}
.custom-control { margin-bottom:.4rem; }
.custom-control-label { font-size:.9rem; }

.btn-eq {
    display:inline-flex; align-items:center; justify-content:center;
    height:38px!important; min-width:140px; font-size:.875rem; font-weight:600;
}
.btn-eq i { font-size:.9rem; margin-right:.4rem; }

#new-sections-container > .form-control:last-child { margin-bottom:0; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    if(window.jQuery && typeof jQuery.fn.tooltip==='function'){
        jQuery('[data-toggle="tooltip"]').tooltip();
    }
    const addButton=document.getElementById('add-section-input');
    const removeButton=document.getElementById('remove-section-input');
    const container=document.getElementById('new-sections-container');
    if(container){
        if(addButton){
            addButton.addEventListener('click',function(){
                const input=document.createElement('input');
                input.type='text';
                input.name='sections[]';
                input.className='form-control mb-2';
                input.placeholder='{{ __("applications.placeholders.section_name") }}';
                container.appendChild(input);
            });
        }
        if(removeButton){
            removeButton.addEventListener('click',function(){
                const inputs=container.querySelectorAll('input.form-control');
                if(inputs.length<=1){
                    if(inputs[0]){
                        inputs[0].value='';
                    }
                    return;
                }
                const last=inputs[inputs.length-1];
                last.parentElement.removeChild(last);
            });
        }
    }
    const cancelButtons=document.querySelectorAll('[data-cancel-button]');
    cancelButtons.forEach(function(button){
        button.addEventListener('click', function(){
            const message=button.getAttribute('data-message') || '';
            if(message === '' || confirm(message)){
                if(window.history.length > 1){
                    window.history.back();
                }else{
                    const fallback=button.getAttribute('data-fallback');
                    if(fallback){
                        window.location.href=fallback;
                    }
                }
            }
        });
    });
});
</script>
@endpush
