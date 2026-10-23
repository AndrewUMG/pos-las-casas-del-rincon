<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder
{
    public const FAMILIAS = [
        'Pastas' => 'Fideos, corbatitas, coditos, etc.',
        'Productos Cárnicos' => 'Pollo y carne de cerdo',
        'Embutidos' => 'Jamones, longanizas, salchichas, etc.',
        'Lácteos' => 'Crema, queso, leche, etc.',
        'Golosinas' => 'Dulces, chocolates, galletas, etc.',
        'Aguas Gaseosas' => 'Bebidas gaseosas',
        'Carbonatados' => 'Bebidas hidratantes y energizantes (Gatorade, Revive, etc.)',
    ];

    public function run(): void
    {
        foreach (self::FAMILIAS as $nombre => $desc) {
            DB::table('familias')->insert(['nombre' => $nombre, 'descripcion' => $desc]);
        }
        $fam = DB::table('familias')->pluck('id', 'nombre');

        $proveedores = [
            ['3456789-1', 'Distribuidora de Pastas La Moderna', 'Carlos Pérez', '2440-1100'],
            ['2345678-2', 'Avícola y Porcina del Sur', 'Ana López', '2440-2200'],
            ['1234567-3', 'Embutidos y Lácteos de Guatemala', 'María García', '2440-3300'],
            ['9876543-4', 'Dulcería Mayorista Central', 'José Morales', '2440-4400'],
            ['8765432-5', 'Embotelladora del Pacífico', 'Luis Ramírez', '2440-5500'],
        ];
        foreach ($proveedores as [$nit, $nombre, $contacto, $tel]) {
            DB::table('proveedores')->insert(['nit' => $nit, 'nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $tel,
                'direccion' => 'Ciudad de Guatemala', 'created_at' => now(), 'updated_at' => now()]);
        }
        $prov = DB::table('proveedores')->pluck('id', 'nit');

        // codigo, nombre, familia, proveedor, precio, precio mayoreo
        $productos = [
            ['7401001000011', 'Fideos 200 g', 'Pastas', '3456789-1', 4.50, 3.90],
            ['7401001000028', 'Corbatitas 200 g', 'Pastas', '3456789-1', 4.75, 4.10],
            ['7401001000035', 'Coditos 200 g', 'Pastas', '3456789-1', 4.50, 3.90],
            ['7401001000042', 'Spaghetti 400 g', 'Pastas', '3456789-1', 8.50, 7.50],
            ['7401002000010', 'Pechuga de pollo (lb)', 'Productos Cárnicos', '2345678-2', 22.00, 20.00],
            ['7401002000027', 'Muslo de pollo (lb)', 'Productos Cárnicos', '2345678-2', 16.00, 14.50],
            ['7401002000034', 'Chuleta de cerdo (lb)', 'Productos Cárnicos', '2345678-2', 28.00, 25.50],
            ['7401002000041', 'Costilla de cerdo (lb)', 'Productos Cárnicos', '2345678-2', 30.00, 27.00],
            ['7401003000019', 'Jamón de pavo 250 g', 'Embutidos', '1234567-3', 18.50, 16.50],
            ['7401003000026', 'Longaniza tradicional (lb)', 'Embutidos', '1234567-3', 24.00, 21.50],
            ['7401003000033', 'Salchicha de pollo 12 u', 'Embutidos', '1234567-3', 15.00, 13.50],
            ['7401004000018', 'Crema pura 450 ml', 'Lácteos', '1234567-3', 14.00, 12.75],
            ['7401004000025', 'Queso fresco (lb)', 'Lácteos', '1234567-3', 25.00, 22.50],
            ['7401004000032', 'Leche entera 1 L', 'Lácteos', '1234567-3', 11.50, 10.50],
            ['7401005000017', 'Chocolate de leche 40 g', 'Golosinas', '9876543-4', 6.00, 5.25],
            ['7401005000024', 'Dulces surtidos 100 u', 'Golosinas', '9876543-4', 18.00, 16.00],
            ['7401005000031', 'Galletas de vainilla', 'Golosinas', '9876543-4', 3.50, 3.00],
            ['7401006000016', 'Coca-Cola 600 ml', 'Aguas Gaseosas', '8765432-5', 7.00, 6.25],
            ['7401006000023', 'Pepsi 600 ml', 'Aguas Gaseosas', '8765432-5', 6.50, 5.75],
            ['7401006000030', 'Agua gaseosa limón 2 L', 'Aguas Gaseosas', '8765432-5', 14.00, 12.50],
            ['7401007000015', 'Gatorade 600 ml', 'Carbonatados', '8765432-5', 10.00, 9.00],
            ['7401007000022', 'Revive 600 ml', 'Carbonatados', '8765432-5', 9.00, 8.00],
            ['7401007000039', 'Bebida energizante 473 ml', 'Carbonatados', '8765432-5', 12.00, 10.75],
        ];
        foreach ($productos as [$codigo, $nombre, $familia, $nit, $precio, $mayoreo]) {
            DB::table('productos')->insert(['codigo' => $codigo, 'nombre' => $nombre, 'familia_id' => $fam[$familia],
                'proveedor_id' => $prov[$nit], 'precio' => $precio, 'precio_mayoreo' => $mayoreo, 'existencia' => 0,
                'activo' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('clientes')->insert([
            ['nit' => 'CF', 'nombre' => 'Consumidor Final', 'direccion' => 'Ciudad', 'telefono' => null, 'email' => null, 'puntos' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nit' => '4567891-0', 'nombre' => 'Familia Hernández', 'direccion' => 'Residenciales Las Flores, casa 12', 'telefono' => '5555-1010', 'email' => null, 'puntos' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['nit' => '7894561-2', 'nombre' => 'Familia Castillo', 'direccion' => 'Colonia El Rincón, lote 4', 'telefono' => '5555-2020', 'email' => null, 'puntos' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
