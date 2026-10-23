<?php

namespace App\Application\Caja\Ports\In;

use App\Domain\Caja\Caja;

/**
 * Cierre de caja con su corte.
 */
interface CerrarCajaUseCase
{
    public function cerrar(int $usuarioId, float $montoContado): Caja;
}
