<?php

namespace App\Application\Ventas\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Lectura por línea de producto.
 */
interface AgregarProductoUseCase
{
    public function agregar(int $ventaId, string $codigo, int $cantidad): Venta;
}
