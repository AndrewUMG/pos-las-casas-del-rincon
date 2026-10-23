<?php

namespace App\Infrastructure\Http\Controllers\Inventario;

use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use App\Application\Catalogos\Ports\In\GestionarProveedoresUseCase;
use App\Application\Inventario\Ports\In\ConsultarComprasUseCase;
use App\Application\Inventario\Ports\In\RegistrarCompraUseCase;
use Illuminate\Http\Request;

/**
 * Adaptador primario del registro de compras a proveedores.
 */
class CompraController
{
    public function __construct(private ConsultarComprasUseCase $compras)
    {
    }

    public function index()
    {
        return view('inventario.compras.index', ['compras' => $this->compras->listar()]);
    }

    public function nueva(GestionarProveedoresUseCase $proveedores, GestionarProductosUseCase $productos)
    {
        return view('inventario.compras.nueva', [
            'proveedores' => $proveedores->listar(), 'productos' => $productos->listar(null, null, true),
        ]);
    }

    public function guardar(Request $request, RegistrarCompraUseCase $registrar)
    {
        $datos = $request->validate([
            'proveedor_id' => 'required|integer', 'numero_documento' => 'required|string|max:40', 'fecha' => 'required|date',
            'lineas' => 'required|array|min:1', 'lineas.*.producto_id' => 'required|integer',
            'lineas.*.cantidad' => 'required|integer|min:1', 'lineas.*.costo_unitario' => 'required|numeric|min:0.01',
        ]);
        $id = $registrar->registrar((int) $datos['proveedor_id'], $request->user()->id, $datos['numero_documento'],
            $datos['fecha'], array_values($datos['lineas']));

        return redirect()->route('compras.mostrar', $id)->with('ok', 'Compra registrada: las existencias fueron actualizadas.');
    }

    public function mostrar(int $compra)
    {
        return view('inventario.compras.mostrar', ['compra' => $this->compras->obtener($compra)]);
    }
}
