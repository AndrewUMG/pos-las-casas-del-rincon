<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Catalogos\Ports\Out\ProveedorRepository;
use App\Domain\Catalogos\Proveedor;
use App\Domain\Shared\ReglaNegocioException;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;
use Illuminate\Database\QueryException;

class EloquentProveedorRepository implements ProveedorRepository
{
    public function buscarPorId(int $id): ?Proveedor
    {
        $m = ProveedorModel::find($id);

        return $m ? Mapeador::proveedor($m) : null;
    }

    public function buscarPorNit(string $nit): ?Proveedor
    {
        $m = ProveedorModel::where('nit', strtoupper(trim($nit)))->first();

        return $m ? Mapeador::proveedor($m) : null;
    }

    public function listar(?string $buscar = null): array
    {
        return ProveedorModel::when($buscar, fn ($q) => $q->where(fn ($w) => $w
            ->where('nombre', 'ilike', "%{$buscar}%")->orWhere('nit', 'ilike', "%{$buscar}%")))
            ->orderBy('nombre')->get()->map(fn ($m) => Mapeador::proveedor($m))->all();
    }

    public function guardar(Proveedor $p): Proveedor
    {
        $m = Mapeador::persistir(ProveedorModel::class, $p->id, [
            'nit' => $p->nit, 'nombre' => $p->nombre, 'contacto' => $p->contacto,
            'telefono' => $p->telefono, 'direccion' => $p->direccion,
        ]);

        return Mapeador::proveedor($m->fresh());
    }

    public function eliminar(int $id): void
    {
        try {
            ProveedorModel::whereKey($id)->delete();
        } catch (QueryException) {
            throw new ReglaNegocioException('No se puede eliminar: el proveedor tiene compras registradas.');
        }
    }
}
