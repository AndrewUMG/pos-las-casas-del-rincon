<?php

namespace App\Application\Promociones\Ports\Out;

use App\Domain\Promociones\Promocion;

/**
 * Promociones registradas.
 */
interface PromocionRepository
{
    /** @return Promocion[] */
    public function listar(): array;
    public function buscarPorId(int $id): ?Promocion;

    /** @return Promocion[] */
    public function vigentes(string $modo, \DateTimeImmutable $hoy): array;
    public function guardar(Promocion $promocion): Promocion;
}
