<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionModel extends Model
{
    protected $table = 'promociones';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'valor' => 'float',
            'activa' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }

    public function familia()
    {
        return $this->belongsTo(FamiliaModel::class, 'familia_id');
    }
}
