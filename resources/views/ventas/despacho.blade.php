@extends('layouts.app')
@section('titulo', 'Despacho')

@section('content')
@if (! $caja)
    <div class="card"><div class="card-body text-center py-5">
        <i class="bi bi-lock text-pos" style="font-size:3rem"></i>
        <h1 class="h4 mt-2">La caja está cerrada</h1>
        <p class="text-muted">Para despachar productos primero debe abrir la caja con su monto inicial.</p>
        @if (in_array('caja', $permisos ?? [], true))
            <a href="{{ route('caja.index') }}" class="btn btn-pos"><i class="bi bi-unlock"></i> Apertura de caja</a>
        @endif
    </div></div>
@elseif (! $venta)
    <div class="card"><div class="card-body text-center py-5">
        <i class="bi bi-cart text-pos" style="font-size:3rem"></i>
        <h1 class="h4 mt-2">Despacho de productos</h1>
        <p class="text-muted">Caja abierta desde {{ $caja->fechaApertura->format('d/m/Y H:i') }}.</p>
        <form method="post" action="{{ route('pos.iniciar') }}">@csrf
            <button class="btn btn-pos btn-lg"><i class="bi bi-plus-circle"></i> Nueva venta</button>
        </form>
    </div></div>
@else
<div class="row g-3">
    {{-- Lectura por línea de producto --}}
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <form id="form-lector" class="row g-2 align-items-end" method="post" action="{{ route('pos.lineas.agregar', $venta['id']) }}">
                    @csrf
                    <div class="col-md-8 position-relative">
                        <label class="form-label small text-muted mb-1"><i class="bi bi-upc-scan"></i> Código de barras o búsqueda por nombre</label>
                        <input id="codigo" name="codigo" class="form-control form-control-lg lector" autocomplete="off" autofocus
                               placeholder="Escanee o escriba el código…" list="sugerencias">
                        <datalist id="sugerencias"></datalist>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Cantidad</label>
                        <input id="cantidad" name="cantidad" type="number" min="1" value="1" class="form-control form-control-lg">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-pos btn-lg"><i class="bi bi-plus-lg"></i> Agregar</button>
                    </div>
                </form>
                <div id="mensaje" class="text-danger small mt-2"></div>
            </div>
        </div>

        <div class="card tabla-despacho">
            <div class="card-header d-flex justify-content-between">
                <span>Venta #{{ $venta['id'] }}</span>
                <span class="text-muted small"><span id="articulos">{{ $venta['articulos'] }}</span> artículo(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Código</th><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">Precio</th>
                        <th class="text-end">Descuento</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                    <tbody id="lineas"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Totales, cliente, cupón QR y puntos --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span id="t-subtotal"></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Descuentos por promoción</span><span id="t-desc-lineas"></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Vale de regalo</span><span id="t-vale"></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Pago con puntos</span><span id="t-puntos"></span></div>
                <hr>
                <div class="text-muted small">TOTAL DE LA COMPRA</div>
                <div class="total-venta" id="t-total"></div>
                <div class="small text-success mt-1" id="t-ganados"></div>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('pos.cobro', $venta['id']) }}" class="btn btn-success btn-lg" id="btn-cobrar"><i class="bi bi-cash-coin"></i> Cobrar (F9)</a>
                    <form method="post" action="{{ route('pos.cancelar', $venta['id']) }}" onsubmit="return confirm('¿Cancelar la venta?')">@csrf
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Cancelar venta</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person"></i> Cliente</div>
            <div class="card-body">
                @if ($cliente)
                    <div class="mb-2"><strong>{{ $cliente->nombre }}</strong> · NIT {{ $cliente->nit }}<br>
                        <span class="text-muted small">Puntos disponibles: <strong>{{ $cliente->puntos }}</strong> ({{ q($cliente->puntos * $valorPunto) }})</span></div>
                @endif
                <form method="post" action="{{ route('pos.cliente', $venta['id']) }}" class="input-group">@csrf
                    <input name="nit" class="form-control" placeholder="NIT del cliente (vacío = CF)" value="{{ $cliente?->nit }}">
                    <button class="btn btn-outline-secondary">Asignar</button>
                </form>
                @if ($cliente && $cliente->puntos > 0)
                    <form method="post" action="{{ route('pos.puntos', $venta['id']) }}" class="input-group mt-2">@csrf
                        <input name="puntos" type="number" min="1" max="{{ $cliente->puntos }}" class="form-control" placeholder="Puntos a canjear">
                        <button class="btn btn-outline-success"><i class="bi bi-star"></i> Pagar con puntos</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-qr-code-scan"></i> Cupón QR de promoción</div>
            <div class="card-body">
                <form method="post" action="{{ route('pos.cupon', $venta['id']) }}" class="input-group">@csrf
                    <input name="token" class="form-control" placeholder="Lea el QR o escriba el código">
                    <button class="btn btn-outline-primary">Canjear</button>
                </form>
                <div class="text-muted small mt-1">Se aplica el descuento indicado en el QR de una factura anterior.</div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if ($venta)
