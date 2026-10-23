<?php

namespace App\Application\Reportes\Ports\Out;

/**
 * Consultas de solo lectura para los reportes.
 */
interface ReportesQueryPort
{
    public function corteCaja(string $desde, string $hasta): array;
    public function devoluciones(string $desde, string $hasta): array;
    public function clientes(string $desde, string $hasta): array;
    public function compras(string $desde, string $hasta): array;
}
