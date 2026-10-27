@extends('layouts.app')
@section('titulo', 'Precios')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Catálogo de precios</h1>
<form class="mb-2" method="get" style="max-width:420px"><input type="hidden" name="familia" value="{{ $familia }}">
    <div class="input-group"><input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar producto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div></form>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="chip {{ $familia ? '' : 'activo' }}" href="?q={{ urlencode($q ?? '') }}">Todas</a>
    @foreach ($familias as $id => $nombre)<a class="chip {{ $familia == $id ? 'activo' : '' }}" href="?familia={{ $id }}&q={{ urlencode($q ?? '') }}">{{ $nombre }}</a>@endforeach
</div>
<form method="post" action="{{ route('precios.actualizar') }}">
    @csrf
    <div class="card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Código</th><th>Producto</th><th>Familia</th><th style="width:170px">Precio (Q)</th><th style="width:170px">Precio mayoreo (Q)</th></tr></thead>
            <tbody>
            @foreach ($productos as $p)
                <tr>
                    <td class="small text-muted">{{ $p->codigo }}</td><td>{{ $p->nombre }}</td><td>{{ $p->familiaNombre }}</td>
                    <td><input type="number" step="0.01" min="0.01" name="precios[{{ $p->id }}][precio]" value="{{ number_format($p->precio, 2, '.', '') }}" data-original="{{ number_format($p->precio, 2, '.', '') }}" class="form-control form-control-sm" required></td>
                    <td><input type="number" step="0.01" min="0.01" name="precios[{{ $p->id }}][precio_mayoreo]" value="{{ $p->precioMayoreo !== null ? number_format($p->precioMayoreo, 2, '.', '') : '' }}" data-original="{{ $p->precioMayoreo !== null ? number_format($p->precioMayoreo, 2, '.', '') : '' }}" class="form-control form-control-sm"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body border-top d-flex justify-content-between align-items-center"><span class="small text-muted" id="estado-cambios">Sin cambios</span><button class="btn btn-pos"><i class="bi bi-save"></i> Guardar precios</button></div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const entradas = document.querySelectorAll('input[data-original]');
const revisar = () => {
    let filas = new Set();
    entradas.forEach(i => { const cambio = i.value !== i.dataset.original; i.classList.toggle('modificado', cambio); if (cambio) filas.add(i.closest('tr')); i.closest('tr').classList.toggle('fila-modificada', filas.has(i.closest('tr'))); });
    document.getElementById('estado-cambios').textContent = filas.size ? filas.size + ' producto(s) con cambios sin guardar' : 'Sin cambios';
};
entradas.forEach(i => i.addEventListener('input', revisar));
</script>
@endpush
