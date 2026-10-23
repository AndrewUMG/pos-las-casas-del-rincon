<?php

namespace App\Application\Reportes\Ports\In;

/**
 * Devoluciones por motivo y fecha.
 */
interface ReporteDevolucionesQuery
{
    public function devoluciones(string $desde, string $hasta): array;
}
