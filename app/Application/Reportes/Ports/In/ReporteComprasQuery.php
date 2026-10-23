<?php

namespace App\Application\Reportes\Ports\In;

/**
 * Compras de productos para existencias.
 */
interface ReporteComprasQuery
{
    public function compras(string $desde, string $hasta): array;
}
