<?php

namespace App\Application\Shared\Ports;

/**
 * Puerto hacia el contexto Inventario (usado por Despacho, Cobros y Devoluciones).
 */
interface InventarioPort
{
    public function descontarPorVenta(int $productoId, int $cantidad, string $referencia): void;
    public function darDeBajaPorDevolucion(int $productoId, int $cantidad, string $referencia, string $motivo): void;
}
