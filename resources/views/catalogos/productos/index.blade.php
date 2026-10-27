@extends('layouts.app')
@section('titulo', 'Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Productos</h1>
    <a href="{{ route('productos.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nuevo producto</a>
</div>
<form class="mb-2" method="get" style="max-width:420px"><input type="hidden" name="familia" value="{{ $familia }}">
    <div class="input-group"><input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o código"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div></form>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="chip {{ $familia ? '' : 'activo' }}" href="?q={{ urlencode($q ?? '') }}">Todas</a>
    @foreach ($familias as $id => $nombre)<a class="chip {{ $familia == $id ? 'activo' : '' }}" href="?familia={{ $id }}&q={{ urlencode($q ?? '') }}">{{ $nombre }}</a>@endforeach
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Código</th><th>Producto</th><th>Familia</th><th class="text-end">Precio</th><th class="text-end">Mayoreo</th>
            <th class="text-end">Existencia</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($productos as $p)
            <tr class="{{ $p->activo ? '' : 'text-muted' }}">
                <td class="font-monospace small text-secondary">{{ $p->codigo }}</td><td class="fw-semibold">{{ $p->nombre }}</td><td>{{ $p->familiaNombre }}</td>
                <td class="text-end">{{ q($p->precio) }}</td><td class="text-end">{{ $p->precioMayoreo ? q($p->precioMayoreo) : '—' }}</td>
                <td class="text-end">@if ($p->existencia <= 0)<span class="badge bg-danger">Agotado</span>@elseif ($p->existencia <= 5)<span class="badge bg-warning">{{ $p->existencia }} · Bajo</span>@else{{ $p->existencia }}@endif</td>
                <td>{!! $p->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' !!}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('productos.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @if ($p->activo)
                        <form method="post" action="{{ route('productos.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('¿Desactivar el producto?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Desactivar"><i class="bi bi-slash-circle"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Sin resultados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
