<?php

namespace App\Application\Seguridad\Ports\In;

use App\Domain\Seguridad\Usuario;

/**
 * Alta, baja y cambios de usuarios.
 */
interface GestionarUsuariosUseCase
{
    /** @return Usuario[] */
    public function listar(): array;
    public function obtener(int $id): Usuario;
    public function guardar(array $datos, ?int $id = null): Usuario;
}
