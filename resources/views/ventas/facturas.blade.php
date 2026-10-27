@extends('layouts.app')
@section('titulo', 'Facturas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Facturas emitidas</h1>
    <a href="{{ route('pos.despacho') }}" class="btn btn-pos"><i class="bi bi-upc-scan"></i> Ir al despacho</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Factura</th><th>Fecha</th><th>NIT</th><th>Cliente</th><th>Forma de pago</th><th class="text-end">Total</th><th></th></tr></thead>
        <tbody>
        @forelse ($facturas as $f)
            <tr>
                <td class="font-monospace text-secondary">{{ $f['numero_completo'] }}</td><td>{{ $f['fecha'] }}</td><td>{{ $f['nit'] }}</td>
                <td>{{ $f['nombre'] }}</td><td><span class="badge {{ str_contains(strtolower($f['metodo']), 'crédito') ? 'bg-info' : (str_contains(strtolower($f['metodo']), 'débito') ? 'bg-primary' : 'bg-secondary') }}">{{ $f['metodo'] }}</span></td><td class="text-end fw-semibold">{{ q($f['total']) }}</td>
                <td class="text-end">
                    <a href="{{ route('facturas.mostrar', $f['id']) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                    <a href="{{ route('facturas.pdf', $f['id']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay facturas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
