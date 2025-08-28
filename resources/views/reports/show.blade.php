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
        <div id="pbi-container" style="width:100%; height: calc(100vh - 160px); border:0;"></div>
    @endif
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/powerbi-client@2.23.1/dist/powerbi.js"></script>
<script>
(function(){
    var data = {
        embedUrl: @json($embed['embedUrl'] ?? ''),
        token:    @json($embed['token'] ?? ''),
        id:       @json($report->id),
    };

    if (!data.embedUrl || !data.token) return;

    var models = window['powerbi-client'].models;
    var config = {
        type: 'report',
        id: data.id,
        embedUrl: data.embedUrl,
        accessToken: data.token,
        tokenType: models.TokenType.Embed,
        settings: {
            panes: { filters: { visible: false }, pageNavigation: { visible: true } },
        }
    };

    var container = document.getElementById('pbi-container');
    try {
        window.powerbi.reset(container);
    } catch(e) {}
    window.powerbi.embed(container, config);
})();
</script>
@endpush
