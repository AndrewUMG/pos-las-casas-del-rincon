<?php

namespace App\Infrastructure\Http\Controllers\Promociones;

use App\Application\Promociones\Ports\In\ConsultarPromocionQrUseCase;

/**
 * Página pública que abre el celular del cliente al leer el QR de la factura.
 */
class PromocionQrController
{
    public function mostrar(string $token, ConsultarPromocionQrUseCase $consulta)
    {
        return view('promociones.qr', ['promo' => $consulta->consultar($token), 'token' => $token]);
    }
}
