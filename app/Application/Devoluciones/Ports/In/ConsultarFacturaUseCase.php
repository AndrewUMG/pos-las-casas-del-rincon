<?php

namespace App\Application\Devoluciones\Ports\In;

/**
 * Productos devolvibles de una factura.
 */
interface ConsultarFacturaUseCase
{
    public function buscar(string $numero): ?array;
}
