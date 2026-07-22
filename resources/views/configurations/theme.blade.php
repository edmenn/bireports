@extends('layouts.app')

@section('title', __('config_theme.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
@endsection

@section('content_header')
    <x-page-header :title="__('settings.menu.theme')" :subtitle="__('config_theme.title')" icon="fas fa-palette" />
@stop

@section('content')
    {{-- Alertas --}}
    @php($savedPresets = $savedPresets ?? collect())
    @php($builtinPresets = $builtinPresets ?? [])
    @php($savedPresetMap = $savedPresets->mapWithKeys(fn($preset) => [$preset->name => $preset->config ?? []])->all())
    @php($selectedThemeName = old('theme_name', $config->theme_name))
    @php($savedPresetIds = $savedPresets->mapWithKeys(fn($preset) => [$preset->name => $preset->id])->all())
    @php($selectedPresetId = old('theme_preset_id', $selectedThemeName && isset($savedPresetIds[$selectedThemeName]) ? $savedPresetIds[$selectedThemeName] : null))
    @if(session('success'))
        <x-ui-alert theme="success" title="{{ __('settings.alerts.success') }}" dismissable>
            {{ session('success') }}
        </x-ui-alert>
    @endif
    @if ($errors->any())
        <x-ui-alert theme="danger" title="{{ __('settings.alerts.validation') }}" dismissable>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui-alert>
    @endif

    <form method="POST" action="{{ route('configurations.update', $config->id) }}" enctype="multipart/form-data" novalidate id="theme-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ url()->current() }}">

        <div class="portal-card mt-3">
            <div class="card-header">
                <span class="portal-icon">
                    <i class="fas fa-swatchbook"></i>
                </span>
                <div>
                    <h5 class="mb-0">{{ __('config_theme.title') }}</h5>
                    <small class="text-muted">{{ __('settings.menu.theme') }}</small>
                </div>
            </div>

            <div class="card-body">
                @php($active = request('tab', 'pagina'))
                <ul class="nav nav-tabs portal-theme-tabs border-0 mb-4" id="themeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $active === 'pagina' ? 'active' : '' }}" id="tab-pagina" data-toggle="tab" href="#pane-pagina" role="tab" aria-controls="pane-pagina" aria-selected="{{ $active === 'pagina' ? 'true' : 'false' }}">
                            <i class="fas fa-desktop mr-1"></i> {{ __('config_theme.tabs.page') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $active === 'login' ? 'active' : '' }}" id="tab-login" data-toggle="tab" href="#pane-login" role="tab" aria-controls="pane-login" aria-selected="{{ $active === 'login' ? 'true' : 'false' }}">
                            <i class="fas fa-sign-in-alt mr-1"></i> {{ __('config_theme.tabs.login') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="themeTabsContent">

                    {{-- ============================================= --}}
                    {{--            CONTENIDO: TEMA PRINCIPAL          --}}
                    {{-- ============================================= --}}
                    <div class="tab-pane fade {{ $active === 'pagina' ? 'show active' : '' }}" id="pane-pagina" role="tabpanel" aria-labelledby="tab-pagina">

                        {{-- Configuración General (mantengo dos campos lado a lado en pantallas grandes) --}}
                        <x-ui-card title="{{ __('config_theme.cards.general.title') }}" icon="fas fa-cog text-lightblue" class="mb-3">
                            <div class="form-row">
                                <div class="form-group col-12 col-lg-6">
                                    <x-ui-input name="site_title" label="{{ __('config_theme.cards.general.fields.site_title.label') }}" placeholder="{{ __('config_theme.cards.general.fields.site_title.placeholder') }}" fgroup-class="mb-3" value="{{ old('site_title', $config->site_title) }}">
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.general.fields.site_title.help') }}</small></x-slot>
                                    </x-ui-input>
                                    <small class="text-muted d-inline-block ms-1">
                                        <i class="far fa-question-circle" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.general.fields.site_title.tooltip') }}"></i>
                                    </small>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <x-ui-select name="theme_name" id="theme_name" label="{{ __('config_theme.cards.general.fields.theme_name.label') }}">
                                        <option value="" @selected($selectedThemeName === null || $selectedThemeName === '')> {{ __('config_theme.cards.general.fields.theme_name.default') }}</option>
                                        @if($savedPresets->isNotEmpty())
                                            @foreach($savedPresets as $preset)
                                                <option value="{{ $preset->name }}" data-preset-id="{{ $preset->id }}" @selected($selectedThemeName === $preset->name)>{{ $preset->name }}</option>
                                            @endforeach
                                        @endif
                                        @if($selectedThemeName === 'Personalizado')
                                            <option value="Personalizado" selected> {{ __('config_theme.cards.general.fields.theme_name.custom') }}</option>
                                        @endif
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.general.fields.theme_name.help') }}</small></x-slot>
                                     </x-ui-select>
                                    <input type="hidden" name="theme_preset_id" id="theme_preset_id" value="{{ $selectedPresetId }}">
                                    <small class="text-muted d-inline-block ms-1">
                                        <i class="far fa-question-circle" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.general.fields.theme_name.tooltip') }}"></i>
                                    </small>
                                 </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="js-delete-preset" data-route="{{ route('configurations.theme.presets.destroy', ['preset' => '__PRESET__']) }}" disabled>
                                        <i class="fas fa-trash"></i> {{ __('config_theme.cards.general.fields.theme_name.delete') }}
                                    </button>
                                </div>
                            </div>
                        </x-ui-card>

                        {{-- Logos y Favicon (grilla 3→2→1 columnas) --}}
                        <x-ui-card title="{{ __('config_theme.cards.branding.title') }}" icon="far fa-images text-lightblue" class="mb-3">
                            <div class="grid-logos">
                                {{-- Logo principal --}}
                                <div class="logo-block">
                                    <x-ui-input-file name="site_logo" label="{{ __('config_theme.cards.branding.fields.site_logo.label') }}" placeholder="{{ __('config_theme.cards.branding.fields.site_logo.placeholder') }}" accept="image/png,image/jpeg">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.branding.fields.site_logo.help') }}
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.branding.fields.site_logo.tooltip') }}"></i>
                                        </small></x-slot>
                                    </x-ui-input-file>
                                    @php($siteLogoW = (int) (old('site_logo_width', $config->site_logo_width ?: 120)))
                                    @php($siteLogoH = (int) (old('site_logo_height', $config->site_logo_height ?: 40)))
                                    @if($config->site_logo_path)
                                        <img id="site-logo-preview" src="{{ Storage::disk('public')->url($config->site_logo_path) }}" alt="{{ __('config_theme.cards.branding.fields.site_logo.preview_alt') }}" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoH }}px;">
                                    @else
                                        <img id="site-logo-preview" src="" alt="{{ __('config_theme.cards.branding.fields.site_logo.preview_alt') }}" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoH }}px; display:none;">
                                    @endif
                                    <div class="d-flex align-items-center gap-2 mt-2" style="max-width: 260px;">
                                        <x-ui-input name="site_logo_width" label="{{ __('settings.login.cards.size.width_label') }}" type="number" min="16" max="256" fgroup-class="mb-0" value="{{ $siteLogoW }}"/>
                                        <x-ui-input name="site_logo_height" label="{{ __('settings.login.cards.size.height_label') }}" type="number" min="16" max="128" fgroup-class="mb-0" value="{{ $siteLogoH }}"/>
                                    </div>
                                </div>

                                {{-- Icono de Preloader (centro al refrescar) --}}
                                <div class="logo-block">
                                    <x-ui-input-file name="preloader_logo" label="{{ __('config_theme.cards.branding.fields.preloader_logo.label') }}" placeholder="{{ __('config_theme.cards.branding.fields.preloader_logo.placeholder') }}" accept="image/png,image/jpeg">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.branding.fields.preloader_logo.help') }}</small></x-slot>
                                    </x-ui-input-file>

                                    <div class="d-flex align-items-end gap-2">
                                        <div>
                                            @php($pw = (int) (old('preloader_logo_width', $config->preloader_logo_width ?: 60)))
                                            @php($ph = (int) (old('preloader_logo_height', $config->preloader_logo_height ?: 60)))
                                            <label class="form-label mb-1">{{ __('config_theme.cards.branding.fields.preloader_logo.size_label') }}</label>
                                            <div class="d-flex align-items-center" style="gap:.5rem; max-width: 260px;">
                                                <input type="number" name="preloader_logo_width" class="form-control" min="16" max="512" step="1" value="{{ $pw }}" style="width:100px" />
                                                <span class="text-muted">×</span>
                                                <input type="number" name="preloader_logo_height" class="form-control" min="16" max="512" step="1" value="{{ $ph }}" style="width:100px" />
                                            </div>
                                            <small class="text-muted">{{ __('config_theme.cards.branding.fields.preloader_logo.hint') }}</small>
                                        </div>
                                    </div>

                                    @if($config->preloader_logo_path)
                                        <img id="preloader-logo-preview" src="{{ Storage::disk('public')->url($config->preloader_logo_path) }}" alt="{{ __('config_theme.cards.branding.fields.preloader_logo.preview_alt') }}" class="img-thumbnail mt-2" style="height: {{ $ph }}px; width: {{ $pw }}px;">
                                    @else
                                        <img id="preloader-logo-preview" src="" alt="{{ __('config_theme.cards.branding.fields.preloader_logo.preview_alt') }}" class="img-thumbnail mt-2" style="height: {{ $ph }}px; width: {{ $pw }}px; display:none;">
                                    @endif
                                </div>

                                {{-- Logo reducido --}}
                                <div class="logo-block">
                                    <x-ui-input-file name="site_logo_compact" label="{{ __('config_theme.cards.branding.fields.site_logo_compact.label') }}" placeholder="{{ __('config_theme.cards.branding.fields.site_logo_compact.placeholder') }}" accept="image/png,image/jpeg">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.branding.fields.site_logo_compact.help') }}
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.branding.fields.site_logo_compact.tooltip') }}"></i>
                                        </small></x-slot>
                                    </x-ui-input-file>
                                    @php($siteLogoCW = (int) (old('site_logo_compact_width', $config->site_logo_compact_width ?: 40)))
                                    @php($siteLogoCH = (int) (old('site_logo_compact_height', $config->site_logo_compact_height ?: 40)))
                                    @if($config->site_logo_compact_path)
                                        <img id="site-logo-compact-preview" src="{{ Storage::disk('public')->url($config->site_logo_compact_path) }}" alt="{{ __('config_theme.cards.branding.fields.site_logo_compact.preview_alt') }}" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoCH }}px;">
                                    @else
                                        <img id="site-logo-compact-preview" src="" alt="{{ __('config_theme.cards.branding.fields.site_logo_compact.preview_alt') }}" class="img-thumbnail mt-1" style="max-height: {{ $siteLogoCH }}px; display:none;">
                                    @endif
                                    <div class="d-flex align-items-center gap-2 mt-2" style="max-width: 260px;">
                                        <x-ui-input name="site_logo_compact_width" label="{{ __('settings.login.cards.size.width_label') }}" type="number" min="16" max="256" fgroup-class="mb-0" value="{{ $siteLogoCW }}"/>
                                        <x-ui-input name="site_logo_compact_height" label="{{ __('settings.login.cards.size.height_label') }}" type="number" min="16" max="128" fgroup-class="mb-0" value="{{ $siteLogoCH }}"/>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="logo-block">
                                    <x-ui-input-file name="site_favicon" label="{{ __('config_theme.cards.branding.fields.site_favicon.label') }}" placeholder="{{ __('config_theme.cards.branding.fields.site_favicon.placeholder') }}" accept="image/x-icon,image/png">
                                        <x-slot name="prependSlot"><div class="input-group-text bg-lightblue"><i class="fas fa-upload"></i></div></x-slot>
                                        <x-slot name="bottom"><small class="text-muted">{{ __('config_theme.cards.branding.fields.site_favicon.help') }}
                                            <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.branding.fields.site_favicon.tooltip') }}"></i>
                                        </small></x-slot>
                                    </x-ui-input-file>
                                    @if($config->site_favicon_path)
                                        <img id="site-favicon-preview" src="{{ Storage::disk('public')->url($config->site_favicon_path) }}" alt="{{ __('config_theme.cards.branding.fields.site_favicon.preview_alt') }}" class="img-thumbnail mt-1" style="height: 32px; width: 32px;">
                                    @else
                                        <img id="site-favicon-preview" src="" alt="{{ __('config_theme.cards.branding.fields.site_favicon.preview_alt') }}" class="img-thumbnail mt-1" style="height: 32px; width: 32px; display:none;">
                                    @endif
                                </div>
                            </div>
                        </x-ui-card>

                        {{-- Paleta de colores (sub-secciones en 2 columnas, auto-wrap) --}}
                        <x-ui-card title="{{ __('config_theme.cards.palette.title') }}" icon="fas fa-palette text-lightblue" class="mb-1">
                            @php($t = is_array($config->theme_config) ? $config->theme_config : (is_string($config->theme_config) ? json_decode($config->theme_config, true) : []))

                            <div class="grid-colors-2">
                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-square mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.base.title') }}
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.palette.groups.base.tooltip') }}"></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.base.fields.main_bg') }}</span><input type="color" name="color_main_bg" value="{{ $t['main_bg'] ?? '#f4f6f9' }}" class="color-item__swatch js-color" data-var="--app-main-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.base.fields.main_text') }}</span><input type="color" name="color_main_text" value="{{ $t['main_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-main-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-window-maximize mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.navbar.title') }}
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.palette.groups.navbar.tooltip') }}"></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.background') }}</span><input type="color" name="color_navbar_bg" value="{{ $t['navbar_bg'] ?? '#374151' }}" class="color-item__swatch js-color" data-var="--app-navbar-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.text') }}</span><input type="color" name="color_navbar_text" value="{{ $t['navbar_text'] ?? '#f9fafb' }}" class="color-item__swatch js-color" data-var="--app-navbar-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-columns mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.sidebar.title') }}
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.palette.groups.sidebar.tooltip') }}"></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.background') }}</span><input type="color" name="color_menu_bg" value="{{ $t['menu_bg'] ?? '#1f2d37' }}" class="color-item__swatch js-color" data-var="--app-menu-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.text') }}</span><input type="color" name="color_menu_text" value="{{ $t['menu_text'] ?? '#d1d5db' }}" class="color-item__swatch js-color" data-var="--app-menu-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="far fa-clone mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.content.title') }}
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.palette.groups.content.tooltip') }}"></i>
                                    </div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.content.fields.card_background') }}</span><input type="color" name="color_container_bg" value="{{ $t['container_bg'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-card-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.content.fields.header_background') }}</span><input type="color" name="color_container_alt_bg" value="{{ $t['container_alt_bg'] ?? '#f8fafc' }}" class="color-item__swatch js-color" data-var="--app-card-alt-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.text') }}</span><input type="color" name="color_container_text" value="{{ $t['container_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-card-text"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-highlighter mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.accents.title') }}
                                        <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip" title="{{ __('config_theme.cards.palette.groups.accents.tooltip') }}"></i>
                                    </div>
                                    <div class="color-grid" style="grid-template-columns: repeat(2, 1fr);">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.highlight') }}</span><input type="color" name="color_highlight_bg" value="{{ $t['highlight_bg'] ?? '#6b7280' }}" class="color-item__swatch js-color" data-var="--app-hi-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.highlight_text') }}</span><input type="color" name="color_highlight_text" value="{{ $t['highlight_text'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-hi-text"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.button') }}</span><input type="color" name="color_btn_bg" value="{{ $t['btn_bg'] ?? '#6b7280' }}" class="color-item__swatch js-color" data-var="--app-btn-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.button_text') }}</span><input type="color" name="color_btn_text" value="{{ $t['btn_text'] ?? '#ffffff' }}" class="color-item__swatch js-color" data-var="--app-btn-text"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.button_hover') }}</span><input type="color" name="color_btn_hover" value="{{ $t['btn_hover'] ?? '#4b5563' }}" class="color-item__swatch js-color" data-var="--app-btn-hover"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.groups.accents.fields.button_active') }}</span><input type="color" name="color_btn_active" value="{{ $t['btn_active'] ?? '#374151' }}" class="color-item__swatch js-color" data-var="--app-btn-active"></label>
                                    </div>
                                </div>

                                <div class="color-card">
                                    <div class="color-card__header"><i class="fas fa-icons mr-1 text-muted"></i> {{ __('config_theme.cards.palette.groups.icons.title') }}</div>
                                    <div class="color-grid">
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.background') }}</span><input type="color" name="color_icon_bg" value="{{ $t['icon_bg'] ?? '#e5e7eb' }}" class="color-item__swatch js-color" data-var="--app-icon-bg"></label>
                                        <label class="color-item"><span>{{ __('config_theme.cards.palette.common.text') }}</span><input type="color" name="color_icon_text" value="{{ $t['icon_text'] ?? '#111111' }}" class="color-item__swatch js-color" data-var="--app-icon-text"></label>
                                    </div>
                                </div>
                            </div>
                        </x-ui-card>
                    </div>

                    {{-- ============================================= --}}
                    {{--              CONTENIDO: TEMA LOGIN            --}}
                    {{-- ============================================= --}}
                    <div class="tab-pane fade {{ $active === 'login' ? 'show active' : '' }}" id="pane-login" role="tabpanel" aria-labelledby="tab-login">

                        <x-ui-card title="{{ __('settings.login.cards.uploads.title') }}" icon="fas fa-upload text-lightblue" class="mb-3">
                            <div class="d-flex flex-column">
                                <x-ui-input-file name="login_logo" label="{{ __('settings.login.cards.uploads.fields.logo.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.logo.placeholder') }}" accept="image/png,image/jpeg"/>
                                <x-ui-input-file name="login_bg" label="{{ __('settings.login.cards.uploads.fields.background.label') }}" placeholder="{{ __('settings.login.cards.uploads.fields.background.placeholder') }}" accept="image/png,image/jpeg">
                                    <x-slot name="bottom">
                                        <small class="text-muted">{{ __('settings.login.cards.uploads.fields.background.help') }}</small>
                                    </x-slot>
                                </x-ui-input-file>
                            </div>
                        </x-ui-card>

                        <x-ui-card title="{{ __('settings.login.cards.preview.title') }}" icon="fas fa-eye text-lightblue" class="mb-1">
                            @if($config->login_logo_path ?? false)
                                <div class="mb-3">
                                    <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.logo_heading') }}</p>
                                    <div class="p-2 border rounded text-center bg-light">
                                         <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="{{ __('settings.login.cards.preview.logo_alt') }}" style="max-height:90px; max-width: 100%;" id="logo-preview">
                                    </div>
                                </div>
                            @else
                                <div class="text-muted text-center py-5">
                                    {{ __('settings.login.cards.preview.no_logo') }}
                                    <img src="" alt="{{ __('settings.login.cards.preview.logo_alt') }}" style="max-height:90px; max-width: 100%; display: none;" id="logo-preview" class="p-2 border rounded text-center bg-light mt-2">
                                </div>
                            @endif

                            @if($config->login_bg_path ?? false)
                                <div class="mt-2">
                                    <p class="mb-1 text-secondary">{{ __('settings.login.cards.preview.background_heading') }}</p>
                                    <div class="border rounded">
                                        <img src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="{{ __('settings.login.cards.preview.background_alt') }}" style="width: 100%; height: 180px; object-fit: cover;" id="bg-preview">
                                    </div>
                                </div>
                            @else
                                <div class="text-muted text-center py-5 @if($config->login_logo_path ?? false) border-top mt-3 pt-3 @endif">
                                    {{ __('settings.login.cards.preview.no_background') }}
                                    <img src="" alt="{{ __('settings.login.cards.preview.background_alt') }}" style="width: 100%; height: 180px; object-fit: cover; display: none;" id="bg-preview" class="border rounded mt-2">
                                </div>
                            @endif
                        </x-ui-card>

                        <x-ui-card title="{{ __('settings.login.cards.size.title') }}" icon="fas fa-ruler-combined text-lightblue" class="mb-3">
                            @php($t = is_array($config->theme_config) ? $config->theme_config : (is_string($config->theme_config) ? json_decode($config->theme_config, true) : []))
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <x-ui-input name="login_logo_width" type="number" min="16" max="512" label="{{ __('settings.login.cards.size.width_label') }}" value="{{ max(16, (int) old('login_logo_width', $t['login_logo_width'] ?? 180)) }}"/>
                                </div>
                                <div class="col-md-3">
                                    <x-ui-input name="login_logo_height" type="number" min="16" max="512" label="{{ __('settings.login.cards.size.height_label') }}" value="{{ max(16, (int) old('login_logo_height', $t['login_logo_height'] ?? 64)) }}"/>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <small class="text-muted">{{ __('settings.login.cards.size.hint') }}</small>
                                </div>
                            </div>
                        </x-ui-card>
                    </div>

                </div>
            </div>
        </div>

        <x-ui-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="theme-form-actions">
                    <x-ui-button type="submit" theme="primary" label="{{ __('settings.actions.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <button type="button" class="btn btn-outline-primary btn-eq" data-toggle="modal" data-target="#presetSaveModal">
                        <i class="fas fa-clone"></i> {{ __('config_theme.buttons.save_as') }}
                    </button>
                    <button type="button" id="js-cancel-changes" class="btn btn-secondary btn-eq" onclick="window.history.back()">
                        <i class="fas fa-times"></i> {{ __('settings.actions.cancel') }}
                    </button>
                </div>
            </div>
        </x-ui-card>
            </div>
        </div>
    </form>
    <div class="modal fade" id="presetSaveModal" tabindex="-1" role="dialog" aria-labelledby="presetSaveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="preset-save-form" method="POST" action="{{ route('configurations.theme.presets.store') }}">
                    @csrf
                    <input type="hidden" name="tab" value="{{ request('tab', 'pagina') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="presetSaveModalLabel">{{ __('config_theme.modal.save.title') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('config_theme.modal.save.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="preset-name-input">{{ __('config_theme.modal.save.name_label') }}</label>
                            <input type="text" class="form-control" id="preset-name-input" name="preset_name" maxlength="50" required autocomplete="off" placeholder="{{ __('config_theme.modal.save.name_placeholder') }}">
                            <small class="text-muted">{{ __('config_theme.modal.save.name_help') }}</small>
                        </div>
                    </div>
                    <div id="preset-hidden-fields" class="d-none"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('settings.actions.cancel') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('settings.actions.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form id="preset-delete-form" method="POST" class="d-none" data-route="{{ route('configurations.theme.presets.destroy', ['preset' => '__PRESET__']) }}">
        @csrf
        @method('DELETE')
        <input type="hidden" name="tab" value="{{ request('tab', 'pagina') }}">
    </form>
@stop

@push('css')
<style>
/* ====== Ajustes para tabs y grillas ====== */

#theme_name.form-control{
    font-size: .9rem;
    line-height: 1.35;
}
#theme_name.form-control option{
    font-size: .9rem;
}

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
.logo-block .img-thumbnail{ max-width: 100%; max-height: 96px; object-fit: contain; }
.logo-block{ min-width: 0; overflow: hidden; }
.logo-block .d-flex[style*="max-width"]{ max-width: none !important; gap: .5rem; }
.logo-block .d-flex[style*="max-width"] > *{ flex: 1 1 0; min-width: 0; }
.logo-block #preloader-logo-preview{ max-height: 96px; max-width: 100%; object-fit: contain; }
.upload-feedback{ margin-top:.35rem; font-size:.82rem; }

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
    background-color: var(--app-card-alt-bg); border: 1px solid var(--bs-border-color); border-radius: .75rem;
    padding: .75rem;
}
.color-card__header {
    font-weight: 600; color: var(--app-card-text); margin-bottom: .75rem; font-size: .9rem;
    text-transform: uppercase; letter-spacing: .05em;
}
.color-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .5rem;
}
.color-item {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    background-color: var(--app-card-bg); border: 1px solid var(--bs-border-color); border-radius: .5rem;
    padding: .35rem .6rem; font-size: .875rem; color: var(--app-card-text);
    transition: all 0.2s ease-in-out;
}
.color-item:hover {
    box-shadow: 0 0 0 1px var(--app-highlight-bg); border-color: var(--app-highlight-bg);
}
.theme-form-actions{ display:flex; flex-wrap:wrap; gap:.75rem; width:100%; max-width:640px; }
.theme-form-actions .btn{ flex:1 1 160px; white-space:nowrap; }
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
        const colorInputs = Array.from(document.querySelectorAll('.js-color'));

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
            icon_bg:'#e5e7eb',
            icon_text:'#111111',
            highlight_bg:'#6b7280',
            highlight_text:'#ffffff',
            btn_bg:'#6b7280',
            btn_text:'#ffffff',
            btn_hover:'#4b5563',
            btn_active:'#374151',
        };

        const presetsBase = {};
        const savedPresetConfigs = @json($savedPresetMap);
        const savedPresetIds = @json($savedPresetIds);
        const presets = Object.assign({}, presetsBase, savedPresetConfigs || {});

        // Personalizado <-> Preset
        const CUSTOM_VALUE = 'Personalizado';
        let applyingPreset = false;
        let customDirty = false;
        const themeNameSelect = document.getElementById('theme_name');
        let lastThemeSelection = themeNameSelect ? themeNameSelect.value : '';
        let customPalette = null;
        const presetIdInput = document.getElementById('theme_preset_id');
        const deleteBtn = document.getElementById('js-delete-preset');
        const deleteForm = document.getElementById('preset-delete-form');

        function getSelectedPresetId() {
            if (!themeNameSelect) return null;
            const value = themeNameSelect.value;
            if (!value || value === CUSTOM_VALUE) return null;
            if (savedPresetIds && Object.prototype.hasOwnProperty.call(savedPresetIds, value)) {
                return savedPresetIds[value];
            }
            const currentOption = themeNameSelect.options[themeNameSelect.selectedIndex] ?? null;
            if (currentOption && currentOption.dataset && currentOption.dataset.presetId) {
                return currentOption.dataset.presetId;
            }
            return null;
        }

        function updatePresetControls() {
            const presetId = getSelectedPresetId();
            if (presetIdInput) {
                presetIdInput.value = presetId ? presetId : '';
            }
            if (deleteBtn) {
                deleteBtn.disabled = !presetId;
                deleteBtn.dataset.presetId = presetId ? String(presetId) : '';
            }
        }

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
        function collectCurrentPalette() {
            const data = {};
            colorInputs.forEach(input => {
                const key = (input.name || '').replace(/^color_/,'');
                if (key) data[key] = input.value;
            });
            return data;
        }

        function updatePreviewFromInputs() {
            colorInputs.forEach(input => {
                const cssVar = input.dataset.var;
                if (cssVar) {
                    document.documentElement.style.setProperty(cssVar, input.value);
                }
            });
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
                customPalette = collectCurrentPalette();
                const presetId = getSelectedPresetId();
                if (!presetId) {
                    ensureCustomOption();
                    if (themeNameSelect && themeNameSelect.value !== CUSTOM_VALUE) {
                        themeNameSelect.value = CUSTOM_VALUE;
                        lastThemeSelection = CUSTOM_VALUE;
                    }
                }
                updatePresetControls();
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
            customPalette = collectCurrentPalette();
            updatePreviewFromInputs();
            const sel = document.getElementById('theme_name');
            if(sel && sel.value !== name) sel.value = name;
            customDirty = false;
            lastThemeSelection = name || '';
            updatePresetControls();
        }
        if (themeNameSelect) {
            themeNameSelect.addEventListener('change', function(event) {
                const selectedPreset = event.target.value;
                if (selectedPreset === CUSTOM_VALUE) {
                    if (!customPalette) {
                        event.target.value = lastThemeSelection || '';
                        return;
                    }
                    applyPresetValues(customPalette);
                    customPalette = collectCurrentPalette();
                    customDirty = false;
                    lastThemeSelection = CUSTOM_VALUE;
                    return;
                }
                if (selectedPreset) {
                    applyPreset(selectedPreset);
                } else {
                    // Predeterminado
                    applyPresetValues(defaultPreset);
                    customPalette = collectCurrentPalette();
                    updatePreviewFromInputs();
                    customDirty = false;
                    lastThemeSelection = '';
                }
                updatePresetControls();
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
                        customDirty = false;
                        customPalette = customPalette && Object.keys(customPalette).length ? customPalette : null;
                        if (themeNameSelect.value === '' || themeNameSelect.value === CUSTOM_VALUE) {
                            themeNameSelect.value = '';
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
                        customPalette = customPalette && Object.keys(customPalette).length ? customPalette : null;
                        lastThemeSelection = matched;
                    } else {
                        // No coincide con ningún preset → Personalizado
                        ensureCustomOption();
                        themeNameSelect.value = CUSTOM_VALUE;
                        customDirty = false;
                        customPalette = current;
                        lastThemeSelection = CUSTOM_VALUE;
                    }
                    updatePresetControls();
                } catch(_) {}
            })();
        } else {
            customPalette = collectCurrentPalette();
        }

        updatePreviewFromInputs();
        updatePresetControls();

        const uploadRules = {
            site_logo: { maxMb: 2, types: ['image/png', 'image/jpeg'] },
            preloader_logo: { maxMb: 1, types: ['image/png', 'image/jpeg'] },
            site_logo_compact: { maxMb: 1, types: ['image/png', 'image/jpeg'] },
            site_favicon: { maxMb: 1, types: ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'] },
            login_logo: { maxMb: 2, types: ['image/png', 'image/jpeg'] },
            login_bg: { maxMb: 2, types: ['image/png', 'image/jpeg'] },
        };
        const uploadMessages = {
            size: @json(__('config_theme.errors.file_too_large')),
            type: @json(__('config_theme.errors.invalid_file_type')),
            image: @json(__('config_theme.errors.invalid_image')),
        };

        function setupImagePreview(inputId, previewId) {
            const inputElement = document.querySelector(`input[name="${inputId}"]`);
            const previewElement = document.getElementById(previewId);
            if (!inputElement || !previewElement) return;

            const feedback = document.createElement('div');
            feedback.className = 'upload-feedback text-danger';
            feedback.setAttribute('role', 'alert');
            feedback.setAttribute('aria-live', 'polite');
            inputElement.closest('.form-group')?.appendChild(feedback);

            inputElement.addEventListener('change', function(event) {
                const file = event.target.files[0];
                const rule = uploadRules[inputId];
                feedback.textContent = '';
                inputElement.setCustomValidity('');
                if (!file) return;

                let message = '';
                if (rule && file.size > rule.maxMb * 1024 * 1024) {
                    message = uploadMessages.size.replace(':max', String(rule.maxMb));
                } else if (rule && !rule.types.includes(file.type)) {
                    message = uploadMessages.type;
                }
                if (message) {
                    feedback.textContent = message;
                    inputElement.setCustomValidity(message);
                    inputElement.value = '';
                    inputElement.reportValidity();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const probe = new Image();
                    probe.onload = function () {
                        previewElement.src = e.target.result;
                        previewElement.style.display = 'block';
                        const cont = previewElement.parentElement;
                        if (cont && cont.classList && cont.classList.contains('text-muted')) {
                            Array.from(cont.childNodes).forEach(n => { if(n.nodeType === Node.TEXT_NODE) n.textContent = ''; });
                        }
                    };
                    probe.onerror = function () {
                        feedback.textContent = uploadMessages.image;
                        inputElement.setCustomValidity(uploadMessages.image);
                        inputElement.value = '';
                    };
                    probe.src = e.target.result;
                };
                reader.onerror = function () {
                    feedback.textContent = uploadMessages.image;
                    inputElement.setCustomValidity(uploadMessages.image);
                    inputElement.value = '';
                };
                reader.readAsDataURL(file);
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

        const presetSaveForm = document.getElementById('preset-save-form');
        if (presetSaveForm) {
            presetSaveForm.addEventListener('submit', function () {
                const hiddenContainer = document.getElementById('preset-hidden-fields');
                if (!hiddenContainer) {
                    return;
                }
                hiddenContainer.innerHTML = '';

                const appendHidden = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value ?? '';
                    hiddenContainer.appendChild(input);
                };

                colorInputs.forEach(input => appendHidden(input.name, input.value));
                ['login_logo_width', 'login_logo_height'].forEach(fieldName => {
                    const el = document.querySelector(`[name="${fieldName}"]`);
                    if (el) appendHidden(fieldName, el.value);
                });
            });

            const presetModal = document.getElementById('presetSaveModal');
            if (presetModal) {
                presetModal.addEventListener('shown.bs.modal', () => {
                    const input = document.getElementById('preset-name-input');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                });
                presetModal.addEventListener('hidden.bs.modal', () => {
                    presetSaveForm.reset();
                    const hiddenContainer = document.getElementById('preset-hidden-fields');
                    if (hiddenContainer) {
                        hiddenContainer.innerHTML = '';
                    }
                });
            }
        }

        if (deleteBtn && deleteForm) {
            deleteBtn.addEventListener('click', function () {
                const presetId = deleteBtn.dataset.presetId;
                if (!presetId) {
                    return;
                }
                if (!confirm('{{ __('config_theme.modal.delete_confirm') }}')) {
                    return;
                }
                const routeTemplate = deleteForm.dataset.route || '';
                deleteForm.action = routeTemplate.replace('__PRESET__', presetId);
                deleteForm.submit();
            });
        }
    });
})();
</script>
@endpush
