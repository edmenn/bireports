@extends('adminlte::page')

@section('title', 'Configuración del Tema')

@section('content_header')
    <h1 class="m-0 text-dark">Configuración del Tema</h1>
@stop

@section('content')
    {{-- Alertas --}}
    @if(session('success'))
        <x-adminlte-alert theme="success" title="Éxito" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Errores de validación" dismissable>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('configuraciones.update', $config->id) }}" enctype="multipart/form-data" novalidate id="theme-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ url()->current() }}">

        <div class="card card-primary card-outline mt-3">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                    @php($active = request('tab', 'pagina'))
                    {{-- Pestañas --}}
                    <ul class="nav nav-tabs border-0" id="themeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $active === 'pagina' ? 'active' : '' }}" id="tab-pagina" data-toggle="tab" href="#pane-pagina" role="tab" aria-controls="pane-pagina" aria-selected="{{ $active === 'pagina' ? 'true' : 'false' }}">
                                <i class="fas fa-desktop mr-1"></i> Tema Principal
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $active === 'login' ? 'active' : '' }}" id="tab-login" data-toggle="tab" href="#pane-login" role="tab" aria-controls="pane-login" aria-selected="{{ $active === 'login' ? 'true' : 'false' }}">
                                <i class="fas fa-sign-in-alt mr-1"></i> Tema del Login
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card-body">
                <div class="tab-content" id="themeTabsContent">

                    {{-- ============================================= --}}
                    {{--            CONTENIDO: TEMA PRINCIPAL          --}}
                    {{-- ============================================= --}}
                    <div class="tab-pane fade {{ $active === 'pagina' ? 'show active' : '' }}" id="pane-pagina" role="tabpanel" aria-labelledby="tab-pagina">

                        {{-- Configuración General (mantengo dos campos lado a lado en pantallas grandes) --}}
                        <x-adminlte-card title="Configuración General" icon="fas fa-cog text-lightblue" class="mb-3">
                            <div class="form-row">
                                <div class="form-group col-12 col-lg-6">
                                    <x-adminlte-input name="site_title" label="Nombre de la página (título)" placeholder="Ej: Mi Portal BI" fgroup-class="mb-3" value="{{ old('site_title', $config->site_title) }}">
                                        <x-slot name="bottom"><small class="text-muted">Se muestra en la pestaña del navegador.</small></x-slot>
                                    </x-adminlte-input>
                                    <small class="text-muted d-inline-block ms-1">
                                        <i class="far fa-question-circle" data-bs-toggle="tooltip" title="Título que verá el usuario en la pestaña del navegador."></i>
                                    </small>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <x-adminlte-select name="theme_name" id="theme_name" label="Tema predefinido">
                                        <option value="">Predeterminado</option>
                                        @foreach(['Azul','Gris','Verde','Rojo','Naranja','Morado','Marrón'] as $preset)
                                            <option value="{{ $preset }}" @selected(old('theme_name', $config->theme_name) === $preset)>{{ $preset }}</option>
                                        @endforeach
                                        <x-slot name="bottom"><small class="text-muted">Aplica una paleta inicial de colores.</small></x-slot>
                                     </x-adminlte-select>
                                    <small class="text-muted d-inline-block ms-1">
                                        <i class="far fa-question-circle" data-bs-toggle="tooltip" title="Aplica una paleta base; luego podés ajustar los colores debajo."></i>
                                    </small>
                                 </div>
                            </div>
                        </x-adminlte-card>

                        {{-- Logos y Favicon (grilla 3→2→1 columnas) --}}
                        <x-adminlte-card title="Logos, Favicon y Preloader" icon="far fa-images text-lightblue" class="mb-3">
                            <div class="grid-logos">
                                {{-- Logo principal --}}
                                <div class="logo-block">
                                    <x-adminlte-input-file name="site_logo" label="Logo principal" placeholder="Elegir archivo..." accept="image/*">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">Formato PNG/JPG/SVG. Recomendado fondo transparente y altura ≤ 60px.
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Se muestra en el encabezado. Usa un archivo liviano para mejor carga."></i>
                                        </small></x-slot>
                                    </x-adminlte-input-file>
                                    @php($siteLogoH = (int) (old('site_logo_height', $config->site_logo_height ?? 60)))
                                    @if($config->site_logo_path)
                                        <img id="site-logo-preview" src="{{ Storage::disk('public')->url($config->site_logo_path) }}" alt="Logo" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoH }}px;">
                                    @else
                                        <img id="site-logo-preview" src="" alt="Logo" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoH }}px; display:none;">
                                    @endif
                                    <div class="d-flex align-items-center gap-2 mt-2" style="max-width: 260px;">
                                        <x-adminlte-input name="site_logo_width" label="Ancho (px)" type="number" min="16" max="256" fgroup-class="mb-0" value="{{ old('site_logo_width', $config->site_logo_width) }}"/>
                                        <x-adminlte-input name="site_logo_height" label="Alto (px)" type="number" min="16" max="128" fgroup-class="mb-0" value="{{ $siteLogoH }}"/>
                                    </div>
                                </div>

                                {{-- Icono de Preloader (centro al refrescar) --}}
                                <div class="logo-block">
                                    <x-adminlte-input-file name="preloader_logo" label="Icono de preloader (centrado al cargar)" placeholder="Elegir archivo..." accept="image/*">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">Se muestra en el centro mientras carga la página.</small></x-slot>
                                    </x-adminlte-input-file>

                                    <div class="d-flex align-items-end gap-2">
                                        <div>
                                            @php($pw = (int) (old('preloader_logo_width', $config->preloader_logo_width ?? 60)))
                                            @php($ph = (int) (old('preloader_logo_height', $config->preloader_logo_height ?? 60)))
                                            <label class="form-label mb-1">Ancho × Alto (px)</label>
                                            <div class="d-flex align-items-center" style="gap:.5rem; max-width: 260px;">
                                                <input type="number" name="preloader_logo_width" class="form-control" min="16" max="512" step="1" value="{{ $pw }}" style="width:100px" />
                                                <span class="text-muted">×</span>
                                                <input type="number" name="preloader_logo_height" class="form-control" min="16" max="512" step="1" value="{{ $ph }}" style="width:100px" />
                                            </div>
                                            <small class="text-muted">Recomendado: 60–120 px.</small>
                                        </div>
                                    </div>

                                    @if($config->preloader_logo_path)
                                        <img id="preloader-logo-preview" src="{{ Storage::disk('public')->url($config->preloader_logo_path) }}" alt="Preloader" class="img-thumbnail mt-2" style="height: {{ $ph }}px; width: {{ $pw }}px;">
                                    @else
                                        <img id="preloader-logo-preview" src="" alt="Preloader" class="img-thumbnail mt-2" style="height: {{ $ph }}px; width: {{ $pw }}px; display:none;">
                                    @endif
                                </div>

                                {{-- Logo reducido --}}
                                <div class="logo-block">
                                    <x-adminlte-input-file name="site_logo_compact" label="Logo reducido" placeholder="Elegir archivo..." accept="image/*">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">Se usa con sidebar colapsada. Altura sugerida ≤ 60px.
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Versión compacta del logo para espacios reducidos."></i>
                                        </small></x-slot>
                                    </x-adminlte-input-file>
                                    @php($siteLogoCH = (int) (old('site_logo_compact_height', $config->site_logo_compact_height ?? 60)))
                                    @if($config->site_logo_compact_path)
                                        <img id="site-logo-compact-preview" src="{{ Storage::disk('public')->url($config->site_logo_compact_path) }}" alt="Logo Compacto" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoCH }}px;">
                                    @else
                                        <img id="site-logo-compact-preview" src="" alt="Logo Compacto" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoCH }}px; display:none;">
                                    @endif
                                    <div class="d-flex align-items-center gap-2 mt-2" style="max-width: 260px;">
                                        <x-adminlte-input name="site_logo_compact_width" label="Ancho (px)" type="number" min="16" max="256" fgroup-class="mb-0" value="{{ old('site_logo_compact_width', $config->site_logo_compact_width) }}"/>
                                        <x-adminlte-input name="site_logo_compact_height" label="Alto (px)" type="number" min="16" max="128" fgroup-class="mb-0" value="{{ $siteLogoCH }}"/>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="logo-block">
                                    <x-adminlte-input-file name="site_favicon" label="Favicon" placeholder="Elegir archivo..." accept="image/x-icon,image/png">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">ICO o PNG 32×32 recomendado.
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Pequeño ícono de pestaña del navegador."></i>
                                        </small></x-slot>
                                    </x-adminlte-input-file>
                                    @if($config->site_favicon_path)
                                        <img id="site-favicon-preview" src="{{ Storage::disk('public')->url($config->site_favicon_path) }}" alt="Favicon" class="img-thumbnail mt-1" style="height: 32px; width: 32px;">
                                    @else
                                        <img id="site-favicon-preview" src="" alt="Favicon" class="img-thumbnail mt-1" style="height: 32px; width: 32px; display:none;">
                                    @endif
                                </div>
                            </div>
                        </x-adminlte-card>

                        {{-- Paleta de colores (sub-secciones en 2 columnas, auto-wrap) --}}
                        <x-adminlte-card title="Paleta de Colores Personalizada" icon="fas fa-palette text-lightblue" class="mb-1">
                            @php($t = is_array($config->theme_config) ? $config->theme_config : (is_string($config->theme_config) ? json_decode($config->theme_config, true) : []))

                            <div class="grid-colors-2">
                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-square mr-1 text-muted"></i> Base
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Colores principales de la interfaz (fondo y texto base)."></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>Fondo principal</span><input type="color" name="color_main_bg" value="{{ $t['main_bg'] ?? '#f4f6f9' }}" class="color-item__swatch js-color" data-var="--app-main-bg"></label>
                                        <label class="color-item"><span>Texto principal</span><input type="color" name="color_main_text" value="{{ $t['main_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-main-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-window-maximize mr-1 text-muted"></i> Navbar
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Barra superior: fondo y texto del encabezado."></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>Fondo</span><input type="color" name="color_navbar_bg" value="{{ $t['navbar_bg'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-navbar-bg"></label>
                                        <label class="color-item"><span>Texto</span><input type="color" name="color_navbar_text" value="{{ $t['navbar_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-navbar-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-columns mr-1 text-muted"></i> Sidebar
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Menú lateral: colores de fondo y textos."></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>Fondo</span><input type="color" name="color_menu_bg" value="{{ $t['menu_bg'] ?? '#343a40' }}" class="color-item__swatch js-color" data-var="--app-menu-bg"></label>
                                        <label class="color-item"><span>Texto</span><input type="color" name="color_menu_text" value="{{ $t['menu_text'] ?? '#c2c7d0' }}" class="color-item__swatch js-color" data-var="--app-menu-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="far fa-clone mr-1 text-muted"></i> Contenido (cards)
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Tarjetas y contenedores: fondos y texto."></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>Card fondo</span><input type="color" name="color_container_bg" value="{{ $t['container_bg'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-card-bg"></label>
                                        <label class="color-item"><span>Header fondo</span><input type="color" name="color_container_alt_bg" value="{{ $t['container_alt_bg'] ?? '#f8fafc' }}" class="color-item__swatch js-color" data-var="--app-card-alt-bg"></label>
                                        <label class="color-item"><span>Texto</span><input type="color" name="color_container_text" value="{{ $t['container_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-card-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-highlighter mr-1 text-muted"></i> Acentos y Botones
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Resaltados y botones: estados normal, hover y activo."></i>
                                    </div>
                                    <div class="color-grid" style="grid-template-columns: repeat(2, 1fr);">
                                        <label class="color-item"><span>Destacado</span><input type="color" name="color_highlight_bg" value="{{ $t['highlight_bg'] ?? '#2563eb' }}" class="color-item__swatch js-color" data-var="--app-hi-bg"></label>
                                        <label class="color-item"><span>Texto destacado</span><input type="color" name="color_highlight_text" value="{{ $t['highlight_text'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-hi-text"></label>
                                        <label class="color-item"><span>Botón</span><input type="color" name="color_btn_bg" value="{{ $t['btn_bg'] ?? '#2563eb' }}" class="color-item__swatch js-color" data-var="--app-btn-bg"></label>
                                        <label class="color-item"><span>Texto botón</span><input type="color" name="color_btn_text" value="{{ $t['btn_text'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-btn-text"></label>
                                        <label class="color-item"><span>Botón hover</span><input type="color" name="color_btn_hover" value="{{ $t['btn_hover'] ?? '#1d4ed8' }}" class="color-item__swatch js-color" data-var="--app-btn-hover"></label>
                                        <label class="color-item"><span>Botón activo</span><input type="color" name="color_btn_active" value="{{ $t['btn_active'] ?? '#1e40af' }}" class="color-item__swatch js-color" data-var="--app-btn-active"></label>
                                    </div>
                                </div>
                            </div>
                        </x-adminlte-card>
                    </div>

                    {{-- ============================================= --}}
                    {{--              CONTENIDO: TEMA LOGIN            --}}
                    {{-- ============================================= --}}
                    <div class="tab-pane fade {{ $active === 'login' ? 'show active' : '' }}" id="pane-login" role="tabpanel" aria-labelledby="tab-login">

                        <x-adminlte-card title="Archivos a Subir" icon="fas fa-upload text-lightblue" class="mb-3">
                            <div class="d-flex flex-column">
                                <x-adminlte-input-file name="login_logo" label="Logo de login (PNG/JPG/SVG)" placeholder="Elegir un archivo..." accept="image/png,image/jpeg,image/svg+xml"/>
                                <x-adminlte-input-file name="login_bg" label="Imagen de fondo (pantalla completa)" placeholder="Elegir un archivo..." accept="image/png,image/jpeg">
                                    <x-slot name="bottom">
                                        <small class="text-muted">Se ajustará para cubrir toda la pantalla en la página de login.</small>
                                    </x-slot>
                                </x-adminlte-input-file>
                            </div>
                        </x-adminlte-card>

                        <x-adminlte-card title="Vistas Previa Actuales" icon="fas fa-eye text-lightblue" class="mb-1">
                            @if($config->login_logo_path ?? false)
                                <div class="mb-3">
                                    <p class="mb-1 text-secondary">Logo de Login:</p>
                                    <div class="p-2 border rounded text-center bg-light">
                                         <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="Login Logo" style="max-height:90px; max-width: 100%;" id="logo-preview">
                                    </div>
                                </div>
                            @else
                                <div class="text-muted text-center py-5">
                                    No hay un logo de login cargado.
                                    <img src="" alt="" style="max-height:90px; max-width: 100%; display: none;" id="logo-preview" class="p-2 border rounded text-center bg-light mt-2">
                                </div>
                            @endif

                            @if($config->login_bg_path ?? false)
                                <div class="mt-2">
                                    <p class="mb-1 text-secondary">Imagen de Fondo:</p>
                                    <div class="border rounded">
                                        <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="Login BG" style="width: 100%; height: 180px; object-fit: cover;" id="bg-preview">
                                    </div>
                                </div>
                            @else
                                <div class="text-muted text-center py-5 @if($config->login_logo_path ?? false) border-top mt-3 pt-3 @endif">
                                    No hay una imagen de fondo cargada.
                                    <img src="" alt="" style="width: 100%; height: 180px; object-fit: cover; display: none;" id="bg-preview" class="border rounded mt-2">
                                </div>
                            @endif
                        </x-adminlte-card>
                    </div>

                </div>
            </div>
        </div>

        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: 0.75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="primary" label="Guardar" icon="fas fa-save" class="btn-eq"/>
                    <button type="button" id="js-cancel-changes" class="btn btn-secondary btn-eq" onclick="window.history.back()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </x-adminlte-card>
    </form>
@stop

@push('css')
<style>
/* ====== Grillas internas para reducir altura ====== */

/* Logos: 3 columnas en >=1200px, 2 en >=768px, 1 en móvil */
.grid-logos{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (max-width: 1199.98px){
    .grid-logos{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px){
    .grid-logos{ grid-template-columns: 1fr; }
}
.logo-block .img-thumbnail{ max-width: 100%; }

/* Paleta: contenedor con 2 columnas auto-wrap (secciones parejas) */
.grid-colors-2{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (max-width: 991.98px){
    .grid-colors-2{ grid-template-columns: 1fr; }
}

/* ====== Estilos existentes para color pickers (se mantienen) ====== */
.color-card {
    background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: .75rem;
    padding: .75rem;
}
.color-card__header {
    font-weight: 600; color: #475569; margin-bottom: .75rem; font-size: .9rem;
    text-transform: uppercase; letter-spacing: .05em;
}
.color-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .5rem;
}
.color-item {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    background-color: #fff; border: 1px solid #e2e8f0; border-radius: .5rem;
    padding: .35rem .6rem; font-size: .875rem; color: #334155;
    transition: all 0.2s ease-in-out;
}
.color-item:hover {
    box-shadow: 0 0 0 1px #cbd5e1; border-color: #cbd5e1;
}
.color-item__swatch {
    width: 28px; height: 28px; padding: 0; border: none; border-radius: .375rem;
    cursor: pointer; background: transparent; -webkit-appearance: none;
    -moz-appearance: none; appearance: none;
}
.color-item__swatch::-webkit-color-swatch-wrapper { padding: 0; }
.color-item__swatch::-webkit-color-swatch { border: 1px solid #e2e8f0; border-radius: .375rem; }
</style>
@endpush

@push('js')
<script>
(function(){
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa tooltips (si Bootstrap está disponible)
        try {
            if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                    new window.bootstrap.Tooltip(el);
                });
            }
        } catch (_) {}
        const colorInputs = document.querySelectorAll('.js-color');
        const presets = {
            'Azul':    { navbar_bg:'#0ea5e9', navbar_text:'#ffffff', menu_bg:'#0b4f6c', menu_text:'#e2e8f0', highlight_bg:'#2563eb', highlight_text:'#ffffff', btn_bg:'#2563eb', btn_text:'#ffffff' },
            'Gris':    { navbar_bg:'#374151', navbar_text:'#f9fafb', menu_bg:'#1f2d37', menu_text:'#d1d5db', highlight_bg:'#6b7280', highlight_text:'#ffffff', btn_bg:'#6b7280', btn_text:'#ffffff' },
            'Verde':   { navbar_bg:'#10b981', navbar_text:'#053b2c', menu_bg:'#064e3b', menu_text:'#d1fae5', highlight_bg:'#059669', highlight_text:'#ffffff', btn_bg:'#10b981', btn_text:'#053b2c' },
            'Rojo':    { navbar_bg:'#ef4444', navbar_text:'#ffffff', menu_bg:'#7f1d1d', menu_text:'#fecaca', highlight_bg:'#dc2626', highlight_text:'#ffffff', btn_bg:'#ef4444', btn_text:'#ffffff' },
            'Naranja': { navbar_bg:'#f97316', navbar_text:'#1f2937', menu_bg:'#7c2d12', menu_text:'#ffedd5', highlight_bg:'#ea580c', highlight_text:'#1f2937', btn_bg:'#f97316', btn_text:'#1f2937' },
            'Morado':  { navbar_bg:'#8b5cf6', navbar_text:'#0f172a', menu_bg:'#4c1d95', menu_text:'#ede9fe', highlight_bg:'#7c3aed', highlight_text:'#0f172a', btn_bg:'#8b5cf6', btn_text:'#0f172a' },
            'Marrón':  { navbar_bg:'#92400e', navbar_text:'#fef3c7', menu_bg:'#431407', menu_text:'#fde68a', highlight_bg:'#b45309', highlight_text:'#fef3c7', btn_bg:'#92400e', btn_text:'#fef3c7' },
        };

        const defaultPreset = {
            main_bg:'#f4f6f9',
            main_text:'#111111',
            navbar_bg:'#374151',
            navbar_text:'#f9fafb',
            menu_bg:'#1f2d37',
            menu_text:'#d1d5db',
            container_bg:'#ffffff',
            container_alt_bg:'#f8fafc',
            container_text:'#111111',
            highlight_bg:'#6b7280',
            highlight_text:'#ffffff',
            btn_bg:'#6b7280',
            btn_text:'#ffffff',
            btn_hover:'#4b5563',
            btn_active:'#374151',
        };

        // Personalizado <-> Preset
        const CUSTOM_VALUE = 'Personalizado';
        let applyingPreset = false;
        let customDirty = false;
        const themeNameSelect = document.getElementById('theme_name');
        let lastThemeSelection = themeNameSelect ? themeNameSelect.value : '';

        function ensureCustomOption() {
            if (!themeNameSelect) return;
            const exists = Array.from(themeNameSelect.options).some(o => o.value === CUSTOM_VALUE);
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = CUSTOM_VALUE;
                opt.textContent = CUSTOM_VALUE;
                themeNameSelect.appendChild(opt);
            }
        }
        function removeCustomOption() {
            if (!themeNameSelect) return;
            const opt = Array.from(themeNameSelect.options).find(o => o.value === CUSTOM_VALUE);
            if (opt) opt.remove();
        }

        document.addEventListener('input', function(e){
            const el = e.target.closest('.js-color');
            if(!el) return;
            const cssVar = el.dataset.var;
            if(!cssVar) return;
            document.documentElement.style.setProperty(cssVar, el.value);

            // Si cambia manualmente un color, pasamos a Personalizado
            if (!applyingPreset) {
                customDirty = true;
                ensureCustomOption();
                if (themeNameSelect && themeNameSelect.value !== CUSTOM_VALUE) {
                    themeNameSelect.value = CUSTOM_VALUE;
                    lastThemeSelection = CUSTOM_VALUE;
                }
            }
        }, {passive:true});
        function applyPresetValues(p){
            if(!p) return;
            applyingPreset = true;
            for (const [k,v] of Object.entries(p)){
                const input = document.querySelector(`[name="color_${k}"]`);
                if(input){
                    input.value = v;
                    input.dispatchEvent(new Event('input',{bubbles:true}));
                }
            }
            applyingPreset = false;
        }
        function applyPreset(name){
            const p = presets[name]; if(!p) return;
            applyPresetValues(p);
            const sel = document.getElementById('theme_name');
            if(sel && sel.value !== name) sel.value = name;
            customDirty = false;
            removeCustomOption();
            lastThemeSelection = name || '';
        }
        if (themeNameSelect) {
            themeNameSelect.addEventListener('change', function(event) {
                const selectedPreset = event.target.value;
                if (selectedPreset === CUSTOM_VALUE) {
                    if (!customDirty) {
                        event.target.value = lastThemeSelection || '';
                        return;
                    }
                    lastThemeSelection = CUSTOM_VALUE;
                    return;
                }
                if (selectedPreset) {
                    applyPreset(selectedPreset);
                } else {
                    // Predeterminado
                    applyPresetValues(defaultPreset);
                    customDirty = false;
                    removeCustomOption();
                    lastThemeSelection = '';
                }
            });

            // Detección al cargar: seleccionar preset si coincide, si no => Personalizado
            (function detectInitialSelection(){
                try {
                    // Construye el mapa de valores actuales por nombre
                    const getVal = (k) => {
                        const el = document.querySelector(`[name="color_${k}"]`);
                        return (el && typeof el.value === 'string') ? el.value.toLowerCase() : '';
                    };
                    const keysDefault = Object.keys(defaultPreset);
                    const equalsAll = (objA, objB, keys) => keys.every(k => String((objA[k]||'')).toLowerCase() === String((objB[k]||'')).toLowerCase());

                    const current = {};
                    keysDefault.forEach(k => current[k] = getVal(k));

                    // Si coincide exactamente con Predeterminado → seleccionar vacío si no hay valor guardado
                    if (equalsAll(current, defaultPreset, keysDefault)) {
                        if (themeNameSelect.value === '' || themeNameSelect.value === CUSTOM_VALUE) {
                            themeNameSelect.value = '';
                            customDirty = false;
                            removeCustomOption();
                            lastThemeSelection = '';
                        }
                        return;
                    }

                    // Buscar un preset que coincida en las claves que define
                    let matched = null;
                    for (const [name, p] of Object.entries(presets)) {
                        const keys = Object.keys(p);
                        const ok = keys.every(k => getVal(k) === String(p[k]||'').toLowerCase());
                        if (ok) { matched = name; break; }
                    }

                    if (matched) {
                        themeNameSelect.value = matched;
                        customDirty = false;
                        removeCustomOption();
                        lastThemeSelection = matched;
                    } else {
                        // No coincide con ningún preset → Personalizado
                        ensureCustomOption();
                        themeNameSelect.value = CUSTOM_VALUE;
                        customDirty = true;
                        lastThemeSelection = CUSTOM_VALUE;
                    }
                } catch(_) {}
            })();
        }

        function setupImagePreview(inputId, previewId) {
            const inputElement = document.querySelector(`input[name="${inputId}"]`);
            const previewElement = document.getElementById(previewId);
            if (!inputElement || !previewElement) return;

            inputElement.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewElement.src = e.target.result;
                        previewElement.style.display = 'block';
                        const cont = previewElement.parentElement;
                        if (cont && cont.classList && cont.classList.contains('text-muted')) {
                            Array.from(cont.childNodes).forEach(n => { if(n.nodeType === Node.TEXT_NODE) n.textContent = ''; });
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        setupImagePreview('login_logo', 'logo-preview');
        setupImagePreview('login_bg', 'bg-preview');
        setupImagePreview('site_logo', 'site-logo-preview');
        setupImagePreview('site_logo_compact', 'site-logo-compact-preview');
        setupImagePreview('site_favicon', 'site-favicon-preview');
        setupImagePreview('preloader_logo', 'preloader-logo-preview');
        // Ajustar tamaño de previews de logos cuando cambian los inputs de alto
        try {
            const h1 = document.querySelector('input[name="site_logo_height"]');
            const p1 = document.getElementById('site-logo-preview');
            if (h1 && p1) {
                h1.addEventListener('input', () => { const v = Math.max(16, Math.min(128, parseInt(h1.value||0,10))); p1.style.maxHeight = v + 'px'; });
            }
            const h2 = document.querySelector('input[name="site_logo_compact_height"]');
            const p2 = document.getElementById('site-logo-compact-preview');
            if (h2 && p2) {
                h2.addEventListener('input', () => { const v = Math.max(16, Math.min(128, parseInt(h2.value||0,10))); p2.style.maxHeight = v + 'px'; });
            }
        } catch (_) {}

        // Actualizar vista previa del tamaño del preloader al cambiar los inputs
        (function bindPreloaderSizePreview(){
            const w = document.querySelector('input[name="preloader_logo_width"]');
            const h = document.querySelector('input[name="preloader_logo_height"]');
            const img = document.getElementById('preloader-logo-preview');
            if (!w || !h || !img) return;
            const clamp = (val, min, max) => Math.max(min, Math.min(max, parseInt(val || 0, 10)));
            const apply = () => {
                const W = clamp(w.value, 16, 512);
                const H = clamp(h.value, 16, 512);
                img.style.width = W + 'px';
                img.style.height = H + 'px';
            };
            w.addEventListener('input', apply);
            h.addEventListener('input', apply);
        })();
    });
})();
</script>
@endpush
