@extends('layouts.app')
@section('titulo', $promocion ? 'Editar promoción' : 'Nueva promoción')

@section('content')
@include('partials.cabecera-ficha', ['titulo' => $promocion ? $promocion->nombre : 'Nueva promoción', 'cancelar' => route('promociones.index')])
<div class="row g-3"><div class="col-lg-8">
<div class="card">
    <div class="card-header">Datos</div>
    <div class="card-body">
        <form id="ficha" method="post" action="{{ $promocion ? route('promociones.update', $promocion->id) : route('promociones.store') }}">
            @csrf @if ($promocion) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $promocion?->nombre) }}" required></div>
                <div class="col-md-4"><label class="form-label">Modo</label>
                    <select name="modo" class="form-select">
                        <option value="automatica" @selected(old('modo', $promocion?->modo) === 'automatica')>Automática (en el despacho)</option>
                        <option value="qr" @selected(old('modo', $promocion?->modo) === 'qr')>Por código QR de la factura</option>
                    </select></div>
                <div class="col-12"><label class="form-label">Tipo de promoción</label>
                    <div class="row g-2" id="tipos">
                        @php($iconosTipo = ['producto_gratuito' => 'bi-bag-heart', 'vale_regalo' => 'bi-gift', 'segundo_mitad' => 'bi-percent', 'mayoreo' => 'bi-boxes', 'puntos' => 'bi-star'])
                        @foreach ($tipos as $t)
                            <div class="col">
                                <input type="radio" class="btn-check" name="tipo" id="tipo-{{ $t->value }}" value="{{ $t->value }}" @checked(old('tipo', $promocion?->tipo->value ?? $tipos[0]->value) === $t->value) required>
                                <label class="opcion-pago flex-column align-items-start" for="tipo-{{ $t->value }}">
                                    <i class="bi {{ $iconosTipo[$t->value] ?? 'bi-tag' }}"></i>
                                    <span class="fw-semibold small">{{ $t->etiqueta() }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div></div>
                <div class="col-md-6"><label class="form-label">Producto promocionado</label>
                    <select name="producto_id" class="form-select"><option value="">—</option>
                        @foreach ($productos as $p)<option value="{{ $p->id }}" @selected(old('producto_id', $promocion?->productoId) == $p->id)>{{ $p->nombre }}</option>@endforeach
                    </select></div>
                <div class="col-md-6"><label class="form-label">Familia (clase de producto)</label>
                    <select name="familia_id" class="form-select"><option value="">—</option>
                        @foreach ($familias as $id => $nombre)<option value="{{ $id }}" @selected(old('familia_id', $promocion?->familiaId) == $id)>{{ $nombre }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><label class="form-label">Cantidad mínima</label>
                    <input type="number" min="1" name="cantidad_minima" class="form-control" value="{{ old('cantidad_minima', $promocion?->cantidadMinima ?? 1) }}" required></div>
                <div class="col-md-3"><label class="form-label" id="etiqueta-valor">Valor</label>
                    <input type="number" step="0.01" min="0" name="valor" class="form-control" value="{{ old('valor', $promocion?->valor ?? 0) }}"></div>
                <div class="col-12"><div class="alert alert-light border small mb-0" id="ayuda"></div></div>
                <div class="col-md-4"><label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', $promocion?->fechaInicio->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-4"><label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin', $promocion?->fechaFin->format('Y-m-d') ?? now()->addMonth()->format('Y-m-d')) }}" required></div>
                <div class="col-12"><label class="form-label">Descripción (opcional)</label>
                    <input name="descripcion" class="form-control" value="{{ old('descripcion', $promocion?->descripcion) }}"></div>
            </div>
        </form>
    </div>
</div>
</div>
<div class="col-lg-4">
<div class="card mb-3"><div class="card-header">Estado</div><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="fw-semibold">Promoción activa</div><div class="small text-muted">Se aplica sola al despachar (o al canjear el QR)</div></div>
        <div class="form-check form-switch m-0"><input type="checkbox" name="activa" value="1" form="ficha" class="form-check-input" id="activa" @checked(old('activa', $promocion?->activa ?? true))></div></div></div></div><div class="card mb-3"><div class="card-header">Así se verá en el despacho</div><div class="card-body"><div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:var(--sup-2)"><div><div class="fw-semibold">Producto × 2</div><div class="promo" id="vista-promo">{{ $promocion?->tipo->etiqueta() ?? 'Promoción' }}</div></div><div class="fw-semibold">Q 0.00</div></div>
        <div class="alert alert-info py-2 small mt-3 mb-0"><i class="bi bi-info-circle"></i> Las promociones automáticas se aplican solas; el cajero no necesita elegirlas.</div></div></div>
</div></div>
@endsection

@push('scripts')
<script>
const ayudas = {
    producto_gratuito: ['Valor (no aplica)', 'Producto gratuito: se regala 1 unidad del producto promocionado al comprar la cantidad mínima (del producto o de la familia). Por QR se agrega automáticamente al canjear.'],
    vale_regalo: ['Monto del vale (Q)', 'Vale de regalo: descuento fijo en quetzales sobre el total de la próxima compra. Solo se entrega por QR.'],
    segundo_mitad: ['Valor (no aplica)', 'Segundo producto a mitad de precio: por cada par de unidades del producto promocionado, la segunda cuesta la mitad.'],
    mayoreo: ['Valor (no aplica)', 'Compra por mayoreo: al comprar la cantidad mínima de productos de la misma clase (familia), se cobra el precio de mayoreo del producto promocionado.'],
    puntos: ['Puntos por unidad', 'Puntos por producto: el cliente gana puntos por cada unidad de la familia indicada, para pagar en su próxima compra.'],
};
const tipos = document.querySelectorAll('input[name=tipo]');
const mostrar = () => { const v = document.querySelector('input[name=tipo]:checked').value; const [e, a] = ayudas[v]; document.getElementById('etiqueta-valor').textContent = e; document.getElementById('ayuda').textContent = a; document.getElementById('vista-promo').textContent = document.querySelector('input[name=tipo]:checked + label span').textContent; };
tipos.forEach(t => t.addEventListener('change', mostrar)); mostrar();
</script>
@endpush
