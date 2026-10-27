@extends('layouts.app')
@section('titulo', 'Existencias')

@section('content')
@php
    $agotados = collect($productos)->where('existencia', '<=', 0)->count();
    $bajos = collect($productos)->filter(fn ($p) => $p['existencia'] > 0 && $p['existencia'] <= 5)->count();
@endphp
<h1 class="h3 titulo-pagina mb-3">Existencias por producto</h1>
<div class="row g-3 mb-3">
    @foreach ([['bi-boxes', '', 'Productos', count($productos), 'En el listado'], ['bi-exclamation-triangle', 'maiz', 'Stock bajo', $bajos, '5 unidades o menos'], ['bi-x-circle', 'rojo', 'Agotados', $agotados, 'Requieren compra']] as [$icono, $tono, $etiqueta, $valor, $sub])
        <div class="col-md-4"><div class="card h-100"><div class="card-body kpi">
                <div class="d-flex align-items-center gap-2"><span class="kpi-icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span><span class="text-muted fw-medium">{{ $etiqueta }}</span></div>
                <div class="kpi-valor">{{ $valor }}</div><div class="small text-muted">{{ $sub }}</div>
            </div></div></div>
    @endforeach
</div>
<form class="mb-3" method="get"><div class="input-group" style="max-width:420px">
    <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar producto o código"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</div></form>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Código</th><th>Producto</th><th>Familia</th><th>Proveedor</th><th class="text-end">Existencia</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @foreach ($productos as $p)
            <tr class="{{ $p['activo'] ? '' : 'text-muted' }}">
                <td class="small">{{ $p['codigo'] }}</td><td>{{ $p['nombre'] }}</td><td>{{ $p['familia'] }}</td><td>{{ $p['proveedor'] }}</td>
                <td class="text-end fw-semibold">{{ $p['existencia'] }}</td>
                <td>@if ($p['existencia'] <= 0)<span class="badge bg-danger">Agotado</span>@elseif ($p['existencia'] <= 5)<span class="badge bg-warning">Stock bajo</span>@else<span class="badge bg-success">Disponible</span>@endif</td>
                <td class="text-end"><a href="{{ route('inventario.kardex', $p['id']) }}" class="btn btn-sm btn-outline-secondary">Kardex</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
