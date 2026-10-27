@extends('layouts.app')
@section('titulo', 'Kardex')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Kardex · {{ $producto->nombre }}</h1>
    <a href="{{ route('inventario.existencias') }}" class="btn btn-outline-secondary">Volver</a>
</div>
@php
    $entradas = collect($movimientos)->where('tipo_clave', 'entrada')->sum('cantidad');
    $salidas = collect($movimientos)->where('tipo_clave', '!=', 'entrada')->sum('cantidad');
@endphp
<div class="row g-3 mb-3">
    @foreach ([['bi-boxes', '', 'Existencia actual', $producto->existencia, $producto->codigo.' · '.$producto->familiaNombre], ['bi-box-arrow-in-down', '', 'Entradas', '+'.$entradas, 'Compras y saldo inicial'], ['bi-box-arrow-up', 'rojo', 'Salidas y bajas', '−'.$salidas, 'Ventas y devoluciones']] as [$icono, $tono, $etiqueta, $valor, $sub])
        <div class="col-md-4"><div class="card h-100"><div class="card-body kpi">
                <div class="d-flex align-items-center gap-2"><span class="kpi-icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span><span class="text-muted fw-medium">{{ $etiqueta }}</span></div>
                <div class="kpi-valor">{{ $valor }}</div><div class="small text-muted">{{ $sub }}</div>
            </div></div></div>
    @endforeach
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Referencia</th><th>Descripción</th><th class="text-end">Entrada</th><th class="text-end">Salida / baja</th><th class="text-end">Existencia</th></tr></thead>
            <tbody>
            @forelse ($movimientos as $m)
                <tr>
                    <td>{{ $m['fecha'] }}</td>
                    <td><span class="badge {{ ['entrada' => 'bg-success', 'salida' => 'bg-primary', 'baja' => 'bg-danger'][$m['tipo_clave']] }}">{{ $m['tipo'] }}</span></td>
                    <td>{{ $m['referencia'] }}</td><td>{{ $m['descripcion'] }}</td>
                    <td class="text-end">{{ $m['tipo_clave'] === 'entrada' ? $m['cantidad'] : '' }}</td>
                    <td class="text-end">{{ $m['tipo_clave'] !== 'entrada' ? $m['cantidad'] : '' }}</td>
                    <td class="text-end fw-semibold">{{ $m['existencia'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Sin movimientos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
