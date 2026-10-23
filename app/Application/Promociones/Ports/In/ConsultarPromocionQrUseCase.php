<?php

namespace App\Application\Promociones\Ports\In;

/**
 * Lectura del QR de la factura desde el celular.
 */
interface ConsultarPromocionQrUseCase
{
    public function consultar(string $token): ?array;
}
