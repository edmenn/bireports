@extends('adminlte::page')

@section('title', __('reports.edit.title'))

@section('content_header')
    {{-- Header: título a la izquierda, acciones a la derecha --}}
    <div class="d-flex align-items-center justify-content-between w-100 mb-3" style="flex-wrap: nowrap;">
        <div class="flex-grow-1 text-left">
            <h1 class="m-0 font-weight-bold text-primary">{{ __('reports.edit.title') }}</h1>
        </div>
        <div class="text-right ml-auto d-flex flex-nowrap">
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mx-1">
                <i class="fas fa-arrow-left mr-1"></i> {{ __("common.back") }}
            </a>
            <a href="{{ route('reports.show', [$report->workspace_id, $report->id]) }}" class="btn btn-sm btn-primary mx-1">
                <i class="fas fa-chart-line mr-1"></i> {{ __('reports.edit.view_report') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('reports.edit.alerts.close') }}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('reports.edit.alerts.errors_title') }}</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('reports.edit.alerts.close') }}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form method="post"
          action="{{ route('reports.update', $report) }}"
          enctype="multipart/form-data"
          class="card shadow-sm border-0">
        @csrf
        @method('PUT')

        <div class="card-body">
            {{-- Datos base (solo lectura) --}}
            <div class="row">
                <div class="col-md-6">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.title') }}</label>
                    <input type="text" class="form-control" value="{{ $report->name }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.workspace_id') }}</label>
                    <input type="text" class="form-control" value="{{ $report->workspace_id }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">{{ __('reports.edit.form.read_only.report_id') }}</label>
                    <input type="text" class="form-control" value="{{ $report->id }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            {{-- Preview solo visual --}}
            <div class="mb-4">
                <label class="font-weight-bold">{{ __('reports.edit.form.preview.label') }}</label>
                <div id="previewBox"
                     class="border rounded p-3 d-flex align-items-center justify-content-center bg-light"
                     style="min-height:160px;">
                    @php $cover = trim((string)($report->effective_cover_url ?? '')); @endphp
                    @if($cover !== '')
                        <img id="previewImg"
                             src="{{ $cover }}"
                             alt="{{ __('reports.index.cards.cover_alt', ['name' => $report->name]) }}"
                             style="max-width:100%; max-height:150px; object-fit:cover;">
                    @else
                        <svg id="previewSvg" style="width:70px; height:auto;" viewBox="0 0 128 128" aria-hidden="true">
                            <defs>
                                <linearGradient id="biYellow" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#FFD93D"/>
                                    <stop offset="100%" stop-color="#FFC107"/>
                                </linearGradient>
                            </defs>
                            <rect x="8" y="28" width="112" height="80" rx="12" fill="url(#biYellow)"/>
                            <rect x="8" y="28" width="112" height="80" rx="12" fill="#000" opacity=".05"/>
                            <rect x="8" y="18" width="56" height="20" rx="6" fill="url(#biYellow)"/>
                            <rect x="8" y="18" width="56" height="20" rx="6" fill="#000" opacity=".05"/>
                            <g transform="translate(28,50)" fill="#fff">
                                <rect x="0"   y="30" width="12" height="28" rx="2"/>
                                <rect x="20"  y="18" width="12" height="40" rx="2"/>
                                <rect x="40"  y="10" width="12" height="48" rx="2"/>
                                <rect x="60"  y="4"  width="12" height="54" rx="2"/>
                            </g>
                        </svg>
                    @endif
                </div>
                <small class="text-muted d-block mt-2">{{ __('reports.edit.form.preview.hint') }}</small>
            </div>

            {{-- Input de archivo debajo del cuadro --}}
            <div class="mb-3">
                <label for="cover_file" class="font-weight-bold">{{ __('reports.edit.form.upload.label') }}</label>
                <div class="d-flex align-items-center flex-wrap custom-upload">
                    <input type="file"
                           name="cover_file"
                           id="cover_file"
                           class="@error('cover_file') is-invalid @enderror sr-only"
                           accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                    <button type="button"
                            id="cover_file_trigger"
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

            {{-- Checkbox predeterminado --}}
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default"
                       {{ old('make_default') ? 'checked' : '' }}>
                <label class="form-check-label" for="make_default">
                    {{ __('reports.edit.form.make_default.label') }}
                </label>
                <small class="form-text text-muted">{{ __('reports.edit.form.make_default.hint') }}</small>
            </div>

            {{-- URL alternativa --}}
            <div class="mb-3">
                <label for="cover_url" class="font-weight-bold">{{ __('reports.edit.form.url.label') }}</label>
                <input type="url"
                       name="cover_url"
                       id="cover_url"
                       class="form-control @error('cover_url') is-invalid @enderror"
                       placeholder="{{ __('reports.edit.form.url.placeholder') }}"
                       value="{{ old('cover_url', $report->cover_url) }}">
                @error('cover_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <small class="form-text text-muted">{{ __('reports.edit.form.url.hint') }}</small>
                @enderror
            </div>

            {{-- Descripción --}}
            <div class="mb-1 d-flex justify-content-between align-items-center">
                <label for="description" class="font-weight-bold mb-0">{{ __('reports.edit.form.description.label') }}</label>
                <small class="text-muted"><span id="descCount">0</span>/2000</small>
            </div>
            <div class="mb-3">
                <textarea name="description"
                          id="description"
                          rows="4"
                          maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="{{ __('reports.edit.form.description.placeholder') }}">{{ old('description', $report->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Checkbox extra --}}
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="allow_resume_outside_window"
                       name="allow_resume_outside_window" value="1"
                       {{ old('allow_resume_outside_window', $report->allow_resume_outside_window ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_resume_outside_window">
                    {{ __('reports.edit.form.allow_resume.label') }}
                </label>
                <small class="form-text text-muted">{{ __('reports.edit.form.allow_resume.hint') }}</small>
            </div>

        </div>

        {{-- Footer: Save izquierda / Cancel derecha --}}
        <div class="card-footer d-flex justify-content-between flex-wrap">
            <button type="submit" class="btn btn-primary btn-eq">
                <i class="fas fa-save mr-1"></i> {{ __('reports.edit.buttons.save') }}
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-eq">
                <i class="fas fa-times mr-1"></i> {{ __('reports.edit.buttons.cancel') }}
            </a>
        </div>
    </form>
@stop

@push('css')
<style>
.btn-eq { min-width: 140px; }
#previewBox img, #previewBox svg { transition: all .2s ease-in-out; }
#previewBox:hover { background-color: #fff3cd; }
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
    let previewImg    = document.getElementById('previewImg');
    let previewSvg    = document.getElementById('previewSvg');
    const statusEmpty    = fileStatus?.dataset.statusEmpty || '';
    const statusSelected = fileStatus?.dataset.statusSelected || '';

    function updateDescCount() {
        const len = (descInput?.value || '').length;
        if (descCount) descCount.textContent = String(len);
    }
    if (descInput) {
        updateDescCount();
        descInput.addEventListener('input', updateDescCount, {passive:true});
    }

    function showImagePreview(src) {
        if (!previewImg) {
            previewImg = document.createElement('img');
            previewImg.id = 'previewImg';
            previewImg.style.maxWidth  = '100%';
            previewImg.style.maxHeight = '150px';
            previewImg.style.objectFit = 'cover';
            previewBox.innerHTML = '';
            previewBox.appendChild(previewImg);
        }
        previewImg.src = src;
    }

    function showFallbackSvg() {
        previewBox.innerHTML = `
            <svg id="previewSvg" style="width:70px; height:auto;" viewBox="0 0 128 128" aria-hidden="true">
                <defs>
                    <linearGradient id="biYellow" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#FFD93D"/>
                        <stop offset="100%" stop-color="#FFC107"/>
                    </linearGradient>
                </defs>
                <rect x="8" y="28" width="112" height="80" rx="12" fill="url(#biYellow)"/>
                <rect x="8" y="28" width="112" height="80" rx="12" fill="#000" opacity=".05"/>
                <rect x="8" y="18" width="56" height="20" rx="6" fill="url(#biYellow)"/>
                <rect x="8" y="18" width="56" height="20" rx="6" fill="#000" opacity=".05"/>
                <g transform="translate(28,50)" fill="#fff">
                    <rect x="0" y="30" width="12" height="28" rx="2"/>
                    <rect x="20" y="18" width="12" height="40" rx="2"/>
                    <rect x="40" y="10" width="12" height="48" rx="2"/>
                    <rect x="60" y="4"  width="12" height="54" rx="2"/>
                </g>
            </svg>`;
        previewImg = null;
    }

    if (fileTrigger && fileInput) {
        fileTrigger.addEventListener('click', () => fileInput.click());
    }

    if (fileInput) {
        if (fileStatus && statusEmpty) {
            fileStatus.textContent = statusEmpty;
        }

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
                reader.onload = e => showImagePreview(e.target.result);
                reader.readAsDataURL(file);
            } else {
                const url = (urlInput?.value || '').trim();
                if (url) showImagePreview(url);
                else showFallbackSvg();
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
                        showImagePreview(u.href);
                    } catch (_) {
                        showFallbackSvg();
                    }
                } else {
                    showFallbackSvg();
                }
            }
        }, {passive:true});
    }
});
</script>
@endpush
