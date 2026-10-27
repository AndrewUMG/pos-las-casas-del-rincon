@extends('layouts.app')
@section('titulo', $cliente ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
@include('partials.cabecera-ficha', ['titulo' => $cliente ? $cliente->nombre : 'Nuevo cliente', 'cancelar' => route('clientes.index')])
<div class="row g-3"><div class="col-lg-8">
<div class="card">
    <div class="card-header">Datos</div>
    <div class="card-body">
        <form id="ficha" method="post" action="{{ $cliente ? route('clientes.update', $cliente->id) : route('clientes.store') }}">
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
        </form>
    </div>
</div>
</div>
<div class="col-lg-4">
@if ($cliente)
<div class="card mb-3"><div class="card-header">Fidelización</div><div class="card-body"><div class="d-flex align-items-center gap-3"><span class="kpi-icono maiz" style="width:52px;height:52px"><i class="bi bi-star"></i></span>
        <div><div class="kpi-valor">{{ $cliente->puntos }} puntos</div><div class="small text-muted">Equivalen a {{ q($cliente->puntos * 0.10) }} en descuentos</div></div></div>
        <div class="alert alert-info py-2 small mt-3 mb-0"><i class="bi bi-info-circle"></i> Los puntos solo cambian con compras y canjes; no se editan a mano.</div></div></div>
@else
<div class="card mb-3"><div class="card-header">Fidelización</div><div class="card-body"><div class="small text-muted">El cliente empezará a acumular puntos con sus compras facturadas a su NIT.</div></div></div>
@endif
</div></div>
@endsection
