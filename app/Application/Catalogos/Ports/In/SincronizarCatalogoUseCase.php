<?php

namespace App\Application\Catalogos\Ports\In;

/**
 * Sincroniza productos, proveedores y precios desde el Core existente.
 */
interface SincronizarCatalogoUseCase
{
    /** @return array{creados:int, actualizados:int, proveedores:int} */
    public function sincronizar(): array;
    public function origen(): string;
}
