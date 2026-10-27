{{-- Contenido de la factura, compartido por la vista en pantalla y el PDF --}}
<table style="width:100%; border-collapse:collapse">
    <tr>
        <td style="vertical-align:top">
            <div style="font-size:20px; font-weight:bold">Las Casas del Rincón Guatemalteco</div>
            <div style="color:#555; font-size:12px">Venta de productos a colonias residenciales · Guatemala</div>
        </td>
        <td style="text-align:right; vertical-align:top">
            <div style="font-size:16px; font-weight:bold">FACTURA</div>
            <div>Serie {{ $factura['serie'] }} · No. {{ str_pad($factura['numero'], 6, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:12px; color:#555">{{ $factura['fecha'] }}</div>
        </td>
    </tr>
</table>
<hr>
<table style="width:100%; font-size:13px">
    <tr><td style="width:90px"><strong>NIT:</strong></td><td>{{ $factura['nit'] }}</td>
        <td style="text-align:right"><strong>Cajero:</strong> {{ $factura['cajero'] }}</td></tr>
    <tr><td><strong>Nombre:</strong></td><td colspan="2">{{ $factura['nombre'] }}</td></tr>
    <tr><td><strong>Dirección:</strong></td><td colspan="2">{{ $factura['direccion'] }}</td></tr>
</table>
<table style="width:100%; border-collapse:collapse; margin-top:12px; font-size:13px">
    <thead>
        <tr style="border-bottom:2px solid #333">
            <th style="text-align:left; padding:4px">Cant.</th><th style="text-align:left; padding:4px">Detalle</th>
            <th style="text-align:right; padding:4px">P. unit.</th><th style="text-align:right; padding:4px">Desc.</th>
            <th style="text-align:right; padding:4px">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($factura['lineas'] as $l)
            <tr style="border-bottom:1px solid #ddd">
                <td style="padding:4px">{{ $l['cantidad'] }}</td>
                <td style="padding:4px">{{ $l['nombre'] }}@if ($l['promocion'])<br><small style="color:#198754">{{ $l['promocion'] }}</small>@endif</td>
                <td style="text-align:right; padding:4px">{{ q($l['precio_unitario']) }}</td>
                <td style="text-align:right; padding:4px">{{ $l['descuento'] > 0 ? '−'.q($l['descuento']) : '' }}</td>
                <td style="text-align:right; padding:4px">{{ q($l['subtotal']) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table style="width:100%; margin-top:8px; font-size:13px">
    <tr><td style="text-align:right">Subtotal:</td><td style="text-align:right; width:120px">{{ q($factura['subtotal']) }}</td></tr>
    @if ($factura['descuento_vale'] > 0)
        <tr><td style="text-align:right">Vale de regalo:</td><td style="text-align:right">−{{ q($factura['descuento_vale']) }}</td></tr>
    @endif
    @if ($factura['descuento_puntos'] > 0)
        <tr><td style="text-align:right">Pago con {{ $factura['puntos_canjeados'] }} puntos:</td><td style="text-align:right">−{{ q($factura['descuento_puntos']) }}</td></tr>
    @endif
    @if ($factura['descuento'] > 0)
        <tr><td style="text-align:right">Total descuentos:</td><td style="text-align:right">−{{ q($factura['descuento']) }}</td></tr>
    @endif
    <tr><td style="text-align:right; font-size:16px"><strong>Total a pagar:</strong></td><td style="text-align:right; font-size:16px"><strong>{{ q($factura['total']) }}</strong></td></tr>
    @if ($factura['pago'])
        <tr><td style="text-align:right; color:#555">Forma de pago:</td><td style="text-align:right; color:#555">{{ $factura['pago']['metodo'] }}</td></tr>
        @if ($factura['pago']['monto_recibido'] !== null)
            <tr><td style="text-align:right; color:#555">Recibido / Cambio:</td><td style="text-align:right; color:#555">{{ q($factura['pago']['monto_recibido']) }} / {{ q($factura['pago']['cambio']) }}</td></tr>
        @endif
    @endif
    @if ($factura['puntos_ganados'] > 0 && $factura['nit'] !== 'CF')
        <tr><td style="text-align:right; color:#198754">Puntos ganados:</td><td style="text-align:right; color:#198754">{{ $factura['puntos_ganados'] }}</td></tr>
    @endif
</table>

@if ($cupon)
    <table style="width:100%; margin-top:16px; border:1px solid #e8a317; background:#fff4d9; border-radius:10px">
        <tr>
            <td class="qr" style="width:190px; text-align:center; padding:8px">
                @isset($qrImagen)<img src="{{ $qrImagen }}" style="width:170px; height:170px">@else{!! $cupon['qr_svg'] !!}@endisset
            </td>
            <td style="padding:8px; vertical-align:middle">
                <div style="font-weight:bold; color:#1c1d1a">¡Escanea este código con tu celular!</div>
                <div style="font-size:13px">Descubre la promoción que ganaste con esta compra y úsala en tu próxima visita.</div>
                <div style="font-size:12px; color:#555; margin-top:6px">Código: <strong>{{ $cupon['token'] }}</strong> · Válido hasta {{ $cupon['vence'] }}</div>
            </td>
        </tr>
    </table>
@endif
<p style="text-align:center; font-size:11px; color:#666; margin-top:16px">¡Gracias por su compra!</p>
