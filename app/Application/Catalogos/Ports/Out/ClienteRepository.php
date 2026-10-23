<?php

namespace App\Application\Catalogos\Ports\Out;

use App\Domain\Catalogos\Cliente;

/**
 * Persistencia de clientes.
 */
interface ClienteRepository
{
    public function buscarPorId(int $id): ?Cliente;
    public function buscarPorNit(string $nit): ?Cliente;

    /** @return Cliente[] */
    public function listar(?string $buscar = null): array;
    public function guardar(Cliente $cliente): Cliente;
    public function eliminar(int $id): void;
}
