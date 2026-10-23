<?php

namespace App\Infrastructure\Http\Controllers\Ventas;

use App\Application\Cobros\Ports\In\CobrarVentaUseCase;
use App\Application\Ventas\Ports\In\CalcularTotalesUseCase;
use App\Domain\Cobros\MetodoPago;
use App\Domain\Shared\Dinero;
use App\Domain\Ventas\Venta;
use App\Infrastructure\Http\Presenters\VentaPresenter;
use Illuminate\Http\Request;

/**
 * Adaptador primario de la interfaz de cobro.
 */
class CobroController
{
    public function formulario(Request $request, int $venta, CalcularTotalesUseCase $ventas)
    {
        $v = $ventas->recalcular($venta);
        abort_if($v->usuarioId !== $request->user()->id, 403);
        if ($v->estado !== Venta::EN_PROCESO) {
            return redirect()->route('pos.factura', $venta);
        }
        if (! $v->tieneLineas()) {
            return redirect()->route('pos.despacho')->with('error', 'Agregue productos antes de cobrar.');
        }

        return view('ventas.cobro', ['venta' => VentaPresenter::arreglo($v), 'metodos' => MetodoPago::cases()]);
    }

    public function cobrar(Request $request, int $venta, CobrarVentaUseCase $cobro, CalcularTotalesUseCase $ventas)
    {
        abort_if($ventas->obtener($venta)->usuarioId !== $request->user()->id, 403);
        $datos = $request->validate([
            'metodo' => 'required|string',
            'monto_recibido' => 'nullable|numeric|min:0',
            'referencia_voucher' => 'nullable|string|max:40',
        ]);
        $pago = $cobro->cobrar($venta, $datos['metodo'],
            isset($datos['monto_recibido']) ? (float) $datos['monto_recibido'] : null, $datos['referencia_voucher'] ?? null);

        $mensaje = 'Venta cobrada con '.mb_strtolower($pago->metodo->etiqueta()).'.';
        if ($pago->metodo === MetodoPago::Efectivo) {
            $mensaje .= ' Cambio: '.Dinero::formato($pago->cambio);
        }

        return redirect()->route('pos.factura', $venta)->with('ok', $mensaje);
    }
}
