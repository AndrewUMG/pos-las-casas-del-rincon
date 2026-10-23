<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso · POS Las Casas del Rincón</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/pos.css') }}" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #714B67, #3d2836); }
        .acceso { max-width: 400px; width: 100%; }
    </style>
</head>
<body>
<div class="container">
    <div class="card acceso mx-auto p-4">
        <div class="text-center mb-3">
            <i class="bi bi-hexagon-fill text-pos" style="font-size:3rem"></i>
            <h1 class="h4 fw-bold mt-2 mb-0">Las Casas del Rincón Guatemalteco</h1>
            <div class="text-muted small">Sistema de Punto de Venta e Inventario</div>
        </div>
        @if (session('error'))
            <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
        @endif
        <form method="post" action="{{ route('login.iniciar') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input name="usuario" class="form-control form-control-lg" value="{{ old('usuario') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-pos btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
        </form>
    </div>
</div>
</body>
</html>
