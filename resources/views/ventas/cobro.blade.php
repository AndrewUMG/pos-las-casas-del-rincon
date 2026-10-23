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
                            <div class="btn-group w-100 mb-3" role="group">
                                @foreach ($metodos as $m)
                                    <input type="radio" class="btn-check" name="metodo" id="m-{{ $m->value }}" value="{{ $m->value }}" @checked(old('metodo', 'efectivo') === $m->value)>
                                    <label class="btn btn-outline-secondary" for="m-{{ $m->value }}">
                                        <i class="bi {{ $m->value === 'efectivo' ? 'bi-cash' : 'bi-credit-card' }}"></i> {{ $m->etiqueta() }}
                                    </label>
                                @endforeach
                            </div>
                            <div id="efectivo">
                                <label class="form-label">Monto recibido</label>
                                <input type="number" step="0.01" min="0" name="monto_recibido" id="recibido" class="form-control form-control-lg" value="{{ old('monto_recibido') }}" autofocus>
                                <div class="mt-3 d-flex justify-content-between fs-5"><span>Cambio</span><strong id="cambio">Q 0.00</strong></div>
                            </div>
                            <div id="tarjeta" class="d-none">
                                <label class="form-label">Referencia del voucher (POS bancario)</label>
                                <input name="referencia_voucher" class="form-control form-control-lg" value="{{ old('referencia_voucher') }}" placeholder="Número de autorización">
                            </div>
                            <button class="btn btn-success btn-lg w-100 mt-4"><i class="bi bi-check2-circle"></i> Confirmar cobro</button>
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
    document.getElementById('cambio').className = cambio < 0 ? 'text-danger' : 'text-success';
};
document.querySelectorAll('input[name=metodo]').forEach(r => r.addEventListener('change', actualizar));
document.getElementById('recibido').addEventListener('input', actualizar);
actualizar();
</script>
@endpush
