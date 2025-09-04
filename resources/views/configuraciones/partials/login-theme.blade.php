<div class="row">

    {{-- COLUMNA IZQUIERDA: ACCIONES (SUBIR ARCHIVOS) --}}
    <div class="col-md-7 d-flex">
        <x-adminlte-card title="Archivos a Subir" icon="fas fa-upload text-lightblue" class="flex-grow-1">
            
            <div class="d-flex flex-column justify-content-around h-100">
                {{-- Campo para el Logo --}}
                <x-adminlte-input-file name="login_logo" label="Logo de login (PNG/JPG/SVG)" placeholder="Elegir un archivo..." accept="image/png,image/jpeg,image/svg+xml"/>
                
                {{-- Campo para la Imagen de Fondo --}}
                <x-adminlte-input-file name="login_bg" label="Imagen de fondo (pantalla completa)" placeholder="Elegir un archivo..." accept="image/png,image/jpeg">
                    <x-slot name="bottom">
                        <small class="text-muted">Se ajustará para cubrir toda la pantalla en la página de login.</small>
                    </x-slot>
                </x-adminlte-input-file>
            </div>

        </x-adminlte-card>
    </div>

    {{-- COLUMNA DERECHA: VISTAS PREVIA --}}
    <div class="col-md-5 d-flex">
        <x-adminlte-card title="Vistas Previa Actuales" icon="fas fa-eye text-lightblue" class="flex-grow-1">

            {{-- Vista Previa del Logo --}}
            @if($config->login_logo_path ?? false)
                <div>
                    <p class="mb-1 text-secondary">Logo de Login:</p>
                    <div class="p-2 border rounded text-center bg-light">
                         {{-- AÑADIMOS UN ID AQUÍ --}}
                         <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="Login Logo" style="max-height:90px; max-width: 100%;" id="logo-preview">
                    </div>
                </div>
            @else
                <div class="text-muted text-center py-5">
                    No hay un logo de login cargado.
                    {{-- AÑADIMOS UN CONTENEDOR DE IMAGEN VACÍO PARA LA VISTA PREVIA --}}
                    <img src="" alt="" style="max-height:90px; max-width: 100%; display: none;" id="logo-preview" class="p-2 border rounded text-center bg-light mt-2">
                </div>
            @endif

            <hr class="@if(!($config->login_logo_path ?? false) || !($config->login_bg_path ?? false)) d-none @endif">

            {{-- Vista Previa del Fondo --}}
            @if($config->login_bg_path ?? false)
                <div class="mt-2">
                    <p class="mb-1 text-secondary">Imagen de Fondo:</p>
                    <div class="border rounded">
                        {{-- AÑADIMOS UN ID AQUÍ --}}
                        <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="Login BG" style="width: 100%; height: 180px; object-fit: cover;" id="bg-preview">
                    </div>
                </div>
            @else
                <div class="text-muted text-center py-5 @if($config->login_logo_path ?? false) border-top mt-3 pt-3 @endif">
                    No hay una imagen de fondo cargada.
                    {{-- AÑADIMOS UN CONTENEDOR DE IMAGEN VACÍO PARA LA VISTA PREVIA --}}
                    <img src="" alt="" style="width: 100%; height: 180px; object-fit: cover; display: none;" id="bg-preview" class="border rounded mt-2">
                </div>
            @endif

        </x-adminlte-card>
    </div>
</div>