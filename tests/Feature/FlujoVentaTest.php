<?php

namespace Tests\Feature;

use App\Application\Caja\Ports\In\AbrirCajaUseCase;
use App\Application\Caja\Ports\In\CerrarCajaUseCase;
use App\Application\Cobros\Ports\In\CobrarVentaUseCase;
use App\Application\Devoluciones\Ports\In\ConsultarFacturaUseCase;
use App\Application\Devoluciones\Ports\In\ProcesarDevolucionUseCase;
use App\Application\Facturacion\Ports\In\GenerarFacturaUseCase;
use App\Application\Facturacion\Ports\In\ImprimirFacturaUseCase;
use App\Application\Promociones\Ports\In\ConsultarPromocionQrUseCase;
use App\Application\Ventas\Ports\In\AgregarProductoUseCase;
use App\Application\Ventas\Ports\In\GestionarVentaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use App\Domain\Shared\ReglaNegocioException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Flujo completo a través de los casos de uso: apertura de caja → despacho → cobro →
 * factura con QR → lectura del QR → canje en una nueva compra → devolución → corte.
 */
class FlujoVentaTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private int $cajero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cajero = DB::table('usuarios')->where('usuario', 'cajero')->value('id');
    }

    private function existencia(string $codigo): int
    {
        return DB::table('productos')->where('codigo', $codigo)->value('existencia');
    }

    public function test_no_se_despacha_sin_caja_abierta(): void
    {
        $this->expectException(ReglaNegocioException::class);
        app(IniciarVentaUseCase::class)->iniciar($this->cajero);
    }

    public function test_flujo_completo_de_venta(): void
    {
        app(AbrirCajaUseCase::class)->abrir($this->cajero, 200);

        // Despacho con promoción automática (2.ª Coca-Cola a mitad de precio).
        $venta = app(IniciarVentaUseCase::class)->iniciar($this->cajero);
        $despacho = app(AgregarProductoUseCase::class);
        $despacho->agregar($venta->id, '7401006000016', 2);      // 2 × Coca-Cola 7.00
        $venta = $despacho->agregar($venta->id, '7401001000011', 1); // 1 × Fideos 4.50
        $this->assertSame(15.0, $venta->total());                // 14 + 4.50 − 3.50

        // Cobro en efectivo: descuenta existencias y registra ingreso en caja.
        $pago = app(CobrarVentaUseCase::class)->cobrar($venta->id, 'efectivo', 20, null);
        $this->assertSame(5.0, $pago->cambio);
        $this->assertSame(38, $this->existencia('7401006000016'));

        // Factura con datos del cliente y QR de promoción (se deja solo el vale para que la prueba sea determinista).
        DB::table('promociones')->where('modo', 'qr')->where('tipo', '!=', 'vale_regalo')->update(['activa' => false]);
        $facturaId = app(GenerarFacturaUseCase::class)->generar($venta->id, '4567891-0', 'Familia Hernández', null);
        $datos = app(ImprimirFacturaUseCase::class)->datos($facturaId);
        $this->assertSame('A-000001', $datos['factura']['numero_completo']);
        $this->assertNotNull($datos['cupon']);
        $this->assertStringContainsString('<svg', $datos['cupon']['qr_svg']);

        // El cliente lee el QR con el celular.
        $promo = app(ConsultarPromocionQrUseCase::class)->consultar($datos['cupon']['url']);
        $this->assertSame('emitido', $promo['estado']);

        // Canje del cupón en la siguiente compra.
        $venta2 = app(IniciarVentaUseCase::class)->iniciar($this->cajero);
        $despacho->agregar($venta2->id, '7401003000019', 2);
        $venta2 = app(GestionarVentaUseCase::class)->canjearCupon($venta2->id, $datos['cupon']['token']);
        $this->assertSame(27.0, $venta2->total());            // 2 × 18.50 − vale Q10
        $this->assertSame('canjeado', app(ConsultarPromocionQrUseCase::class)->consultar($datos['cupon']['token'])['estado']);

        // El mismo cupón no se puede usar dos veces.
        try {
            app(GestionarVentaUseCase::class)->canjearCupon($venta2->id, $datos['cupon']['token']);
            $this->fail('Se permitió canjear dos veces el cupón.');
        } catch (ReglaNegocioException) {
        }
        app(CobrarVentaUseCase::class)->cobrar($venta2->id, 'debito', null, 'VOUCHER-123');

        // Devolución por daño de 1 Coca-Cola de la primera factura.
        $factura = app(ConsultarFacturaUseCase::class)->buscar('A-1');
        $coca = DB::table('productos')->where('codigo', '7401006000016')->value('id');
        app(ProcesarDevolucionUseCase::class)->procesar($factura['id'], $this->cajero, 'dano', 'Botella rota', [$coca => 1]);
        $this->assertSame(1, app(ConsultarFacturaUseCase::class)->buscar('A-1')['lineas'][0]['devuelto']);
        $this->assertDatabaseHas('movimientos_inventario', ['tipo' => 'baja', 'producto_id' => $coca]);

        // Corte de caja: 200 inicial + 15.00 efectivo − 5.25 (devolución al precio efectivo) = 209.75
        $caja = app(CerrarCajaUseCase::class)->cerrar($this->cajero, 209.75);
        $this->assertSame(209.75, $caja->montoEsperado);
        $this->assertSame(0.0, $caja->diferencia);
    }
}
