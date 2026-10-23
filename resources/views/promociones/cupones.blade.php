@extends('layouts.app')
@section('titulo', 'Cupones QR')

@section('content')
<h1 class="h3 titulo-pagina mb-3"><i class="bi bi-qr-code"></i> Cupones QR emitidos</h1>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Código</th><th>Factura</th><th>Cliente</th><th>Promoción</th><th>Tipo</th><th>Vence</th><th>Estado</th><th>Canjeado</th></tr></thead>
        <tbody>
        @forelse ($cupones as $c)
            <tr>
                <td><a href="{{ route('promocion.qr', $c['token']) }}" target="_blank" class="font-monospace">{{ $c['token'] }}</a></td>
                <td>{{ $c['factura'] }}</td><td>{{ $c['cliente'] }}</td><td>{{ $c['promocion'] }}</td><td>{{ $c['tipo'] }}</td><td>{{ $c['vence'] }}</td>
                <td><span class="badge {{ $c['estado'] === 'canjeado' ? 'bg-secondary' : 'bg-success' }}">{{ ucfirst($c['estado']) }}</span></td>
                <td>{{ $c['canjeado_en'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aún no se han emitido cupones.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
