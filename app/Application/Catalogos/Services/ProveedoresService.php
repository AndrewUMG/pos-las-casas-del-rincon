<?php

namespace App\Application\Catalogos\Services;

use App\Application\Catalogos\Ports\In\GestionarProveedoresUseCase;
use App\Application\Catalogos\Ports\Out\ProveedorRepository;
use App\Domain\Catalogos\Proveedor;
use App\Domain\Shared\ReglaNegocioException;

class ProveedoresService implements GestionarProveedoresUseCase
{
    public function __construct(private ProveedorRepository $proveedores)
    {
    }

    public function listar(?string $buscar = null): array
    {
        return $this->proveedores->listar($buscar);
    }

    public function obtener(int $id): Proveedor
    {
        return $this->proveedores->buscarPorId($id) ?? throw new ReglaNegocioException('El proveedor no existe.');
    }

    public function guardar(array $datos, ?int $id = null): Proveedor
    {
        $existente = $this->proveedores->buscarPorNit($datos['nit'] ?? '');
        if ($existente && $existente->id !== $id) {
            throw new ReglaNegocioException('Ya existe un proveedor con ese NIT.');
        }

        return $this->proveedores->guardar(new Proveedor($id, $datos['nit'] ?? '', $datos['nombre'] ?? '',
            $datos['contacto'] ?? null, $datos['telefono'] ?? null, $datos['direccion'] ?? null));
    }

    public function eliminar(int $id): void
    {
        $this->obtener($id);
        $this->proveedores->eliminar($id);
    }
}
