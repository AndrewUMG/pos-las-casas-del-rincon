<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use Illuminate\Http\Request;

class InicioController
{
    public function __invoke(Request $request, ConsultarCajaUseCase $cajas, IniciarVentaUseCase $ventas)
    {
        $caja = $cajas->abiertaDe($request->user()->id);

        return view('inicio', [
            'caja' => $caja,
            'resumen' => $caja ? $cajas->resumen($caja->id) : null,
            'ventaEnProceso' => $ventas->ventaActual($request->user()->id),
        ]);
    }
}
