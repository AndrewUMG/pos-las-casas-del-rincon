<?php

namespace App\Infrastructure\Qr;

use App\Application\Facturacion\Ports\Out\GeneradorQrPort;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SimpleQrCodeAdapter implements GeneradorQrPort
{
    public function svg(string $contenido, int $tamano = 180): string
    {
        $svg = (string) QrCode::format('svg')->size($tamano)->margin(1)->errorCorrection('M')->generate($contenido);

        // Se quita la declaración XML para poder incrustarlo en HTML.
        return preg_replace('/^<\?xml.*?\?>\s*/', '', $svg);
    }

    public function urlPromocion(string $token): string
    {
        return route('promocion.qr', $token);
    }
}
