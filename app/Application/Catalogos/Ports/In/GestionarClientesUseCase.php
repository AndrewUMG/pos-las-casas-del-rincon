<?php

namespace App\Application\Catalogos\Ports\In;

use App\Domain\Catalogos\Cliente;

/**
 * Catálogo de clientes.
 */
interface GestionarClientesUseCase
{
    /** @return Cliente[] */
    public function listar(?string $buscar = null): array;
    public function obtener(int $id): Cliente;
    public function buscarPorNit(string $nit): ?Cliente;
    public function guardar(array $datos, ?int $id = null): Cliente;
    public function eliminar(int $id): void;
}
