@extends('layouts.app')
@section('titulo', 'Kardex')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0"><i class="bi bi-journal-text"></i> Kardex · {{ $producto->nombre }}</h1>
    <a href="{{ route('inventario.existencias') }}" class="btn btn-outline-secondary">Volver</a>
</div>
<div class="card">
    <div class="card-body d-flex gap-4">
        <div><div class="text-muted small">Código</div>{{ $producto->codigo }}</div>
        <div><div class="text-muted small">Familia</div>{{ $producto->familiaNombre }}</div>
        <div><div class="text-muted small">Existencia actual</div><strong>{{ $producto->existencia }}</strong></div>
    </div>
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
