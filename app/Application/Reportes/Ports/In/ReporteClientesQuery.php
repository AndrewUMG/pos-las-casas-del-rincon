<?php

namespace App\Application\Reportes\Ports\In;

/**
 * Clientes y sus compras.
 */
interface ReporteClientesQuery
{
    public function clientes(string $desde, string $hasta): array;
}
