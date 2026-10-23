<?php

namespace App\Application\Facturacion\Ports\In;

/**
 * Datos de la factura para pantalla, impresión y PDF.
 */
interface ImprimirFacturaUseCase
{
    public function datos(int $facturaId): array;
    public function pdf(int $facturaId): string;
    public function listar(): array;

    /** Id de la factura emitida para la venta, si existe. */
    public function deVenta(int $ventaId): ?int;
}
