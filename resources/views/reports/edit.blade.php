@extends('adminlte::page')

@include('portal.partials.report-card-styles')

@section('title', __('reports.edit.title'))

@section('content_header')
    {{-- Unified header with title, icon, and aligned actions --}}
    <x-page-header :title="__('reports.edit.title')" icon="fas fa-edit">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('common.back') }}</span>
            </a>
            <a href="{{ route('reports.show', [$report->workspace_id, $report->id]) }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-chart-line"></i>
                <span>{{ __('reports.edit.view_report') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="{{ __('reports.edit.alerts.success_title') }}" dismissable class="mb-2">
            {{ session('status') }}
        </x-adminlte-alert>
    @endif

    @if($errors->any())
        <x-adminlte-alert theme="danger" title="{{ __('reports.edit.alerts.errors_title') }}" dismissable class="mb-2">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ __('reports.edit.card_title') }}" icon="fas fa-edit text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-3">

        <form method="post" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Read-only base data --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.title') }}</label>
                    <input type="text" class="form-control" value="{{ $report->name }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.workspace_id') }}</label>
                    <input type="text" class="form-control" value="{{ $report->workspace_id }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.report_id') }}</label>
                    <input type="text" class="form-control" value="{{ $report->id }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            {{-- Preview --}}
            <div class="mb-4">
                <label class="font-weight-bold">{{ __('reports.edit.form.preview.label') }}</label>
                @php
                    $scaleSource  = old('cover_scale', $report->cover_scale ?? 1);
                    $scaleNumeric = is_numeric($scaleSource) ? (float) $scaleSource : 1.0;
                    if ($scaleNumeric < 0.5 || $scaleNumeric > 1.5) {
                        $percent     = (int) round($scaleNumeric);
                        $percent     = $percent < 50 ? 50 : ($percent > 150 ? 150 : $percent);
                        $storedScale = $percent / 100;
                    } else {
                        $storedScale = $scaleNumeric;
                    }
                    $initialScale = (int) round($storedScale * 100);
                    $initialScale = $initialScale < 50 ? 50 : ($initialScale > 150 ? 150 : $initialScale);
                    $scaleFactor  = $storedScale;
                    $cover        = trim((string)($report->effective_cover_url ?? ''));
                    $isDefaultCover = $cover !== '' && \Illuminate\Support\Str::contains($cover, 'report-covers/default');
                @endphp
                <div class="card card-outline card-secondary report-preview-card">
                    <div class="card-header py-2">
                        <h3 class="card-title mb-0">{{ __('reports.edit.preview.card_title') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="report-card report-preview-tile shadow-none border">
                            <div id="previewBox" class="report-cover bg-light">
                                @if($cover !== '')
                                    <img id="previewImg"
                                         src="{{ $cover }}"
                                         alt="{{ __('reports.index.cards.cover_alt', ['name' => $report->name]) }}"
                                         class="cover-scale-target {{ $isDefaultCover ? 'cover-default' : 'cover-full' }}"
                                         style="transform: scale({{ number_format($scaleFactor, 3, '.', '') }});">
                                @else
                                    <div id="previewFallback" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-chart-bar fa-3x text-warning opacity-50"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="px-3 pt-3 pb-2 text-center">
                                <h3 class="h6 font-weight-bold text-dark mb-0 text-truncate" title="{{ $report->name }}">
                                    {{ $report->name }}
                                </h3>
                                @if(!empty($report->workspace?->name))
                                    <span class="badge badge-light border mt-2 d-inline-flex align-items-center justify-content-center px-2 py-1">
                                        <i class="fas fa-layer-group mr-1"></i>{{ $report->workspace->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div id="previewControls" class="report-preview-controls {{ $cover !== '' ? '' : 'd-none' }}">
                            <label for="cover_scale_slider" class="font-weight-bold mb-2">{{ __('reports.edit.preview.scale_label') }}</label>
                            <input type="range"
                                   class="custom-range"
                                   id="cover_scale_slider"
                                   min="50"
                                   max="150"
                                   step="1"
                                   value="{{ $initialScale }}">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small id="cover_scale_value"
                                       class="text-muted font-weight-bold"
                                       data-template="{{ __('reports.edit.preview.scale_display', ['value' => ':value']) }}">
                                    {{ __('reports.edit.preview.scale_display', ['value' => $initialScale]) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="cover_scale" id="cover_scale" value="{{ number_format($storedScale, 4, '.', '') }}">
                <small class="text-muted d-block mt-2">{{ __('reports.edit.form.preview.hint') }}</small>
            </div>

            {{-- Upload input --}}
            <div class="mb-3">
                <label for="cover_file" class="font-weight-bold">{{ __('reports.edit.form.upload.label') }}</label>
                <div class="d-flex align-items-center flex-wrap custom-upload">
                    <input type="file" name="cover_file" id="cover_file"
                           class="@error('cover_file') is-invalid @enderror sr-only"
                           accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                    <button type="button" id="cover_file_trigger"
                            class="btn btn-outline-secondary mr-2 mb-2 mb-md-0">
                        <i class="fas fa-folder-open mr-1"></i>{{ __('reports.edit.form.upload.button') }}
                    </button>
                    <span id="cover_file_status"
                          class="text-muted small"
                          aria-live="polite"
                          data-status-empty="{{ __('reports.edit.form.upload.status.empty') }}"
                          data-status-selected="{{ __('reports.edit.form.upload.status.selected') }}">
                        {{ __('reports.edit.form.upload.status.empty') }}
                    </span>
                </div>
                @error('cover_file')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <small class="form-text text-muted">{{ __('reports.edit.form.upload.hint') }}</small>
                @enderror
            </div>

            {{-- Default checkbox --}}
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default"
                       {{ old('make_default') ? 'checked' : '' }}>
                <label class="form-check-label" for="make_default">
                    {{ __('reports.edit.form.make_default.label') }}
                </label>
                <small class="form-text text-muted">{{ __('reports.edit.form.make_default.hint') }}</small>
            </div>

            {{-- Alternative URL --}}
            <div class="mb-3">
                <label for="cover_url" class="font-weight-bold">{{ __('reports.edit.form.url.label') }}</label>
                <input type="url" name="cover_url" id="cover_url"
                       class="form-control @error('cover_url') is-invalid @enderror"
                       placeholder="{{ __('reports.edit.form.url.placeholder') }}"
                       value="{{ old('cover_url', $report->cover_url) }}">
                @error('cover_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <small class="form-text text-muted">{{ __('reports.edit.form.url.hint') }}</small>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-1 d-flex justify-content-between align-items-center">
                <label for="description" class="font-weight-bold mb-0">{{ __('reports.edit.form.description.label') }}</label>
                <small class="text-muted"><span id="descCount">0</span>/2000</small>
            </div>
            <div class="mb-3">
                <textarea name="description" id="description" rows="4" maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="{{ __('reports.edit.form.description.placeholder') }}">{{ old('description', $report->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Extra switch --}}
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="allow_resume_outside_window" name="allow_resume_outside_window" value="1"
                       {{ old('allow_resume_outside_window', $report->allow_resume_outside_window ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_resume_outside_window">
                    {{ __('reports.edit.form.allow_resume.label') }}
                </label>
                <small class="form-text text-muted">{{ __('reports.edit.form.allow_resume.hint') }}</small>
            </div>

            <div class="d-flex justify-content-between flex-wrap mt-4">
                <button type="submit" class="btn btn-primary btn-eq">
                    <i class="fas fa-save mr-1"></i>{{ __('reports.edit.buttons.save') }}
                </button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-eq">
                    <i class="fas fa-times mr-1"></i>{{ __('reports.edit.buttons.cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

@push('css')
<style>
/* Header buttons */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
}
.header-actions a.btn,
.header-actions button.btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 38px !important;
    line-height: 1 !important;
    padding: 0 .9rem !important;
    font-size: .875rem !important;
}
.header-actions i { font-size: .9rem; margin-right: .4rem; }

/* Form adjustments */
.form-control {
    height: calc(1.9rem + 2px);
    font-size: .875rem;
}
.btn-eq {
    min-width: 140px;
    height: calc(1.9rem + 2px);
    font-size: .875rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.btn-eq i { font-size: .9rem; margin-right: .3rem; }

/* Preview */
.report-preview-card { border-color: #d2d6de; border-radius: .5rem; }
.report-preview-card .card-header { border-bottom: 1px solid #e1e6eb; border-top-left-radius: .5rem; border-top-right-radius: .5rem; }
.report-preview-tile { border-style: dashed; border-color: #ced4da; border-radius: .75rem; }
.report-preview-tile .report-cover { min-height: var(--cover-height, 170px); }
.report-preview-tile .cover-scale-target { transition: transform .2s ease-in-out; transform-origin: center center; }
.report-preview-controls { margin-top: 1.5rem; }
.report-preview-controls .custom-range { height: 1.1rem; }

/* Responsive buttons */
@media (max-width: 480px) {
    .btn-eq { flex: 1 1 auto; margin-top: .5rem; }
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput   = document.getElementById('cover_file');
    const fileTrigger = document.getElementById('cover_file_trigger');
    const fileStatus  = document.getElementById('cover_file_status');
    const urlInput    = document.getElementById('cover_url');
    const descInput   = document.getElementById('description');
    const descCount   = document.getElementById('descCount');
    const previewBox  = document.getElementById('previewBox');
    const previewControls = document.getElementById('previewControls');
    const scaleSlider = document.getElementById('cover_scale_slider');
    const scaleInput  = document.getElementById('cover_scale');
    const scaleValue  = document.getElementById('cover_scale_value');
    let previewImg    = document.getElementById('previewImg');
    let previewFallback = document.getElementById('previewFallback');
    const statusEmpty    = fileStatus?.dataset.statusEmpty || '';
    const statusSelected = fileStatus?.dataset.statusSelected || '';
    const scaleTemplate      = scaleValue?.dataset.template || 'Current size: :value%';
    const scaleMinPercent    = scaleSlider ? Number(scaleSlider.min || 50) : 50;
    const scaleMaxPercent    = scaleSlider ? Number(scaleSlider.max || 150) : 150;
    const defaultScaleFactor = scaleInput ? Number(scaleInput.value || 1) : 1;
    const defaultPercent     = Math.round(defaultScaleFactor * 100);

    function updateDescCount() {
        const len = (descInput?.value || '').length;
        if (descCount) descCount.textContent = String(len);
    }
    if (descInput) {
        updateDescCount();
        descInput.addEventListener('input', updateDescCount, {passive:true});
    }

    if (previewImg) {
        previewImg.dataset.defaultClass = previewImg.classList.contains('cover-default') ? 'cover-default' : 'cover-full';
    }

    function clampScale(value) {
        const numeric = Number(value) || defaultPercent || 100;
        return Math.min(scaleMaxPercent, Math.max(scaleMinPercent, numeric));
    }

    function togglePreviewControls(visible) {
        if (!previewControls) return;
        previewControls.classList.toggle('d-none', !visible);
    }

    function updateScaleDisplay(value, options = {}) {
        const { syncInput = true, syncSlider = true } = options;
        const normalized = clampScale(value);
        if (scaleValue) {
            scaleValue.textContent = scaleTemplate.replace(':value', String(normalized));
        }
        if (scaleInput && syncInput) {
            scaleInput.value = (normalized / 100).toFixed(4);
        }
        if (scaleSlider && syncSlider) {
            scaleSlider.value = normalized;
        }
        if (previewImg) {
            previewImg.style.transform = `scale(${normalized / 100})`;
        }
    }

    function showImagePreview(src, options = {}) {
        const { imageClass } = options;
        if (!previewBox) return;
        if (!previewImg) {
            previewImg = document.createElement('img');
            previewImg.id = 'previewImg';
            previewBox.innerHTML = '';
            previewBox.appendChild(previewImg);
        }
        const desiredClass = imageClass || previewImg.dataset.defaultClass || (src.includes('report-covers/default') ? 'cover-default' : 'cover-full');
        previewImg.dataset.defaultClass = desiredClass;
        previewImg.className = `cover-scale-target ${desiredClass}`;
        previewImg.src = src;
        previewFallback = null;
        togglePreviewControls(true);
        const currentPercent = scaleInput ? Number(scaleInput.value) * 100 : defaultPercent;
        updateScaleDisplay(currentPercent, { syncSlider: true, syncInput: true });
    }

    function showFallbackIcon() {
        if (!previewBox) return;
        previewBox.innerHTML = `
            <div id="previewFallback" class="w-100 h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-chart-bar fa-3x text-warning opacity-50"></i>
            </div>`;
        previewImg = null;
        previewFallback = document.getElementById('previewFallback');
        togglePreviewControls(false);
    }

    if (fileTrigger && fileInput) {
        fileTrigger.addEventListener('click', () => fileInput.click());
    }

    if (fileInput) {
        if (fileStatus && statusEmpty) fileStatus.textContent = statusEmpty;

        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (fileStatus) {
                fileStatus.textContent = file
                    ? statusSelected.replace(':name', file.name)
                    : statusEmpty;
            }
            if (file) {
                if (urlInput) urlInput.value = '';
                const reader = new FileReader();
                reader.onload = e => showImagePreview(e.target.result, { imageClass: 'cover-full' });
                reader.readAsDataURL(file);
            } else {
                const url = (urlInput?.value || '').trim();
                if (url) showImagePreview(url, { imageClass: url.includes('report-covers/default') ? 'cover-default' : 'cover-full' });
                else showFallbackIcon();
            }
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', function () {
            const val = (this.value || '').trim();
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                if (val) {
                    try {
                        const u = new URL(val);
                        const isDefault = u.href.includes('report-covers/default');
                        showImagePreview(u.href, { imageClass: isDefault ? 'cover-default' : 'cover-full' });
                    } catch (_) {
                        showFallbackIcon();
                    }
                } else {
                    showFallbackIcon();
                }
            }
        }, {passive:true});
    }

    if (scaleSlider) {
        scaleSlider.addEventListener('input', function () {
            updateScaleDisplay(this.value, { syncInput: true, syncSlider: false });
        }, {passive:true});
        scaleSlider.addEventListener('change', function () {
            updateScaleDisplay(this.value, { syncInput: true, syncSlider: false });
        }, {passive:true});
    }

    if (previewImg) {
        previewImg.classList.add('cover-scale-target');
        togglePreviewControls(true);
        const initialPercent = scaleInput ? Number(scaleInput.value) * 100 : defaultPercent;
        updateScaleDisplay(initialPercent, { syncSlider: true, syncInput: true });
    } else {
        togglePreviewControls(false);
    }
});
</script>
@endpush
