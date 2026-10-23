<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Catalogos\Ports\Out\ProductoRepository;
use App\Domain\Catalogos\Producto;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\FamiliaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;

class EloquentProductoRepository implements ProductoRepository
{
    public function buscarPorId(int $id): ?Producto
    {
        $m = ProductoModel::with('familia')->find($id);

        return $m ? Mapeador::producto($m) : null;
    }

    public function buscarPorCodigo(string $codigo): ?Producto
    {
        $m = ProductoModel::with('familia')->where('codigo', trim($codigo))->first();

        return $m ? Mapeador::producto($m) : null;
    }

    public function listar(?string $buscar = null, ?int $familiaId = null, bool $soloActivos = false): array
    {
        return ProductoModel::with('familia')
            ->when($buscar, fn ($q) => $q->where(fn ($w) => $w
                ->where('nombre', 'ilike', "%{$buscar}%")->orWhere('codigo', 'ilike', "%{$buscar}%")))
            ->when($familiaId, fn ($q) => $q->where('familia_id', $familiaId))
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')->get()->map(fn ($m) => Mapeador::producto($m))->all();
    }

    public function guardar(Producto $p): Producto
    {
        $m = Mapeador::persistir(ProductoModel::class, $p->id, [
            'codigo' => $p->codigo, 'nombre' => $p->nombre, 'familia_id' => $p->familiaId,
            'proveedor_id' => $p->proveedorId, 'precio' => $p->precio, 'precio_mayoreo' => $p->precioMayoreo,
            'existencia' => $p->existencia, 'activo' => $p->activo,
        ]);

        return $this->buscarPorId($m->id);
    }

    public function familias(): array
    {
        return FamiliaModel::orderBy('id')->pluck('nombre', 'id')->all();
    }

    public function familiaPorNombre(string $nombre): ?int
    {
        return FamiliaModel::whereRaw('lower(nombre) = ?', [mb_strtolower(trim($nombre))])->value('id');
    }
}
