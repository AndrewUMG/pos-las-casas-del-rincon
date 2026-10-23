<?php

namespace App\Application\Devoluciones\Ports\In;

/**
 * Devolución de productos por daño o vencimiento.
 */
interface ProcesarDevolucionUseCase
{
    /** @param array<int,int> $cantidades producto_id => cantidad */
    public function procesar(int $facturaId, int $usuarioId, string $motivo, ?string $observacion, array $cantidades): int;
    public function listar(): array;
    public function obtener(int $id): array;
}
