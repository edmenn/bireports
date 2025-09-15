@extends('adminlte::page')

@section('title', 'Mi Inicio')

@section('content_header')
    <h1>Mi Inicio</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card mb-3">
        <div class="card-header">Carpetas</div>
        <div class="card-body p-2">
            <div class="nav nav-pills flex-wrap">
                @forelse($folders as $f)
                    <a class="nav-link {{ ($selected && $selected->id===$f->id) ? 'active' : '' }}"
                       href="{{ route('portal.index', ['carpeta_id'=>$f->id]) }}">
                        <i class="fas fa-folder mr-1"></i> {{ $f->name }}
                        <span class="badge badge-light ml-2">{{ $f->reports_count }}</span>
                    </a>
                @empty
                    <span class="text-muted p-2">Sin carpetas asignadas.</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                Reportes @if($selected) <small class="text-muted">en {{ $selected->name }}</small>@endif
            </div>
        </div>
        <div class="card-body">
            @if(($reports ?? collect())->isEmpty())
                <div class="text-center text-muted py-4">No hay reportes para mostrar.</div>
            @else
                <div class="row">
                    @foreach($reports as $r)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0">{{ $r->name }}</h5>
                                    <form action="{{ route('portal.fav.toggle', $r) }}" method="POST" class="js-fav-form">
                                        @csrf
                                        @php($isFav = in_array($r->id, $favIds))
                                        <button class="btn btn-sm btn-fav js-fav-btn {{ $isFav ? 'is-on' : '' }}" data-report-id="{{ $r->id }}" title="Favorito">
                                            <i class="{{ $isFav ? 'fas' : 'far' }} fa-star"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-auto">
                                    @if($r->workspace_id)
                                        <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-chart-line mr-1"></i> Abrir
                                        </a>
                                    @else
                                        <span class="text-muted">Workspace no asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@stop

@push('js')
<script>
(function(){
    document.querySelectorAll('form.js-fav-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const btn = form.querySelector('.js-fav-btn');
            const icon = btn?.querySelector('i');
            if (!btn) { form.submit(); return; }
            const url = form.getAttribute('action');
            const token = form.querySelector('input[name=_token]')?.value;
            fetch(url, {
                    method:'POST',
                    headers:{
                        'X-Requested-With':'XMLHttpRequest',
                        'X-CSRF-TOKEN':token,
                        'Accept':'application/json',
                        'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body:new URLSearchParams({_token:token}),
                    credentials:'same-origin'
                })
                .then(function(resp){ if(!resp.ok){ throw new Error('HTTP '+resp.status); } return resp.json().catch(function(){ return {}; }); })
                .then(function(data){
                    btn.classList.toggle('is-on');
                    if (icon){
                        if (icon.classList.contains('fas')){
                            icon.classList.remove('fas'); icon.classList.add('far');
                        } else {
                            icon.classList.remove('far'); icon.classList.add('fas');
                        }
                    }
                })
                .catch(function(){ form.submit(); });
        }, {passive:false});
    });
})();
</script>
@endpush
