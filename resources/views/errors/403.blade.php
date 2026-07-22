{{-- resources/views/errors/403.blade.php --}}
@extends('layouts.app')

@section('title', __('errors.forbidden.title'))

@section('content_header')
    <x-page-header :title="__('errors.forbidden.title')" icon="fas fa-lock" />
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-body py-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-lock fa-3x text-danger"></i>
                    </div>
                    <p class="lead mb-4">{{ __('errors.forbidden.message') }}</p>
                    <a href="{{ route('portal.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        {{ __('errors.forbidden.cta') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
