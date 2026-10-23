@extends('layouts.app')
@section('titulo', 'Compra #'.$compra['id'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Compra #{{ $compra['id'] }}</h1>
    <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>
<div class="card">
    <div class="card-body row">
        <div class="col-md-3"><div class="text-muted small">Fecha</div>{{ $compra['fecha'] }}</div>
        <div class="col-md-3"><div class="text-muted small">Documento</div>{{ $compra['numero_documento'] }}</div>
        <div class="col-md-3"><div class="text-muted small">Proveedor</div>{{ $compra['proveedor'] }}</div>
        <div class="col-md-3"><div class="text-muted small">Registró</div>{{ $compra['usuario'] }}</div>
    </div>
    <table class="table mb-0">
        <thead><tr><th>Código</th><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Costo unitario</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($compra['lineas'] as $l)
            <tr><td>{{ $l['codigo'] }}</td><td>{{ $l['nombre'] }}</td><td class="text-end">{{ $l['cantidad'] }}</td>
                <td class="text-end">{{ q($l['costo_unitario']) }}</td><td class="text-end">{{ q($l['subtotal']) }}</td></tr>
        @endforeach
        <tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td class="text-end">{{ q($compra['total']) }}</td></tr>
        </tbody>
    </table>
</div>
@endsection
