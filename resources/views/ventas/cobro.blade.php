@extends('layouts.app')
@section('titulo', 'Cobro')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h1 class="h3 titulo-pagina mb-3"><i class="bi bi-cash-coin"></i> Cobro de la venta #{{ $venta['id'] }}</h1>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Detalle</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($venta['lineas'] as $l)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $l['cantidad'] }} × {{ $l['nombre'] }}
                                    @if ($l['promocion'])<div class="promo text-success">{{ $l['promocion'] }}</div>@endif</span>
                                <span>{{ q($l['subtotal']) }}</span>
                            </li>
                        @endforeach
                        @if ($venta['descuento_vale'] > 0)
                            <li class="list-group-item d-flex justify-content-between text-success"><span>Vale de regalo</span><span>−{{ q($venta['descuento_vale']) }}</span></li>
                        @endif
                        @if ($venta['descuento_puntos'] > 0)
                            <li class="list-group-item d-flex justify-content-between text-success"><span>Pago con {{ $venta['puntos_canjeados'] }} puntos</span><span>−{{ q($venta['descuento_puntos']) }}</span></li>
                        @endif
                    </ul>
                    <div class="card-body border-top">
                        <div class="text-muted small">TOTAL A COBRAR</div>
                        <div class="total-venta">{{ q($venta['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Forma de pago</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('pos.cobrar', $venta['id']) }}" id="form-cobro">
                            @csrf
                            <div class="row g-2 mb-4">
                                @foreach ($metodos as $m)
                                    <div class="col">
                                        <input type="radio" class="btn-check" name="metodo" id="m-{{ $m->value }}" value="{{ $m->value }}" @checked(old('metodo', 'efectivo') === $m->value)>
                                        <label class="opcion-pago" for="m-{{ $m->value }}">
                                            <i class="bi {{ $m->value === 'efectivo' ? 'bi-cash' : 'bi-credit-card' }}"></i>
                                            <span class="fw-semibold">{{ $m->etiqueta() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div id="efectivo">
                                <div class="d-flex gap-4 flex-wrap">
                                    <div class="flex-grow-1" style="min-width:240px">
                                        <label class="form-label">Monto recibido</label>
                                        <input type="number" step="0.01" min="0" name="monto_recibido" id="recibido" class="form-control monto-grande" value="{{ old('monto_recibido') }}" autofocus>
                                        <div class="d-flex flex-wrap gap-2 my-3">
                                            <button type="button" class="chip" data-monto="{{ $venta['total'] }}">Exacto</button>
                                            @foreach ([50, 100, 200] as $b)
                                                @if ($b >= $venta['total'])<button type="button" class="chip" data-monto="{{ $b }}">Q {{ $b }}</button>@endif
                                            @endforeach
                                        </div>
                                        <div class="cambio-caja" id="caja-cambio"><span>Cambio a devolver</span><strong id="cambio">Q 0.00</strong></div>
                                    </div>
                                    <div class="teclado d-none d-md-grid align-self-start" id="teclado">
                                        @foreach (['7','8','9','4','5','6','1','2','3','.','0','←'] as $k)
                                            <button type="button" data-tecla="{{ $k }}" class="{{ $k === '←' ? 'borrar' : '' }}">{{ $k }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div id="tarjeta" class="d-none">
                                <div class="alert alert-info py-2 small"><i class="bi bi-info-circle"></i> Cobre en el terminal bancario e ingrese el número de autorización del voucher.</div>
                                <label class="form-label">Referencia del voucher (POS bancario)</label>
                                <input name="referencia_voucher" class="form-control form-control-lg" value="{{ old('referencia_voucher') }}" placeholder="Número de autorización">
                            </div>
                            <button class="btn btn-success btn-lg w-100 mt-4"><i class="bi bi-check2-circle"></i> Confirmar cobro · {{ q($venta['total']) }}</button>
                            <a href="{{ route('pos.despacho') }}" class="btn btn-link w-100">Volver al despacho</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const total = {{ $venta['total'] }};
const actualizar = () => {
    const metodo = document.querySelector('input[name=metodo]:checked').value;
    document.getElementById('efectivo').classList.toggle('d-none', metodo !== 'efectivo');
    document.getElementById('tarjeta').classList.toggle('d-none', metodo === 'efectivo');
    const cambio = (parseFloat(document.getElementById('recibido').value) || 0) - total;
    document.getElementById('cambio').textContent = 'Q ' + Math.max(0, cambio).toFixed(2);
    document.getElementById('caja-cambio').classList.toggle('falta', cambio < 0);
};
const recibido = document.getElementById('recibido');
document.querySelectorAll('[data-monto]').forEach(b => b.addEventListener('click', () => { recibido.value = parseFloat(b.dataset.monto).toFixed(2); actualizar(); }));
document.querySelectorAll('[data-tecla]').forEach(b => b.addEventListener('click', () => {
    const t = b.dataset.tecla; let v = recibido.value;
    if (t === '←') v = v.slice(0, -1); else if (t !== '.' || !v.includes('.')) v += t;
    recibido.value = v; actualizar();
}));
document.querySelectorAll('input[name=metodo]').forEach(r => r.addEventListener('change', actualizar));
document.getElementById('recibido').addEventListener('input', actualizar);
actualizar();
</script>
@endpush
