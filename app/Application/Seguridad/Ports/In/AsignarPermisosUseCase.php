<?php

namespace App\Application\Seguridad\Ports\In;

/**
 * Opciones del menú permitidas para cada rol.
 */
interface AsignarPermisosUseCase
{
    public function roles(): array;
    public function permisos(): array;
    public function asignar(int $rolId, array $permisoIds): void;

    /** @return string[] claves de permiso del usuario */
    public function permisosDeUsuario(int $usuarioId): array;
}
