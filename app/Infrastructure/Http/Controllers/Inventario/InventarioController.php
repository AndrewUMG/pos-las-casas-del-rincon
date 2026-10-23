<?php

namespace App\Infrastructure\Http\Controllers\Inventario;

use App\Application\Inventario\Ports\In\ConsultarExistenciasUseCase;
use App\Application\Inventario\Ports\In\ConsultarKardexUseCase;
use Illuminate\Http\Request;

class InventarioController
{
    public function existencias(Request $request, ConsultarExistenciasUseCase $existencias)
    {
        return view('inventario.existencias', ['productos' => $existencias->existencias($request->query('q')), 'q' => $request->query('q')]);
    }

    public function kardex(int $producto, ConsultarKardexUseCase $kardex)
    {
        return view('inventario.kardex', $kardex->kardex($producto));
    }
}
