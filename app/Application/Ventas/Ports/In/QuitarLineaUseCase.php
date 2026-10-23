<?php

namespace App\Application\Ventas\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Corrige una línea del despacho.
 */
interface QuitarLineaUseCase
{
    public function quitar(int $ventaId, int $productoId): Venta;
}
