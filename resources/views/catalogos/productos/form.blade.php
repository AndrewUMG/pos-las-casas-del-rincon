@extends('layouts.app')
@section('titulo', $producto ? 'Editar producto' : 'Nuevo producto')

@section('content')
@include('partials.cabecera-ficha', ['titulo' => $producto ? $producto->nombre : 'Nuevo producto', 'cancelar' => route('productos.index')])
<div class="row g-3"><div class="col-lg-8">
<div class="card">
    <div class="card-header">Datos</div>
    <div class="card-body">
        <form id="ficha" method="post" action="{{ $producto ? route('productos.update', $producto->id) : route('productos.store') }}">
            @csrf @if ($producto) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Código de barras</label>
                    <input name="codigo" class="form-control" value="{{ old('codigo', $producto?->codigo) }}" required></div>
                <div class="col-md-8"><label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $producto?->nombre) }}" required></div>
                <div class="col-md-6"><label class="form-label">Familia</label>
                    <select name="familia_id" class="form-select" required><option value="">Seleccione…</option>
                        @foreach ($familias as $id => $nombre)<option value="{{ $id }}" @selected(old('familia_id', $producto?->familiaId) == $id)>{{ $nombre }}</option>@endforeach
                    </select></div>
                <div class="col-md-6"><label class="form-label">Proveedor</label>
                    <select name="proveedor_id" class="form-select"><option value="">—</option>
                        @foreach ($proveedores as $p)<option value="{{ $p->id }}" @selected(old('proveedor_id', $producto?->proveedorId) == $p->id)>{{ $p->nombre }}</option>@endforeach
                    </select></div>
                <div class="col-md-4"><label class="form-label">Precio (Q)</label>
                    <input type="number" step="0.01" min="0.01" name="precio" class="form-control" value="{{ old('precio', $producto?->precio) }}" required></div>
                <div class="col-md-4"><label class="form-label">Precio de mayoreo (Q)</label>
                    <input type="number" step="0.01" min="0.01" name="precio_mayoreo" class="form-control" value="{{ old('precio_mayoreo', $producto?->precioMayoreo) }}"></div>
                <div class="col-md-4"><label class="form-label">Existencia</label>
                    <input class="form-control" value="{{ $producto?->existencia ?? 0 }}" disabled>
                    <div class="form-text">Cambia con compras, ventas y devoluciones.</div></div>            </div>
        </form>
    </div>
</div>
</div>
<div class="col-lg-4">
@if ($producto)
<div class="card mb-3"><div class="card-header">Existencia</div><div class="card-body"><div class="d-flex align-items-center gap-3"><span class="kpi-icono" style="width:52px;height:52px"><i class="bi bi-boxes"></i></span>
        <div><div class="kpi-valor">{{ $producto->existencia }} unidades</div><div class="small text-muted">Solo cambia con compras, ventas y devoluciones</div></div></div>
        <a href="{{ route('inventario.kardex', $producto->id) }}" class="btn btn-outline-secondary w-100 mt-3"><i class="bi bi-journal-text"></i> Ver kardex del producto</a></div></div>
@endif
<div class="card mb-3"><div class="card-header">Disponibilidad</div><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="fw-semibold">Producto activo</div><div class="small text-muted">Disponible para la venta en el despacho</div></div>
        <div class="form-check form-switch m-0"><input type="hidden" name="activo" value="0" form="ficha"><input type="checkbox" name="activo" value="1" form="ficha" class="form-check-input" id="activo" @checked(old('activo', $producto?->activo ?? true))></div></div></div></div>
</div></div>
@endsection
