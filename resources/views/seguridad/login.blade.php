<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso · POS Las Casas del Rincón</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/pos.css') }}" rel="stylesheet">
    <style>.acceso { max-width: 420px; width: 100%; box-shadow: 0 20px 50px rgba(8, 40, 31, .35); }</style>
</head>
<body class="pagina-acceso">
<div class="container">
    <div class="card acceso mx-auto p-4 p-md-5">
        <div class="text-center mb-3">
            <i class="bi bi-hexagon-fill" style="font-size:3rem;color:var(--maiz-500)"></i>
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
