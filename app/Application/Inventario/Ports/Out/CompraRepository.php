<?php

namespace App\Application\Inventario\Ports\Out;

use App\Domain\Inventario\Compra;

/**
 * Persistencia de compras.
 */
interface CompraRepository
{
    public function guardar(Compra $compra): int;
    public function listar(): array;
    public function obtener(int $id): ?array;
}
