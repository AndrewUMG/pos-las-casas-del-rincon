<?php

namespace App\Application\Cobros\Ports\Out;

use App\Domain\Cobros\Pago;

/**
 * Persistencia de pagos.
 */
interface PagoRepository
{
    public function guardar(Pago $pago): void;
    public function deVenta(int $ventaId): ?array;
}
