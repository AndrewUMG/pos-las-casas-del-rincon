@extends('layouts.app')
@section('titulo', 'Facturación')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Facturación</h1>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Datos del cliente para la factura</div>
            <div class="card-body">
                <p class="text-muted">Total pagado: <strong class="text-dark">{{ q($venta['total']) }}</strong>.
                    Si el cliente no proporciona NIT se emite a <strong>CF</strong> (consumidor final).</p>
                <form method="post" action="{{ route('pos.facturar', $venta['id']) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">NIT</label>
                        <div class="input-group"><input name="nit" id="nit" class="form-control form-control-lg" value="{{ old('nit', $cliente?->nit ?? 'CF') }}" autofocus><button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('nit').value='CF'">Usar CF (consumidor final)</button></div>
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
    <div class="col-lg-5">
        <div class="card"><div class="card-header">Resumen</div><div class="card-body d-flex flex-column gap-2">
            <div class="d-flex justify-content-between"><span class="text-muted">Venta</span><span>#{{ $venta['id'] }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Estado</span><span class="badge bg-success">Cobrada</span></div>
            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-1"><span class="fw-semibold">Total pagado</span><span class="kpi-valor">{{ q($venta['total']) }}</span></div>
        </div></div>
    </div>
</div>
@endsection
