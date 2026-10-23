<?php

namespace App\Application\Facturacion\Ports\Out;

use App\Domain\Facturacion\Factura;

/**
 * Persistencia de facturas.
 */
interface FacturaRepository
{
    public function siguienteNumero(string $serie): int;
    public function guardar(Factura $factura): int;
    public function obtener(int $id): ?array;
    public function buscarPorNumero(string $serie, int $numero): ?array;
    public function deVenta(int $ventaId): ?int;
    public function listar(): array;
}
