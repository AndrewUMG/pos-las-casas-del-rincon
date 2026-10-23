@extends('layouts.app')
@section('titulo', 'Devolución #'.$devolucion['id'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Devolución #{{ $devolucion['id'] }}</h1>
    <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><div class="text-muted small">Fecha</div>{{ $devolucion['fecha'] }}</div>
            <div class="col-md-3"><div class="text-muted small">Factura</div>{{ $devolucion['factura'] }}</div>
            <div class="col-md-3"><div class="text-muted small">Cliente</div>{{ $devolucion['cliente'] }}</div>
            <div class="col-md-3"><div class="text-muted small">Motivo</div>{{ $devolucion['motivo'] }}</div>
        </div>
        @if ($devolucion['observacion'])<p class="mt-3 mb-0"><strong>Observación:</strong> {{ $devolucion['observacion'] }}</p>@endif
    </div>
    <table class="table mb-0">
        <thead><tr><th>Código</th><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($devolucion['lineas'] as $l)
            <tr><td>{{ $l['codigo'] }}</td><td>{{ $l['nombre'] }}</td><td class="text-end">{{ $l['cantidad'] }}</td>
                <td class="text-end">{{ q($l['precio_unitario']) }}</td><td class="text-end">{{ q($l['subtotal']) }}</td></tr>
        @endforeach
        <tr class="fw-bold"><td colspan="4" class="text-end">Reembolso</td><td class="text-end">{{ q($devolucion['total']) }}</td></tr>
        </tbody>
    </table>
</div>
@endsection
