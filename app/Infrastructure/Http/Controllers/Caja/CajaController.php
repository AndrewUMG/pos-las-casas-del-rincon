<?php

namespace App\Infrastructure\Http\Controllers\Caja;

use App\Application\Caja\Ports\In\AbrirCajaUseCase;
use App\Application\Caja\Ports\In\CerrarCajaUseCase;
use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use App\Domain\Shared\Dinero;
use Illuminate\Http\Request;

/**
 * Adaptador primario de la apertura de caja y del corte de caja.
 */
class CajaController
{
    public function __construct(private ConsultarCajaUseCase $cajas)
    {
    }

    public function index(Request $request)
    {
        $caja = $this->cajas->abiertaDe($request->user()->id);

        return view('caja.index', [
            'caja' => $caja,
            'resumen' => $caja ? $this->cajas->resumen($caja->id) : null,
            'historial' => $this->cajas->historial($request->user()->id),
        ]);
    }

    public function abrir(Request $request, AbrirCajaUseCase $abrir)
    {
        $datos = $request->validate(['monto_inicial' => 'required|numeric|min:0']);
        $abrir->abrir($request->user()->id, (float) $datos['monto_inicial']);

        return redirect()->route('pos.despacho')->with('ok', 'Caja abierta con '.Dinero::formato((float) $datos['monto_inicial']).'.');
    }

    public function cerrar(Request $request, CerrarCajaUseCase $cerrar, IniciarVentaUseCase $ventas)
    {
        $datos = $request->validate(['monto_contado' => 'required|numeric|min:0']);
        if ($ventas->ventaActual($request->user()->id)?->tieneLineas()) {
            return back()->with('error', 'Tiene una venta en proceso: cóbrela o cancélela antes del corte de caja.');
        }
        $caja = $cerrar->cerrar($request->user()->id, (float) $datos['monto_contado']);

        return redirect()->route('caja.mostrar', $caja->id)->with('ok', 'Caja cerrada. Corte de caja generado.');
    }

    public function mostrar(Request $request, int $caja)
    {
        $resumen = $this->cajas->resumen($caja);
        $esAdmin = in_array('administracion', $request->attributes->get('permisos', []), true);
        abort_if($resumen['caja']->usuarioId !== $request->user()->id && ! $esAdmin, 403);

        return view('caja.corte', ['resumen' => $resumen]);
    }
}
