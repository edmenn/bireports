@extends('adminlte::page')

@section('title', __('admin.title'))

@section('content_header')
    <x-page-header :title="__('admin.title')" icon="fas fa-user-shield" />
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {{ __('admin.welcome') }}
        </div>
    </div>
@stop
