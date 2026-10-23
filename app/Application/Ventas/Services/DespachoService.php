<?php

namespace App\Application\Ventas\Services;

use App\Application\Catalogos\Ports\Out\ClienteRepository;
use App\Application\Catalogos\Ports\Out\ProductoRepository;
use App\Application\Shared\Ports\CajaPort;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Application\Ventas\Ports\In\AgregarProductoUseCase;
use App\Application\Ventas\Ports\In\CalcularTotalesUseCase;
use App\Application\Ventas\Ports\In\GestionarVentaUseCase;
use App\Application\Ventas\Ports\In\IniciarVentaUseCase;
use App\Application\Ventas\Ports\In\QuitarLineaUseCase;
use App\Application\Ventas\Ports\Out\MotorPromocionesPort;
use App\Application\Ventas\Ports\Out\VentaRepository;
use App\Domain\Catalogos\Cliente;
use App\Domain\Catalogos\Producto;
use App\Domain\Promociones\TipoPromocion;
use App\Domain\Shared\ReglaNegocioException;
use App\Domain\Ventas\Venta;

/**
 * Despacho (punto de venta): lectura por línea, subtotales por línea y total de la compra.
 */
class DespachoService implements IniciarVentaUseCase, AgregarProductoUseCase, QuitarLineaUseCase,
    CalcularTotalesUseCase, GestionarVentaUseCase
{
    public function __construct(
        private VentaRepository $ventas,
        private ProductoRepository $productos,
        private ClienteRepository $clientes,
        private MotorPromocionesPort $promociones,
        private CajaPort $caja,
        private Transaccion $transaccion,
        private Reloj $reloj,
    ) {
    }

    public function ventaActual(int $usuarioId): ?Venta
    {
        return $this->ventas->enProcesoDe($usuarioId);
    }

    public function iniciar(int $usuarioId): Venta
    {
        if ($venta = $this->ventas->enProcesoDe($usuarioId)) {
            return $venta;
        }
        $cajaId = $this->caja->cajaAbiertaDe($usuarioId)
            ?? throw new ReglaNegocioException('Debe abrir la caja antes de despachar productos.');

        return $this->ventas->guardar(Venta::iniciar($cajaId, $usuarioId, $this->reloj->ahora()));
    }

    public function agregar(int $ventaId, string $codigo, int $cantidad): Venta
    {
        $venta = $this->obtener($ventaId);
        $producto = $this->productos->buscarPorCodigo(trim($codigo))
            ?? throw new ReglaNegocioException("No existe un producto con el código «{$codigo}».");
        if (! $producto->activo) {
            throw new ReglaNegocioException("El producto {$producto->nombre} está inactivo.");
        }
        $this->agregarProducto($venta, $producto, $cantidad);

        return $this->recalcularYGuardar($venta);
    }

    public function quitar(int $ventaId, int $productoId): Venta
    {
        $venta = $this->obtener($ventaId);
        $venta->quitarLinea($productoId);

        return $this->recalcularYGuardar($venta);
    }

    public function recalcular(int $ventaId): Venta
    {
        return $this->recalcularYGuardar($this->obtener($ventaId));
    }

    public function obtener(int $ventaId): Venta
    {
        return $this->ventas->buscarPorId($ventaId) ?? throw new ReglaNegocioException('La venta no existe.');
    }

    public function asignarCliente(int $ventaId, ?string $nit): Venta
    {
        $venta = $this->obtener($ventaId);
        $nit = strtoupper(trim((string) $nit));
        if ($nit === '' || $nit === Cliente::CONSUMIDOR_FINAL) {
            $venta->asignarCliente(null);
        } else {
            $cliente = $this->clientes->buscarPorNit($nit)
                ?? throw new ReglaNegocioException("No hay un cliente registrado con NIT {$nit}.");
            $venta->asignarCliente($cliente->id);
        }

        return $this->recalcularYGuardar($venta);
    }

    /** Canje del cupón leído del QR de una factura anterior. */
    public function canjearCupon(int $ventaId, string $token): Venta
    {
        return $this->transaccion->ejecutar(function () use ($ventaId, $token) {
            $venta = $this->obtener($ventaId);
            $venta->asegurarEnProceso();
            $promo = $this->promociones->canjearCupon($venta, $token);

            // Producto gratuito: se agrega el producto promocionado si aún no está en la venta.
            if ($promo->tipo === TipoPromocion::ProductoGratuito && $venta->cantidadDe($promo->productoId) < $promo->cantidadMinima) {
                $producto = $this->productos->buscarPorId($promo->productoId)
                    ?? throw new ReglaNegocioException('El producto de la promoción no existe.');
                $this->agregarProducto($venta, $producto, $promo->cantidadMinima - $venta->cantidadDe($promo->productoId));
            }

            return $this->recalcularYGuardar($venta);
        });
    }

    public function canjearPuntos(int $ventaId, int $puntos): Venta
    {
        return $this->transaccion->ejecutar(function () use ($ventaId, $puntos) {
            $venta = $this->obtener($ventaId);
            $this->promociones->aplicar($venta);
            $this->promociones->canjearPuntos($venta, $puntos);

            return $this->ventas->guardar($venta);
        });
    }

    public function cancelar(int $ventaId): void
    {
        $this->transaccion->ejecutar(function () use ($ventaId) {
            $venta = $this->obtener($ventaId);
            $venta->asegurarEnProceso();
            $this->promociones->liberar($venta);
            $this->ventas->eliminar($ventaId);
        });
    }

    private function agregarProducto(Venta $venta, Producto $producto, int $cantidad): void
    {
        $venta->agregarProducto($producto->id, $producto->codigo, $producto->nombre, $producto->familiaId,
            $producto->precio, $producto->precioMayoreo, $cantidad, $producto->existencia);
    }

    private function recalcularYGuardar(Venta $venta): Venta
    {
        $this->promociones->aplicar($venta);

        return $this->ventas->guardar($venta);
    }
}
