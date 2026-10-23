<?php

namespace App\Application\Inventario\Ports\In;

/**
 * Historial de movimientos de un producto.
 */
interface ConsultarKardexUseCase
{
    public function kardex(int $productoId): array;
}
