<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionesSeeder extends Seeder
{
    public function run(): void
    {
        $p = DB::table('productos')->pluck('id', 'nombre');
        $f = DB::table('familias')->pluck('id', 'nombre');
        $desde = now()->subDays(30)->format('Y-m-d');
        $hasta = now()->addYear()->format('Y-m-d');

        $promos = [
            // Automáticas: se aplican solas en el despacho.
            ['Segunda Coca-Cola a mitad de precio', 'segundo_mitad', 'automatica', $p['Coca-Cola 600 ml'], null, 1, 0],
            ['Mayoreo en Pastas (3 o más)', 'mayoreo', 'automatica', null, $f['Pastas'], 3, 0],
            ['Puntos por Lácteos', 'puntos', 'automatica', null, $f['Lácteos'], 1, 2],
            // Entregadas con el código QR de la factura.
            ['Vale de regalo Q10', 'vale_regalo', 'qr', null, null, 1, 10],
            ['Gatorade gratis', 'producto_gratuito', 'qr', $p['Gatorade 600 ml'], null, 1, 0],
            ['Segundo jamón a mitad de precio', 'segundo_mitad', 'qr', $p['Jamón de pavo 250 g'], null, 1, 0],
            ['Mayoreo en Golosinas', 'mayoreo', 'qr', null, $f['Golosinas'], 3, 0],
            ['Triple puntos en Embutidos', 'puntos', 'qr', null, $f['Embutidos'], 1, 3],
        ];
        foreach ($promos as [$nombre, $tipo, $modo, $producto, $familia, $min, $valor]) {
            DB::table('promociones')->insert([
                'nombre' => $nombre, 'tipo' => $tipo, 'modo' => $modo, 'producto_id' => $producto, 'familia_id' => $familia,
                'cantidad_minima' => $min, 'valor' => $valor, 'descripcion' => null, 'fecha_inicio' => $desde,
                'fecha_fin' => $hasta, 'activa' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
