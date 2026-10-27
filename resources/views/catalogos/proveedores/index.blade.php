@extends('layouts.app')
@section('titulo', 'Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Proveedores</h1>
    <a href="{{ route('proveedores.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nuevo proveedor</a>
</div>
<form class="mb-3" method="get"><div class="input-group" style="max-width:420px">
    <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o NIT"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</div></form>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>NIT</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Dirección</th><th></th></tr></thead>
        <tbody>
        @forelse ($proveedores as $p)
            <tr>
                <td class="fw-semibold">{{ $p->nit }}</td><td>{{ $p->nombre }}</td><td>{{ $p->contacto }}</td><td>{{ $p->telefono }}</td><td>{{ $p->direccion }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('proveedores.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="post" action="{{ route('proveedores.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar el proveedor?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Sin resultados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
