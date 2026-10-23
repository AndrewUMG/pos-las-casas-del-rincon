<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Inventario\Ports\Out\ExistenciaRepository;
use App\Domain\Catalogos\Producto;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;

class EloquentExistenciaRepository implements ExistenciaRepository
{
    public function producto(int $productoId): ?Producto
    {
        // Bloqueo de fila para que dos cajas no descuenten la misma existencia a la vez.
        $m = ProductoModel::with('familia')->lockForUpdate()->find($productoId);

        return $m ? Mapeador::producto($m) : null;
    }

    public function actualizarExistencia(int $productoId, int $existencia): void
    {
        ProductoModel::whereKey($productoId)->update(['existencia' => $existencia]);
    }

    public function listar(?string $buscar = null): array
    {
        return ProductoModel::with(['familia', 'proveedor'])
            ->when($buscar, fn ($q) => $q->where(fn ($w) => $w
                ->where('nombre', 'ilike', "%{$buscar}%")->orWhere('codigo', 'ilike', "%{$buscar}%")))
            ->orderBy('familia_id')->orderBy('nombre')->get()->map(fn ($p) => [
                'id' => $p->id, 'codigo' => $p->codigo, 'nombre' => $p->nombre, 'familia' => $p->familia->nombre,
                'proveedor' => $p->proveedor?->nombre, 'existencia' => $p->existencia, 'activo' => $p->activo,
            ])->all();
    }
}
