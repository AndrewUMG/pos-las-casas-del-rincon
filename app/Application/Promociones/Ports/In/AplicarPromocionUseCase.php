<?php

namespace App\Application\Promociones\Ports\In;

use App\Domain\Ventas\Venta;
use App\Domain\Promociones\Promocion;

/**
 * Aplica promociones y cupones a una venta; emite cupones para la factura.
 */
interface AplicarPromocionUseCase
{
    public function aplicar(Venta $venta): void;
    public function canjearCupon(Venta $venta, string $token): Promocion;
    public function liberarCupones(int $ventaId): void;

    /** @return array{token:string, promocion:string, detalle:string}|null */
    public function emitirCupon(int $facturaId, ?int $clienteId): ?array;
    public function cuponDeFactura(int $facturaId): ?array;
}
