<?php

namespace App\Application\Ventas\Ports\Out;

use App\Domain\Ventas\Venta;

/**
 * Persistencia de la venta (raíz de agregado).
 */
interface VentaRepository
{
    public function buscarPorId(int $id): ?Venta;
    public function enProcesoDe(int $usuarioId): ?Venta;
    public function guardar(Venta $venta): Venta;
    public function eliminar(int $id): void;
}