@push('scripts')
<script>
const venta = @json($venta);
const rutas = {
    agregar: @json(route('pos.lineas.agregar', $venta['id'])),
    quitar: @json(url('/pos/'.$venta['id'].'/lineas')),
    productos: @json(route('pos.productos')),
};
const token = document.querySelector('meta[name=csrf-token]').content;
const q = n => 'Q ' + Number(n).toLocaleString('es-GT', {minimumFractionDigits: 2, maximumFractionDigits: 2});
const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));

function pintar(v) {
    const filas = v.lineas.map(l => `
        <tr>
            <td class="text-muted small">${esc(l.codigo)}</td>
            <td>${esc(l.nombre)}${l.promocion ? `<div class="promo text-success"><i class="bi bi-gift"></i> ${esc(l.promocion)}</div>` : ''}</td>
            <td class="text-end">${l.cantidad}</td>
            <td class="text-end">${q(l.precio)}</td>
            <td class="text-end text-success">${l.descuento > 0 ? '−' + q(l.descuento) : ''}</td>
            <td class="text-end fw-semibold">${q(l.subtotal)}</td>
            <td class="text-end"><button class="btn btn-sm btn-link text-danger" onclick="quitar(${l.producto_id})" title="Quitar línea"><i class="bi bi-trash"></i></button></td>
        </tr>`).join('');
    document.getElementById('lineas').innerHTML = filas || '<tr><td colspan="7" class="text-center text-muted py-5">Escanee el primer producto…</td></tr>';
    document.getElementById('articulos').textContent = v.articulos;
    document.getElementById('t-subtotal').textContent = q(v.subtotal);
    document.getElementById('t-desc-lineas').textContent = v.descuento_lineas > 0 ? '−' + q(v.descuento_lineas) : q(0);
    document.getElementById('t-vale').textContent = v.descuento_vale > 0 ? '−' + q(v.descuento_vale) : q(0);
    document.getElementById('t-puntos').textContent = v.descuento_puntos > 0 ? `−${q(v.descuento_puntos)} (${v.puntos_canjeados} pts)` : q(0);
    document.getElementById('t-total').textContent = q(v.total);
    document.getElementById('t-ganados').textContent = v.puntos_ganados > 0 ? `El cliente ganará ${v.puntos_ganados} punto(s) con esta compra.` : '';
    document.getElementById('btn-cobrar').classList.toggle('disabled', v.lineas.length === 0);
}

async function enviar(url, metodo, cuerpo) {
    const r = await fetch(url, {method: metodo, headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token}, body: cuerpo ? JSON.stringify(cuerpo) : null});
    const datos = await r.json();
    if (!r.ok) throw new Error(datos.message || 'No se pudo completar la operación.');
    return datos;
}

document.getElementById('form-lector').addEventListener('submit', async e => {
    e.preventDefault();
    const codigo = document.getElementById('codigo'), cantidad = document.getElementById('cantidad'), msg = document.getElementById('mensaje');
    msg.textContent = '';
    if (!codigo.value.trim()) return;
    try {
        pintar(await enviar(rutas.agregar, 'POST', {codigo: codigo.value.trim(), cantidad: parseInt(cantidad.value || '1')}));
    } catch (err) { msg.textContent = err.message; }
    codigo.value = ''; cantidad.value = 1; codigo.focus();
});

async function quitar(productoId) {
    try { pintar(await enviar(`${rutas.quitar}/${productoId}`, 'DELETE')); }
    catch (err) { document.getElementById('mensaje').textContent = err.message; }
    document.getElementById('codigo').focus();
}

// Sugerencias por nombre (cuando no se usa lector de código de barras).
let espera;
document.getElementById('codigo').addEventListener('input', e => {
    clearTimeout(espera);
    const texto = e.target.value.trim();
    if (texto.length < 2 || /^\d{6,}$/.test(texto)) return;
    espera = setTimeout(async () => {
        const r = await fetch(`${rutas.productos}?q=${encodeURIComponent(texto)}`, {headers: {'Accept': 'application/json'}});
        const lista = await r.json();
        document.getElementById('sugerencias').innerHTML = lista.map(p =>
            `<option value="${esc(p.codigo)}">${esc(p.nombre)} · ${q(p.precio)} · exist. ${p.existencia}</option>`).join('');
    }, 250);
});

document.addEventListener('keydown', e => { if (e.key === 'F9' && venta.lineas !== undefined) { e.preventDefault(); document.getElementById('btn-cobrar').click(); } });
pintar(venta);
</script>
@endpush
@endif
