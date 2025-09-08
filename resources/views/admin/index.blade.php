@extends('adminlte::page')

@section('title', __('admin.title'))

@section('content_header')
    <h1>{{ __('admin.title') }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {{ __('admin.welcome') }}
        </div>
    </div>
@stop

