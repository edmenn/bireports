<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Logo de login (PNG/JPG/SVG)</label>
        <input type="file" name="login_logo" accept="image/png,image/jpeg,image/svg+xml" class="form-control @error('login_logo') is-invalid @enderror">
        @error('login_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if($config->login_logo_path ?? false)
            <div class="mt-2">
                <small class="text-muted d-block">Actual:</small>
                <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="Login Logo" style="max-height:60px">
            </div>
        @endif
    </div>
    <div class="col-md-8">
        <label class="form-label">Imagen de fondo (pantalla completa)</label>
        <input type="file" name="login_bg" accept="image/png,image/jpeg" class="form-control @error('login_bg') is-invalid @enderror">
        @error('login_bg')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <small class="text-muted">Se ajustará a cubrir toda la pantalla en la página de login.</small>
    </div>
    <div class="col-md-4">
        @if($config->login_bg_path ?? false)
            <small class="text-muted d-block">Vista previa:</small>
            <img class="border" src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="Login BG" style="max-width:100%; max-height:120px; object-fit:cover;">
        @endif
    </div>
</div>

