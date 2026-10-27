@extends('layouts.app')
@section('titulo', 'Core de Consumo Masivo')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Sincronizar con el Core</h1>
<div class="row g-3"><div class="col-lg-7">
<div class="card">
    <div class="card-header">Core de Empresas de Consumo Masivo (sistema existente)</div>
    <div class="card-body">
        <p>Trae del Core los <strong>productos, proveedores y precios</strong>. Los productos nuevos se crean con existencia 0
            (su inventario ingresa con las compras) y los existentes actualizan nombre, familia y precios.</p>
        <p class="text-muted small mb-4">Origen configurado: <code>{{ $origen }}</code><br>
            Se cambia con <code>CORE_DRIVER</code> (<code>archivo</code> o <code>http</code>) y <code>CORE_API_URL</code> en el archivo <code>.env</code>.</p>
        <div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle"></i> Sincronizar puede cambiar precios de productos existentes. Revise el resultado después.</div>
        <form method="post" action="{{ route('core.sincronizar') }}" onsubmit="return confirm('¿Sincronizar el catálogo con el Core?')">
            @csrf<button class="btn btn-pos btn-lg w-100"><i class="bi bi-arrow-repeat"></i> Sincronizar ahora</button>
        </form>
    </div>
</div>
</div>
<div class="col-lg-5">
    <div class="card"><div class="card-header">Resultado de la última sincronización</div><div class="card-body">
        @if (session('resultado'))
            @php($r = session('resultado'))
            <div class="row g-2">
                @foreach ([['Nuevos', $r['creados'], 'ok'], ['Actualizados', $r['actualizados'], ''], ['Proveedores nuevos', $r['proveedores'], 'ok']] as [$l, $v, $t])
                    <div class="col-4"><div class="resultado {{ $t }}"><div class="small">{{ $l }}</div><strong>{{ $v }}</strong></div></div>
                @endforeach
            </div>
        @else
            <div class="text-muted small">Aún no se ha sincronizado en esta sesión. El resultado aparecerá aquí.</div>
        @endif
    </div></div>
</div></div>
@endsection
