@extends('layouts.app')
@section('titulo', $usuario ? 'Editar usuario' : 'Nuevo usuario')

@section('content')
@include('partials.cabecera-ficha', ['titulo' => $usuario ? $usuario->nombre : 'Nuevo usuario', 'cancelar' => route('usuarios.index')])
<div class="row g-3"><div class="col-lg-8">
<div class="card">
    <div class="card-header">Datos</div>
    <div class="card-body">
        <form id="ficha" method="post" action="{{ $usuario ? route('usuarios.update', $usuario->id) : route('usuarios.store') }}">
            @csrf @if ($usuario) @method('PUT') @endif
            <div class="mb-3"><label class="form-label">Nombre completo</label>
                <input name="nombre" class="form-control" value="{{ old('nombre', $usuario?->nombre) }}" required></div>
            <div class="mb-3"><label class="form-label">Usuario</label>
                <input name="usuario" class="form-control" value="{{ old('usuario', $usuario?->usuario) }}" required></div>
            <div class="mb-3"><label class="form-label">Rol</label>
                <select name="rol_id" class="form-select" required>
                    @foreach ($roles as $r)<option value="{{ $r['id'] }}" @selected(old('rol_id', $usuario?->rolId) == $r['id'])>{{ $r['nombre'] }}</option>@endforeach
                </select></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" {{ $usuario ? '' : 'required' }}>
                    @if ($usuario)<div class="form-text">Déjela vacía para no cambiarla.</div>@endif</div>
                <div class="col-md-6"><label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control"></div>
            </div>
        </form>
    </div>
</div>
</div>
<div class="col-lg-4">
<div class="card mb-3"><div class="card-header">Estado de la cuenta</div><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="fw-semibold">Usuario activo</div><div class="small text-muted">Puede iniciar sesión</div></div>
        <div class="form-check form-switch m-0"><input type="checkbox" name="activo" value="1" form="ficha" class="form-check-input" id="activo" @checked(old('activo', $usuario?->activo ?? true))></div></div>
        <div class="alert alert-info py-2 small mt-3 mb-0"><i class="bi bi-info-circle"></i> El rol Cajero ve Inicio, Punto de venta, Caja y Devoluciones. Los permisos se editan en Roles y permisos.</div></div></div>
</div></div>
@endsection
