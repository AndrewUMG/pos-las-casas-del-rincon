@extends('layouts.app')
@section('titulo', 'Inicio')

@section('content')
@php
    $puede = fn ($p) => in_array($p, $permisos ?? [], true);
    $modulos = [
        ['pos', 'pos.despacho', 'bi-upc-scan', 'Punto de Venta', 'Despacho, cobro y factura con QR'],
        ['caja', 'caja.index', 'bi-cash-stack', 'Caja', 'Apertura y corte de caja'],
        ['devoluciones', 'devoluciones.index', 'bi-arrow-return-left', 'Devoluciones', 'Por daño o vencimiento'],
        ['catalogos', 'productos.index', 'bi-journal-bookmark', 'Catálogos', 'Clientes, proveedores, productos y precios'],
        ['inventario', 'inventario.existencias', 'bi-boxes', 'Inventario', 'Compras, existencias y kardex'],
        ['promociones', 'promociones.index', 'bi-gift', 'Promociones', 'Descuentos y cupones QR'],
        ['reportes', 'reportes.index', 'bi-bar-chart', 'Reportes', 'Corte, devoluciones, clientes y compras'],
        ['administracion', 'usuarios.index', 'bi-shield-lock', 'Administración', 'Usuarios, roles y permisos'],
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 titulo-pagina mb-0">Bienvenido, {{ auth()->user()->nombre }}</h1>
    <span class="text-muted">{{ now()->translatedFormat('l d \d\e F \d\e Y') }}</span>
</div>

@if ($puede('caja'))
    @if ($caja)
        <div class="row g-3 mb-4">
            @foreach ([
                ['bi-unlock', 'gris', 'Monto inicial', q($caja->montoInicial), 'Abierta desde las '.$caja->fechaApertura->format('G:i')],
                ['bi-cash-stack', '', 'Ventas del turno', q($resumen['total_ventas']), 'Todos los métodos de pago'],
                ['bi-lock', 'maiz', 'Efectivo esperado', q($resumen['efectivo_esperado']), 'Inicial + ventas en efectivo − devoluciones'],
            ] as [$icono, $tono, $etiqueta, $valor, $sub])
                <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body kpi">
                    <div class="d-flex align-items-center gap-2"><span class="kpi-icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span><span class="text-muted fw-medium">{{ $etiqueta }}</span></div>
                    <div class="kpi-valor">{{ $valor }}</div><div class="small text-muted">{{ $sub }}</div>
                </div></div></div>
            @endforeach
            <div class="col-md-6 col-xl-3"><div class="card h-100 border-0" style="background:var(--jade-800)"><div class="card-body d-flex flex-column justify-content-between gap-3">
                <div class="text-white fw-semibold">{{ $ventaEnProceso ? 'Hay una venta en proceso' : 'Todo listo para vender' }}</div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pos.despacho') }}" class="btn btn-sm" style="background:var(--maiz-500);color:var(--tx)"><i class="bi bi-upc-scan"></i> {{ $ventaEnProceso ? 'Continuar venta' : 'Ir al despacho' }}</a>
                    <a href="{{ route('caja.index') }}" class="btn btn-sm btn-outline-light">Corte de caja</a>
                </div>
            </div></div></div>
        </div>
    @else
        <div class="card mb-4"><div class="card-body d-flex flex-wrap align-items-center gap-3">
            <span class="kpi-icono gris"><i class="bi bi-lock"></i></span>
            <div><div class="fw-semibold">Caja cerrada</div><div class="text-muted">Abra la caja indicando el monto inicial para empezar a vender.</div></div>
            <a href="{{ route('caja.index') }}" class="btn btn-pos ms-auto"><i class="bi bi-unlock"></i> Apertura de caja</a>
        </div></div>
    @endif
@endif

<div class="row g-3">
    @foreach ($modulos as [$permiso, $ruta, $icono, $nombre, $desc])
        @if ($puede($permiso))
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route($ruta) }}" class="card modulo h-100 text-decoration-none text-dark">
                    <div class="card-body">
                        <i class="bi {{ $icono }}"></i>
                        <h2 class="h5 mt-2 mb-1">{{ $nombre }}</h2>
                        <div class="text-muted small">{{ $desc }}</div>
                    </div>
                </a>
            </div>
        @endif
    @endforeach
</div>
@endsection
