@extends('layouts.app')
@section('titulo', 'Roles y permisos')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Roles y permisos</h1>
<div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle"></i> El rol Administrador siempre conserva el acceso a Administración para evitar bloqueos. Los cambios se aplican en el siguiente inicio de sesión.</div>
<div class="row g-3">
    @foreach ($roles as $r)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">{{ $r['nombre'] }} <span class="text-muted small fw-normal">· {{ $r['descripcion'] }}</span></div>
                <div class="card-body">
                    <form method="post" action="{{ route('roles.asignar', $r['id']) }}">
                        @csrf
                        @foreach ($catalogoPermisos as $p)
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $p['id'] }}" id="p{{ $r['id'] }}-{{ $p['id'] }}"
                                    @checked(in_array($p['id'], $r['permiso_ids']))>
                                <label class="form-check-label" for="p{{ $r['id'] }}-{{ $p['id'] }}"><strong>{{ $p['nombre'] }}</strong>
                                    <span class="text-muted small">— {{ $p['modulo'] }}</span></label>
                            </div>
                        @endforeach
                        <button class="btn btn-pos btn-sm mt-3">Guardar permisos</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
