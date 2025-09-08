@extends('adminlte::page')

@section('title', 'Editar reporte')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="m-0">Editar reporte</h1>

        {{-- Acciones de header: ancho fijo + buen espaciado --}}
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-eq">
                <i class="fas fa-arrow-left me-1"></i> {{ __("common.back") }}
            </a>
            <a href="{{ route('reports.show', [$report->workspace_id, $report->id]) }}" class="btn btn-primary btn-eq">
                <i class="fas fa-chart-line me-1"></i> Ver reporte
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa los errores:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
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
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título</label>
                    <input type="text" class="form-control" value="{{ $report->name }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Workspace ID</label>
                    <input type="text" class="form-control" value="{{ $report->workspace_id }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report ID</label>
                    <input type="text" class="form-control" value="{{ $report->id }}" readonly>
                </div>
            </div>

            <hr class="my-4">

            {{-- Preview actual (usa effective_cover_url) --}}
            <div class="mb-3">
                <label class="form-label">Vista previa</label>
                <div id="previewBox"
                     class="border rounded p-3 d-flex align-items-center gap-3"
                     style="background:#fff8e1; min-height:140px;">
                    <div class="flex-shrink-0"
                         style="width: 220px; height: 130px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        @php $cover = trim((string)($report->effective_cover_url ?? '')); @endphp
                        @if($cover !== '')
                            <img id="previewImg"
                                 src="{{ $cover }}"
                                 alt="cover"
                                 style="max-width:100%; max-height:100%; object-fit:cover;">
                        @else
                            {{-- Fallback: ícono BI amarillo/blanco --}}
                            <svg id="previewSvg" style="width:70%; height:auto;" viewBox="0 0 128 128" aria-hidden="true">
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
                    <div class="text-muted small">
                        Si no subes archivo ni defines una URL válida, se mostrará el cover predeterminado (si existe) o el ícono BI.
                    </div>
                </div>
            </div>

            {{-- Subir archivo (prioridad sobre URL) --}}
            <div class="mb-2">
                <label for="cover_file" class="form-label">Subir imagen (opcional)</label>
                <input type="file"
                       name="cover_file"
                       id="cover_file"
                       class="form-control @error('cover_file') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                @error('cover_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="form-text">Formatos: JPG, PNG, WEBP, GIF o SVG. Máximo 2MB.</div>
                @enderror
            </div>

            {{-- Marcar como predeterminada (solo aplica a archivo subido) --}}
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="make_default" name="make_default"
                       {{ old('make_default') ? 'checked' : '' }}>
                <label class="form-check-label" for="make_default">
                    Usar esta imagen como <strong>predeterminada</strong> para reportes sin portada
                </label>
            </div>

            {{-- URL de imagen (opcional) --}}
            <div class="mb-3">
                <label for="cover_url" class="form-label">URL de imagen (opcional)</label>
                <input type="url"
                       name="cover_url"
                       id="cover_url"
                       class="form-control @error('cover_url') is-invalid @enderror"
                       placeholder="https://tu-cdn/portadas/reporte.png"
                       value="{{ old('cover_url', $report->cover_url) }}">
                @error('cover_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="form-text">Si subes archivo, la URL será ignorada. La casilla de predeterminado solo aplica al archivo.</div>
                @enderror
            </div>

            {{-- Descripción --}}
            <div class="mb-1 d-flex justify-content-between align-items-center">
                <label for="description" class="form-label mb-0">Descripción (opcional)</label>
                <small class="text-muted"><span id="descCount">0</span>/2000</small>
            </div>
            <div class="mb-3">
                <textarea name="description"
                          id="description"
                          rows="4"
                          maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Breve descripción del contenido, público objetivo, filtros destacados, etc.">{{ old('description', $report->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Encendido fuera de ventana (on-demand) --}}
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="allow_resume_outside_window"
                       name="allow_resume_outside_window" value="1"
                       {{ old('allow_resume_outside_window', $report->allow_resume_outside_window ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_resume_outside_window">
                    Permitir encender la capacidad fuera del horario operativo para este reporte
                </label>
                <div class="form-text">Si está activo, cualquier usuario podrá encenderla al abrir este reporte; luego se apagará por inactividad.</div>
            </div>

        </div>

        {{-- Footer: botones iguales y con buen aire --}}
        <div class="card-footer d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-eq">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-eq">Guardar cambios</button>
        </div>
    </form>
@stop

@push('css')
<style>
/* Ancho mínimo uniforme para todos los botones de acción */
.btn-eq{ min-width: 138px; }

/* En móviles, que los botones puedan ocupar todo el ancho y no queden pegados */
@media (max-width: 480px){
    .btn-eq{ flex: 1 1 auto; }
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput  = document.getElementById('cover_file');
    const urlInput   = document.getElementById('cover_url');
    const descInput  = document.getElementById('description');
    const descCount  = document.getElementById('descCount');

    // Preview elements
    const previewBox = document.getElementById('previewBox');
    let previewImg   = document.getElementById('previewImg'); // puede no existir
    let previewSvg   = document.getElementById('previewSvg'); // puede no existir

    // --- Contador descripción ---
    function updateDescCount() {
        const len = (descInput?.value || '').length;
        if (descCount) descCount.textContent = String(len);
    }
    if (descInput) {
        updateDescCount();
        descInput.addEventListener('input', updateDescCount, {passive:true});
    }

    // --- Helpers de preview ---
    function showImagePreview(src) {
        if (!previewImg) {
            // crear el <img> si no existe
            previewImg = document.createElement('img');
            previewImg.id = 'previewImg';
            previewImg.style.maxWidth  = '100%';
            previewImg.style.maxHeight = '100%';
            previewImg.style.objectFit = 'cover';
            // reemplazar SVG si existe
            const box = previewBox.querySelector('div.flex-shrink-0');
            if (previewSvg && previewSvg.parentNode) previewSvg.parentNode.removeChild(previewSvg);
            box.appendChild(previewImg);
        }
        previewImg.src = src;
    }

    function showFallbackSvg() {
        // Si hay un <img>, lo quitamos y reponemos SVG
        const holder = previewBox.querySelector('div.flex-shrink-0');
        if (previewImg && previewImg.parentNode) {
            previewImg.parentNode.removeChild(previewImg);
            previewImg = null;
        }
        if (!previewSvg) {
            const ns = 'http://www.w3.org/2000/svg';
            previewSvg = document.createElementNS(ns, 'svg');
            previewSvg.setAttribute('id', 'previewSvg');
            previewSvg.setAttribute('viewBox', '0 0 128 128');
            previewSvg.setAttribute('aria-hidden', 'true');
            previewSvg.setAttribute('style', 'width:70%; height:auto;');

            const defs = document.createElementNS(ns, 'defs');
            const grad = document.createElementNS(ns, 'linearGradient');
            grad.setAttribute('id', 'biYellow');
            grad.setAttribute('x1', '0'); grad.setAttribute('y1', '0');
            grad.setAttribute('x2', '0'); grad.setAttribute('y2', '1');
            const s1 = document.createElementNS(ns, 'stop'); s1.setAttribute('offset','0%'); s1.setAttribute('stop-color','#FFD93D');
            const s2 = document.createElementNS(ns, 'stop'); s2.setAttribute('offset','100%'); s2.setAttribute('stop-color','#FFC107');
            grad.appendChild(s1); grad.appendChild(s2); defs.appendChild(grad);

            const rect1 = document.createElementNS(ns, 'rect'); rect1.setAttribute('x','8'); rect1.setAttribute('y','28'); rect1.setAttribute('width','112'); rect1.setAttribute('height','80'); rect1.setAttribute('rx','12'); rect1.setAttribute('fill','url(#biYellow)');
            const rect1s = document.createElementNS(ns, 'rect'); rect1s.setAttribute('x','8'); rect1s.setAttribute('y','28'); rect1s.setAttribute('width','112'); rect1s.setAttribute('height','80'); rect1s.setAttribute('rx','12'); rect1s.setAttribute('fill','#000'); rect1s.setAttribute('opacity','.05');

            const rect2 = document.createElementNS(ns, 'rect'); rect2.setAttribute('x','8'); rect2.setAttribute('y','18'); rect2.setAttribute('width','56'); rect2.setAttribute('height','20'); rect2.setAttribute('rx','6'); rect2.setAttribute('fill','url(#biYellow)');
            const rect2s = document.createElementNS(ns, 'rect'); rect2s.setAttribute('x','8'); rect2s.setAttribute('y','18'); rect2s.setAttribute('width','56'); rect2s.setAttribute('height','20'); rect2s.setAttribute('rx','6'); rect2s.setAttribute('fill','#000'); rect2s.setAttribute('opacity','.05');

            const g = document.createElementNS(ns, 'g'); g.setAttribute('transform','translate(28,50)'); g.setAttribute('fill','#fff');
            const bars = [
                {x:0,  y:30, w:12, h:28},
                {x:20, y:18, w:12, h:40},
                {x:40, y:10, w:12, h:48},
                {x:60, y:4,  w:12, h:54},
            ];
            bars.forEach(b => {
                const r = document.createElementNS(ns, 'rect');
                r.setAttribute('x', String(b.x));
                r.setAttribute('y', String(b.y));
                r.setAttribute('width',  String(b.w));
                r.setAttribute('height', String(b.h));
                r.setAttribute('rx','2');
                g.appendChild(r);
            });

            previewSvg.appendChild(defs);
            previewSvg.appendChild(rect1); previewSvg.appendChild(rect1s);
            previewSvg.appendChild(rect2); previewSvg.appendChild(rect2s);
            previewSvg.appendChild(g);
            holder.appendChild(previewSvg);
        }
    }

    // --- Eventos de preview ---
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                // Prioridad archivo > URL: si hay archivo, limpiamos URL
                if (urlInput) urlInput.value = '';
                const reader = new FileReader();
                reader.onload = e => showImagePreview(e.target.result);
                reader.readAsDataURL(file);
            } else {
                // si quitó el archivo, ver si hay URL válida
                const url = (urlInput?.value || '').trim();
                if (url) {
                    showImagePreview(url);
                } else {
                    showFallbackSvg();
                }
            }
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', function () {
            const val = (this.value || '').trim();
            // Solo previsualizar URL si NO hay archivo seleccionado
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                if (val) {
                    try {
                        // Validación básica de URL
                        const u = new URL(val);
                        showImagePreview(u.href);
                    } catch (_) {
                        // URL inválida → mostramos fallback
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
