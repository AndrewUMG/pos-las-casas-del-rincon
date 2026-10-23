@extends('layouts.app')
@section('titulo', 'Precios')

@section('content')
<h1 class="h3 titulo-pagina mb-3"><i class="bi bi-tags"></i> Catálogo de precios</h1>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar producto"></div>
    <div class="col-md-3"><select name="familia" class="form-select"><option value="">Todas las familias</option>
        @foreach ($familias as $id => $nombre)<option value="{{ $id }}" @selected($familia == $id)>{{ $nombre }}</option>@endforeach
    </select></div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filtrar</button></div>
</form>
<form method="post" action="{{ route('precios.actualizar') }}">
    @csrf
    <div class="card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Código</th><th>Producto</th><th>Familia</th><th style="width:170px">Precio (Q)</th><th style="width:170px">Precio mayoreo (Q)</th></tr></thead>
            <tbody>
            @foreach ($productos as $p)
                <tr>
                    <td class="small text-muted">{{ $p->codigo }}</td><td>{{ $p->nombre }}</td><td>{{ $p->familiaNombre }}</td>
                    <td><input type="number" step="0.01" min="0.01" name="precios[{{ $p->id }}][precio]" value="{{ number_format($p->precio, 2, '.', '') }}" class="form-control form-control-sm" required></td>
                    <td><input type="number" step="0.01" min="0.01" name="precios[{{ $p->id }}][precio_mayoreo]" value="{{ $p->precioMayoreo !== null ? number_format($p->precioMayoreo, 2, '.', '') : '' }}" class="form-control form-control-sm"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body border-top text-end"><button class="btn btn-pos"><i class="bi bi-save"></i> Guardar precios</button></div>
    </div>
</form>
@endsection
