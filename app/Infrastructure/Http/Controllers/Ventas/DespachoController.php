<?php

namespace App\Infrastructure\Http\Controllers\Ventas;

use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Catalogos\Ports\In\GestionarClientesUseCase;
use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use App\Application\Ventas\Ports\In\AgregarProductoUseCase;
use App\Application\Ventas\Ports\In\CalcularTotalesUseCase;
use App\Application\Ventas\Ports\In\GestionarVentaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use App\Application\Ventas\Ports\In\QuitarLineaUseCase;
use App\Domain\Ventas\Venta;
use App\Infrastructure\Http\Presenters\VentaPresenter;
use Illuminate\Http\Request;

/**
 * Adaptador primario del despacho (punto de venta).
 */
class DespachoController
{
    public function __construct(private CalcularTotalesUseCase $ventas)
    {
    }

    public function pantalla(Request $request, IniciarVentaUseCase $iniciar, ConsultarCajaUseCase $cajas, GestionarClientesUseCase $clientes)
    {
        $venta = $iniciar->ventaActual($request->user()->id);
        $cliente = $venta?->clienteId ? $clientes->obtener($venta->clienteId) : null;

        return view('ventas.despacho', [
            'caja' => $cajas->abiertaDe($request->user()->id),
            'venta' => $venta ? VentaPresenter::arreglo($venta) : null,
            'cliente' => $cliente,
            'valorPunto' => (float) config('pos.valor_punto'),
        ]);
    }

    public function iniciar(Request $request, IniciarVentaUseCase $iniciar)
    {
        $iniciar->iniciar($request->user()->id);

        return redirect()->route('pos.despacho');
    }

    /** Lectura por línea: código de barras o código del producto. */
    public function agregar(Request $request, int $venta, AgregarProductoUseCase $agregar)
    {
        $this->propia($request, $venta);
        $datos = $request->validate(['codigo' => 'required|string|max:40', 'cantidad' => 'nullable|integer|min:1|max:999']);

        return $this->respuesta($request, $agregar->agregar($venta, $datos['codigo'], (int) ($datos['cantidad'] ?? 1)));
    }

    public function quitar(Request $request, int $venta, int $producto, QuitarLineaUseCase $quitar)
    {
        $this->propia($request, $venta);

        return $this->respuesta($request, $quitar->quitar($venta, $producto));
    }

    public function cliente(Request $request, int $venta, GestionarVentaUseCase $gestion)
    {
        $this->propia($request, $venta);
        $gestion->asignarCliente($venta, $request->input('nit'));

        return back()->with('ok', 'Cliente asignado a la venta.');
    }

    public function cupon(Request $request, int $venta, GestionarVentaUseCase $gestion)
    {
        $this->propia($request, $venta);
        $request->validate(['token' => 'required|string|max:255']);
        $gestion->canjearCupon($venta, $request->input('token'));

        return back()->with('ok', 'Cupón QR canjeado: la promoción se aplicó a la venta.');
    }

    public function puntos(Request $request, int $venta, GestionarVentaUseCase $gestion)
    {
        $this->propia($request, $venta);
        $datos = $request->validate(['puntos' => 'required|integer|min:1']);
        $gestion->canjearPuntos($venta, (int) $datos['puntos']);

        return back()->with('ok', "Se canjearon {$datos['puntos']} puntos.");
    }

    public function cancelar(Request $request, int $venta, GestionarVentaUseCase $gestion)
    {
        $this->propia($request, $venta);
        $gestion->cancelar($venta);

        return redirect()->route('pos.despacho')->with('ok', 'La venta fue cancelada.');
    }

    public function buscarProductos(Request $request, GestionarProductosUseCase $productos)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(array_map(fn ($p) => [
            'codigo' => $p->codigo, 'nombre' => $p->nombre, 'precio' => $p->precio, 'existencia' => $p->existencia,
        ], array_slice($productos->listar($q, null, true), 0, 15)));
    }

    private function propia(Request $request, int $ventaId): Venta
    {
        $venta = $this->ventas->obtener($ventaId);
        abort_if($venta->usuarioId !== $request->user()->id, 403, 'La venta pertenece a otro cajero.');

        return $venta;
    }

    private function respuesta(Request $request, Venta $venta)
    {
        return $request->expectsJson()
            ? response()->json(VentaPresenter::arreglo($venta))
            : redirect()->route('pos.despacho');
    }
}
