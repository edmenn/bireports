{{-- ==================================================================== --}}
{{-- INICIO: NUEVA ESTRUCTURA DE DOS COLUMNAS PARA EL TEMA DEL LOGIN      --}}
{{-- ==================================================================== --}}

<div class="row">

    {{-- COLUMNA IZQUIERDA: ACCIONES (SUBIR ARCHIVOS) --}}
    <div class="col-md-7">
        <x-ui-card title="{{ __('settings.login.cards.uploads.title') }}" icon="fas fa-upload text-lightblue">

            {{-- Campo para el Logo --}}
            <x-ui-input-file name="login_logo" label="{{ __('settings.login.cards.uploads.fields.logo.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.logo.placeholder') }}" accept="image/*">
                <x-slot name="prependSlot">
                    <div class="input-group-text bg-lightblue">
                        <i class="fas fa-image"></i>
                    </div>
                </x-slot>
                {{-- Tooltip: recomendaciones para el logo de login --}}
                <x-slot name="bottom">
                    <small class="text-muted">{{ __('settings.login.cards.uploads.fields.logo.help') }}
                        <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.login.cards.uploads.fields.logo.tooltip') }}"></i>
                    </small>
                </x-slot>
            </x-ui-input-file>

            <hr>

            {{-- Campo para la Imagen de Fondo --}}
            <x-ui-input-file name="login_bg" label="{{ __('settings.login.cards.uploads.fields.background.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.background.placeholder') }}" accept="image/jpeg,image/png">
                <x-slot name="prependSlot">
                    <div class="input-group-text bg-lightblue">
                        <i class="fas fa-image"></i>
                    </div>
                </x-slot>
                <x-slot name="bottom">
                    <small class="text-muted">{{ __('settings.login.cards.uploads.fields.background.help') }}
                        <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.login.cards.uploads.fields.background.tooltip') }}"></i>
                    </small>
                </x-slot>
            </x-ui-input-file>

        </x-ui-card>
    </div>

    {{-- COLUMNA DERECHA: VISTAS PREVIA --}}
    <div class="col-md-5 d-flex">
        <div class="w-100">
            <x-ui-card title="{{ __('settings.login.cards.preview.title') }}" icon="fas fa-eye text-lightblue" class="h-100">

                {{-- Vista Previa del Logo --}}
                @if($config->login_logo_path)
                    <div>
                        <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.logo_heading') }}</p>
                        <div class="p-2 border rounded text-center bg-light">
                             <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="{{ __('settings.login.cards.preview.logo_alt') }}" style="max-height:90px; max-width: 100%;">
                        </div>
                    </div>
                @else
                    <div class="text-muted text-center py-5">{{ __('settings.login.cards.preview.no_logo') }}</div>
                @endif

                <hr class="@if(!$config->login_logo_path || !$config->login_bg_path) d-none @endif">

                {{-- Vista Previa del Fondo --}}
                @if($config->login_bg_path)
                    <div class="mt-2">
                        <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.background_heading') }}</p>
                        <div class="border rounded">
                            <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="{{ __('settings.login.cards.preview.background_alt') }}" style="width: 100%; height: 120px; object-fit: cover;">
                        </div>
                    </div>
                @else
                    <div class="text-muted text-center py-5 @if($config->login_logo_path) border-top mt-3 pt-3 @endif">{{ __('settings.login.cards.preview.no_background') }}</div>
                @endif

            </x-ui-card>
        </div>
    </div>
</div>

{{-- Tamaño del logo de login (opcional) --}}
<div class="row mt-3">
    <div class="col-md-7">
        <x-ui-card title="{{ __('settings.login.cards.size.title') }}" icon="fas fa-ruler-combined text-lightblue">
            @php($t = is_array($config->theme_config) ? $config->theme_config : (is_string($config->theme_config) ? json_decode($config->theme_config, true) : []))
            <div class="row">
                <div class="col-md-4">
                    <x-ui-input name="login_logo_width" type="number" min="16" max="512" label="{{ __('settings.login.cards.size.width_label') }}" value="{{ max(16, (int) old('login_logo_width', $t['login_logo_width'] ?? 180)) }}"/>
                </div>
                <div class="col-md-4">
                    <x-ui-input name="login_logo_height" type="number" min="16" max="512" label="{{ __('settings.login.cards.size.height_label') }}" value="{{ max(16, (int) old('login_logo_height', $t['login_logo_height'] ?? 64)) }}"/>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <small class="text-muted">{{ __('settings.login.cards.size.hint') }}</small>
                </div>
            </div>
        </x-ui-card>
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
