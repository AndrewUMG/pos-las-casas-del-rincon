<?php

namespace App\Application\Promociones\Ports\In;

use App\Domain\Promociones\Promocion;

/**
 * Tipos de promoción y su vigencia.
 */
interface ConfigurarPromocionUseCase
{
    /** @return Promocion[] */
    public function listar(): array;
    public function obtener(int $id): Promocion;
    public function guardar(array $datos, ?int $id = null): Promocion;
    public function cupones(): array;
}
