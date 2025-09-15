@extends('adminlte::page')

@section('title', 'Mis reportes')

@section('content_header')
    <h1>Mis reportes</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        @if($reports->isEmpty())
            <div class="text-center text-muted">No tenés reportes asignados.</div>
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
                                    <button class="btn btn-sm btn-fav js-fav-btn {{ $isFav ? 'is-on' : '' }}" title="Favorito">
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
            {{ $reports->links() }}
        @endif
    </div></div>
@stop

@push('js')
<script>
(function(){
    document.querySelectorAll('form.js-fav-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const btn = form.querySelector('.js-fav-btn');
            if (!btn) { form.submit(); return; }
            const url = form.getAttribute('action');
            const token = form.querySelector('input[name=_token]')?.value;
            fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token,'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, body:new URLSearchParams({_token:token}), credentials:'same-origin' })
                .then(function(resp){ if(!resp.ok){ throw new Error('HTTP '+resp.status); } return resp.json().catch(function(){ return {}; }); })
                .then(function(data){
                    btn.classList.toggle('is-on');
                    const icon = btn.querySelector('i');
                    if (icon){
                        if (icon.classList.contains('fas')){ icon.classList.remove('fas'); icon.classList.add('far'); }
                        else { icon.classList.remove('far'); icon.classList.add('fas'); }
                    }
                })
                .catch(function(){ form.submit(); });
        }, {passive:false});
    });
})();
</script>
@endpush
