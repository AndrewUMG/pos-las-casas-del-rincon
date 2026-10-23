<?php

namespace App\Application\Inventario\Ports\Out;

use App\Domain\Inventario\TipoMovimiento;

/**
 * Kardex de movimientos.
 */
interface MovimientoRepository
{
    public function registrar(int $productoId, TipoMovimiento $tipo, int $cantidad, int $existenciaResultante, string $referencia, string $descripcion, \DateTimeImmutable $fecha): void;
    public function kardex(int $productoId): array;
}
