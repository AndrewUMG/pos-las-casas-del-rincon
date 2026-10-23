@extends('layouts.app')
@section('titulo', $cliente ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
<div class="row justify-content-center"><div class="col-lg-7">
<div class="card">
    <div class="card-header">{{ $cliente ? 'Editar cliente' : 'Nuevo cliente' }}</div>
    <div class="card-body">
        <form method="post" action="{{ $cliente ? route('clientes.update', $cliente->id) : route('clientes.store') }}">
            @csrf @if ($cliente) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">NIT</label>
                    <input name="nit" class="form-control" value="{{ old('nit', $cliente?->nit) }}" required></div>
                <div class="col-md-8"><label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $cliente?->nombre) }}" required></div>
                <div class="col-12"><label class="form-label">Dirección</label>
                    <input name="direccion" class="form-control" value="{{ old('direccion', $cliente?->direccion) }}"></div>
                <div class="col-md-5"><label class="form-label">Teléfono</label>
                    <input name="telefono" class="form-control" value="{{ old('telefono', $cliente?->telefono) }}"></div>
                <div class="col-md-7"><label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $cliente?->email) }}"></div>
                @if ($cliente)<div class="col-12 text-muted small">Puntos acumulados: {{ $cliente->puntos }} (solo cambian con compras y canjes).</div>@endif
            </div>
            <div class="mt-4 d-flex gap-2"><button class="btn btn-pos">Guardar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
        </form>
    </div>
</div>
</div></div>
@endsection
