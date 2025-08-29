{{-- resources/views/errors/friendly.blade.php (autónoma, sin layout, con CDN) --}}
@php
    // Variables provistas por bootstrap/app.php
    $http   = $status   ?? 500;
    $code   = $code     ?? $http;
    $msg    = $message  ?? 'Ocurrió un error.';
    $debugT = $debug    ?? null;
    // NO usamos route(), solo URLs seguras para evitar RouteNotFoundException
    $back   = $back_url ?? url()->previous() ?? url('/');
    $home   = url('/');         // inicio seguro
    $login  = url('/login');    // login genérico (no rompe si no existe la ruta con nombre)
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error (HTTP {{ $http }})</title>

    {{-- AdminLTE 3 + deps por CDN (evitamos assets locales) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" integrity="sha384-f0b1rXv0f8uZ8Zy6X2mVt4d7V4G8k7m0m2NQyW3D4Q9GQyW3Y3bJ8Z0R3jv5v3bV" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15/css/all.min.css" integrity="sha384-7uj0Qz2R3XcC2y0l2+UQy8f3W3b9Xl1E1p7Vx7Ww8uX8kGz6D9JZ0N3h4QWzG3b1" crossorigin="anonymous">
    {{-- Fuente opcional para look AdminLTE --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=swap" rel="stylesheet">

    <style>
        html, body { height: 100%; }
        body { background: #f4f6f9; font-family: 'Source Sans Pro', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; }
        .error-wrapper { min-height: 100%; display: flex; flex-direction: column; }
        .error-header { background: #fff; border-bottom: 1px solid #dee2e6; }
        .error-content { flex: 1 1 auto; }
        .container-max { max-width: 980px; }
        pre.debug { white-space: pre-wrap; max-height: 55vh; overflow: auto; font-size: .875rem; line-height: 1.25rem; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: .25rem; padding: .75rem; }
        .navbar-brand { font-weight: 600; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="error-wrapper">

    {{-- Header simple (sin parciales ni llamadas a route()) --}}
    <nav class="error-header navbar navbar-expand navbar-white navbar-light">
        <div class="container container-max">
            <a class="navbar-brand" href="{{ $home }}">
                <i class="fas fa-th-large mr-2"></i> PowerBIDash
            </a>
            <div class="ml-auto">
                <a href="{{ $back }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <a href="{{ $home }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-home mr-1"></i> Inicio
                </a>
            </div>
        </div>
    </nav>

    {{-- Contenido --}}
    <section class="error-content content">
        <div class="container container-max pt-3">

            <div class="alert alert-danger">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                    <div>
                        <strong>{{ $msg }}</strong>
                        <div class="small text-muted mt-1">
                            Código: <code>{{ $code }}</code> · HTTP: <code>{{ $http }}</code>
                            @if(config('app.debug'))
                                <span class="badge badge-info ml-2">APP_DEBUG=TRUE</span>
                            @endif
                        </div>

                        @if(!empty($debugT))
                            <details class="mt-3">
                                <summary>Ver detalle técnico</summary>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Stack trace (diagnóstico)</span>
                                    <button id="copyDebugBtn" class="btn btn-xs btn-outline-secondary" type="button">
                                        <i class="fas fa-copy mr-1"></i> Copiar
                                    </button>
                                </div>
                                <pre id="debugBlock" class="debug">{!! $debugT !!}</pre>
                            </details>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sugerencias rápidas (sin route()) --}}
            <div class="card">
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Si estabas abriendo un reporte, verifica tu conexión y vuelve a intentar.</li>
                        <li>Si el problema persiste, comparte el “detalle técnico” con soporte.</li>
                        <li>Si tu sesión caducó, intenta <a href="{{ $login }}">iniciar sesión</a> nuevamente.</li>
                    </ul>
                </div>
            </div>

        </div>
    </section>

    {{-- Footer simple --}}
    <footer class="main-footer text-sm m-0 p-2" style="background:#fff; border-top:1px solid #dee2e6;">
        <div class="container container-max d-flex justify-content-between">
            <span>© {{ date('Y') }} PowerBIDash</span>
            <span class="text-muted">Error handler amigable</span>
        </div>
    </footer>
</div>

{{-- JS por CDN (sin dependencias locales) --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6/dist/jquery.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js" crossorigin="anonymous"></script>
<script>
(function(){
    const btn = document.getElementById('copyDebugBtn');
    const pre = document.getElementById('debugBlock');
    if (btn && pre) {
        btn.addEventListener('click', async () => {
            try {
                const text = pre.innerText || pre.textContent || '';
                await navigator.clipboard.writeText(text);
                btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-success');
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado';
                setTimeout(() => { btn.classList.add('btn-outline-secondary'); btn.classList.remove('btn-success'); btn.innerHTML = '<i class="fas fa-copy mr-1"></i> Copiar'; }, 1800);
            } catch (e) { /* noop */ }
        });
    }
})();
</script>
</body>
</html>
