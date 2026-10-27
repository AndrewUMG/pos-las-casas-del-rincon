@extends('layouts.app')
@section('titulo', 'Promociones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Promociones</h1>
    <a href="{{ route('promociones.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nueva promoción</a>
</div>
<div class="alert alert-light border small">
    <strong>Automática:</strong> se aplica sola en el despacho cuando se cumplen las condiciones.
    <strong class="ms-2">Por QR:</strong> al facturar se asigna una de estas promociones al código QR de la factura; el cliente la descubre con su celular y la canjea en su próxima compra.
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Promoción</th><th>Tipo</th><th>Modo</th><th>Aplica a</th><th>Detalle</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($promociones as $p)
            <tr>
                <td class="fw-semibold">{{ $p->nombre }}</td><td>{{ $p->tipo->etiqueta() }}</td>
                <td>{!! $p->modo === 'qr' ? '<span class="badge bg-primary"><i class="bi bi-qr-code"></i> QR</span>' : '<span class="badge bg-info text-dark">Automática</span>' !!}</td>
                <td>{{ $p->productoNombre ?? ($p->familiaNombre ? 'Familia '.$p->familiaNombre : 'Toda la compra') }}</td>
                <td class="small">{{ $p->detalle() }}</td>
                <td class="small text-nowrap">{{ $p->fechaInicio->format('d/m/Y') }} – {{ $p->fechaFin->format('d/m/Y') }}</td>
                <td>{!! $p->vigente(new DateTimeImmutable) ? '<span class="badge bg-success">Vigente</span>' : '<span class="badge bg-secondary">No vigente</span>' !!}</td>
                <td class="text-end"><a href="{{ route('promociones.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No hay promociones.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
