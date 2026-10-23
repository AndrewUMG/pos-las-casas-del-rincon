<?php

namespace Database\Seeders;

use App\Application\Inventario\Ports\In\RegistrarCompraUseCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Existencias iniciales registradas como compras (pasan por el caso de uso para generar el kardex).
 */
class InventarioInicialSeeder extends Seeder
{
    public function run(RegistrarCompraUseCase $compras): void
    {
        $admin = DB::table('usuarios')->where('usuario', 'admin')->value('id');
        $porProveedor = DB::table('productos')->get()->groupBy('proveedor_id');

        foreach ($porProveedor as $proveedorId => $productos) {
            $lineas = $productos->map(fn ($p) => [
                'producto_id' => $p->id,
                'cantidad' => 40,
                'costo_unitario' => round($p->precio * 0.7, 2),
            ])->all();
            $compras->registrar((int) $proveedorId, $admin, 'INI-'.$proveedorId, now()->subDays(7)->format('Y-m-d'), $lineas);
        }
    }
}
