@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Usuarios</h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nuevo usuario</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @foreach ($usuarios as $u)
            <tr>
                <td><div class="d-flex align-items-center gap-2"><span class="avatar" style="width:36px;height:36px">{{ mb_strtoupper(mb_substr($u->nombre, 0, 1)) }}</span><span class="fw-semibold">{{ $u->nombre }}</span></div></td><td class="font-monospace">{{ $u->usuario }}</td><td><span class="badge {{ $u->rolNombre === 'Administrador' ? 'bg-primary' : 'bg-info' }}">{{ $u->rolNombre }}</span></td>
                <td>{!! $u->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' !!}</td>
                <td class="text-end"><a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
