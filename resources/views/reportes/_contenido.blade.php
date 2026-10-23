{{-- Contenido de los reportes (pantalla y PDF) --}}
@php
    $th = 'style="text-align:left; padding:5px; border-bottom:2px solid #444; font-size:11px; text-transform:uppercase"';
    $thr = 'style="text-align:right; padding:5px; border-bottom:2px solid #444; font-size:11px; text-transform:uppercase"';
    $td = 'style="padding:5px; border-bottom:1px solid #ddd"';
    $tdr = 'style="padding:5px; border-bottom:1px solid #ddd; text-align:right"';
@endphp
<p style="color:#555; font-size:13px">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>

@if ($tipo === 'corte-caja')
    <table style="width:100%; border-collapse:collapse; font-size:12px">
        <thead><tr><th {!! $th !!}>Caja</th><th {!! $th !!}>Cajero</th><th {!! $th !!}>Apertura</th><th {!! $th !!}>Cierre</th>
            <th {!! $thr !!}>Inicial</th><th {!! $thr !!}>Ventas</th><th {!! $thr !!}>Efectivo</th><th {!! $thr !!}>Crédito</th><th {!! $thr !!}>Débito</th>
            <th {!! $thr !!}>Devol.</th><th {!! $thr !!}>Esperado</th><th {!! $thr !!}>Contado</th><th {!! $thr !!}>Diferencia</th></tr></thead>
        <tbody>
        @forelse ($datos['cajas'] as $c)
            <tr><td {!! $td !!}>#{{ $c['id'] }}</td><td {!! $td !!}>{{ $c['cajero'] }}</td><td {!! $td !!}>{{ $c['apertura'] }}</td>
                <td {!! $td !!}>{{ $c['cierre'] ?? 'Abierta' }}</td><td {!! $tdr !!}>{{ q($c['monto_inicial']) }}</td>
                <td {!! $tdr !!}>{{ $c['num_ventas'] }}</td><td {!! $tdr !!}>{{ q($c['efectivo']) }}</td><td {!! $tdr !!}>{{ q($c['credito']) }}</td>
                <td {!! $tdr !!}>{{ q($c['debito']) }}</td><td {!! $tdr !!}>{{ q($c['egresos']) }}</td><td {!! $tdr !!}>{{ q($c['esperado']) }}</td>
                <td {!! $tdr !!}>{{ $c['contado'] !== null ? q($c['contado']) : '—' }}</td>
                <td {!! $tdr !!}>{{ $c['diferencia'] !== null ? q($c['diferencia']) : '—' }}</td></tr>
        @empty
            <tr><td colspan="13" {!! $td !!}>No hay cajas en el período.</td></tr>
        @endforelse
        </tbody>
    </table>
    <table style="margin-top:14px; font-size:13px">
        <tr><td style="padding:2px 12px 2px 0">Número de ventas:</td><td style="text-align:right"><strong>{{ $datos['totales']['num_ventas'] }}</strong></td></tr>
        <tr><td style="padding:2px 12px 2px 0">Ventas en efectivo:</td><td style="text-align:right">{{ q($datos['totales']['efectivo']) }}</td></tr>
        <tr><td style="padding:2px 12px 2px 0">Ventas con tarjeta de crédito:</td><td style="text-align:right">{{ q($datos['totales']['credito']) }}</td></tr>
        <tr><td style="padding:2px 12px 2px 0">Ventas con tarjeta de débito:</td><td style="text-align:right">{{ q($datos['totales']['debito']) }}</td></tr>
        <tr><td style="padding:2px 12px 2px 0"><strong>Total de ventas e ingresos:</strong></td><td style="text-align:right"><strong>{{ q($datos['totales']['ventas']) }}</strong></td></tr>
        <tr><td style="padding:2px 12px 2px 0">Devoluciones (egresos):</td><td style="text-align:right">−{{ q($datos['totales']['egresos']) }}</td></tr>
        <tr><td style="padding:2px 12px 2px 0"><strong>Neto del período:</strong></td><td style="text-align:right"><strong>{{ q($datos['totales']['ventas'] - $datos['totales']['egresos']) }}</strong></td></tr>
    </table>

