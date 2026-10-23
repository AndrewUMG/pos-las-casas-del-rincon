<?php

namespace App\Application\Catalogos\Ports\In;

use App\Domain\Catalogos\Proveedor;

/**
 * Catálogo de proveedores.
 */
interface GestionarProveedoresUseCase
{
    /** @return Proveedor[] */
    public function listar(?string $buscar = null): array;
    public function obtener(int $id): Proveedor;
    public function guardar(array $datos, ?int $id = null): Proveedor;
    public function eliminar(int $id): void;
}
