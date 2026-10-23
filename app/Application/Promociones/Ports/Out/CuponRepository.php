<?php

namespace App\Application\Promociones\Ports\Out;

use App\Domain\Promociones\Cupon;

/**
 * Cupones emitidos con el QR.
 */
interface CuponRepository
{
    public function buscarPorToken(string $token): ?Cupon;

    /** @return Cupon[] */
    public function canjeadosEnVenta(int $ventaId): array;
    public function deFactura(int $facturaId): ?Cupon;
    public function guardar(Cupon $cupon): Cupon;
    public function listar(): array;
}
