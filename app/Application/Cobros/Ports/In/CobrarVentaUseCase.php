<?php

namespace App\Application\Cobros\Ports\In;

use App\Domain\Cobros\Pago;

/**
 * Cobro de la venta en efectivo, crédito o débito.
 */
interface CobrarVentaUseCase
{
    public function cobrar(int $ventaId, string $metodo, ?float $montoRecibido, ?string $referencia): Pago;
}
