<?php

namespace App\Infrastructure\Http\Controllers\Devoluciones;

use App\Application\Devoluciones\Ports\In\ConsultarFacturaUseCase;
use App\Application\Devoluciones\Ports\In\ProcesarDevolucionUseCase;
use App\Domain\Devoluciones\MotivoDevolucion;
use Illuminate\Http\Request;

/**
 * Adaptador primario de la interfaz de devoluciones.
 */
class DevolucionController
{
    public function __construct(private ProcesarDevolucionUseCase $devoluciones)
    {
    }

    public function index()
    {
        return view('devoluciones.index', ['devoluciones' => $this->devoluciones->listar()]);
    }

    public function nueva(Request $request, ConsultarFacturaUseCase $consulta)
    {
        $numero = trim((string) $request->query('numero'));
        $factura = $numero !== '' ? $consulta->buscar($numero) : null;
        if ($numero !== '' && ! $factura) {
            session()->now('error', "No se encontró la factura «{$numero}».");
        }

        return view('devoluciones.nueva', ['numero' => $numero, 'factura' => $factura, 'motivos' => MotivoDevolucion::cases()]);
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'factura_id' => 'required|integer',
            'motivo' => 'required|string',
            'observacion' => 'nullable|string|max:255',
            'cantidades' => 'required|array',
            'cantidades.*' => 'nullable|integer|min:0',
        ]);
        $id = $this->devoluciones->procesar((int) $datos['factura_id'], $request->user()->id, $datos['motivo'],
            $datos['observacion'] ?? null, array_map('intval', array_filter($datos['cantidades'])));

        return redirect()->route('devoluciones.mostrar', $id)->with('ok', 'Devolución registrada y reembolso descontado de caja.');
    }

    public function mostrar(int $devolucion)
    {
        return view('devoluciones.mostrar', ['devolucion' => $this->devoluciones->obtener($devolucion)]);
    }
}
