@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0"><i class="bi bi-person-gear"></i> Usuarios</h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nuevo usuario</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @foreach ($usuarios as $u)
            <tr>
                <td>{{ $u->nombre }}</td><td class="font-monospace">{{ $u->usuario }}</td><td>{{ $u->rolNombre }}</td>
                <td>{!! $u->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' !!}</td>
                <td class="text-end"><a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
