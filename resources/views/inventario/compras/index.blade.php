@extends('layouts.app')
@section('titulo', 'Compras')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Compras a proveedores</h1>
    <a href="{{ route('compras.nueva') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Registrar compra</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>#</th><th>Fecha</th><th>Documento</th><th>Proveedor</th><th>Registró</th><th class="text-end">Productos</th><th class="text-end">Total</th><th></th></tr></thead>
        <tbody>
        @forelse ($compras as $c)
            <tr>
                <td>{{ $c['id'] }}</td><td>{{ $c['fecha'] }}</td><td>{{ $c['numero_documento'] }}</td><td>{{ $c['proveedor'] }}</td>
                <td>{{ $c['usuario'] }}</td><td class="text-end"><span class="badge bg-secondary">{{ $c['productos'] }} productos</span></td><td class="text-end">{{ q($c['total']) }}</td>
                <td class="text-end"><a href="{{ route('compras.mostrar', $c['id']) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No hay compras registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
