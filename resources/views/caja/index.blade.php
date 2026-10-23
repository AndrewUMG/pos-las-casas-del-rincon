@extends('layouts.app')
@section('titulo', 'Caja')

@section('content')
<h1 class="h3 titulo-pagina mb-3"><i class="bi bi-cash-stack"></i> Caja</h1>
<div class="row g-3">
    <div class="col-lg-5">
        @if (! $caja)
            <div class="card">
                <div class="card-header">Apertura de caja</div>
                <div class="card-body">
                    <p class="text-muted">Indique con cuánto dinero en efectivo inicia la caja.</p>
                    <form method="post" action="{{ route('caja.abrir') }}">
                        @csrf
                        <label class="form-label">Monto inicial (Q)</label>
                        <input type="number" step="0.01" min="0" name="monto_inicial" class="form-control form-control-lg mb-3" value="{{ old('monto_inicial') }}" required autofocus>
                        <button class="btn btn-pos btn-lg w-100"><i class="bi bi-unlock"></i> Abrir caja</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between"><span>Caja #{{ $caja->id }} abierta</span>
                    <span class="badge bg-success">{{ $caja->fechaApertura->format('d/m/Y H:i') }}</span></div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <tr><td>Monto inicial</td><td class="text-end">{{ q($caja->montoInicial) }}</td></tr>
                        <tr><td>Ventas en efectivo</td><td class="text-end">{{ q($resumen['ingresos']['efectivo'] ?? 0) }}</td></tr>
                        <tr><td>Ventas con tarjeta de crédito</td><td class="text-end">{{ q($resumen['ingresos']['credito'] ?? 0) }}</td></tr>
                        <tr><td>Ventas con tarjeta de débito</td><td class="text-end">{{ q($resumen['ingresos']['debito'] ?? 0) }}</td></tr>
                        <tr><td>Devoluciones (egresos)</td><td class="text-end text-danger">−{{ q($resumen['total_egresos']) }}</td></tr>
                        <tr class="fw-bold"><td>Efectivo esperado en caja</td><td class="text-end">{{ q($resumen['efectivo_esperado']) }}</td></tr>
                    </table>
                    <form method="post" action="{{ route('caja.cerrar') }}" onsubmit="return confirm('¿Cerrar la caja y generar el corte?')">
                        @csrf
                        <label class="form-label">Efectivo contado al cierre (Q)</label>
                        <input type="number" step="0.01" min="0" name="monto_contado" class="form-control form-control-lg mb-3" required>
                        <button class="btn btn-danger btn-lg w-100"><i class="bi bi-lock"></i> Cerrar caja (corte de caja)</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-7">
        @if ($caja)
            <div class="card mb-3">
                <div class="card-header">Movimientos del turno</div>
                <div class="table-responsive" style="max-height:320px">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Hora</th><th>Tipo</th><th>Método</th><th>Descripción</th><th class="text-end">Monto</th></tr></thead>
                        <tbody>
                        @forelse ($resumen['movimientos'] as $m)
                            <tr><td>{{ $m['fecha'] }}</td><td>{{ ucfirst($m['tipo']) }}</td><td>{{ ucfirst($m['metodo']) }}</td>
                                <td>{{ $m['descripcion'] }}</td><td class="text-end {{ $m['tipo'] === 'egreso' ? 'text-danger' : '' }}">{{ q($m['monto']) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin movimientos todavía.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-header">Mis aperturas y cortes</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>#</th><th>Apertura</th><th>Cierre</th><th class="text-end">Inicial</th><th class="text-end">Esperado</th>
                        <th class="text-end">Contado</th><th class="text-end">Diferencia</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($historial as $h)
                        <tr>
                            <td>{{ $h['id'] }}</td><td>{{ $h['apertura'] }}</td><td>{{ $h['cierre'] ?? '—' }}</td>
                            <td class="text-end">{{ q($h['monto_inicial']) }}</td>
                            <td class="text-end">{{ $h['monto_esperado'] !== null ? q($h['monto_esperado']) : '—' }}</td>
                            <td class="text-end">{{ $h['monto_contado'] !== null ? q($h['monto_contado']) : '—' }}</td>
                            <td class="text-end {{ ($h['diferencia'] ?? 0) < 0 ? 'text-danger' : '' }}">{{ $h['diferencia'] !== null ? q($h['diferencia']) : '—' }}</td>
                            <td><a href="{{ route('caja.mostrar', $h['id']) }}" class="btn btn-sm btn-link">Ver</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
