<?php

return [
    // Valor en quetzales de cada punto acumulado por el cliente.
    'valor_punto' => (float) env('POS_VALOR_PUNTO', 0.10),

    // Serie de las facturas emitidas.
    'serie_factura' => env('POS_SERIE_FACTURA', 'A'),

    // Días de vigencia del cupón que se entrega con el código QR de la factura.
    'vigencia_cupon_dias' => (int) env('POS_VIGENCIA_CUPON_DIAS', 30),

    // Core de Empresas de Consumo Masivo (sistema existente).
    // archivo: lee storage/app/core/catalogo.json · http: consume la API del Core.
    'core' => [
        'driver' => env('CORE_DRIVER', 'archivo'),
        'url' => env('CORE_API_URL'),
        'token' => env('CORE_API_TOKEN'),
        'archivo' => storage_path('app/core/catalogo.json'),
    ],
];
