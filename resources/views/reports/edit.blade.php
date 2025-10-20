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
                    $sliderMin = 50;
                    $sliderMax = 150;
                    $initialSlider = (int) round($storedScale * 100);
                    $initialSlider = $initialSlider < $sliderMin ? $sliderMin : ($initialSlider > $sliderMax ? $sliderMax : $initialSlider);
                    $scaleFactor  = $storedScale;
                    $cover        = trim((string)($report->effective_cover_url ?? ''));
                    $isDefaultCover = $cover !== '' && \Illuminate\Support\Str::contains($cover, 'report-covers/default');
                @endphp
                <div class="report-preview-card">
                    <div class="report-preview-header text-center">
                        <span class="report-preview-icon mr-2"><i class="fas fa-chart-area"></i></span>
                        <span class="report-preview-title">{{ __('reports.edit.preview.card_title') }}</span>
                    </div>
                    <div class="report-preview-body">
                        <div class="preview-card-wrapper">
                            <div class="report-card preview-card-tile shadow-sm mb-4 mx-auto">
                                <div id="previewBox" class="report-cover">
                                    <div class="cover-link preview-cover-link d-flex align-items-center justify-content-center w-100 h-100">
                                        @if($cover !== '')
                                            <img id="previewImg"
                                                 src="{{ $cover }}"
                                                 alt="{{ __('reports.index.cards.cover_alt', ['name' => $report->name]) }}"
                                                 class="cover-scale-target {{ $isDefaultCover ? 'cover-default' : 'cover-full' }}"
                                                 style="transform: scale({{ number_format($scaleFactor, 3, '.', '') }}, {{ number_format($scaleFactor, 3, '.', '') }});">
                                        @else
                                            <div id="previewFallback" class="preview-fallback w-100 h-100 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-chart-bar fa-3x text-warning opacity-50"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="px-3 pt-3 pb-3 text-center">
                                    <h3 class="h6 font-weight-bold text-dark mb-2 text-truncate" title="{{ $report->name }}">
                                        {{ $report->name }}
                                    </h3>
                                    <div class="d-flex justify-content-center flex-wrap align-items-center">
                                        <button type="button"
                                                class="btn btn-sm btn-primary mx-1 preview-action-btn"
                                                title="{{ __('reports.index.cards.open_title') }}">
                                            <i class="fas fa-chart-line mr-1"></i>{{ __('reports.index.actions.open') }}
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-warning mx-1 preview-action-btn"
                                                title="{{ __('reports.index.cards.edit_title') }}">
                                            <i class="fas fa-edit mr-1"></i>{{ __('reports.index.actions.edit') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="previewControls" class="preview-control-card report-preview-controls {{ $cover !== '' ? '' : 'd-none' }}">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="cover_scale_slider" class="font-weight-bold mb-0">{{ __('reports.edit.preview.width_label') }}</label>
                                        <small id="cover_scale_value"
                                               class="text-muted font-weight-bold"
                                               data-template="{{ __('reports.edit.preview.width_display', ['value' => ':value']) }}">
                                            {{ __('reports.edit.preview.width_display', ['value' => $initialSlider]) }}
                                        </small>
                                    </div>
                                    <input type="range"
                                           class="custom-range preview-range"
                                           id="cover_scale_slider"
                                           min="{{ $sliderMin }}"
                                           max="{{ $sliderMax }}"
                                           step="1"
                                           value="{{ $initialSlider }}">
                                </div>
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="cover_scale_slider_y" class="font-weight-bold mb-0">{{ __('reports.edit.preview.height_label') }}</label>
                                        <small id="cover_scale_value_y"
                                               class="text-muted font-weight-bold"
                                               data-template="{{ __('reports.edit.preview.height_display', ['value' => ':value']) }}">
                                            {{ __('reports.edit.preview.height_display', ['value' => $initialSlider]) }}
                                        </small>
                                    </div>
                                    <input type="range"
                                           class="custom-range preview-range"
                                           id="cover_scale_slider_y"
                                           min="{{ $sliderMin }}"
                                           max="{{ $sliderMax }}"
                                           step="1"
                                           value="{{ $initialSlider }}"
                                           disabled>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="cover_scale_lock" checked>
                                    <label class="custom-control-label font-weight-bold" for="cover_scale_lock">{{ __('reports.edit.preview.lock_label') }}</label>
                                </div>
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
.report-preview-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    box-shadow: 0 .25rem .5rem rgba(0,0,0,.05);
    overflow: hidden;
}
.report-preview-header {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem 1rem .75rem;
    border-bottom: 1px solid rgba(0,0,0,.05);
}
.report-preview-icon { color: #007bff; font-size: 1.25rem; line-height: 1; }
.report-preview-title { font-weight: 600; color: #1f2d3d; font-size: 1.05rem; }
.report-preview-body {
    padding: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}
.preview-card-wrapper {
    max-width: 340px;
    width: 100%;
    margin: 0 auto;
}
.preview-card-wrapper .report-card {
    width: 100%;
    max-width: 340px;
    margin: 0 auto;
}
.preview-card-tile {
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    box-shadow: 0 .25rem .5rem rgba(0,0,0,.05) !important;
    position: relative;
    background: #fff;
}
.preview-fallback { color: #ced4da; }
.preview-action-btn {
    height: 38px;
    padding: 0 .9rem;
    font-size: .875rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.preview-action-btn i { font-size: .875rem; }
.report-preview-controls .form-group { margin-bottom: 1.25rem; }
.preview-range { height: 1.1rem; }
.report-preview-controls small { font-size: .8rem; }
.preview-control-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    box-shadow: 0 .25rem .5rem rgba(0,0,0,.05);
    padding: 1.25rem;
    margin: 1.5rem auto 0;
    max-width: 340px;
}
.preview-control-card .form-group:last-child { margin-bottom: 0; }
.preview-card-wrapper .preview-control-card { width: 100%; }
.preview-cover-link {
    width: 100%;
    height: 100%;
    border-radius: .5rem;
    overflow: hidden;
}
.preview-cover-link .preview-fallback {
    border-radius: .5rem;
}

/* Responsive buttons */
@media (max-width: 480px) {
    .btn-eq { flex: 1 1 auto; margin-top: .5rem; }
}
@media (max-width: 576px) {
    .preview-action-btn { flex: 1 1 auto; margin-top: .5rem; }
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
    const widthSlider  = document.getElementById('cover_scale_slider');
    const heightSlider = document.getElementById('cover_scale_slider_y');
    const lockToggle   = document.getElementById('cover_scale_lock');
    const scaleInput   = document.getElementById('cover_scale');
    const widthLabel   = document.getElementById('cover_scale_value');
    const heightLabel  = document.getElementById('cover_scale_value_y');
    let previewImg     = document.getElementById('previewImg');
    const statusEmpty    = fileStatus?.dataset.statusEmpty || '';
    const statusSelected = fileStatus?.dataset.statusSelected || '';
    const sliderMin = widthSlider ? Number(widthSlider.min || 50) : 50;
    const sliderMax = widthSlider ? Number(widthSlider.max || 150) : 150;
    const scaleMin  = sliderMin / 100;
    const scaleMax  = sliderMax / 100;

    function updateDescCount() {
        const len = (descInput?.value || '').length;
        if (descCount) descCount.textContent = String(len);
    }
    if (descInput) {
        updateDescCount();
        descInput.addEventListener('input', updateDescCount, {passive:true});
    }

    function clamp(value, min, max, fallback = min) {
        const num = Number(value);
        if (Number.isNaN(num)) return fallback;
        return Math.min(max, Math.max(min, num));
    }

    function scaleFromSlider(sliderValue) {
        const numeric = clamp(sliderValue, sliderMin, sliderMax, sliderMin);
        return numeric / 100;
    }

    function sliderFromScale(scaleValue) {
        const clamped = clamp(scaleValue, scaleMin, scaleMax, scaleMin);
        return Math.round(clamped * 100);
    }

    function togglePreviewControls(visible) {
        if (!previewControls) return;
        previewControls.classList.toggle('d-none', !visible);
    }

    function ensurePreviewContainer() {
        if (!previewBox) return null;
        let container = previewBox.querySelector('.preview-cover-link');
        if (!container) {
            container = document.createElement('div');
            container.className = 'cover-link preview-cover-link d-flex align-items-center justify-content-center w-100 h-100';
            previewBox.innerHTML = '';
            previewBox.appendChild(container);
        }
        return container;
    }

    function updateLabels(widthValue, heightValue) {
        if (widthLabel) {
            const template = widthLabel.dataset.template || 'Width: :value %';
            widthLabel.textContent = template.replace(':value', String(widthValue));
        }
        if (heightLabel) {
            const template = heightLabel.dataset.template || 'Height: :value %';
            heightLabel.textContent = template.replace(':value', String(heightValue));
        }
    }

    function applyScale() {
        if (!widthSlider || !heightSlider) return;
        const widthValue  = clamp(widthSlider.value, sliderMin, sliderMax, sliderMin);
        const heightValue = clamp(heightSlider.value, sliderMin, sliderMax, sliderMin);
        widthSlider.value  = widthValue;
        heightSlider.value = heightValue;

        const widthScale  = scaleFromSlider(widthValue);
        const heightScale = scaleFromSlider(heightValue);

        if (previewImg) {
            previewImg.style.transform = `scale(${widthScale}, ${heightScale})`;
        }
        if (scaleInput) {
            scaleInput.value = widthScale.toFixed(4);
        }
        updateLabels(widthValue, heightValue);
    }

    function syncHeightToWidth() {
        if (!widthSlider || !heightSlider) return;
        heightSlider.value = widthSlider.value;
        heightSlider.setAttribute('disabled', 'disabled');
        applyScale();
    }

    function unlockHeightSlider() {
        if (!heightSlider) return;
        heightSlider.removeAttribute('disabled');
        applyScale();
    }

    function showImagePreview(src, options = {}) {
        const { imageClass } = options;
        if (!previewBox) return;
        const container = ensurePreviewContainer();
        if (!container) return;
        container.innerHTML = '';

        previewImg = document.createElement('img');
        previewImg.id = 'previewImg';
        const coverClass = imageClass || (typeof src === 'string' && src.includes('report-covers/default') ? 'cover-default' : 'cover-full');
        previewImg.className = `cover-scale-target ${coverClass}`.trim();
        previewImg.src = src;
        container.appendChild(previewImg);

        togglePreviewControls(true);
        applyScale();
    }

    function showFallbackIcon() {
        if (!previewBox) return;
        const container = ensurePreviewContainer();
        if (!container) return;
        container.innerHTML = `
            <div id="previewFallback" class="preview-fallback w-100 h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-chart-bar fa-3x text-warning opacity-50"></i>
            </div>`;
        previewImg = null;
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

    if (widthSlider) {
        widthSlider.addEventListener('input', function () {
            if (lockToggle?.checked && heightSlider) {
                heightSlider.value = widthSlider.value;
            }
            applyScale();
        }, {passive:true});
    }

    if (heightSlider) {
        heightSlider.addEventListener('input', function () {
            if (lockToggle?.checked && widthSlider) {
                widthSlider.value = heightSlider.value;
            }
            applyScale();
        }, {passive:true});
    }

    if (lockToggle) {
        lockToggle.addEventListener('change', function () {
            if (this.checked) {
                syncHeightToWidth();
            } else {
                unlockHeightSlider();
            }
        });
    }

    if (scaleInput && widthSlider) {
        const storedScale = clamp(scaleInput.value, scaleMin, scaleMax, 1);
        const initialSlider = sliderFromScale(storedScale);
        widthSlider.value = initialSlider;
        if (heightSlider) heightSlider.value = initialSlider;
    }

    if (lockToggle?.checked) {
        syncHeightToWidth();
    } else {
        unlockHeightSlider();
    }

    if (previewImg) {
        togglePreviewControls(true);
        applyScale();
    } else {
        togglePreviewControls(false);
    }
});
</script>
@endpush
