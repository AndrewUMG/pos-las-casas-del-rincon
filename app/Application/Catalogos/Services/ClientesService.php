<?php

namespace App\Application\Catalogos\Services;

use App\Application\Catalogos\Ports\In\GestionarClientesUseCase;
use App\Application\Catalogos\Ports\Out\ClienteRepository;
use App\Domain\Catalogos\Cliente;
use App\Domain\Shared\ReglaNegocioException;

class ClientesService implements GestionarClientesUseCase
{
    public function __construct(private ClienteRepository $clientes)
    {
    }

    public function listar(?string $buscar = null): array
    {
        return $this->clientes->listar($buscar);
    }

    public function obtener(int $id): Cliente
    {
        return $this->clientes->buscarPorId($id) ?? throw new ReglaNegocioException('El cliente no existe.');
    }

    public function buscarPorNit(string $nit): ?Cliente
    {
        return $this->clientes->buscarPorNit($nit);
    }

    public function guardar(array $datos, ?int $id = null): Cliente
    {
        $existente = $this->clientes->buscarPorNit($datos['nit'] ?? '');
        if ($existente && $existente->id !== $id) {
            throw new ReglaNegocioException('Ya existe un cliente con ese NIT.');
        }
        // Los puntos solo cambian por compras y canjes.
        $puntos = $id ? $this->obtener($id)->puntos : 0;

        return $this->clientes->guardar(new Cliente($id, $datos['nit'] ?? '', $datos['nombre'] ?? '',
            $datos['direccion'] ?? null, $datos['telefono'] ?? null, $datos['email'] ?? null, $puntos));
    }

    public function eliminar(int $id): void
    {
        if ($this->obtener($id)->nit === Cliente::CONSUMIDOR_FINAL) {
            throw new ReglaNegocioException('El cliente Consumidor Final no se puede eliminar.');
        }
        $this->clientes->eliminar($id);
    }
}
