@extends('layouts.app')
@section('titulo', 'Devoluciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0"><i class="bi bi-arrow-return-left"></i> Devoluciones</h1>
    <a href="{{ route('devoluciones.nueva') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nueva devolución</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>#</th><th>Fecha</th><th>Factura</th><th>Cliente</th><th>Motivo</th><th>Registró</th><th class="text-end">Reembolso</th><th></th></tr></thead>
        <tbody>
        @forelse ($devoluciones as $d)
            <tr>
                <td>{{ $d['id'] }}</td><td>{{ $d['fecha'] }}</td><td>{{ $d['factura'] }}</td><td>{{ $d['cliente'] }}</td>
                <td><span class="badge {{ $d['motivo'] === 'Daño' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $d['motivo'] }}</span></td>
                <td>{{ $d['usuario'] }}</td><td class="text-end">{{ q($d['total']) }}</td>
                <td class="text-end"><a href="{{ route('devoluciones.mostrar', $d['id']) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No hay devoluciones registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
