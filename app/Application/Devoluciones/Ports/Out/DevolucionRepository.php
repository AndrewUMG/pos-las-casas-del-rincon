<?php

namespace App\Application\Devoluciones\Ports\Out;

use App\Domain\Devoluciones\Devolucion;

/**
 * Persistencia de devoluciones.
 */
interface DevolucionRepository
{
    public function guardar(Devolucion $devolucion): int;

    /** @return array<int,int> producto_id => cantidad devuelta */
    public function devueltoPorProducto(int $facturaId): array;
    public function listar(): array;
    public function obtener(int $id): ?array;
}
