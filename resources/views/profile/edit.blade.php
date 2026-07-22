@extends('layouts.app')

@section('title', __('Profile'))

@section('content_header')
    <x-page-header :title="__('Profile')" icon="fas fa-user-circle" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <style>
                            /* Reutiliza estilos de badges de roles para consistencia */
                            .badge-role{ background:#eef2ff;color:#4338ca;border:1px solid rgba(67,56,202,.15);font-weight:600; }
                            .badge-admin  { background:#fee2e2; color:#b91c1c; border:1px solid rgba(185,28,28,.25); font-weight:700; }
                            .badge-editor { background:#fef9c3; color:#a16207; border:1px solid rgba(161,98,7,.25);  font-weight:700; }
                            .badge-viewer{ background:#dcfce7; color:#166534; border:1px solid rgba(22,101,52,.25);  font-weight:700; }
                            .u-badge-fixed{ display:inline-block; min-width:90px; text-align:center; white-space:nowrap; }
                        </style>
                        {{-- Roles asignados (solo lectura) --}}
                        <div class="mb-3">
                            <label class="form-label d-block">{{ __('profiles.assigned_roles.label') }}</label>
                            @foreach(($user->roles ?? collect()) as $role)
                                @php
                                    $key = strtolower($role->name ?? '');
                                    $style = '';
                                    if (!empty($role->badge_bg) || !empty($role->badge_text)) {
                                        $bg = $role->badge_bg ?: '#eef2ff';
                                        $tx = $role->badge_text ?: '#4338ca';
                                        $style = "background: {$bg}; color: {$tx}; border:1px solid {$tx}; font-weight:700;";
                                    }
                                    if ($style === '') {
                                        $badgeClass = 'badge-role';
                                        if ($key === 'admin')      $badgeClass = 'badge-admin';
                                        elseif ($key === 'editor') $badgeClass = 'badge-editor';
                                        elseif ($key === 'viewer')  $badgeClass = 'badge-viewer';
                                    }
                                @endphp
                                <span class="badge u-badge-fixed {{ $style === '' ? $badgeClass : '' }}" @if($style) style="{{ $style }}" @endif>
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>

                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
            {{-- Eliminación de cuenta no disponible aquí (solo admin desde ABM de usuarios) --}}
        </div>
    </div>
@endsection
