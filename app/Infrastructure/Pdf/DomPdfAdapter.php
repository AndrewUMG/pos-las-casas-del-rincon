<?php

namespace App\Infrastructure\Pdf;

use App\Application\Shared\Ports\GeneradorPdfPort;
use Barryvdh\DomPDF\Facade\Pdf;

class DomPdfAdapter implements GeneradorPdfPort
{
    public function generar(string $plantilla, array $datos, string $orientacion = 'portrait'): string
    {
        return Pdf::loadView($plantilla, $datos)->setPaper('letter', $orientacion)->output();
    }
}
