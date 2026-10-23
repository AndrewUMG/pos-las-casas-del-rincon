<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura['numero_completo'] }}</title>
    <style>body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #222; }</style>
</head>
<body>
@php
    // DomPDF incrusta el QR como imagen SVG codificada en base64.
    $qrImagen = $cupon ? 'data:image/svg+xml;base64,'.base64_encode($cupon['qr_svg']) : null;
@endphp
@include('ventas._factura-cuerpo', ['qrImagen' => $qrImagen])
</body>
</html>
