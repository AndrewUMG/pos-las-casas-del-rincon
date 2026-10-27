@extends('layouts.app')
@section('titulo', $proveedor ? 'Editar proveedor' : 'Nuevo proveedor')

@section('content')
@include('partials.cabecera-ficha', ['titulo' => $proveedor ? $proveedor->nombre : 'Nuevo proveedor', 'cancelar' => route('proveedores.index')])
<div class="row g-3"><div class="col-lg-8">
<div class="card">
    <div class="card-header">Datos</div>
    <div class="card-body">
        <form id="ficha" method="post" action="{{ $proveedor ? route('proveedores.update', $proveedor->id) : route('proveedores.store') }}">
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
        </form>
    </div>
</div>
</div>
<div class="col-lg-4">
<div class="card mb-3"><div class="card-header">Productos del proveedor</div><div class="card-body"><div class="small text-muted">Cada producto se asigna a su proveedor desde el <a href="{{ route('productos.index') }}">catálogo de productos</a>. Las compras de mercadería se registran por proveedor en Inventario.</div></div></div>
</div></div>
@endsection
