<?php

namespace App\Application\Inventario\Ports\In;

/**
 * Consulta de compras registradas.
 */
interface ConsultarComprasUseCase
{
    public function listar(): array;
    public function obtener(int $id): array;
}
