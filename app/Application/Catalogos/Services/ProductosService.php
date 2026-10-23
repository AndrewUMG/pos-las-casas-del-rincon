<?php

namespace App\Application\Catalogos\Services;

use App\Application\Catalogos\Ports\In\ActualizarPreciosUseCase;
use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use App\Application\Catalogos\Ports\Out\ProductoRepository;
use App\Application\Shared\Ports\Transaccion;
use App\Domain\Catalogos\Producto;
use App\Domain\Shared\ReglaNegocioException;

class ProductosService implements GestionarProductosUseCase, ActualizarPreciosUseCase
{
    public function __construct(
        private ProductoRepository $productos,
        private Transaccion $transaccion,
    ) {
    }

    public function listar(?string $buscar = null, ?int $familiaId = null, bool $soloActivos = false): array
    {
        return $this->productos->listar($buscar, $familiaId, $soloActivos);
    }

    public function obtener(int $id): Producto
    {
        return $this->productos->buscarPorId($id) ?? throw new ReglaNegocioException('El producto no existe.');
    }

    public function buscarPorCodigo(string $codigo): ?Producto
    {
        return $this->productos->buscarPorCodigo(trim($codigo));
    }

    public function guardar(array $datos, ?int $id = null): Producto
    {
        $codigo = trim($datos['codigo'] ?? '');
        $existente = $this->productos->buscarPorCodigo($codigo);
        if ($existente && $existente->id !== $id) {
            throw new ReglaNegocioException("Ya existe un producto con el código {$codigo}.");
        }
        if (! isset($this->productos->familias()[(int) ($datos['familia_id'] ?? 0)])) {
            throw new ReglaNegocioException('Seleccione la familia del producto.');
        }
        // La existencia solo cambia por compras, ventas y devoluciones (kardex).
        $existencia = $id ? $this->obtener($id)->existencia : 0;

        return $this->productos->guardar(new Producto(
            $id, $codigo, $datos['nombre'] ?? '', (int) $datos['familia_id'],
            ! empty($datos['proveedor_id']) ? (int) $datos['proveedor_id'] : null,
            (float) ($datos['precio'] ?? 0), self::decimalONulo($datos['precio_mayoreo'] ?? null),
            $existencia, (bool) ($datos['activo'] ?? true),
        ));
    }

    public function desactivar(int $id): void
    {
        $producto = $this->obtener($id);
        $producto->activo = false;
        $this->productos->guardar($producto);
    }

    public function familias(): array
    {
        return $this->productos->familias();
    }

    public function actualizar(array $precios): int
    {
        return $this->transaccion->ejecutar(function () use ($precios) {
            $cambiados = 0;
            foreach ($precios as $id => $p) {
                $producto = $this->obtener((int) $id);
                $precio = (float) $p['precio'];
                $mayoreo = self::decimalONulo($p['precio_mayoreo'] ?? null);
                if ($producto->precio === $precio && $producto->precioMayoreo === $mayoreo) {
                    continue;
                }
                $producto->cambiarPrecios($precio, $mayoreo);
                $this->productos->guardar($producto);
                $cambiados++;
            }

            return $cambiados;
        });
    }

    private static function decimalONulo(mixed $valor): ?float
    {
        return $valor === null || $valor === '' ? null : (float) $valor;
    }
}