@elseif ($tipo === 'devoluciones')
    <table style="width:100%; border-collapse:collapse; font-size:12px">
        <thead><tr><th {!! $th !!}>#</th><th {!! $th !!}>Fecha</th><th {!! $th !!}>Factura</th><th {!! $th !!}>Cliente</th><th {!! $th !!}>Motivo</th>
            <th {!! $th !!}>Productos</th><th {!! $th !!}>Observación</th><th {!! $th !!}>Registró</th><th {!! $thr !!}>Reembolso</th></tr></thead>
        <tbody>
        @forelse ($datos['devoluciones'] as $d)
            <tr><td {!! $td !!}>{{ $d['id'] }}</td><td {!! $td !!}>{{ $d['fecha'] }}</td><td {!! $td !!}>{{ $d['factura'] }}</td><td {!! $td !!}>{{ $d['cliente'] }}</td>
                <td {!! $td !!}>{{ $d['motivo'] }}</td><td {!! $td !!}>{{ $d['productos'] }}</td><td {!! $td !!}>{{ $d['observacion'] }}</td>
                <td {!! $td !!}>{{ $d['usuario'] }}</td><td {!! $tdr !!}>{{ q($d['total']) }}</td></tr>
        @empty
            <tr><td colspan="9" {!! $td !!}>No hay devoluciones en el período.</td></tr>
        @endforelse
        </tbody>
    </table>
    <table style="margin-top:14px; font-size:13px">
        @foreach ($datos['por_motivo'] as $motivo => $total)
            <tr><td style="padding:2px 12px 2px 0">Por {{ mb_strtolower($motivo) }}:</td><td style="text-align:right">{{ q($total) }}</td></tr>
        @endforeach
        <tr><td style="padding:2px 12px 2px 0"><strong>Total devuelto:</strong></td><td style="text-align:right"><strong>{{ q($datos['total']) }}</strong></td></tr>
    </table>

@elseif ($tipo === 'clientes')
    <table style="width:100%; border-collapse:collapse; font-size:12px">
        <thead><tr><th {!! $th !!}>NIT</th><th {!! $th !!}>Cliente</th><th {!! $th !!}>Teléfono</th><th {!! $th !!}>Correo</th>
            <th {!! $thr !!}>Facturas</th><th {!! $thr !!}>Total comprado</th><th {!! $th !!}>Última compra</th><th {!! $thr !!}>Puntos</th></tr></thead>
        <tbody>
        @foreach ($datos['clientes'] as $c)
            <tr><td {!! $td !!}>{{ $c['nit'] }}</td><td {!! $td !!}>{{ $c['nombre'] }}</td><td {!! $td !!}>{{ $c['telefono'] }}</td><td {!! $td !!}>{{ $c['email'] }}</td>
                <td {!! $tdr !!}>{{ $c['facturas'] }}</td><td {!! $tdr !!}>{{ q($c['total']) }}</td><td {!! $td !!}>{{ $c['ultima'] ?? '—' }}</td>
                <td {!! $tdr !!}>{{ $c['puntos'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <p style="margin-top:14px; font-size:13px">Clientes con compras en el período: <strong>{{ $datos['con_compras'] }}</strong> ·
        Total facturado: <strong>{{ q($datos['total']) }}</strong></p>

@elseif ($tipo === 'compras')
    <h3 style="font-size:15px">Compras registradas</h3>
    <table style="width:100%; border-collapse:collapse; font-size:12px">
        <thead><tr><th {!! $th !!}>#</th><th {!! $th !!}>Fecha</th><th {!! $th !!}>Documento</th><th {!! $th !!}>Proveedor</th><th {!! $thr !!}>Total</th></tr></thead>
        <tbody>
        @forelse ($datos['compras'] as $c)
            <tr><td {!! $td !!}>{{ $c['id'] }}</td><td {!! $td !!}>{{ $c['fecha'] }}</td><td {!! $td !!}>{{ $c['documento'] }}</td>
                <td {!! $td !!}>{{ $c['proveedor'] }}</td><td {!! $tdr !!}>{{ q($c['total']) }}</td></tr>
        @empty
            <tr><td colspan="5" {!! $td !!}>No hay compras en el período.</td></tr>
        @endforelse
        </tbody>
    </table>
    <h3 style="font-size:15px; margin-top:18px">Productos comprados para existencias</h3>
    <table style="width:100%; border-collapse:collapse; font-size:12px">
        <thead><tr><th {!! $th !!}>Código</th><th {!! $th !!}>Producto</th><th {!! $th !!}>Familia</th><th {!! $thr !!}>Cantidad</th>
            <th {!! $thr !!}>Costo prom.</th><th {!! $thr !!}>Total</th><th {!! $thr !!}>Existencia actual</th></tr></thead>
        <tbody>
        @foreach ($datos['productos'] as $p)
            <tr><td {!! $td !!}>{{ $p['codigo'] }}</td><td {!! $td !!}>{{ $p['nombre'] }}</td><td {!! $td !!}>{{ $p['familia'] }}</td>
                <td {!! $tdr !!}>{{ $p['cantidad'] }}</td><td {!! $tdr !!}>{{ q($p['costo_promedio']) }}</td><td {!! $tdr !!}>{{ q($p['total']) }}</td>
                <td {!! $tdr !!}>{{ $p['existencia'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <p style="margin-top:14px; font-size:13px">Total comprado en el período: <strong>{{ q($datos['total']) }}</strong></p>
@endif
