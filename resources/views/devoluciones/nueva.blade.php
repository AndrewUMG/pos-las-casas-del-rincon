@extends('layouts.app')
@section('titulo', 'Nueva devolución')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Nueva devolución</h1>
<div class="card mb-3"><div class="card-header">1 · Buscar la factura</div><div class="card-body">
    <form method="get" action="{{ route('devoluciones.nueva') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Número de factura</label>
            <input name="numero" class="form-control form-control-lg" value="{{ $numero }}" placeholder="Ej. A-000015" autofocus>
        </div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-lg"><i class="bi bi-search"></i> Buscar</button></div>
    </form>
</div></div>

@if ($factura)
<form method="post" action="{{ route('devoluciones.guardar') }}">
    @csrf
    <input type="hidden" name="factura_id" value="{{ $factura['id'] }}">
    <div class="card mb-3">
        <div class="card-header">2 · Seleccione los productos a devolver · Factura {{ $factura['numero_completo'] }} · {{ $factura['fecha'] }} · {{ $factura['nombre'] }} (NIT {{ $factura['nit'] }})</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Producto</th><th class="text-end">Vendido</th><th class="text-end">Ya devuelto</th>
                    <th class="text-end">Precio pagado</th><th style="width:150px">Cantidad a devolver</th></tr></thead>
                <tbody>
                @foreach ($factura['lineas'] as $l)
                    <tr>
                        <td>{{ $l['nombre'] }} <span class="text-muted small">{{ $l['codigo'] }}</span></td>
                        <td class="text-end">{{ $l['cantidad'] }}</td><td class="text-end">{{ $l['devuelto'] }}</td>
                        <td class="text-end">{{ q($l['precio_efectivo']) }}</td>
                        <td><input type="number" min="0" max="{{ $l['disponible'] }}" name="cantidades[{{ $l['producto_id'] }}]"
                                   value="{{ old('cantidades.'.$l['producto_id'], 0) }}" class="form-control" @disabled($l['disponible'] === 0)></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card"><div class="card-header">3 · Motivo y reembolso</div><div class="card-body row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Motivo</label>
            <select name="motivo" class="form-select" required>
                @foreach ($motivos as $m)<option value="{{ $m->value }}" @selected(old('motivo') === $m->value)>{{ $m->etiqueta() }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Observación</label>
            <input name="observacion" class="form-control" value="{{ old('observacion') }}" placeholder="Ej. empaque roto, producto vencido el…">
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-pos btn-lg" onclick="return confirm('¿Registrar la devolución? El reembolso se descontará de su caja.')">
                <i class="bi bi-check2-circle"></i> Registrar devolución</button>
        </div>
        <div class="col-12 text-muted small">El producto devuelto se registra como baja en el kardex (no regresa al inventario) y el reembolso se registra como egreso en efectivo de la caja abierta.</div>
    </div></div>
</form>
@endif
@endsection
