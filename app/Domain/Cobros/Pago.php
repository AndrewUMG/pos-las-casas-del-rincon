<?php

namespace App\Domain\Cobros;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Pago de una venta en efectivo, tarjeta de crédito o tarjeta de débito.
 */
class Pago
{
    private function __construct(
        public readonly int $ventaId,
        public readonly MetodoPago $metodo,
        public readonly float $monto,
        public readonly ?float $montoRecibido,
        public readonly float $cambio,
        public readonly ?string $referenciaVoucher,
        public readonly \DateTimeImmutable $fecha,
    ) {
    }

    public static function registrar(int $ventaId, MetodoPago $metodo, float $total, ?float $montoRecibido,
        ?string $referencia, \DateTimeImmutable $ahora): self
    {
        if ($metodo === MetodoPago::Efectivo) {
            if ($montoRecibido === null || $montoRecibido < $total) {
                throw new ReglaNegocioException('El monto recibido en efectivo debe ser mayor o igual al total.');
            }

            return new self($ventaId, $metodo, $total, Dinero::redondear($montoRecibido),
                Dinero::redondear($montoRecibido - $total), null, $ahora);
        }

        if ($referencia === null || trim($referencia) === '') {
            throw new ReglaNegocioException('Ingrese la referencia del voucher de la tarjeta.');
        }

        return new self($ventaId, $metodo, $total, null, 0, trim($referencia), $ahora);
    }
}
