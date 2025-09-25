@extends('adminlte::page')

@section('title', __('applications.index.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">{{ __('applications.index.title') }}</h1>
            <small class="text-muted">{{ __('applications.index.subtitle') }}</small>
        </div>
        <a href="{{ route('applications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('applications.actions.create') }}
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('applications.actions.close_alert') }}"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('applications.fields.name') }}</th>
                        <th>{{ __('applications.fields.slug') }}</th>
                        <th>{{ __('applications.fields.status') }}</th>
                        <th class="text-center">{{ __('applications.fields.sections') }}</th>
                        <th class="text-center">{{ __('applications.fields.reports') }}</th>
                        <th class="text-center">{{ __('applications.fields.users') }}</th>
                        <th class="text-center">{{ __('applications.fields.groups') }}</th>                        <th class="text-right">{{ __('applications.fields.updated_at') }}</th>
                        <th class="text-right">{{ __('applications.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $app)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $app->name }}</div>
                                <div class="text-muted small">#{{ $app->uuid }}</div>
                            </td>
                            <td><code>{{ $app->slug }}</code></td>
                            <td>
                                @if ($app->is_active)
                                    <span class="badge bg-success">{{ __('applications.status.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('applications.status.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $app->sections->count() }}</td>
                            <td class="text-center">{{ $app->reports_count }}</td>
                            <td class="text-center">{{ $app->users_count }}</td>
                            <td class="text-center">{{ $app->groups_count }}</td>
                            <td class="text-center">{{ $app->roles_count }}</td>
                            <td class="text-right text-muted small">{{ optional($app->updated_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('applications.edit', $app) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> {{ __('applications.actions.edit') }}
                                    </a>
                                    <form action="{{ route('applications.destroy', $app) }}" method="POST" onsubmit="return confirm('{{ __('applications.confirm.delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                {{ __('applications.index.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $applications->links() }}
        </div>
    </div>
@stop