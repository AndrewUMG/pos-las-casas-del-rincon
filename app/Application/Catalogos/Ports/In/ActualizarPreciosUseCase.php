<?php

namespace App\Application\Catalogos\Ports\In;

/**
 * Precio normal y precio de mayoreo de los productos.
 */
interface ActualizarPreciosUseCase
{
    /** @param array<int, array{precio: float, precio_mayoreo: ?float}> $precios */
    public function actualizar(array $precios): int;
}
