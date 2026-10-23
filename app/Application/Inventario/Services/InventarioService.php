<?php

namespace App\Application\Inventario\Services;

use App\Application\Inventario\Ports\In\AjustarExistenciasUseCase;
use App\Application\Inventario\Ports\In\ConsultarComprasUseCase;
use App\Application\Inventario\Ports\In\ConsultarExistenciasUseCase;
use App\Application\Inventario\Ports\In\ConsultarKardexUseCase;
use App\Application\Inventario\Ports\In\RegistrarCompraUseCase;
use App\Application\Inventario\Ports\Out\CompraRepository;
use App\Application\Inventario\Ports\Out\ExistenciaRepository;
use App\Application\Inventario\Ports\Out\MovimientoRepository;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Domain\Catalogos\Producto;
use App\Domain\Inventario\Compra;
use App\Domain\Inventario\TipoMovimiento;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Control del inventario que ingresa (compras) y sale (ventas y bajas) con su kardex.
 */
class InventarioService implements RegistrarCompraUseCase, ConsultarComprasUseCase, ConsultarExistenciasUseCase,
    ConsultarKardexUseCase, AjustarExistenciasUseCase
{
    public function __construct(
        private CompraRepository $compras,
        private ExistenciaRepository $existencias,
        private MovimientoRepository $movimientos,
        private Transaccion $transaccion,
        private Reloj $reloj,
    ) {
    }

    public function registrar(int $proveedorId, int $usuarioId, string $numeroDocumento, string $fecha, array $lineas): int
    {
        $compra = new Compra(null, $proveedorId, $usuarioId, $numeroDocumento, new \DateTimeImmutable($fecha));
        foreach ($lineas as $l) {
            $compra->agregarLinea((int) $l['producto_id'], (int) $l['cantidad'], (float) $l['costo_unitario']);
        }
        $compra->validar();

        return $this->transaccion->ejecutar(function () use ($compra) {
            $id = $this->compras->guardar($compra);
            foreach ($compra->lineas() as $l) {
                $producto = $this->producto($l['producto_id']);
                $producto->aumentarExistencia($l['cantidad']);
                $this->existencias->actualizarExistencia($producto->id, $producto->existencia);
                $this->movimientos->registrar($producto->id, TipoMovimiento::Entrada, $l['cantidad'], $producto->existencia,
                    "COMPRA-{$id}", "Compra doc. {$compra->numeroDocumento}", $this->reloj->ahora());
            }

            return $id;
        });
    }

    public function listar(): array
    {
        return $this->compras->listar();
    }

    public function obtener(int $id): array
    {
        return $this->compras->obtener($id) ?? throw new ReglaNegocioException('La compra no existe.');
    }

    public function existencias(?string $buscar = null): array
    {
        return $this->existencias->listar($buscar);
    }

    public function kardex(int $productoId): array
    {
        return ['producto' => $this->producto($productoId), 'movimientos' => $this->movimientos->kardex($productoId)];
    }

    public function registrarSalida(int $productoId, int $cantidad, string $referencia, string $descripcion): void
    {
        $producto = $this->producto($productoId);
        $producto->disminuirExistencia($cantidad);
        $this->existencias->actualizarExistencia($productoId, $producto->existencia);
        $this->movimientos->registrar($productoId, TipoMovimiento::Salida, $cantidad, $producto->existencia,
            $referencia, $descripcion, $this->reloj->ahora());
    }

    /**
     * El producto devuelto por daño o vencimiento no regresa al inventario vendible:
     * queda registrado como baja en el kardex sin aumentar la existencia.
     */
    public function registrarBaja(int $productoId, int $cantidad, string $referencia, string $descripcion): void
    {
        $producto = $this->producto($productoId);
        $this->movimientos->registrar($productoId, TipoMovimiento::Baja, $cantidad, $producto->existencia,
            $referencia, $descripcion, $this->reloj->ahora());
    }

    private function producto(int $id): Producto
    {
        return $this->existencias->producto($id) ?? throw new ReglaNegocioException('El producto no existe.');
    }
}
