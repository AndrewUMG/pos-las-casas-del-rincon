<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalogos\Cliente;
use App\Domain\Catalogos\Producto;
use App\Domain\Catalogos\Proveedor;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;

/**
 * Traduce modelos Eloquent (infraestructura) a entidades del dominio.
 */
final class Mapeador
{
    public static function producto(ProductoModel $m): Producto
    {
        return new Producto($m->id, $m->codigo, $m->nombre, $m->familia_id, $m->proveedor_id, (float) $m->precio,
            $m->precio_mayoreo !== null ? (float) $m->precio_mayoreo : null, (int) $m->existencia, (bool) $m->activo,
            $m->relationLoaded('familia') ? $m->familia?->nombre : null);
    }

    public static function cliente(ClienteModel $m): Cliente
    {
        return new Cliente($m->id, $m->nit, $m->nombre, $m->direccion, $m->telefono, $m->email, (int) $m->puntos);
    }

    public static function proveedor(ProveedorModel $m): Proveedor
    {
        return new Proveedor($m->id, $m->nit, $m->nombre, $m->contacto, $m->telefono, $m->direccion);
    }

    /**
     * Inserta si la entidad es nueva (id nulo) o actualiza si ya existe.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelo
     */
    public static function persistir(string $modelo, ?int $id, array $atributos): \Illuminate\Database\Eloquent\Model
    {
        if ($id === null) {
            return $modelo::create($atributos);
        }
        $m = $modelo::findOrFail($id);
        $m->update($atributos);

        return $m;
    }

    public static function fecha(mixed $valor): ?\DateTimeImmutable
    {
        return $valor ? \DateTimeImmutable::createFromInterface($valor) : null;
    }
}
