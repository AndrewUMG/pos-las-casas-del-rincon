<?php

namespace App\Application\Inventario\Ports\In;

/**
 * Existencia actual por producto.
 */
interface ConsultarExistenciasUseCase
{
    public function existencias(?string $buscar = null): array;
}
