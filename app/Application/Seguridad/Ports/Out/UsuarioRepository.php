<?php

namespace App\Application\Seguridad\Ports\Out;

use App\Domain\Seguridad\Usuario;

/**
 * Persistencia de usuarios.
 */
interface UsuarioRepository
{
    public function buscarPorUsuario(string $usuario): ?Usuario;
    public function buscarPorId(int $id): ?Usuario;

    /** @return Usuario[] */
    public function listar(): array;
    public function existeUsuario(string $usuario, ?int $exceptoId = null): bool;
    public function guardar(Usuario $usuario): Usuario;
}
