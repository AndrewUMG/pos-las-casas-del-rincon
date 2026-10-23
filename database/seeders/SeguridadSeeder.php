<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeguridadSeeder extends Seeder
{
    /** Opciones del menú interactivo (una por módulo). */
    public const PERMISOS = [
        ['pos', 'Punto de Venta', 'Despacho, cobro y facturación'],
        ['caja', 'Caja', 'Apertura y corte de caja'],
        ['devoluciones', 'Devoluciones', 'Devolución por daño o vencimiento'],
        ['catalogos', 'Catálogos', 'Clientes, proveedores, productos y precios'],
        ['inventario', 'Inventario', 'Compras, existencias y kardex'],
        ['promociones', 'Promociones', 'Promociones y cupones QR'],
        ['reportes', 'Reportes', 'Corte de caja, devoluciones, clientes y compras'],
        ['administracion', 'Administración', 'Usuarios, roles y permisos'],
    ];

    public function run(): void
    {
        foreach (self::PERMISOS as [$clave, $nombre, $modulo]) {
            DB::table('permisos')->insert(['clave' => $clave, 'nombre' => $nombre, 'modulo' => $modulo]);
        }
        $ids = DB::table('permisos')->pluck('id', 'clave');

        $admin = DB::table('roles')->insertGetId(['nombre' => 'Administrador', 'descripcion' => 'Acceso a todos los módulos', 'created_at' => now(), 'updated_at' => now()]);
        $cajero = DB::table('roles')->insertGetId(['nombre' => 'Cajero', 'descripcion' => 'Punto de venta, caja y devoluciones', 'created_at' => now(), 'updated_at' => now()]);

        foreach ($ids as $id) {
            DB::table('rol_permiso')->insert(['rol_id' => $admin, 'permiso_id' => $id]);
        }
        foreach (['pos', 'caja', 'devoluciones'] as $clave) {
            DB::table('rol_permiso')->insert(['rol_id' => $cajero, 'permiso_id' => $ids[$clave]]);
        }

        DB::table('usuarios')->insert([
            ['nombre' => 'Administrador del sistema', 'usuario' => 'admin', 'password' => Hash::make('admin123'), 'rol_id' => $admin, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cajero de turno', 'usuario' => 'cajero', 'password' => Hash::make('cajero123'), 'rol_id' => $cajero, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
