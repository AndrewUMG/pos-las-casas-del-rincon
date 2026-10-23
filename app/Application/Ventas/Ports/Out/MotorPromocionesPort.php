<?php

namespace App\Application\Ventas\Ports\Out;

use App\Domain\Ventas\Venta;
use App\Domain\Promociones\Promocion;

/**
 * Puerto hacia el contexto Promociones.
 */
interface MotorPromocionesPort
{
    public function aplicar(Venta $venta): void;
    public function canjearCupon(Venta $venta, string $token): Promocion;
    public function canjearPuntos(Venta $venta, int $puntos): void;
    public function liberar(Venta $venta): void;
}
