<?php

namespace App\Domain\Devoluciones;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Devolución de productos de una factura por daño o vencimiento.
 */
class Devolucion
{
    /** @var array<int, array{producto_id:int, nombre:string, cantidad:int, precio_unitario:float, subtotal:float}> */
    private array $lineas = [];

    public function __construct(
        public readonly ?int $id,
        public readonly int $facturaId,
        public readonly int $usuarioId,
        public readonly int $cajaId,
        public readonly MotivoDevolucion $motivo,
        public readonly ?string $observacion,
        public readonly \DateTimeImmutable $fecha,
    ) {
    }

    /**
     * @param  int  $disponible  cantidad vendida menos lo ya devuelto
     */
    public function agregarProducto(int $productoId, string $nombre, int $cantidad, int $disponible, float $precioUnitario): void
    {
        if ($cantidad <= 0) {
            return;
        }
        if ($cantidad > $disponible) {
            throw new ReglaNegocioException("No puede devolver más de {$disponible} unidad(es) de {$nombre}.");
        }
        $this->lineas[] = [
            'producto_id' => $productoId,
            'nombre' => $nombre,
            'cantidad' => $cantidad,
            'precio_unitario' => Dinero::redondear($precioUnitario),
            'subtotal' => Dinero::redondear($cantidad * $precioUnitario),
        ];
    }

    public function lineas(): array
    {
        return $this->lineas;
    }

    public function total(): float
    {
        return Dinero::redondear(array_sum(array_column($this->lineas, 'subtotal')));
    }

    public function validar(): void
    {
        if ($this->lineas === []) {
            throw new ReglaNegocioException('Seleccione al menos un producto a devolver.');
        }
    }
}
