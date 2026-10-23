<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu promoción · Las Casas del Rincón Guatemalteco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(160deg, #714B67, #3d2836); min-height: 100vh; }
        .tarjeta { max-width: 440px; }
        .icono { font-size: 4rem; }
    </style>
</head>
<body class="d-flex align-items-center py-4">
<div class="container">
    <div class="card tarjeta mx-auto text-center shadow-lg border-0">
        <div class="card-body p-4">
            <div class="small text-uppercase text-muted fw-semibold">Las Casas del Rincón Guatemalteco</div>
            @if (! $promo)
                <i class="bi bi-question-circle icono text-secondary"></i>
                <h1 class="h4">Código no válido</h1>
                <p class="text-muted">No encontramos una promoción para el código <strong>{{ $token }}</strong>.</p>
            @else
                @php
                    $iconos = ['producto_gratuito' => 'bi-bag-heart', 'vale_regalo' => 'bi-gift', 'segundo_mitad' => 'bi-percent',
                        'mayoreo' => 'bi-boxes', 'puntos' => 'bi-star'];
                @endphp
                <i class="bi {{ $iconos[$promo['tipo']] }} icono" style="color:#714B67"></i>
                @if ($promo['estado'] === 'emitido')
                    <h1 class="h3 fw-bold">¡Ganaste una promoción!</h1>
                @elseif ($promo['estado'] === 'canjeado')
                    <h1 class="h4">Esta promoción ya fue utilizada</h1>
                @else
                    <h1 class="h4">Esta promoción ya venció</h1>
                @endif
                <span class="badge rounded-pill fs-6 my-2" style="background:#714B67">{{ $promo['tipo_etiqueta'] }}</span>
                <p class="lead mb-1">{{ $promo['detalle'] }}</p>
                @if ($promo['descripcion'])<p class="text-muted">{{ $promo['descripcion'] }}</p>@endif
                <div class="border rounded p-3 my-3 bg-light">
                    <div class="small text-muted">Presenta este código en caja</div>
                    <div class="fs-3 font-monospace fw-bold">{{ $promo['token'] }}</div>
                    <div class="small text-muted">Válido hasta el {{ $promo['vence'] }} · un solo uso</div>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
