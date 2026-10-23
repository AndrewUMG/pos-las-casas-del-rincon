@extends('layouts.app')
@section('titulo', $proveedor ? 'Editar proveedor' : 'Nuevo proveedor')

@section('content')
<div class="row justify-content-center"><div class="col-lg-7">
<div class="card">
    <div class="card-header">{{ $proveedor ? 'Editar proveedor' : 'Nuevo proveedor' }}</div>
    <div class="card-body">
        <form method="post" action="{{ $proveedor ? route('proveedores.update', $proveedor->id) : route('proveedores.store') }}">
            @csrf @if ($proveedor) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">NIT</label>
                    <input name="nit" class="form-control" value="{{ old('nit', $proveedor?->nit) }}" required></div>
                <div class="col-md-8"><label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $proveedor?->nombre) }}" required></div>
                <div class="col-md-6"><label class="form-label">Contacto</label>
                    <input name="contacto" class="form-control" value="{{ old('contacto', $proveedor?->contacto) }}"></div>
                <div class="col-md-6"><label class="form-label">Teléfono</label>
                    <input name="telefono" class="form-control" value="{{ old('telefono', $proveedor?->telefono) }}"></div>
                <div class="col-12"><label class="form-label">Dirección</label>
                    <input name="direccion" class="form-control" value="{{ old('direccion', $proveedor?->direccion) }}"></div>
            </div>
            <div class="mt-4 d-flex gap-2"><button class="btn btn-pos">Guardar</button>
                <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
        </form>
    </div>
</div>
</div></div>
@endsection
