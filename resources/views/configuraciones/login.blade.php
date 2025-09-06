{{-- ==================================================================== --}}
{{-- INICIO: NUEVA ESTRUCTURA DE DOS COLUMNAS PARA EL TEMA DEL LOGIN      --}}
{{-- ==================================================================== --}}

<div class="row">

    {{-- COLUMNA IZQUIERDA: ACCIONES (SUBIR ARCHIVOS) --}}
    <div class="col-md-7">
        <x-adminlte-card title="Archivos a Subir" icon="fas fa-upload text-lightblue">

            {{-- Campo para el Logo --}}
            <x-adminlte-input-file name="login_logo" label="Logo de login (PNG/JPG/SVG)" placeholder="Elegir un archivo..." accept="image/*">
                <x-slot name="prependSlot">
                    <div class="input-group-text bg-lightblue">
                        <i class="fas fa-image"></i>
                    </div>
                </x-slot>
                {{-- Tooltip: recomendaciones para el logo de login --}}
                <x-slot name="bottom">
                    <small class="text-muted">Se recomienda fondo transparente y altura ≤ 90px.
                        <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Formatos admitidos: PNG, JPG o SVG. Se muestra sobre el formulario de login."></i>
                    </small>
                </x-slot>
            </x-adminlte-input-file>

            <hr>

            {{-- Campo para la Imagen de Fondo --}}
            <x-adminlte-input-file name="login_bg" label="Imagen de fondo (pantalla completa)" placeholder="Elegir un archivo..." accept="image/jpeg,image/png">
                <x-slot name="prependSlot">
                    <div class="input-group-text bg-lightblue">
                        <i class="fas fa-image"></i>
                    </div>
                </x-slot>
                <x-slot name="bottom">
                    <small class="text-muted">Se ajustará para cubrir toda la pantalla en la página de login.
                        <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Sugerencia: usar 1920×1080 o superior, con buena compresión."></i>
                    </small>
                </x-slot>
            </x-adminlte-input-file>

        </x-adminlte-card>
    </div>

    {{-- COLUMNA DERECHA: VISTAS PREVIA --}}
    <div class="col-md-5 d-flex">
        <div class="w-100">
            <x-adminlte-card title="Vistas Previa Actuales" icon="fas fa-eye text-lightblue" class="h-100">

                {{-- Vista Previa del Logo --}}
                @if($config->login_logo_path)
                    <div>
                        <p class="mb-1 text-secondary">Logo de Login:</p>
                        <div class="p-2 border rounded text-center bg-light">
                             <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="Login Logo" style="max-height:90px; max-width: 100%;">
                        </div>
                    </div>
                @else
                    <div class="text-muted text-center py-5">No hay un logo de login cargado.</div>
                @endif

                <hr class="@if(!$config->login_logo_path || !$config->login_bg_path) d-none @endif">

                {{-- Vista Previa del Fondo --}}
                @if($config->login_bg_path)
                    <div class="mt-2">
                        <p class="mb-1 text-secondary">Imagen de Fondo:</p>
                        <div class="border rounded">
                            <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="Login BG" style="width: 100%; height: 120px; object-fit: cover;">
                        </div>
                    </div>
                @else
                    <div class="text-muted text-center py-5 @if($config->login_logo_path) border-top mt-3 pt-3 @endif">No hay una imagen de fondo cargada.</div>
                @endif

            </x-adminlte-card>
        </div>
    </div>
</div>

{{-- ==================================================================== --}}
{{-- FIN: NUEVA ESTRUCTURA                                                --}}
{{-- ==================================================================== --}}

@push('js')
<script>
(function(){
    // Inicializa tooltips en esta pantalla (si Bootstrap está disponible)
    try {
        if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new window.bootstrap.Tooltip(el);
            });
        }
    } catch (_) {}
})();
</script>
@endpush

@push('js')
<script>
(function(){
    // Inicializa tooltips en esta pantalla (si Bootstrap está disponible)
    try {
        if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new window.bootstrap.Tooltip(el);
            });
        }
    } catch (_) {}
})();
</script>
@endpush
