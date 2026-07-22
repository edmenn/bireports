<div class="row">

    {{-- COLUMNA IZQUIERDA: ACCIONES (SUBIR ARCHIVOS) --}}
    <div class="col-md-7 d-flex">
        <x-adminlte-card title="{{ __('settings.login.cards.uploads.title') }}" icon="fas fa-upload text-lightblue" class="flex-grow-1">
            
            <div class="d-flex flex-column justify-content-around h-100">
                {{-- Campo para el Logo --}}
                <x-adminlte-input-file name="login_logo" label="{{ __('settings.login.cards.uploads.fields.logo.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.logo.placeholder') }}" accept="image/png,image/jpeg"/>
                
                {{-- Campo para la Imagen de Fondo --}}
                <x-adminlte-input-file name="login_bg" label="{{ __('settings.login.cards.uploads.fields.background.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.background.placeholder') }}" accept="image/png,image/jpeg">
                    <x-slot name="bottom">
                        <small class="text-muted">{{ __('settings.login.cards.uploads.fields.background.help') }}</small>
                    </x-slot>
                </x-adminlte-input-file>
            </div>

        </x-adminlte-card>
    </div>

    {{-- COLUMNA DERECHA: VISTAS PREVIA --}}
    <div class="col-md-5 d-flex">
        <x-adminlte-card title="{{ __('settings.login.cards.preview.title') }}" icon="fas fa-eye text-lightblue" class="flex-grow-1">

            {{-- Vista Previa del Logo --}}
            @if($config->login_logo_path ?? false)
                <div>
                    <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.logo_heading') }}</p>
                    <div class="p-2 border rounded text-center bg-light">
                         {{-- AÑADIMOS UN ID AQUÍ --}}
                         <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="{{ __('settings.login.cards.preview.logo_alt') }}" style="max-height:90px; max-width: 100%;" id="logo-preview">
                    </div>
                </div>
            @else
                <div class="text-muted text-center py-5">
                    {{ __('settings.login.cards.preview.no_logo') }}
                    {{-- AÑADIMOS UN CONTENEDOR DE IMAGEN VACÍO PARA LA VISTA PREVIA --}}
                    <img src="" alt="{{ __('settings.login.cards.preview.logo_alt') }}" style="max-height:90px; max-width: 100%; display: none;" id="logo-preview" class="p-2 border rounded text-center bg-light mt-2">
                </div>
            @endif

            <hr class="@if(!($config->login_logo_path ?? false) || !($config->login_bg_path ?? false)) d-none @endif">

            {{-- Vista Previa del Fondo --}}
            @if($config->login_bg_path ?? false)
                <div class="mt-2">
                    <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.background_heading') }}</p>
                    <div class="border rounded">
                        {{-- AÑADIMOS UN ID AQUÍ --}}
                        <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="{{ __('settings.login.cards.preview.background_alt') }}" style="width: 100%; height: 180px; object-fit: cover;" id="bg-preview">
                    </div>
                </div>
            @else
                <div class="text-muted text-center py-5 @if($config->login_logo_path ?? false) border-top mt-3 pt-3 @endif">
                    {{ __('settings.login.cards.preview.no_background') }}
                    {{-- AÑADIMOS UN CONTENEDOR DE IMAGEN VACÍO PARA LA VISTA PREVIA --}}
                    <img src="" alt="{{ __('settings.login.cards.preview.background_alt') }}" style="width: 100%; height: 180px; object-fit: cover; display: none;" id="bg-preview" class="border rounded mt-2">
                </div>
            @endif

        </x-adminlte-card>
    </div>
</div>
