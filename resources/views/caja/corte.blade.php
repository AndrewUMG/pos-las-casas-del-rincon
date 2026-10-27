@extends('layouts.app')
@section('titulo', 'Corte de caja')

@section('content')
@php($c = $resumen['caja'])
<div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <h1 class="h3 titulo-pagina mb-0">Corte de caja #{{ $c->id }}</h1>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir</button>
        <a href="{{ route('caja.index') }}" class="btn btn-pos">Volver a caja</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <div class="row g-2 mb-3">
                @foreach ([['Esperado', q($c->montoEsperado ?? $resumen['efectivo_esperado']), ''], ['Contado', $c->montoContado !== null ? q($c->montoContado) : '—', ''], ['Diferencia', $c->diferencia !== null ? q($c->diferencia) : '—', ($c->diferencia ?? 0) < 0 ? 'falta' : 'ok']] as [$l, $v, $t])
                    <div class="col-4"><div class="resultado {{ $t }}"><div class="small">{{ $l }}</div><strong>{{ $v }}</strong></div></div>
                @endforeach
            </div>
            <table class="table mb-0">
                <tr><td>Estado</td><td class="text-end"><span class="badge {{ $c->estaAbierta() ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($c->estado) }}</span></td></tr>
                <tr><td>Apertura</td><td class="text-end">{{ $c->fechaApertura->format('d/m/Y H:i') }}</td></tr>
                <tr><td>Cierre</td><td class="text-end">{{ $c->fechaCierre?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                <tr><td>Monto inicial</td><td class="text-end">{{ q($c->montoInicial) }}</td></tr>
                <tr><td>Ventas en efectivo</td><td class="text-end">{{ q($resumen['ingresos']['efectivo'] ?? 0) }}</td></tr>
                <tr><td>Ventas con tarjeta de crédito</td><td class="text-end">{{ q($resumen['ingresos']['credito'] ?? 0) }}</td></tr>
                <tr><td>Ventas con tarjeta de débito</td><td class="text-end">{{ q($resumen['ingresos']['debito'] ?? 0) }}</td></tr>
                <tr class="fw-semibold"><td>Total de ventas</td><td class="text-end">{{ q($resumen['total_ventas']) }}</td></tr>
                <tr><td>Devoluciones (egresos)</td><td class="text-end text-danger">−{{ q($resumen['total_egresos']) }}</td></tr>
                <tr class="fw-bold"><td>Efectivo esperado</td><td class="text-end">{{ q($c->montoEsperado ?? $resumen['efectivo_esperado']) }}</td></tr>
                <tr class="fw-bold"><td>Efectivo contado (cierre)</td><td class="text-end">{{ $c->montoContado !== null ? q($c->montoContado) : '—' }}</td></tr>
                <tr class="fw-bold {{ ($c->diferencia ?? 0) < 0 ? 'text-danger' : 'text-success' }}"><td>Diferencia</td>
                    <td class="text-end">{{ $c->diferencia !== null ? q($c->diferencia) : '—' }}</td></tr>
            </table>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Movimientos</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Método</th><th>Descripción</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                    @forelse ($resumen['movimientos'] as $m)
                        <tr><td>{{ $m['fecha'] }}</td><td><span class="badge {{ $m['tipo'] === 'egreso' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($m['tipo']) }}</span></td><td>{{ ucfirst($m['metodo']) }}</td>
                            <td>{{ $m['descripcion'] }}</td><td class="text-end {{ $m['tipo'] === 'egreso' ? 'text-danger' : '' }}">{{ q($m['monto']) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Sin movimientos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
