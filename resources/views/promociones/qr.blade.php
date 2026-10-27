<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu promoción · Las Casas del Rincón Guatemalteco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #0c3b31; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; }
        h1 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .tarjeta { border-radius: 1.25rem !important; }
        .circulo { width: 72px; height: 72px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 1rem 0 .5rem; background: #fff4d9; color: #8f5f00; }
        .circulo.gris { background: #ebebe5; color: #575850; } .circulo.rojo { background: #fbeaea; color: #9b2222; }
        .tarjeta { max-width: 440px; }
        .icono { font-size: 2rem; }
    </style>
</head>
<body class="d-flex align-items-center py-4">
<div class="container">
    <div class="card tarjeta mx-auto text-center shadow-lg border-0">
        <div class="card-body p-4">
            <div class="small text-uppercase text-muted fw-semibold">Las Casas del Rincón Guatemalteco</div>
            @if (! $promo)
                <div class="circulo rojo"><i class="bi bi-x-lg icono"></i></div>
                <h1 class="h4">Código no válido</h1>
                <p class="text-muted">No encontramos una promoción para el código <strong>{{ $token }}</strong>.</p>
            @else
                @php
                    $iconos = ['producto_gratuito' => 'bi-bag-heart', 'vale_regalo' => 'bi-gift', 'segundo_mitad' => 'bi-percent',
                        'mayoreo' => 'bi-boxes', 'puntos' => 'bi-star'];
                @endphp
                <div class="circulo {{ $promo['estado'] === 'emitido' ? '' : 'gris' }}"><i class="bi {{ $promo['estado'] === 'canjeado' ? 'bi-check-lg' : $iconos[$promo['tipo']] }} icono"></i></div>
                @if ($promo['estado'] === 'emitido')
                    <h1 class="h3 fw-bold">¡Ganaste una promoción!</h1>
                @elseif ($promo['estado'] === 'canjeado')
                    <h1 class="h4">Esta promoción ya fue utilizada</h1>
                @else
                    <h1 class="h4">Esta promoción ya venció</h1>
                @endif
                <span class="badge rounded-pill fs-6 my-2" style="background:#e3f3ee;color:#0f4a3d">{{ $promo['tipo_etiqueta'] }}</span>
                <p class="lead mb-1">{{ $promo['detalle'] }}</p>
                @if ($promo['descripcion'])<p class="text-muted">{{ $promo['descripcion'] }}</p>@endif
                <div class="rounded-3 p-3 my-3" style="background:#fafaf7;border:1px solid #deded8">
                    <div class="small text-muted">Presenta este código en caja</div>
                    <div class="fs-4 fw-bold" style="font-family:'JetBrains Mono',monospace;color:#8f5f00">{{ $promo['token'] }}</div>
                    <div class="small text-muted">Válido hasta el {{ $promo['vence'] }} · un solo uso</div>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
