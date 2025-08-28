@extends('adminlte::page')

@section('title', 'Reporte: '.$report->name)

@section('content_header')
    <h1>{{ $report->name }}</h1>
@stop

@section('content')
    @if(empty($embed['token'] ?? null) || empty($embed['embedUrl'] ?? null))
        <div class="alert alert-danger">
            No se pudo generar el token de incrustación.
            @isset($embed)
                <pre style="white-space: pre-wrap">{{ json_encode($embed, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
            @endisset
        </div>
    @else
        <div id="pbi-container" style="width:100%; height: calc(100vh - 160px); border:0; border-radius:8px; overflow:hidden;"></div>
    @endif
@stop

@push('js')
{{-- SDK oficial de Power BI --}}
<script src="https://cdn.jsdelivr.net/npm/powerbi-client@2.23.1/dist/powerbi.js"></script>
<script>
(function () {
    // ===== Datos provistos por el controlador =====
    const embedUrl    = @json($embed['embedUrl'] ?? '');
    const accessToken = @json($embed['token'] ?? '');
    const reportId    = @json($report->id); // GUID del reporte
    // Opcionales (si tu controlador los pasa)
    const workspaceId = @json($workspaceId ?? null);
    const datasetId   = @json($datasetId ?? null);
    const rlsRole     = @json($rlsRole ?? null);

    if (!embedUrl || !accessToken || !reportId) return;

    // ===== Helper: POST a /api/audit =====
    async function sendAudit(payload) {
        try {
            await fetch(@json(route('api.audit.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(Object.assign({
                    report_uuid: reportId,
                    workspace_uuid: workspaceId || null,
                    dataset_uuid: datasetId || null,
                }, payload))
            });
        } catch (e) {
            console.warn('Audit send failed', e);
        }
    }

    // ===== Configuración de embed (TokenType.Embed) =====
    const models = window['powerbi-client'].models;
    const config = {
        type: 'report',
        id: reportId,
        embedUrl: embedUrl,
        accessToken: accessToken,
        tokenType: models.TokenType.Embed,
        settings: {
            panes: {
                filters: { visible: true, expanded: false },
                pageNavigation: { visible: true }
            },
            navContentPaneEnabled: true
        }
    };

    // ===== Render =====
    const container = document.getElementById('pbi-container');
    try { window.powerbi.reset(container); } catch (e) {}

    const report = window.powerbi.embed(container, config);

    // Primer registro: llegada a la vista
    sendAudit({
        action: 'view',
        rls_role: rlsRole || null,
        occurred_at: new Date().toISOString()
    });

    // loaded
    report.on('loaded', function () {
        sendAudit({
            action: 'loaded',
            rls_role: rlsRole || null,
            occurred_at: new Date().toISOString()
        });
    });

    // rendered
    report.on('rendered', async function () {
        try {
            const pages = await report.getPages();
            const active = pages.find(p => p.isActive);
            sendAudit({
                action: 'render',
                page_name: active ? (active.displayName || active.name) : null,
                rls_role: rlsRole || null,
                occurred_at: new Date().toISOString()
            });
        } catch (e) {
            sendAudit({
                action: 'error',
                success: false,
                error_message: 'rendered handler failed: ' + (e?.message || e),
                occurred_at: new Date().toISOString()
            });
        }
    });

    // pageChanged
    report.on('pageChanged', function (evt) {
        try {
            const info = evt?.detail?.newPage;
            sendAudit({
                action: 'page_change',
                page_name: info ? (info.displayName || info.name) : null,
                occurred_at: new Date().toISOString()
            });
        } catch (e) { /* silencioso */ }
    });

    // filtersApplied
    report.on('filtersApplied', async function () {
        try {
            const filters = await report.getFilters();
            sendAudit({
                action: 'filter_change',
                filters: filters || null,
                occurred_at: new Date().toISOString()
            });
        } catch (e) { /* silencioso */ }
    });

    // buttonClicked (detección básica de export)
    report.on('buttonClicked', function (evt) {
        try {
            const btn = evt?.detail?.title || evt?.detail?.reportObjectId || 'button';
            const knownExports = ['Export', 'Export to file', 'Export data', 'Exportar', 'Exportar a archivo', 'Exportar datos'];
            const isExport = knownExports.some(x => String(btn).toLowerCase().includes(x.toLowerCase()));

            sendAudit({
                action: isExport ? 'export' : 'button_click',
                bookmark: !isExport ? String(btn) : null,
                occurred_at: new Date().toISOString()
            });
        } catch (e) { /* silencioso */ }
    });

    // error
    report.on('error', function (evt) {
        const msg = evt?.detail;
        sendAudit({
            action: 'error',
            success: false,
            error_message: (typeof msg === 'string') ? msg : JSON.stringify(msg),
            occurred_at: new Date().toISOString()
        });
    });
})();
</script>
@endpush
