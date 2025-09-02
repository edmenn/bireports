@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Usuarios</h1>
        <div class="btn-group">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo usuario
            </a>
            <button
                type="button"
                class="btn btn-outline-primary"
                data-toggle="modal" data-target="#modalSyncEntra"
                data-bs-toggle="modal" data-bs-target="#modalSyncEntra"
            >
                <i class="fab fa-microsoft"></i> Importar Entra
            </button>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))  <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('warning'))  <div class="alert alert-warning">{{ session('warning') }}</div> @endif
    @if (session('error'))    <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o email">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary">Buscar</button>
                    </div>
                </div>
            </form>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->roles->pluck('name')->join(', ') }}</td>
                            <td class="text-right">
                                <a href="{{ route('users.edit', ['usuario' => $u->id]) }}" class="btn btn-sm btn-info">Editar</a>
                                <form action="{{ route('users.destroy', ['usuario' => $u->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center p-4 text-muted">Sin usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $users->links() }}
        </div>
    </div>

    @php
        $roles = $roles ?? \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','name');
    @endphp

    {{-- ===== Modal Previsualización/Selección Entra ===== --}}
    <div class="modal fade" id="modalSyncEntra" tabindex="-1" role="dialog" aria-labelledby="modalSyncEntraLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalSyncEntraLabel">
                        <i class="fab fa-microsoft"></i> Importar desde Entra (previsualizar y seleccionar)
                    </h5>
                    <button type="button" class="close d-none d-sm-block" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <button type="button" class="btn-close d-block d-sm-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    {{-- Filtros de búsqueda --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="pv_query">Prefijo (displayName/UPN)</label>
                            <input type="text" id="pv_query" class="form-control" placeholder="Ej: juan, maria, it-">
                            <small class="form-text text-muted">Busca con <code>startsWith</code>.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="pv_domain">Solo dominio</label>
                            <input type="text" id="pv_domain" class="form-control" placeholder="empresa.com">
                            <small class="form-text text-muted">Sin @ (ej: sarcom.com.py).</small>
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-center">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="pv_only_active" value="1">
                                <label class="custom-control-label" for="pv_only_active">Solo activos</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-2">
                        <button id="btnPreviewSearch" class="btn btn-outline-primary mr-2" type="button">
                            <span class="btn-text"><i class="fas fa-search"></i> Buscar</span>
                            <span class="btn-wait d-none"><i class="fas fa-spinner fa-spin"></i> Buscando…</span>
                        </button>

                        <button id="btnPreviewMore" class="btn btn-outline-secondary d-none" type="button">
                            <span class="btn-more-text"><i class="fas fa-plus"></i> Cargar más</span>
                            <span class="btn-more-wait d-none"><i class="fas fa-spinner fa-spin"></i> Cargando…</span>
                        </button>

                        <small id="statusText" class="text-muted ml-3" aria-live="polite"></small>
                    </div>

                    {{-- Tabla de resultados --}}
                    <div class="table-responsive border">
                        <table class="table table-sm table-hover mb-0" id="tblPreview">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;">
                                        <input type="checkbox" id="chkAll">
                                    </th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPreview">
                                <tr>
                                    <td colspan="3" class="text-center text-muted p-3">
                                        Busca para ver resultados…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="small text-muted mt-2">
                        Seleccionados: <span id="selCount">0</span>
                    </div>

                    <hr>

                    {{-- Form de importación (solo seleccionados) --}}
                    <form id="formImportSelected" method="POST" action="{{ route('users.sync-entra.selected') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="assign_role">Rol a asignar si es nuevo</label>
                                <select name="assign_role" id="assign_role" class="form-control">
                                    <option value="">(ninguno)</option>
                                    @foreach($roles as $roleName => $label)
                                        <option value="{{ $roleName }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Campo contenedor para inputs hidden de seleccionados --}}
                        <div id="selectedContainer"></div>

                        <button id="btnImportSelected" type="submit" class="btn btn-primary" disabled>
                            <span class="btn-text"><i class="fas fa-download"></i> Importar seleccionados</span>
                            <span class="btn-wait d-none"><i class="fas fa-spinner fa-spin"></i> Importando…</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
