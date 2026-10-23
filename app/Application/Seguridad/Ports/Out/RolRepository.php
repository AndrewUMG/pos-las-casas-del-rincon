<?php

namespace App\Application\Seguridad\Ports\Out;

/**
 * Roles y permisos del menú.
 */
interface RolRepository
{
    public function listarConPermisos(): array;
    public function listarPermisos(): array;
    public function existe(int $rolId): bool;
    public function sincronizarPermisos(int $rolId, array $permisoIds): void;
    public function permisosDeUsuario(int $usuarioId): array;
}
