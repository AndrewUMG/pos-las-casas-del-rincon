<?php

namespace Tests\Unit\Dominio;

use App\Domain\Caja\Caja;
use App\Domain\Cobros\MetodoPago;
use App\Domain\Cobros\Pago;
use App\Domain\Promociones\MotorPromociones;
use App\Domain\Promociones\Promocion;
use App\Domain\Promociones\TipoPromocion;
use App\Domain\Shared\ReglaNegocioException;
use App\Domain\Ventas\Venta;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas del dominio puro: no usan base de datos ni Laravel.
 */
class VentaYPromocionesTest extends TestCase
{
    private const PASTAS = 1;
    private const GASEOSAS = 6;

    private function venta(): Venta
    {
        $v = new Venta(1, 1, 1, null, new \DateTimeImmutable('2026-10-23'));
        $v->agregarProducto(10, 'P1', 'Fideos', self::PASTAS, 4.50, 3.90, 2, 100);
        $v->agregarProducto(20, 'G1', 'Coca-Cola', self::GASEOSAS, 7.00, 6.25, 3, 100);

        return $v;
    }

    private function promo(TipoPromocion $tipo, ?int $producto, ?int $familia, int $min = 1, float $valor = 0, string $modo = 'automatica'): Promocion
    {
        return new Promocion(null, 'Promo', $tipo, $modo, $producto, $familia, $min, $valor, null,
            new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-12-31'));
    }

    public function test_subtotal_por_linea_y_total_de_la_compra(): void
    {
        $v = $this->venta();
        $this->assertSame(9.0, $v->lineas()[0]->subtotal());
        $this->assertSame(21.0, $v->lineas()[1]->subtotal());
        $this->assertSame(30.0, $v->total());
    }

    public function test_lectura_repetida_suma_cantidad_y_valida_existencia(): void
    {
        $v = $this->venta();
        $v->agregarProducto(10, 'P1', 'Fideos', self::PASTAS, 4.50, 3.90, 1, 100);
        $this->assertSame(3, $v->cantidadDe(10));

        $this->expectException(ReglaNegocioException::class);
        $v->agregarProducto(10, 'P1', 'Fideos', self::PASTAS, 4.50, 3.90, 98, 100);
    }

    public function test_segundo_producto_a_mitad_de_precio(): void
    {
        $v = $this->venta();
        (new MotorPromociones)->aplicar($v, [$this->promo(TipoPromocion::SegundoMitad, 20, null)]);
        // 3 Coca-Cola: 1 par → descuento 3.50
        $this->assertSame(3.5, $v->lineas()[1]->descuento);
        $this->assertSame(26.5, $v->total());
    }

    public function test_mayoreo_requiere_cantidad_minima_de_la_misma_clase(): void
    {
        $v = $this->venta();
        $motor = new MotorPromociones;
        $mayoreo = $this->promo(TipoPromocion::Mayoreo, null, self::PASTAS, 3);
        $motor->aplicar($v, [$mayoreo]);
        $this->assertSame(0.0, $v->descuentoLineas());

        $v->agregarProducto(10, 'P1', 'Fideos', self::PASTAS, 4.50, 3.90, 1, 100);
        $motor->aplicar($v, [$mayoreo]);
        // 3 × (4.50 − 3.90) = 1.80
        $this->assertSame(1.8, $v->descuentoLineas());
    }

    public function test_producto_gratuito_vale_y_puntos(): void
    {
        $v = $this->venta();
        (new MotorPromociones)->aplicar($v, [
            $this->promo(TipoPromocion::ProductoGratuito, 20, null),
            $this->promo(TipoPromocion::ValeRegalo, null, null, 1, 10, 'qr'),
            $this->promo(TipoPromocion::Puntos, null, self::PASTAS, 1, 2),
        ]);
        // 30 − 7 (Coca-Cola gratis) − 10 (vale) = 13
        $this->assertSame(13.0, $v->total());
        $this->assertSame(4, $v->puntosGanados);
    }

    public function test_canje_de_puntos_no_supera_el_total(): void
    {
        $v = $this->venta();
        $v->asignarCliente(5);
        $v->canjearPuntos(100, 0.10);
        $this->assertSame(20.0, $v->total());

        $this->expectException(ReglaNegocioException::class);
        $v->canjearPuntos(250, 0.10);
    }

    public function test_pago_en_efectivo_calcula_cambio_y_tarjeta_exige_voucher(): void
    {
        $pago = Pago::registrar(1, MetodoPago::Efectivo, 26.50, 50, null, new \DateTimeImmutable);
        $this->assertSame(23.5, $pago->cambio);

        $this->expectException(ReglaNegocioException::class);
        Pago::registrar(1, MetodoPago::Credito, 26.50, null, '', new \DateTimeImmutable);
    }

    public function test_corte_de_caja(): void
    {
        $caja = Caja::abrir(1, 200, new \DateTimeImmutable);
        $caja->cerrar(350.25, 20, 530, new \DateTimeImmutable);
        $this->assertSame(530.25, $caja->montoEsperado);
        $this->assertSame(-0.25, $caja->diferencia);
        $this->assertFalse($caja->estaAbierta());
    }
}
