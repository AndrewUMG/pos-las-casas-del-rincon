<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; } h1 { font-size: 18px; margin: 0; }</style>
</head>
<body>
<table style="width:100%; border-bottom:2px solid #14705c; margin-bottom:8px">
    <tr>
        <td><div style="font-weight:bold; color:#14705c">Las Casas del Rincón Guatemalteco</div><h1>{{ $titulo }}</h1></td>
        <td style="text-align:right; font-size:11px; color:#555">Generado: {{ $generado }}<br>Por: {{ $usuario }}</td>
    </tr>
</table>
@include('reportes._contenido')
</body>
</html>
