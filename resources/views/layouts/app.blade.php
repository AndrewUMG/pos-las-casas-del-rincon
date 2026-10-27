<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Inicio') · POS Las Casas del Rincón</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/pos.css') }}" rel="stylesheet">
    @stack('estilos')
</head>
<body>
@php
    $puede = fn ($p) => in_array($p, $permisos ?? [], true);
    $activo = fn (...$rutas) => request()->routeIs(...$rutas) ? 'active' : '';
    $modulosMigas = ['pos' => 'Punto de venta', 'facturas' => 'Punto de venta', 'caja' => 'Caja', 'devoluciones' => 'Devoluciones',
        'clientes' => 'Catálogos', 'proveedores' => 'Catálogos', 'productos' => 'Catálogos', 'precios' => 'Catálogos', 'core' => 'Catálogos',
        'compras' => 'Inventario', 'inventario' => 'Inventario', 'promociones' => 'Promociones', 'reportes' => 'Reportes',
        'usuarios' => 'Administración', 'roles' => 'Administración'];
    $moduloActual = $modulosMigas[explode('.', request()->route()?->getName() ?? '')[0]] ?? null;
@endphp

{{-- Menú interactivo: cada opción corresponde a un módulo y se muestra según el rol del usuario --}}
<nav class="navbar navbar-expand-lg navbar-dark menu-pos d-print-none">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('inicio') }}">
            <i class="bi bi-hexagon-fill"></i> <span>Las Casas del Rincón<small>Punto de venta e inventario</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                @if ($puede('pos'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $activo('pos.*', 'facturas.*') }}" href="#" data-bs-toggle="dropdown">Punto de Venta</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('pos.despacho') }}"><i class="bi bi-upc-scan"></i> Despacho</a></li>
                            <li><a class="dropdown-item" href="{{ route('facturas.index') }}"><i class="bi bi-receipt"></i> Facturas</a></li>
                        </ul>
                    </li>
                @endif
                @if ($puede('caja'))
                    <li class="nav-item"><a class="nav-link {{ $activo('caja.*') }}" href="{{ route('caja.index') }}">Caja</a></li>
                @endif
                @if ($puede('devoluciones'))
                    <li class="nav-item"><a class="nav-link {{ $activo('devoluciones.*') }}" href="{{ route('devoluciones.index') }}">Devoluciones</a></li>
                @endif
                @if ($puede('catalogos'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $activo('clientes.*', 'proveedores.*', 'productos.*', 'precios.*', 'core.*') }}" href="#" data-bs-toggle="dropdown">Catálogos</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('clientes.index') }}"><i class="bi bi-people"></i> Clientes</a></li>
                            <li><a class="dropdown-item" href="{{ route('proveedores.index') }}"><i class="bi bi-truck"></i> Proveedores</a></li>
                            <li><a class="dropdown-item" href="{{ route('productos.index') }}"><i class="bi bi-box-seam"></i> Productos</a></li>
                            <li><a class="dropdown-item" href="{{ route('precios.index') }}"><i class="bi bi-tags"></i> Precios</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('core.index') }}"><i class="bi bi-cloud-arrow-down"></i> Sincronizar con el Core</a></li>
                        </ul>
                    </li>
                @endif
                @if ($puede('inventario'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $activo('compras.*', 'inventario.*') }}" href="#" data-bs-toggle="dropdown">Inventario</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('compras.index') }}"><i class="bi bi-cart-plus"></i> Compras</a></li>
                            <li><a class="dropdown-item" href="{{ route('inventario.existencias') }}"><i class="bi bi-boxes"></i> Existencias y kardex</a></li>
                        </ul>
                    </li>
                @endif
                @if ($puede('promociones'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $activo('promociones.*') }}" href="#" data-bs-toggle="dropdown">Promociones</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('promociones.index') }}"><i class="bi bi-gift"></i> Promociones</a></li>
                            <li><a class="dropdown-item" href="{{ route('promociones.cupones') }}"><i class="bi bi-qr-code"></i> Cupones QR emitidos</a></li>
                        </ul>
                    </li>
                @endif
                @if ($puede('reportes'))
                    <li class="nav-item"><a class="nav-link {{ $activo('reportes.*') }}" href="{{ route('reportes.index') }}">Reportes</a></li>
                @endif
                @if ($puede('administracion'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $activo('usuarios.*', 'roles.*') }}" href="#" data-bs-toggle="dropdown">Administración</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('usuarios.index') }}"><i class="bi bi-person-gear"></i> Usuarios</a></li>
                            <li><a class="dropdown-item" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock"></i> Roles y permisos</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
            @if ($cajaAbierta ?? null)
                <a href="{{ route('caja.index') }}" class="estado-caja me-3">Caja abierta · desde las {{ $cajaAbierta->fechaApertura->format('G:i') }}</a>
            @endif
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="dropdown">
                    <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->nombre, 0, 1)) }}</span>
                    <span class="lh-1 text-start">{{ auth()->user()->nombre }}<small class="d-block opacity-75" style="font-size:.7rem">{{ auth()->user()->rol->nombre }}</small></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="post" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

@if ($moduloActual)
    <div class="panel-control d-print-none">
        <nav class="migas" aria-label="Ruta de navegación">
            <span>{{ $moduloActual }}</span>@if (trim($__env->yieldContent('titulo')) !== $moduloActual)<i class="bi bi-chevron-right"></i><span>@yield('titulo', 'Inicio')</span>@endif
        </nav>
    </div>
@endif

<main class="container-fluid py-4 px-lg-4">
    @if (session('ok'))
        <div class="alert alert-success alert-dismissible fade show d-print-none"><i class="bi bi-check-circle"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-print-none"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger d-print-none"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
