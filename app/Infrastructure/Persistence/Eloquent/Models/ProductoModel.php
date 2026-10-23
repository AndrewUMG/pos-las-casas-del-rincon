<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'precio' => 'float',
            'precio_mayoreo' => 'float',
            'existencia' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function familia()
    {
        return $this->belongsTo(FamiliaModel::class, 'familia_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'proveedor_id');
    }
}
