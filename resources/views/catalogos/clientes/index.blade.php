@extends('layouts.app')
@section('titulo', 'Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Clientes</h1>
    <a href="{{ route('clientes.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nuevo cliente</a>
</div>
<form class="mb-3" method="get"><div class="input-group" style="max-width:420px">
    <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o NIT"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</div></form>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>NIT</th><th>Nombre</th><th>Dirección</th><th>Teléfono</th><th>Correo</th><th class="text-end">Puntos</th><th></th></tr></thead>
        <tbody>
        @forelse ($clientes as $c)
            <tr>
                <td class="fw-semibold">{{ $c->nit }}</td><td>{{ $c->nombre }}</td><td>{{ $c->direccion }}</td><td>{{ $c->telefono }}</td>
                <td>{{ $c->email }}</td><td class="text-end">@if ($c->puntos > 0)<span class="badge bg-warning">{{ $c->puntos }} pts</span>@else<span class="text-muted">—</span>@endif</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('clientes.edit', $c->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="post" action="{{ route('clientes.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar el cliente?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Sin resultados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
