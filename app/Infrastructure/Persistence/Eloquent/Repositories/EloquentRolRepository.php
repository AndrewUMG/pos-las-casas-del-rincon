<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Seguridad\Ports\Out\RolRepository;
use App\Infrastructure\Persistence\Eloquent\Models\PermisoModel;
use App\Infrastructure\Persistence\Eloquent\Models\RolModel;
use Illuminate\Support\Facades\DB;

class EloquentRolRepository implements RolRepository
{
    public function listarConPermisos(): array
    {
        return RolModel::with('permisos')->orderBy('id')->get()->map(fn ($r) => [
            'id' => $r->id,
            'nombre' => $r->nombre,
            'descripcion' => $r->descripcion,
            'permiso_ids' => $r->permisos->pluck('id')->all(),
        ])->all();
    }

    public function listarPermisos(): array
    {
        return PermisoModel::orderBy('id')->get(['id', 'clave', 'nombre', 'modulo'])->toArray();
    }

    public function existe(int $rolId): bool
    {
        return RolModel::whereKey($rolId)->exists();
    }

    public function sincronizarPermisos(int $rolId, array $permisoIds): void
    {
        RolModel::findOrFail($rolId)->permisos()->sync($permisoIds);
    }

    public function permisosDeUsuario(int $usuarioId): array
    {
        return DB::table('usuarios')
            ->join('rol_permiso', 'rol_permiso.rol_id', '=', 'usuarios.rol_id')
            ->join('permisos', 'permisos.id', '=', 'rol_permiso.permiso_id')
            ->where('usuarios.id', $usuarioId)
            ->pluck('permisos.clave')->all();
    }
}
