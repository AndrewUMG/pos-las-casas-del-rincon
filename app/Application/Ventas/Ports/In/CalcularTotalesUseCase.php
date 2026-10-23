<?php

namespace App\Application\Ventas\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Recalcula subtotales por línea y total de la compra.
 */
interface CalcularTotalesUseCase
{
    public function recalcular(int $ventaId): Venta;
    public function obtener(int $ventaId): Venta;
}
