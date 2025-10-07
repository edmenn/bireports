@extends('adminlte::page')

@section('title', __('roles.page.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0">{{ __('roles.page.title') }}</h1>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('roles.index.header_button') }}
        </a>
    </div>
@stop

@section('content')
    @if (session('success')) <x-adminlte-alert theme="success" title="{{ __('roles.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert> @endif
    @if (session('warning')) <x-adminlte-alert theme="warning" title="{{ __('roles.alerts.warning') }}" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert> @endif
    @if (session('error'))   <x-adminlte-alert theme="danger"  title="{{ __('roles.alerts.error') }}"    dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>   @endif

    <x-adminlte-card title="{{ __('roles.index.list_title') }}" icon="fas fa-user-shield text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        <div class="p-3">
            <form method="GET" class="mb-3" role="search">
                <div class="form-row">
                    <div class="form-group col-md-6 mb-2">
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('roles.index.search_placeholder') }}" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3 mb-2 d-flex align-items-stretch">
                        <button class="btn btn-primary mr-2 flex-fill" type="submit">
                            <i class="fas fa-search"></i> {{ __('roles.index.search') }}
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary flex-fill">{{ __('roles.index.clear') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('roles.index.table.name') }}</th>
                        <th class="text-center" style="width:140px;">{{ __('roles.index.table.users') }}</th>
                        <th class="text-center" style="width:140px;">{{ __('roles.index.table.permissions') }}</th>
                        <th class="text-center" style="width:180px;">{{ __('roles.index.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="py-2">
                            <strong>{{ $role->name }}</strong>
                        </td>
                        <td class="text-center">
                            {{ $userCounts[$role->id] ?? 0 }}
                        </td>
                        <td class="text-center">
                            {{ $role->permissions->count() }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> {{ __('common.edit') }}</a>
                            @if (strtolower($role->name) !== 'admin')
                                @php $count = (int)($userCounts[$role->id] ?? 0); @endphp
                                @if ($count === 0)
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('{{ __('roles.index.delete_confirm', ['name' => $role->name]) }}');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('roles.form.buttons.delete') }}"><i class="fas fa-trash"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                            title="{{ __('roles.index.table.cant_delete', ['count' => $count]) }}">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('roles.index.table.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">{{ $roles->links() }}</div>
    </x-adminlte-card>
@endsection
