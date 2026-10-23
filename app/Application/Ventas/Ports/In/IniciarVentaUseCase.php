<?php

namespace App\Application\Ventas\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Nueva venta en la caja abierta del cajero.
 */
interface IniciarVentaUseCase
{
    public function ventaActual(int $usuarioId): ?Venta;
    public function iniciar(int $usuarioId): Venta;
}
