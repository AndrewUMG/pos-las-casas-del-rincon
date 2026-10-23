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
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-4">
            @if ($caja)
                <div><span class="badge bg-success fs-6"><i class="bi bi-unlock"></i> Caja abierta</span></div>
                <div><div class="text-muted small">Apertura</div><strong>{{ $caja->fechaApertura->format('d/m/Y H:i') }}</strong></div>
                <div><div class="text-muted small">Monto inicial</div><strong>{{ q($caja->montoInicial) }}</strong></div>
                <div><div class="text-muted small">Ventas del turno</div><strong>{{ q($resumen['total_ventas']) }}</strong></div>
                <div><div class="text-muted small">Efectivo esperado</div><strong>{{ q($resumen['efectivo_esperado']) }}</strong></div>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('pos.despacho') }}" class="btn btn-pos"><i class="bi bi-upc-scan"></i> {{ $ventaEnProceso ? 'Continuar venta' : 'Ir al despacho' }}</a>
                    <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary">Corte de caja</a>
                </div>
            @else
                <div><span class="badge bg-secondary fs-6"><i class="bi bi-lock"></i> Caja cerrada</span></div>
                <div class="text-muted">Abra la caja indicando el monto inicial para empezar a vender.</div>
                <a href="{{ route('caja.index') }}" class="btn btn-pos ms-auto"><i class="bi bi-unlock"></i> Apertura de caja</a>
            @endif
        </div>
    </div>
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
