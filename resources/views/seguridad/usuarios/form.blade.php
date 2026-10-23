@extends('layouts.app')
@section('titulo', $usuario ? 'Editar usuario' : 'Nuevo usuario')

@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card">
    <div class="card-header">{{ $usuario ? 'Editar usuario' : 'Nuevo usuario' }}</div>
    <div class="card-body">
        <form method="post" action="{{ $usuario ? route('usuarios.update', $usuario->id) : route('usuarios.store') }}">
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
            <div class="form-check mb-3">
                <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" @checked(old('activo', $usuario?->activo ?? true))>
                <label class="form-check-label" for="activo">Usuario activo</label>
            </div>
            <div class="d-flex gap-2"><button class="btn btn-pos">Guardar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
        </form>
    </div>
</div>
</div></div>
@endsection
