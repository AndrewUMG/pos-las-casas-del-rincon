<?php

namespace Database\Seeders;

use App\Application\Caja\Ports\In\AbrirCajaUseCase;
use App\Application\Cobros\Ports\In\CobrarVentaUseCase;
use App\Application\Devoluciones\Ports\In\ProcesarDevolucionUseCase;
use App\Application\Facturacion\Ports\In\GenerarFacturaUseCase;
use App\Application\Inventario\Ports\In\RegistrarCompraUseCase;
use App\Application\Ventas\Ports\In\AgregarProductoUseCase;
use App\Application\Ventas\Ports\In\GestionarVentaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos de demostración para presentar el sistema (opcional).
 * Se generan a través de los casos de uso, igual que si un usuario los registrara:
 * una compra, caja abierta del cajero, tres ventas facturadas (una con canje de cupón QR),
 * una devolución y una venta en proceso en el despacho.
 *
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cajero = DB::table('usuarios')->where('usuario', 'cajero')->value('id');
        $admin = DB::table('usuarios')->where('usuario', 'admin')->value('id');
        $producto = fn (string $codigo) => DB::table('productos')->where('codigo', $codigo)->value('id');

        app(RegistrarCompraUseCase::class)->registrar(
            DB::table('proveedores')->where('nit', '8765432-5')->value('id'), $admin, 'FAC-20451', now()->format('Y-m-d'), [
                ['producto_id' => $producto('7401006000016'), 'cantidad' => 24, 'costo_unitario' => 4.90],
                ['producto_id' => $producto('7401007000015'), 'cantidad' => 12, 'costo_unitario' => 7.00],
            ]);

        app(AbrirCajaUseCase::class)->abrir($cajero, 300);
        $iniciar = app(IniciarVentaUseCase::class);
        $agregar = app(AgregarProductoUseCase::class);
        $cobrar = app(CobrarVentaUseCase::class);
        $facturar = app(GenerarFacturaUseCase::class);

        // Venta 1: efectivo, cliente con NIT (gana puntos por lácteos y recibe un cupón QR).
        $venta = $iniciar->iniciar($cajero);
        $agregar->agregar($venta->id, '7401006000016', 2);
        $agregar->agregar($venta->id, '7401001000011', 3);
        $agregar->agregar($venta->id, '7401004000025', 1);
        $cobrar->cobrar($venta->id, 'efectivo', 50, null);
        $factura1 = $facturar->generar($venta->id, '7894561-2', 'Familia Castillo', 'Colonia El Rincón, lote 4');

        // Venta 2: tarjeta de débito, consumidor final.
        $venta = $iniciar->iniciar($cajero);
        $agregar->agregar($venta->id, '7401003000019', 2);
        $agregar->agregar($venta->id, '7401005000017', 4);
        $cobrar->cobrar($venta->id, 'debito', null, 'AUT-778812');
        $facturar->generar($venta->id, 'CF', null, null);

        // Venta 3: canje del cupón QR de la factura 1 y pago con tarjeta de crédito.
        $token = DB::table('cupones')->where('factura_id', $factura1)->value('token');
        $venta = $iniciar->iniciar($cajero);
        $agregar->agregar($venta->id, '7401003000019', 2);
        $agregar->agregar($venta->id, '7401006000023', 2);
        if ($token) {
            try {
                app(GestionarVentaUseCase::class)->canjearCupon($venta->id, $token);
            } catch (\Throwable) {
                // El cupón asignado al azar puede no aplicar a estos productos; la demostración continúa.
            }
        }
        $cobrar->cobrar($venta->id, 'credito', null, 'AUT-554120');
        $facturar->generar($venta->id, '4567891-0', 'Familia Hernández', 'Residenciales Las Flores, casa 12');

        // Devolución por daño de una Coca-Cola de la factura 1.
        app(ProcesarDevolucionUseCase::class)->procesar($factura1, $cajero, 'dano', 'Botella con fuga', [$producto('7401006000016') => 1]);

        // Venta en proceso, para mostrar el despacho con productos.
        $venta = $iniciar->iniciar($cajero);
        $agregar->agregar($venta->id, '7401006000016', 2);
        $agregar->agregar($venta->id, '7401001000028', 3);
        $agregar->agregar($venta->id, '7401004000018', 2);
    }
}
