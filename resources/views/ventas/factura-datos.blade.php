@extends('layouts.app')
@section('titulo', 'Facturación')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt"></i> Datos del cliente para la factura · Venta #{{ $venta['id'] }}</div>
            <div class="card-body">
                <p class="text-muted">Total pagado: <strong class="text-dark">{{ q($venta['total']) }}</strong>.
                    Si el cliente no proporciona NIT se emite a <strong>CF</strong> (consumidor final).</p>
                <form method="post" action="{{ route('pos.facturar', $venta['id']) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">NIT</label>
                        <input name="nit" class="form-control form-control-lg" value="{{ old('nit', $cliente?->nit ?? 'CF') }}" autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input name="nombre" class="form-control" value="{{ old('nombre', $cliente?->nombre) }}" placeholder="Consumidor Final">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input name="direccion" class="form-control" value="{{ old('direccion', $cliente?->direccion) }}" placeholder="Ciudad">
                    </div>
                    @if ($venta['puntos_ganados'] > 0)
                        <div class="alert alert-success py-2 small"><i class="bi bi-star"></i>
                            Con esta compra se acreditarán {{ $venta['puntos_ganados'] }} punto(s) al cliente (si factura con su NIT).</div>
                    @endif
                    <button class="btn btn-pos btn-lg w-100"><i class="bi bi-printer"></i> Generar factura</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
