<?php

namespace App\Application\Caja\Ports\In;

use App\Domain\Caja\Caja;

/**
 * Estado y resumen de la caja.
 */
interface ConsultarCajaUseCase
{
    public function abiertaDe(int $usuarioId): ?Caja;
    public function obtener(int $cajaId): Caja;
    public function resumen(int $cajaId): array;
    public function historial(int $usuarioId): array;
}
