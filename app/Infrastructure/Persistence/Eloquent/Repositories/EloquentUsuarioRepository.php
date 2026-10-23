<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Seguridad\Ports\Out\UsuarioRepository;
use App\Domain\Seguridad\Usuario;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\UsuarioModel;

class EloquentUsuarioRepository implements UsuarioRepository
{
    public function buscarPorUsuario(string $usuario): ?Usuario
    {
        $m = UsuarioModel::with('rol')->whereRaw('lower(usuario) = ?', [mb_strtolower($usuario)])->first();

        return $m ? $this->aDominio($m) : null;
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $m = UsuarioModel::with('rol')->find($id);

        return $m ? $this->aDominio($m) : null;
    }

    public function listar(): array
    {
        return UsuarioModel::with('rol')->orderBy('nombre')->get()->map(fn ($m) => $this->aDominio($m))->all();
    }

    public function existeUsuario(string $usuario, ?int $exceptoId = null): bool
    {
        return UsuarioModel::whereRaw('lower(usuario) = ?', [mb_strtolower($usuario)])
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))->exists();
    }

    public function guardar(Usuario $u): Usuario
    {
        $m = Mapeador::persistir(UsuarioModel::class, $u->id, [
            'nombre' => $u->nombre, 'usuario' => $u->usuario, 'password' => $u->passwordHash,
            'rol_id' => $u->rolId, 'activo' => $u->activo,
        ]);

        return $this->buscarPorId($m->id);
    }

    private function aDominio(UsuarioModel $m): Usuario
    {
        return new Usuario($m->id, $m->nombre, $m->usuario, $m->password, $m->rol_id, (bool) $m->activo, $m->rol?->nombre);
    }
}
