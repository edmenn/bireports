@extends('adminlte::page')
@section('title','Editar cliente')

@section('content_header')
    <h1>Editar cliente</h1>
@stop

@section('content')
    @include('admin.clients.partials.form', ['action' => route('admin.clients.update', $client), 'method' => 'PUT', 'client' => $client])
@stop
