@extends('layouts.app')
@section('titulo', 'Factura '.$factura['numero_completo'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 d-print-none factura bg-transparent">
    <h1 class="h4 titulo-pagina mb-0">Factura {{ $factura['numero_completo'] }}</h1>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir</button>
        <a href="{{ route('facturas.pdf', $factura['id']) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-filetype-pdf"></i> PDF</a>
        <a href="{{ route('pos.despacho') }}" class="btn btn-pos"><i class="bi bi-plus-circle"></i> Nueva venta</a>
    </div>
</div>
<div class="card factura"><div class="card-body p-4">
    @include('ventas._factura-cuerpo')
</div></div>
@endsection
