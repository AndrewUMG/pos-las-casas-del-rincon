<?php

namespace App\Application\Promociones\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Pago con puntos acumulados por el cliente.
 */
interface CanjearPuntosUseCase
{
    public function canjear(Venta $venta, int $puntos): void;
    public function devolver(int $clienteId, int $puntos): void;
    public function acreditar(int $clienteId, int $puntos): void;
}
