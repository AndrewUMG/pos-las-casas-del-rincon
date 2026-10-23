<?php

namespace App\Application\Catalogos\Ports\Out;

/**
 * Consulta al Core de Empresas de Consumo Masivo (sistema existente).
 */
interface CatalogoCorePort
{
    /** @return array<int, array{codigo:string, nombre:string, familia:string, precio:float, precio_mayoreo:?float, proveedor_nit:?string, proveedor_nombre:?string}> */
    public function obtenerCatalogo(): array;
    public function descripcion(): string;
}
