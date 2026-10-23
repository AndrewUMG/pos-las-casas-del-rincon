<?php

namespace App\Application\Catalogos\Ports\Out;

use App\Domain\Catalogos\Proveedor;

/**
 * Persistencia de proveedores.
 */
interface ProveedorRepository
{
    public function buscarPorId(int $id): ?Proveedor;
    public function buscarPorNit(string $nit): ?Proveedor;

    /** @return Proveedor[] */
    public function listar(?string $buscar = null): array;
    public function guardar(Proveedor $proveedor): Proveedor;
    public function eliminar(int $id): void;
}
