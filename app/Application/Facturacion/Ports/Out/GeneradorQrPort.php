<?php

namespace App\Application\Facturacion\Ports\Out;

/**
 * Imagen del código QR.
 */
interface GeneradorQrPort
{
    /** @return string imagen SVG */
    public function svg(string $contenido, int $tamano = 180): string;
    public function urlPromocion(string $token): string;
}
