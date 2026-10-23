<?php

namespace App\Domain\Seguridad;

use App\Domain\Shared\ReglaNegocioException;

/**
 * Usuario del sistema (cajero o administrador).
 */
class Usuario
{
    public function __construct(
        public readonly ?int $id,
        public string $nombre,
        public string $usuario,
        public string $passwordHash,
        public int $rolId,
        public bool $activo = true,
        public ?string $rolNombre = null,
    ) {
        if (trim($nombre) === '' || trim($usuario) === '') {
            throw new ReglaNegocioException('El nombre y el usuario son obligatorios.');
        }
    }

    public function puedeIniciarSesion(): void
    {
        if (! $this->activo) {
            throw new ReglaNegocioException('El usuario está inactivo y no puede iniciar sesión.');
        }
    }
}
