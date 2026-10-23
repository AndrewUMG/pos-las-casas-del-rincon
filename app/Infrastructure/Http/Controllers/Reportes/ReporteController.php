<?php

namespace App\Infrastructure\Http\Controllers\Reportes;

use App\Application\Reportes\Ports\In\ReporteClientesQuery;
use App\Application\Reportes\Ports\In\ReporteComprasQuery;
use App\Application\Reportes\Ports\In\ReporteCorteCajaQuery;
use App\Application\Reportes\Ports\In\ReporteDevolucionesQuery;
use App\Application\Shared\Ports\GeneradorPdfPort;
use Illuminate\Http\Request;

class ReporteController
{
    private const TITULOS = [
        'corte-caja' => 'Cierre del día (corte de caja)',
        'devoluciones' => 'Reporte de devoluciones',
        'clientes' => 'Reporte de clientes',
        'compras' => 'Reporte de compras de productos para existencias',
    ];

    public function __construct(
        private ReporteCorteCajaQuery $corte,
        private ReporteDevolucionesQuery $devoluciones,
        private ReporteClientesQuery $clientes,
        private ReporteComprasQuery $compras,
        private GeneradorPdfPort $pdf,
    ) {
    }

    public function index()
    {
        return view('reportes.index', ['titulos' => self::TITULOS]);
    }

    public function mostrar(Request $request, string $tipo)
    {
        $desde = $request->query('desde', now()->format('Y-m-d'));
        $hasta = $request->query('hasta', now()->format('Y-m-d'));
        $datos = match ($tipo) {
            'corte-caja' => $this->corte->corteCaja($desde, $hasta),
            'devoluciones' => $this->devoluciones->devoluciones($desde, $hasta),
            'clientes' => $this->clientes->clientes($desde, $hasta),
            'compras' => $this->compras->compras($desde, $hasta),
        };
        $vista = ['tipo' => $tipo, 'titulo' => self::TITULOS[$tipo], 'desde' => $desde, 'hasta' => $hasta, 'datos' => $datos,
            'generado' => now()->format('d/m/Y H:i'), 'usuario' => $request->user()->nombre];

        if ($request->query('formato') === 'pdf') {
            return response($this->pdf->generar('reportes.pdf', $vista, 'landscape'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"reporte-{$tipo}-{$desde}-{$hasta}.pdf\"",
            ]);
        }

        return view('reportes.mostrar', $vista);
    }
}
