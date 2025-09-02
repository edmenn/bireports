@extends('adminlte::page')

@section('title', 'Tema')

@section('content_header')
    <h1 class="m-0">Tema</h1>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="OK" dismissable>
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

    <form method="POST" action="{{ route('configuraciones.update', $config->id) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ route('configuraciones.tema') }}">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                @php($active = request('tab', 'pagina'))
                <ul class="nav nav-tabs border-0" id="themeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $active === 'pagina' ? 'active' : '' }}" id="tab-pagina" data-toggle="tab" href="#pane-pagina" role="tab" aria-controls="pane-pagina" aria-selected="{{ $active === 'pagina' ? 'true' : 'false' }}">Configuración de la página</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $active === 'login' ? 'active' : '' }}" id="tab-login" data-toggle="tab" href="#pane-login" role="tab" aria-controls="pane-login" aria-selected="{{ $active === 'login' ? 'true' : 'false' }}">Tema del login</a>
                    </li>
                </ul>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-palette"></i> Cambiar de tema
                    </button>
                    <div class="dropdown-menu dropdown-menu-right p-0">
                        @foreach(['Azul','Gris','Verde','Rojo','Naranja','Morado','Marrón'] as $preset)
                            <button class="dropdown-item js-apply-preset" type="button" data-preset="{{ $preset }}">{{ $preset }}</button>
                        @endforeach
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item text-danger js-reset-theme" type="button">
                            <i class="fas fa-undo"></i> Volver a colores de fábrica
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content" id="themeTabsContent">
                    {{-- TAB: Página --}}
                    <div class="tab-pane fade {{ $active === 'pagina' ? 'show active' : '' }}" id="pane-pagina" role="tabpanel" aria-labelledby="tab-pagina">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de la página (título)</label>
                                <input type="text" name="site_title" class="form-control @error('site_title') is-invalid @enderror" value="{{ old('site_title', $config->site_title) }}" maxlength="255" placeholder="Ej: Mi Portal BI">
                                @error('site_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">Se muestra en la pestaña del navegador, junto al favicon.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tema predefinido</label>
                                <select name="theme_name" id="theme_name" class="form-control">
                                    <option value="">Predeterminado</option>
                                    @foreach(['Azul','Gris','Verde','Rojo','Naranja','Morado','Marrón'] as $preset)
                                        <option value="{{ $preset }}" @selected(old('theme_name', $config->theme_name) === $preset)>{{ $preset }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Aplica una paleta inicial; luego podés ajustar colores finos.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clases del menú de navegación (navbar)</label>
                                <select name="theme_navbar_class" class="form-control @error('theme_navbar_class') is-invalid @enderror">
                                    <option value="">Predeterminado</option>
                                    @foreach([
                                        'navbar-white navbar-light' => 'Blanco (claro)',
                                        'navbar-dark navbar-primary' => 'Primario (oscuro)',
                                        'navbar-dark navbar-dark' => 'Oscuro',
                                        'navbar-light navbar-info' => 'Info (claro)',
                                        'navbar-dark navbar-teal' => 'Teal (oscuro)'
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('theme_navbar_class', $config->theme_navbar_class) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('theme_navbar_class')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">AdminLTE acepta combinaciones como <code>navbar-dark navbar-primary</code>.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clases de la barra lateral (sidebar)</label>
                                <select name="theme_sidebar_class" class="form-control @error('theme_sidebar_class') is-invalid @enderror">
                                    <option value="">Predeterminado</option>
                                    @foreach([
                                        'sidebar-dark-primary elevation-4' => 'Oscuro primario',
                                        'sidebar-light-primary elevation-4' => 'Claro primario',
                                        'sidebar-dark-light elevation-4' => 'Oscuro (light)',
                                        'sidebar-dark-info elevation-4' => 'Oscuro info',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('theme_sidebar_class', $config->theme_sidebar_class) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('theme_sidebar_class')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Logo principal (PNG/JPG/SVG)</label>
                                <input type="file" name="site_logo" accept="image/png,image/jpeg,image/svg+xml" class="form-control @error('site_logo') is-invalid @enderror">
                                @error('site_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @if($config->site_logo_path)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Actual:</small>
                                        <img src="{{ Storage::disk('public')->url($config->site_logo_path) }}" alt="Logo" style="max-height:60px">
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Favicon (ICO o PNG)</label>
                                <input type="file" name="site_favicon" accept="image/x-icon,image/vnd.microsoft.icon,image/png" class="form-control @error('site_favicon') is-invalid @enderror">
                                @error('site_favicon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @if($config->site_favicon_path)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Actual:</small>
                                        <img src="{{ Storage::disk('public')->url($config->site_favicon_path) }}" alt="Favicon" style="height:32px;width:32px">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Logo reducido (compacto)</label>
                                <input type="file" name="site_logo_compact" accept="image/png,image/jpeg,image/svg+xml" class="form-control @error('site_logo_compact') is-invalid @enderror">
                                @error('site_logo_compact')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @if($config->site_logo_compact_path)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Actual:</small>
                                        <img src="{{ Storage::disk('public')->url($config->site_logo_compact_path) }}" alt="Logo compacto" style="max-height:40px">
                                    </div>
                                @endif
                            </div>

                            <div class="col-12"><hr></div>

                            {{-- Personalizar Colores (estilo acorde a captura) --}}
                            @php($t = is_array($config->theme_config) ? $config->theme_config : (is_string($config->theme_config) ? json_decode($config->theme_config, true) : []))

                            <div class="col-md-3">
                                <label class="form-label">Color del fondo principal <i class="far fa-question-circle" title="Color base del contenido"></i></label>
                                <input type="color" name="color_main_bg" value="{{ $t['main_bg'] ?? '#f4f6f9' }}" class="form-control form-control-color js-color" data-var="--app-main-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color del texto principal <i class="far fa-question-circle" title="Texto general"></i></label>
                                <input type="color" name="color_main_text" value="{{ $t['main_text'] ?? '#111111' }}" class="form-control form-control-color js-color" data-var="--app-main-text">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Barra de navegación – Color del fondo <i class="far fa-question-circle" title="Color del menú superior"></i></label>
                                <input type="color" name="color_navbar_bg" value="{{ $t['navbar_bg'] ?? '#ffffff' }}" class="form-control form-control-color js-color" data-var="--app-navbar-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Barra de navegación – Color del texto <i class="far fa-question-circle" title="Texto del menú superior"></i></label>
                                <input type="color" name="color_navbar_text" value="{{ $t['navbar_text'] ?? '#111111' }}" class="form-control form-control-color js-color" data-var="--app-navbar-text">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Menú – Color del fondo <i class="far fa-question-circle" title="Fondo de la barra lateral"></i></label>
                                <input type="color" name="color_menu_bg" value="{{ $t['menu_bg'] ?? '#343a40' }}" class="form-control form-control-color js-color" data-var="--app-menu-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Menú – Color del texto <i class="far fa-question-circle" title="Texto de la barra lateral"></i></label>
                                <input type="color" name="color_menu_text" value="{{ $t['menu_text'] ?? '#c2c7d0' }}" class="form-control form-control-color js-color" data-var="--app-menu-text">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destacado – Color del fondo <i class="far fa-question-circle" title="Color al pasar el mouse o activos"></i></label>
                                <input type="color" name="color_highlight_bg" value="{{ $t['highlight_bg'] ?? '#2563eb' }}" class="form-control form-control-color js-color" data-var="--app-hi-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destacado – Color del texto <i class="far fa-question-circle" title="Texto en elementos destacados"></i></label>
                                <input type="color" name="color_highlight_text" value="{{ $t['highlight_text'] ?? '#ffffff' }}" class="form-control form-control-color js-color" data-var="--app-hi-text">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Contenedor – Color de fondo principal <i class="far fa-question-circle" title="Cards o paneles"></i></label>
                                <input type="color" name="color_container_bg" value="{{ $t['container_bg'] ?? '#ffffff' }}" class="form-control form-control-color js-color" data-var="--app-card-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Contenedor – Color de fondo secundario <i class="far fa-question-circle" title="Encabezados de card"></i></label>
                                <input type="color" name="color_container_alt_bg" value="{{ $t['container_alt_bg'] ?? '#f8fafc' }}" class="form-control form-control-color js-color" data-var="--app-card-alt-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Contenedor – Color del texto principal <i class="far fa-question-circle" title="Texto dentro de cards"></i></label>
                                <input type="color" name="color_container_text" value="{{ $t['container_text'] ?? '#111111' }}" class="form-control form-control-color js-color" data-var="--app-card-text">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Icono fondo</label>
                                <input type="color" name="color_icon_bg" value="{{ $t['icon_bg'] ?? '#e5e7eb' }}" class="form-control form-control-color js-color" data-var="--app-icon-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Icono texto</label>
                                <input type="color" name="color_icon_text" value="{{ $t['icon_text'] ?? '#111111' }}" class="form-control form-control-color js-color" data-var="--app-icon-text">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Botón fondo</label>
                                <input type="color" name="color_btn_bg" value="{{ $t['btn_bg'] ?? '#2563eb' }}" class="form-control form-control-color js-color" data-var="--app-btn-bg">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Botón texto</label>
                                <input type="color" name="color_btn_text" value="{{ $t['btn_text'] ?? '#ffffff' }}" class="form-control form-control-color js-color" data-var="--app-btn-text">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Botón hover</label>
                                <input type="color" name="color_btn_hover" value="{{ $t['btn_hover'] ?? '#1d4ed8' }}" class="form-control form-control-color js-color" data-var="--app-btn-hover">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Botón activo</label>
                                <input type="color" name="color_btn_active" value="{{ $t['btn_active'] ?? '#1e40af' }}" class="form-control form-control-color js-color" data-var="--app-btn-active">
                            </div>

                            <div class="col-12"><hr></div>

                            {{-- footer fijo, no editable desde UI --}}
                        </div>
                    </div>
                        </div>
                    </div>

                    {{-- TAB: Login --}}
                    <div class="tab-pane fade {{ $active === 'login' ? 'show active' : '' }}" id="pane-login" role="tabpanel" aria-labelledby="tab-login">
                        @include('configuraciones.partials.login-theme', ['config' => $config])
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </form>
@push('js')
<script>
(function(){
    // Previsualización en tiempo real
    document.addEventListener('input', function(e){
        const el = e.target.closest('.js-color');
        if(!el) return;
        const cssVar = el.getAttribute('data-var');
        if(!cssVar) return;
        document.documentElement.style.setProperty(cssVar, el.value);
    }, {passive:true});

    // Presets de tema
    const presets = {
        'Azul': { navbar_bg:'#0ea5e9', navbar_text:'#ffffff', menu_bg:'#0b4f6c', menu_text:'#e2e8f0', highlight_bg:'#2563eb', highlight_text:'#ffffff', btn_bg:'#2563eb', btn_text:'#ffffff' },
        'Gris': { navbar_bg:'#374151', navbar_text:'#f9fafb', menu_bg:'#1f2937', menu_text:'#d1d5db', highlight_bg:'#6b7280', highlight_text:'#ffffff', btn_bg:'#6b7280', btn_text:'#ffffff' },
        'Verde': { navbar_bg:'#10b981', navbar_text:'#053b2c', menu_bg:'#064e3b', menu_text:'#d1fae5', highlight_bg:'#059669', highlight_text:'#ffffff', btn_bg:'#10b981', btn_text:'#053b2c' },
        'Rojo': { navbar_bg:'#ef4444', navbar_text:'#ffffff', menu_bg:'#7f1d1d', menu_text:'#fecaca', highlight_bg:'#dc2626', highlight_text:'#ffffff', btn_bg:'#ef4444', btn_text:'#ffffff' },
        'Naranja':{ navbar_bg:'#f97316', navbar_text:'#1f2937', menu_bg:'#7c2d12', menu_text:'#ffedd5', highlight_bg:'#ea580c', highlight_text:'#1f2937', btn_bg:'#f97316', btn_text:'#1f2937' },
        'Morado': { navbar_bg:'#8b5cf6', navbar_text:'#0f172a', menu_bg:'#4c1d95', menu_text:'#ede9fe', highlight_bg:'#7c3aed', highlight_text:'#0f172a', btn_bg:'#8b5cf6', btn_text:'#0f172a' },
        'Marrón': { navbar_bg:'#92400e', navbar_text:'#fef3c7', menu_bg:'#431407', menu_text:'#fde68a', highlight_bg:'#b45309', highlight_text:'#fef3c7', btn_bg:'#92400e', btn_text:'#fef3c7' },
    };
    function applyPreset(name){
        const p = presets[name];
        if(!p) return;
        for(const [k,v] of Object.entries(p)){
            const input = document.querySelector(`[name="color_${k}"]`);
            if(input){ input.value = v; input.dispatchEvent(new Event('input',{bubbles:true})); }
        }
        const sel = document.getElementById('theme_name');
        if(sel) sel.value = name;
    }

    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-apply-preset');
        if(!btn) return;
        applyPreset(btn.getAttribute('data-preset'));
    });

    // Reset a colores de fábrica con un form oculto (evita forms anidados)
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-reset-theme');
        if(!btn) return;
        if(!confirm('¿Restablecer colores de fábrica?')) return;
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = @json(route('configuraciones.tema.reset'));
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = @json(csrf_token());
        f.appendChild(token);
        document.body.appendChild(f);
        f.submit();
    });

    const sel = document.getElementById('theme_name');
    if(sel){ sel.addEventListener('change', (e)=> applyPreset(e.target.value)); }

    // Fallback para tabs si Bootstrap JS no está activo
    document.addEventListener('click', function(e){
        const link = e.target.closest('a.nav-link');
        if(!link) return;
        const href = link.getAttribute('href');
        if(!href || !href.startsWith('#')) return;
        const pane = document.querySelector(href);
        if(!pane) return;
        e.preventDefault();
        // desactivar todos
        document.querySelectorAll('#themeTabs a.nav-link').forEach(a => a.classList.remove('active'));
        document.querySelectorAll('#themeTabsContent .tab-pane').forEach(p => p.classList.remove('show','active'));
        // activar el elegido
        link.classList.add('active');
        pane.classList.add('show','active');
    });
})();
</script>
@endpush
@stop
