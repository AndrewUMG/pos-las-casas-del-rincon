<?php

namespace App\Application\Catalogos\Ports\Out;

use App\Domain\Catalogos\Producto;

/**
 * Persistencia de productos y familias.
 */
interface ProductoRepository
{
    public function buscarPorId(int $id): ?Producto;
    public function buscarPorCodigo(string $codigo): ?Producto;

    /** @return Producto[] */
    public function listar(?string $buscar = null, ?int $familiaId = null, bool $soloActivos = false): array;
    public function guardar(Producto $producto): Producto;

    /** @return array<int,string> */
    public function familias(): array;
    public function familiaPorNombre(string $nombre): ?int;
}
