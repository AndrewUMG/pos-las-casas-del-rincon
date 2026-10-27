@extends('layouts.app')
@section('titulo', 'Registrar compra')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Registrar compra de mercadería</h1>
<form method="post" action="{{ route('compras.guardar') }}">
    @csrf
    <div class="card mb-3"><div class="card-body row g-3">
        <div class="col-md-5"><label class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-select" required><option value="">Seleccione…</option>
                @foreach ($proveedores as $p)<option value="{{ $p->id }}" @selected(old('proveedor_id') == $p->id)>{{ $p->nombre }} ({{ $p->nit }})</option>@endforeach
            </select></div>
        <div class="col-md-4"><label class="form-label">No. de factura del proveedor</label>
            <input name="numero_documento" class="form-control" value="{{ old('numero_documento') }}" required></div>
        <div class="col-md-3"><label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->format('Y-m-d')) }}" required></div>
    </div></div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Productos comprados</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarFila()"><i class="bi bi-plus"></i> Agregar producto</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Producto</th><th style="width:130px">Cantidad</th><th style="width:160px">Costo unitario (Q)</th><th class="text-end" style="width:140px">Subtotal</th><th style="width:50px"></th></tr></thead>
                <tbody id="filas"></tbody>
                <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">Total de la compra</td><td class="text-end" id="total">Q 0.00</td><td></td></tr></tfoot>
            </table>
        </div>
        <div class="card-body border-top text-end">
            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-pos"><i class="bi bi-save"></i> Registrar compra</button>
        </div>
    </div>
</form>

<template id="plantilla">
    <tr>
        <td><select class="form-select producto" required><option value="">Seleccione…</option>
            @foreach ($productos as $p)<option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }} (exist. {{ $p->existencia }})</option>@endforeach
        </select></td>
        <td><input type="number" min="1" value="1" class="form-control cantidad" required></td>
        <td><input type="number" min="0.01" step="0.01" class="form-control costo" required></td>
        <td class="text-end subtotal">Q 0.00</td>
        <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove(); recalcular()"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let indice = 0;
function agregarFila() {
    const fila = document.getElementById('plantilla').content.firstElementChild.cloneNode(true);
    fila.querySelector('.producto').name = `lineas[${indice}][producto_id]`;
    fila.querySelector('.cantidad').name = `lineas[${indice}][cantidad]`;
    fila.querySelector('.costo').name = `lineas[${indice}][costo_unitario]`;
    indice++;
    fila.querySelectorAll('input').forEach(i => i.addEventListener('input', recalcular));
    document.getElementById('filas').appendChild(fila);
}
function recalcular() {
    let total = 0;
    document.querySelectorAll('#filas tr').forEach(f => {
        const s = (parseFloat(f.querySelector('.cantidad').value) || 0) * (parseFloat(f.querySelector('.costo').value) || 0);
        f.querySelector('.subtotal').textContent = 'Q ' + s.toFixed(2);
        total += s;
    });
    document.getElementById('total').textContent = 'Q ' + total.toFixed(2);
}
agregarFila();
</script>
@endpush