(function () {
    const routePreview = @json(route('users.sync-entra.preview'));
    const tbody  = document.getElementById('tbodyPreview');
    const btnSearch = document.getElementById('btnPreviewSearch');
    const btnMore   = document.getElementById('btnPreviewMore');
    const chkAll    = document.getElementById('chkAll');
    const selCount  = document.getElementById('selCount');
    const formImp   = document.getElementById('formImportSelected');
    const btnImp    = document.getElementById('btnImportSelected');
    const selContainer = document.getElementById('selectedContainer');
    const statusText = document.getElementById('statusText');

    const pvQuery  = document.getElementById('pv_query');
    const pvDomain = document.getElementById('pv_domain');
    const pvOnly   = document.getElementById('pv_only_active');

    let nextLink = null; // para paginar

    function setSearching(on) {
        if (!btnSearch) return;
        const t = btnSearch.querySelector('.btn-text');
        const w = btnSearch.querySelector('.btn-wait');
        if (on) {
            btnSearch.setAttribute('disabled', 'disabled');
            if (t && w) { t.classList.add('d-none'); w.classList.remove('d-none'); }
            statusText.textContent = 'Buscando…';
            // placeholder en la tabla
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-3"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando…</td></tr>';
        } else {
            btnSearch.removeAttribute('disabled');
            if (t && w) { t.classList.remove('d-none'); w.classList.add('d-none'); }
            statusText.textContent = '';
        }
    }

    function setLoadingMore(on) {
        if (!btnMore) return;
        const t = btnMore.querySelector('.btn-more-text');
        const w = btnMore.querySelector('.btn-more-wait');
        if (on) {
            btnMore.setAttribute('disabled', 'disabled');
            if (t && w) { t.classList.add('d-none'); w.classList.remove('d-none'); }
            statusText.textContent = 'Cargando más…';
        } else {
            btnMore.removeAttribute('disabled');
            if (t && w) { t.classList.remove('d-none'); w.classList.add('d-none'); }
            statusText.textContent = '';
        }
    }

    function getFilters(next = null) {
        const q  = pvQuery.value.trim();
        const d  = pvDomain.value.trim();
        const oa = pvOnly.checked ? '1' : '';
        const params = new URLSearchParams();
        if (q)  params.append('query', q);
        if (d)  params.append('domain', d);
        if (oa) params.append('only_active', '1');
        if (next) params.append('next', next);
        return params;
    }

    async function fetchRows(append = false) {
        const url = routePreview + '?' + getFilters(nextLink).toString();
        if (!append) setSearching(true); else setLoadingMore(true);

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) {
                throw new Error('HTTP ' + res.status + ' ' + res.statusText);
            }

            const json = await res.json();

            if (!append) tbody.innerHTML = '';
            const temp = document.createElement('tbody');
            temp.innerHTML = json.rows || '';

            // mover <tr> al tbody principal
            [...temp.children].forEach(tr => tbody.appendChild(tr));

            nextLink = json.next || null;
            btnMore.classList.toggle('d-none', !nextLink);
            wireCheckboxes();
            updateSelected();

            const count = Number(json.count || 0);
            if (!append) {
                statusText.textContent = (count > 0)
                    ? `Se encontraron ${count} usuarios en esta página.`
                    : 'Sin resultados.';
            } else {
                statusText.textContent = (count > 0)
                    ? `Se agregaron ${count} más.`
                    : 'No hay más resultados.';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-danger p-3"><i class="fas fa-exclamation-triangle mr-2"></i>Error obteniendo datos: '
                + (e && e.message ? e.message : 'desconocido') + '</td></tr>';
            btnMore.classList.add('d-none');
            statusText.textContent = 'Error al buscar.';
            // Para depurar en consola:
            if (window && window.console) console.error('Preview error:', e);
        } finally {
            if (!append) setSearching(false); else setLoadingMore(false);
        }
    }

    function wireCheckboxes() {
        const chks = tbody.querySelectorAll('.chk-user');
        chks.forEach(chk => {
            chk.addEventListener('change', updateSelected, { passive: true });
        });
    }

    function updateSelected() {
        const chks = tbody.querySelectorAll('.chk-user');
        let count = 0;
        selContainer.innerHTML = '';
        chks.forEach((chk) => {
            if (chk.checked) {
                count++;
                const email = chk.value;
                const name  = chk.getAttribute('data-name') || '';
                // inputs hidden selected[i][email], selected[i][name]
                const iEmail = document.createElement('input');
                iEmail.type  = 'hidden';
                iEmail.name  = `selected[${count}][email]`;
                iEmail.value = email;

                const iName  = document.createElement('input');
                iName.type   = 'hidden';
                iName.name   = `selected[${count}][name]`;
                iName.value  = name;

                selContainer.appendChild(iEmail);
                selContainer.appendChild(iName);
            }
        });
        selCount.textContent = count;
        btnImp.disabled = (count === 0);
        // checkbox maestro:
        const all = tbody.querySelectorAll('.chk-user');
        const allChecked = (all.length > 0) && [...all].every(x => x.checked);
        chkAll.checked = allChecked;
    }

    // Click en Buscar
    if (btnSearch) {
        btnSearch.addEventListener('click', function(){
            nextLink = null;
            fetchRows(false);
        }, { passive: true });
    }

    // Enter para buscar (en cualquiera de los inputs)
    [pvQuery, pvDomain].forEach(el => {
        if (!el) return;
        el.addEventListener('keydown', function(ev){
            if (ev.key === 'Enter') {
                ev.preventDefault();
                nextLink = null;
                fetchRows(false);
            }
        });
    });
    if (pvOnly) {
        pvOnly.addEventListener('change', function(){
            nextLink = null;
            fetchRows(false);
        }, { passive: true });
    }

    // Click en Cargar más
    if (btnMore) {
        btnMore.addEventListener('click', function(){
            if (nextLink) fetchRows(true);
        }, { passive: true });
    }

    // Maestro seleccionar todos
    if (chkAll) {
        chkAll.addEventListener('change', function(){
            const chks = tbody.querySelectorAll('.chk-user');
            chks.forEach(chk => { chk.checked = chkAll.checked; });
            updateSelected();
        }, { passive: true });
    }

    // Spinner al enviar importación
    if (formImp && btnImp) {
        formImp.addEventListener('submit', function(){
            if (btnImp.getAttribute('data-busy') === '1') return;
            btnImp.setAttribute('data-busy', '1');
            const t = btnImp.querySelector('.btn-text');
            const w = btnImp.querySelector('.btn-wait');
            if (t && w) { t.classList.add('d-none'); w.classList.remove('d-none'); }
            btnImp.setAttribute('disabled', 'disabled');
        });
    }
})();
</script>
@endsection
