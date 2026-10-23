<?php

namespace App\Application\Seguridad\Services;

use App\Application\Seguridad\Ports\In\AsignarPermisosUseCase;
use App\Application\Seguridad\Ports\In\CerrarSesionUseCase;
use App\Application\Seguridad\Ports\In\GestionarUsuariosUseCase;
use App\Application\Seguridad\Ports\In\IniciarSesionUseCase;
use App\Application\Seguridad\Ports\Out\PasswordHasher;
use App\Application\Seguridad\Ports\Out\RolRepository;
use App\Application\Seguridad\Ports\Out\SesionPort;
use App\Application\Seguridad\Ports\Out\UsuarioRepository;
use App\Domain\Seguridad\Usuario;
use App\Domain\Shared\ReglaNegocioException;

class SeguridadService implements IniciarSesionUseCase, CerrarSesionUseCase, GestionarUsuariosUseCase, AsignarPermisosUseCase
{
    public function __construct(
        private UsuarioRepository $usuarios,
        private RolRepository $roles,
        private PasswordHasher $hasher,
        private SesionPort $sesion,
    ) {
    }

    public function iniciar(string $usuario, string $password): void
    {
        $u = $this->usuarios->buscarPorUsuario(trim($usuario));
        if (! $u || ! $this->hasher->verificar($password, $u->passwordHash)) {
            throw new ReglaNegocioException('Usuario o contraseña incorrectos.');
        }
        $u->puedeIniciarSesion();
        $this->sesion->iniciar($u->id);
    }

    public function cerrar(): void
    {
        $this->sesion->cerrar();
    }

    public function listar(): array
    {
        return $this->usuarios->listar();
    }

    public function obtener(int $id): Usuario
    {
        return $this->usuarios->buscarPorId($id) ?? throw new ReglaNegocioException('El usuario no existe.');
    }

    public function guardar(array $datos, ?int $id = null): Usuario
    {
        $usuario = trim($datos['usuario'] ?? '');
        if ($this->usuarios->existeUsuario($usuario, $id)) {
            throw new ReglaNegocioException("El usuario «{$usuario}» ya existe.");
        }
        if (! $this->roles->existe((int) ($datos['rol_id'] ?? 0))) {
            throw new ReglaNegocioException('Seleccione un rol válido.');
        }
        $password = $datos['password'] ?? '';
        if ($id === null && strlen($password) < 6) {
            throw new ReglaNegocioException('La contraseña debe tener al menos 6 caracteres.');
        }
        if ($password !== '' && strlen($password) < 6) {
            throw new ReglaNegocioException('La contraseña debe tener al menos 6 caracteres.');
        }

        $actual = $id ? $this->obtener($id) : null;
        $hash = $password !== '' ? $this->hasher->hash($password) : $actual->passwordHash;

        return $this->usuarios->guardar(new Usuario(
            $id, $datos['nombre'] ?? '', $usuario, $hash, (int) $datos['rol_id'], (bool) ($datos['activo'] ?? true)
        ));
    }

    public function roles(): array
    {
        return $this->roles->listarConPermisos();
    }

    public function permisos(): array
    {
        return $this->roles->listarPermisos();
    }

    public function asignar(int $rolId, array $permisoIds): void
    {
        if (! $this->roles->existe($rolId)) {
            throw new ReglaNegocioException('El rol no existe.');
        }
        $this->roles->sincronizarPermisos($rolId, array_map('intval', $permisoIds));
    }

    public function permisosDeUsuario(int $usuarioId): array
    {
        return $this->roles->permisosDeUsuario($usuarioId);
    }
}
