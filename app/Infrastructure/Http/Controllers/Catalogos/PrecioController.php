<?php

namespace App\Infrastructure\Http\Controllers\Catalogos;

use App\Application\Catalogos\Ports\In\ActualizarPreciosUseCase;
use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use Illuminate\Http\Request;

/**
 * Catálogo de precios: precio normal y precio de mayoreo por producto.
 */
class PrecioController
{
    public function index(Request $request, GestionarProductosUseCase $productos)
    {
        $familia = $request->integer('familia') ?: null;

        return view('catalogos.precios', [
            'productos' => $productos->listar($request->query('q'), $familia, true),
            'familias' => $productos->familias(), 'q' => $request->query('q'), 'familia' => $familia,
        ]);
    }

    public function actualizar(Request $request, ActualizarPreciosUseCase $precios)
    {
        $datos = $request->validate([
            'precios' => 'required|array',
            'precios.*.precio' => 'required|numeric|min:0.01',
            'precios.*.precio_mayoreo' => 'nullable|numeric|min:0.01',
        ]);
        $n = $precios->actualizar($datos['precios']);

        return back()->with('ok', "Se actualizaron los precios de {$n} producto(s).");
    }
}
