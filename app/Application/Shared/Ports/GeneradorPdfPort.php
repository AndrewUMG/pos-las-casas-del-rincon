<?php

namespace App\Application\Shared\Ports;

/**
 * Genera un documento PDF a partir de una plantilla.
 */
interface GeneradorPdfPort
{
    /** @return string contenido binario del PDF */
    public function generar(string $plantilla, array $datos, string $orientacion = 'portrait'): string;
}
