<?php

namespace App\Application\Ventas\Ports\In;

use App\Domain\Ventas\Venta;

/**
 * Cliente, cupones, puntos y cancelación de la venta en proceso.
 */
interface GestionarVentaUseCase
{
    public function asignarCliente(int $ventaId, ?string $nit): Venta;
    public function canjearCupon(int $ventaId, string $token): Venta;
    public function canjearPuntos(int $ventaId, int $puntos): Venta;
    public function cancelar(int $ventaId): void;
}
