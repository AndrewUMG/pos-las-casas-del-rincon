@extends('layouts.app')
@section('titulo', $producto ? 'Editar producto' : 'Nuevo producto')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
<div class="card">
    <div class="card-header">{{ $producto ? 'Editar producto' : 'Nuevo producto' }}</div>
    <div class="card-body">
        <form method="post" action="{{ $producto ? route('productos.update', $producto->id) : route('productos.store') }}">
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
                    <div class="form-text">Cambia con compras, ventas y devoluciones.</div></div>
                <div class="col-12 form-check ms-2">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" @checked(old('activo', $producto?->activo ?? true))>
                    <label class="form-check-label" for="activo">Producto activo (disponible para la venta)</label>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2"><button class="btn btn-pos">Guardar</button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
        </form>
    </div>
</div>
</div></div>
@endsection
