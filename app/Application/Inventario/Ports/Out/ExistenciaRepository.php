<?php

namespace App\Application\Inventario\Ports\Out;

use App\Domain\Catalogos\Producto;

/**
 * Existencia actual de los productos.
 */
interface ExistenciaRepository
{
    public function producto(int $productoId): ?Producto;
    public function actualizarExistencia(int $productoId, int $existencia): void;
    public function listar(?string $buscar = null): array;
}
