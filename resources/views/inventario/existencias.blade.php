@extends('layouts.app')
@section('titulo', 'Existencias')

@section('content')
<h1 class="h3 titulo-pagina mb-3"><i class="bi bi-boxes"></i> Existencias por producto</h1>
<form class="mb-3" method="get"><div class="input-group" style="max-width:420px">
    <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar producto o código"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</div></form>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Código</th><th>Producto</th><th>Familia</th><th>Proveedor</th><th class="text-end">Existencia</th><th></th></tr></thead>
        <tbody>
        @foreach ($productos as $p)
            <tr class="{{ $p['activo'] ? '' : 'text-muted' }}">
                <td class="small">{{ $p['codigo'] }}</td><td>{{ $p['nombre'] }}</td><td>{{ $p['familia'] }}</td><td>{{ $p['proveedor'] }}</td>
                <td class="text-end fw-semibold {{ $p['existencia'] <= 5 ? 'text-danger' : '' }}">{{ $p['existencia'] }}</td>
                <td class="text-end"><a href="{{ route('inventario.kardex', $p['id']) }}" class="btn btn-sm btn-outline-secondary">Kardex</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
