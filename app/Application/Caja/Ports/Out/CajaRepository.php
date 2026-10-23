<?php

namespace App\Application\Caja\Ports\Out;

use App\Domain\Caja\Caja;

/**
 * Persistencia de la caja.
 */
interface CajaRepository
{
    public function abiertaDe(int $usuarioId): ?Caja;
    public function buscarPorId(int $id): ?Caja;
    public function guardar(Caja $caja): Caja;
    public function historial(int $usuarioId): array;
}
