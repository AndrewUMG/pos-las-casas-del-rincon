<?php

namespace App\Application\Reportes\Services;

use App\Application\Reportes\Ports\In\ReporteClientesQuery;
use App\Application\Reportes\Ports\In\ReporteComprasQuery;
use App\Application\Reportes\Ports\In\ReporteCorteCajaQuery;
use App\Application\Reportes\Ports\In\ReporteDevolucionesQuery;
use App\Application\Reportes\Ports\Out\ReportesQueryPort;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Reportes de solo lectura filtrados por rango de fechas.
 */
class ReportesService implements ReporteCorteCajaQuery, ReporteDevolucionesQuery, ReporteClientesQuery, ReporteComprasQuery
{
    public function __construct(private ReportesQueryPort $consultas)
    {
    }

    public function corteCaja(string $desde, string $hasta): array
    {
        return $this->consultas->corteCaja(...$this->rango($desde, $hasta));
    }

    public function devoluciones(string $desde, string $hasta): array
    {
        return $this->consultas->devoluciones(...$this->rango($desde, $hasta));
    }

    public function clientes(string $desde, string $hasta): array
    {
        return $this->consultas->clientes(...$this->rango($desde, $hasta));
    }

    public function compras(string $desde, string $hasta): array
    {
        return $this->consultas->compras(...$this->rango($desde, $hasta));
    }

    private function rango(string $desde, string $hasta): array
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $desde);
        $h = \DateTimeImmutable::createFromFormat('Y-m-d', $hasta);
        if (! $d || ! $h) {
            throw new ReglaNegocioException('Indique un rango de fechas válido.');
        }
        if ($h < $d) {
            throw new ReglaNegocioException('La fecha final no puede ser anterior a la inicial.');
        }

        return [$d->format('Y-m-d'), $h->format('Y-m-d')];
    }
}
