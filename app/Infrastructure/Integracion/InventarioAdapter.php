<?php

namespace App\Infrastructure\Integracion;

use App\Application\Inventario\Ports\In\AjustarExistenciasUseCase;
use App\Application\Shared\Ports\InventarioPort;

/**
 * Adaptador entre contextos: Despacho/Cobros/Devoluciones → Inventario.
 */
class InventarioAdapter implements InventarioPort
{
    public function __construct(private AjustarExistenciasUseCase $inventario)
    {
    }

    public function descontarPorVenta(int $productoId, int $cantidad, string $referencia): void
    {
        $this->inventario->registrarSalida($productoId, $cantidad, $referencia, 'Venta');
    }

    public function darDeBajaPorDevolucion(int $productoId, int $cantidad, string $referencia, string $motivo): void
    {
        $this->inventario->registrarBaja($productoId, $cantidad, $referencia, "Devolución por {$motivo} – producto dado de baja");
    }
}
