<?php

namespace App\Infrastructure\Http\Controllers\Ventas;

use App\Application\Catalogos\Ports\In\GestionarClientesUseCase;
use App\Application\Facturacion\Ports\In\GenerarFacturaUseCase;
use App\Application\Facturacion\Ports\In\ImprimirFacturaUseCase;
use App\Application\Ventas\Ports\In\CalcularTotalesUseCase;
use App\Domain\Ventas\Venta;
use App\Infrastructure\Http\Presenters\VentaPresenter;
use Illuminate\Http\Request;

/**
 * Adaptador primario de la interfaz de facturación.
 */
class FacturaController
{
    public function formulario(int $venta, CalcularTotalesUseCase $ventas, GestionarClientesUseCase $clientes, ImprimirFacturaUseCase $facturas)
    {
        $v = $ventas->obtener($venta);
        if ($v->estado === Venta::FACTURADA) {
            return redirect()->route('facturas.mostrar', $facturas->deVenta($venta));
        }
        if ($v->estado !== Venta::PAGADA) {
            return redirect()->route('pos.cobro', $venta);
        }

        return view('ventas.factura-datos', [
            'venta' => VentaPresenter::arreglo($v),
            'cliente' => $v->clienteId ? $clientes->obtener($v->clienteId) : null,
        ]);
    }

    public function generar(Request $request, int $venta, GenerarFacturaUseCase $facturacion)
    {
        $datos = $request->validate([
            'nit' => 'nullable|string|max:20', 'nombre' => 'nullable|string|max:150', 'direccion' => 'nullable|string|max:255',
        ]);
        $id = $facturacion->generar($venta, $datos['nit'] ?? null, $datos['nombre'] ?? null, $datos['direccion'] ?? null);

        return redirect()->route('facturas.mostrar', $id)->with('ok', 'Factura generada.');
    }

    public function index(ImprimirFacturaUseCase $facturas)
    {
        return view('ventas.facturas', ['facturas' => $facturas->listar()]);
    }

    public function mostrar(int $factura, ImprimirFacturaUseCase $facturas)
    {
        return view('ventas.factura', $facturas->datos($factura));
    }

    public function pdf(int $factura, ImprimirFacturaUseCase $facturas)
    {
        $datos = $facturas->datos($factura);

        return response($facturas->pdf($factura), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura-'.$datos['factura']['numero_completo'].'.pdf"',
        ]);
    }
}
