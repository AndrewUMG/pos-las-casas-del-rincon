<?php

namespace App\Application\Facturacion\Ports\In;

/**
 * Factura de una venta pagada con los datos del cliente.
 */
interface GenerarFacturaUseCase
{
    public function generar(int $ventaId, ?string $nit, ?string $nombre, ?string $direccion): int;
}
