<?php

namespace App\Application\Reportes\Ports\In;

/**
 * Cierre del día: ventas e ingresos (corte de caja).
 */
interface ReporteCorteCajaQuery
{
    public function corteCaja(string $desde, string $hasta): array;
}
