<?php

namespace App\Application\Cobros\Ports\In;

/**
 * Cambio del pago en efectivo.
 */
interface CalcularCambioUseCase
{
    public function cambio(float $total, float $montoRecibido): float;
}
