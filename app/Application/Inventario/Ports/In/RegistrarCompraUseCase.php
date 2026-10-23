<?php

namespace App\Application\Inventario\Ports\In;

/**
 * Registra el ingreso de mercadería comprada a un proveedor.
 */
interface RegistrarCompraUseCase
{
    /** @param array<int, array{producto_id:int, cantidad:int, costo_unitario:float}> $lineas */
    public function registrar(int $proveedorId, int $usuarioId, string $numeroDocumento, string $fecha, array $lineas): int;
}
