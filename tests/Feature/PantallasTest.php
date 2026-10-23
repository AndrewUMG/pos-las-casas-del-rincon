<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\UsuarioModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Recorre las pantallas a través de HTTP (adaptadores primarios).
 */
class PantallasTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_login_valida_credenciales_contra_la_base_de_datos(): void
    {
        $this->post('/login', ['usuario' => 'admin', 'password' => 'incorrecta'])->assertSessionHas('error');
        $this->assertGuest();
        $this->post('/login', ['usuario' => 'admin', 'password' => 'admin123'])->assertRedirect('/inicio');
        $this->assertAuthenticated();
    }

    public function test_el_cajero_no_ve_modulos_de_administracion(): void
    {
        $cajero = UsuarioModel::where('usuario', 'cajero')->first();
        $this->actingAs($cajero)->get('/productos')->assertForbidden();
        $this->actingAs($cajero)->get('/inicio')->assertOk()->assertDontSee('Administración')->assertSee('Punto de Venta');
    }

    public function test_pantallas_del_administrador(): void
    {
        $admin = UsuarioModel::where('usuario', 'admin')->first();
        $producto = DB::table('productos')->value('id');
        $rutas = ['/inicio', '/pos', '/facturas', '/caja', '/devoluciones', '/devoluciones/nueva', '/clientes', '/clientes/create',
            '/proveedores', '/proveedores/create', '/productos', '/productos/create', "/productos/{$producto}/edit", '/precios',
            '/core', '/compras', '/compras/nueva', '/existencias', "/kardex/{$producto}", '/promociones', '/promociones/create',
            '/promociones/1/edit', '/cupones', '/reportes', '/usuarios', '/usuarios/create', '/roles'];
        foreach ($rutas as $ruta) {
            $this->actingAs($admin)->get($ruta)->assertOk();
        }
    }

    public function test_flujo_de_venta_por_la_web(): void
    {
        $cajero = UsuarioModel::where('usuario', 'cajero')->first();
        $this->actingAs($cajero);

        $this->post('/caja/abrir', ['monto_inicial' => 100])->assertRedirect('/pos');
        $this->post('/pos/iniciar')->assertRedirect('/pos');
        $venta = DB::table('ventas')->value('id');

        $this->postJson("/pos/{$venta}/lineas", ['codigo' => '7401004000018', 'cantidad' => 2])
            ->assertOk()->assertJsonPath('total', 28)->assertJsonPath('puntos_ganados', 4);
        $this->postJson("/pos/{$venta}/lineas", ['codigo' => 'NO-EXISTE'])->assertStatus(422);
        $this->get('/pos')->assertOk()->assertSee('Venta #'.$venta);

        $this->get("/pos/{$venta}/cobro")->assertOk();
        $this->post("/pos/{$venta}/cobro", ['metodo' => 'efectivo', 'monto_recibido' => 50])->assertRedirect("/pos/{$venta}/factura");
        $this->get("/pos/{$venta}/factura")->assertOk();
        $this->post("/pos/{$venta}/factura", ['nit' => '7894561-2', 'nombre' => 'Familia Castillo']);
        $factura = DB::table('facturas')->value('id');

        $this->get("/facturas/{$factura}")->assertOk()->assertSee('A-000001')->assertSee('<svg', false);
        $this->get("/facturas/{$factura}/pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(4, DB::table('clientes')->where('nit', '7894561-2')->value('puntos'));

        $token = DB::table('cupones')->value('token');
        auth()->logout();
        $this->get("/promocion/{$token}")->assertOk()->assertSee('Ganaste una promoción');
        $this->actingAs($cajero);

        $this->get('/devoluciones/nueva?numero=A-1')->assertOk()->assertSee('Crema pura');
        $this->get('/caja')->assertOk();
    }

    public function test_resto_de_acciones_por_la_web(): void
    {
        $admin = UsuarioModel::where('usuario', 'admin')->first();
        $this->actingAs($admin);
        $p = fn ($codigo) => DB::table('productos')->where('codigo', $codigo)->value('id');

        // Caja y despacho: quitar línea, cliente, puntos y cancelación.
        $this->post('/caja/abrir', ['monto_inicial' => 50]);
        $this->post('/pos/iniciar');
        $venta = DB::table('ventas')->value('id');
        $this->postJson("/pos/{$venta}/lineas", ['codigo' => '7401001000011', 'cantidad' => 3])->assertOk();
        $this->postJson("/pos/{$venta}/lineas", ['codigo' => '7401006000016'])->assertOk();
        $this->deleteJson("/pos/{$venta}/lineas/".$p('7401006000016'))->assertOk()->assertJsonCount(1, 'lineas');
        $this->post("/pos/{$venta}/cliente", ['nit' => '4567891-0'])->assertSessionHas('ok');
        $this->post("/pos/{$venta}/puntos", ['puntos' => 20])->assertSessionHas('ok');
        $this->assertSame(30, DB::table('clientes')->where('nit', '4567891-0')->value('puntos'));
        $this->post("/pos/{$venta}/cancelar")->assertRedirect('/pos');
        $this->assertSame(50, DB::table('clientes')->where('nit', '4567891-0')->value('puntos'));

        // Venta con tarjeta, factura CF y canje del cupón en otra venta.
        $this->post('/pos/iniciar');
        $venta = DB::table('ventas')->max('id');
        $this->postJson("/pos/{$venta}/lineas", ['codigo' => '7401003000019', 'cantidad' => 2])->assertOk();
        $this->post("/pos/{$venta}/cobro", ['metodo' => 'credito', 'referencia_voucher' => 'AUT-998']);
        $this->post("/pos/{$venta}/factura", ['nit' => 'CF']);
        $token = DB::table('cupones')->value('token');
        $this->post('/pos/iniciar');
        $venta2 = DB::table('ventas')->max('id');
        $this->postJson("/pos/{$venta2}/lineas", ['codigo' => '7401003000019', 'cantidad' => 2])->assertOk();
        $this->post("/pos/{$venta2}/cupon", ['token' => url('/promocion/'.$token)])->assertSessionHas('ok');
        $this->post("/pos/{$venta2}/cancelar");

        // Devolución por vencimiento.
        $factura = DB::table('facturas')->value('id');
        $this->post('/devoluciones', ['factura_id' => $factura, 'motivo' => 'vencimiento', 'cantidades' => [$p('7401003000019') => 1]])
            ->assertRedirect();
        $this->get('/devoluciones/'.DB::table('devoluciones')->value('id'))->assertOk();
        $this->post('/devoluciones', ['factura_id' => $factura, 'motivo' => 'dano', 'cantidades' => [$p('7401003000019') => 5]])
            ->assertSessionHas('error');

        // Compra, precios, catálogos, promociones, usuarios y roles.
        $proveedor = DB::table('proveedores')->value('id');
        $this->post('/compras', ['proveedor_id' => $proveedor, 'numero_documento' => 'F-100', 'fecha' => now()->format('Y-m-d'),
            'lineas' => [['producto_id' => $p('7401001000011'), 'cantidad' => 10, 'costo_unitario' => 3]]])->assertRedirect();
        $this->assertSame(50, DB::table('productos')->where('codigo', '7401001000011')->value('existencia'));
        $this->post('/precios', ['precios' => [$p('7401001000011') => ['precio' => 5, 'precio_mayoreo' => 4.25]]])->assertSessionHas('ok');
        $this->post('/precios', ['precios' => [$p('7401001000011') => ['precio' => 5, 'precio_mayoreo' => 6]]])->assertSessionHas('error');
        $this->post('/clientes', ['nit' => '111-1', 'nombre' => 'Nuevo Cliente'])->assertRedirect('/clientes');
        $this->post('/proveedores', ['nit' => '222-2', 'nombre' => 'Nuevo Proveedor'])->assertRedirect('/proveedores');
        $this->post('/productos', ['codigo' => '999', 'nombre' => 'Producto nuevo', 'familia_id' => 1, 'precio' => 3, 'activo' => 1])->assertRedirect('/productos');
        $this->post('/promociones', ['nombre' => 'Vale Q5', 'tipo' => 'vale_regalo', 'modo' => 'qr', 'cantidad_minima' => 1, 'valor' => 5,
            'fecha_inicio' => now()->format('Y-m-d'), 'fecha_fin' => now()->addDays(5)->format('Y-m-d'), 'activa' => 1])->assertRedirect('/promociones');
        $this->post('/promociones', ['nombre' => 'Vale auto', 'tipo' => 'vale_regalo', 'modo' => 'automatica', 'cantidad_minima' => 1, 'valor' => 5,
            'fecha_inicio' => now()->format('Y-m-d'), 'fecha_fin' => now()->addDays(5)->format('Y-m-d')])->assertSessionHas('error');
        $this->post('/usuarios', ['nombre' => 'Cajera 2', 'usuario' => 'cajera2', 'rol_id' => 2, 'password' => 'secreto1',
            'password_confirmation' => 'secreto1', 'activo' => 1])->assertRedirect('/usuarios');
        $this->post('/roles/2/permisos', ['permisos' => [1, 2, 3, 7]])->assertSessionHas('ok');

        // Corte de caja.
        $this->post('/caja/cerrar', ['monto_contado' => 50])->assertRedirect();
        $this->assertSame('cerrada', DB::table('cajas')->value('estado'));
    }

    public function test_reportes_en_pantalla_y_pdf(): void
    {
        $admin = UsuarioModel::where('usuario', 'admin')->first();
        foreach (['corte-caja', 'devoluciones', 'clientes', 'compras'] as $tipo) {
            $this->actingAs($admin)->get("/reportes/{$tipo}?desde=2020-01-01&hasta=2030-12-31")->assertOk();
            $this->actingAs($admin)->get("/reportes/{$tipo}?desde=2020-01-01&hasta=2030-12-31&formato=pdf")
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        }
    }

    public function test_sincronizacion_con_el_core(): void
    {
        $admin = UsuarioModel::where('usuario', 'admin')->first();
        $this->actingAs($admin)->post('/core/sincronizar')->assertSessionHas('ok');
        $this->assertDatabaseHas('productos', ['codigo' => '7401004000049', 'existencia' => 0]);
        $this->assertDatabaseHas('productos', ['codigo' => '7401007000015', 'precio' => 10.50]);
    }
}
