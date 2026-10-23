<?php

namespace App\Application\Facturacion\Ports\Out;

/**
 * Puerto hacia Promociones para emitir el cupón del QR.
 */
interface AsignarPromocionPort
{
    public function emitirCupon(int $facturaId, ?int $clienteId): ?array;
    public function cuponDeFactura(int $facturaId): ?array;
    public function acreditarPuntos(int $clienteId, int $puntos): void;
}
