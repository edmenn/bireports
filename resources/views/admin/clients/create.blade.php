@extends('adminlte::page')
@section('title','Nuevo cliente')

@section('content_header')
    <h1>Nuevo cliente</h1>
@stop

@section('content')
    @include('admin.clients.partials.form', ['action' => route('admin.clients.store'), 'method' => 'POST'])
@stop
