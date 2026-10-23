<?php

namespace App\Application\Caja\Ports\In;

use App\Domain\Caja\Caja;

/**
 * Apertura de caja con su monto inicial.
 */
interface AbrirCajaUseCase
{
    public function abrir(int $usuarioId, float $montoInicial): Caja;
}
