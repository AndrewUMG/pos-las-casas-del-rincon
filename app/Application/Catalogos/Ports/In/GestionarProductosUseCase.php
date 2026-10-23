<?php

namespace App\Application\Catalogos\Ports\In;

use App\Domain\Catalogos\Producto;

/**
 * Catálogo de productos por familia.
 */
interface GestionarProductosUseCase
{
    /** @return Producto[] */
    public function listar(?string $buscar = null, ?int $familiaId = null, bool $soloActivos = false): array;
    public function obtener(int $id): Producto;
    public function buscarPorCodigo(string $codigo): ?Producto;
    public function guardar(array $datos, ?int $id = null): Producto;
    public function desactivar(int $id): void;

    /** @return array<int,string> */
    public function familias(): array;
}
