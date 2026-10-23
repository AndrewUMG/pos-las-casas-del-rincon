<?php

namespace App\Domain\Ventas;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Raíz de agregado del despacho.
 * Total = suma de subtotales por línea − descuentos de la venta (vale de regalo y puntos).
 */
class Venta
{
    public const EN_PROCESO = 'en_proceso';
    public const PAGADA = 'pagada';
    public const FACTURADA = 'facturada';

    /** @var array<int, LineaVenta> indexado por producto */
    private array $lineas = [];

    /** Descuento a nivel de venta (vale de regalo). */
    public float $descuentoVale = 0;

    public float $descuentoPuntos = 0;

    public int $puntosCanjeados = 0;

    public int $puntosGanados = 0;

    public function __construct(
        public ?int $id,
        public readonly int $cajaId,
        public readonly int $usuarioId,
        public ?int $clienteId,
        public readonly \DateTimeImmutable $fecha,
        public string $estado = self::EN_PROCESO,
    ) {
    }

    public static function iniciar(int $cajaId, int $usuarioId, \DateTimeImmutable $ahora): self
    {
        return new self(null, $cajaId, $usuarioId, null, $ahora);
    }

    public function asegurarEnProceso(): void
    {
        if ($this->estado !== self::EN_PROCESO) {
            throw new ReglaNegocioException('La venta ya fue cobrada y no puede modificarse.');
        }
    }

    public function restaurarLinea(LineaVenta $linea): void
    {
        $this->lineas[$linea->productoId] = $linea;
    }

    /** Lectura por línea: si el producto ya está en la venta se suma la cantidad. */
    public function agregarProducto(int $productoId, string $codigo, string $nombre, int $familiaId,
        float $precio, ?float $precioMayoreo, int $cantidad, int $existencia): void
    {
        $this->asegurarEnProceso();
        $nueva = $this->cantidadDe($productoId) + $cantidad;
        if ($cantidad <= 0) {
            throw new ReglaNegocioException('La cantidad debe ser mayor a cero.');
        }
        if ($nueva > $existencia) {
            throw new ReglaNegocioException("Existencia insuficiente de {$nombre}: disponible {$existencia}.");
        }
        if (isset($this->lineas[$productoId])) {
            $this->lineas[$productoId]->cantidad = $nueva;
        } else {
            $this->lineas[$productoId] = new LineaVenta($productoId, $codigo, $nombre, $familiaId, $precio, $precioMayoreo, $cantidad);
        }
    }

    public function quitarLinea(int $productoId): void
    {
        $this->asegurarEnProceso();
        unset($this->lineas[$productoId]);
    }

    public function cantidadDe(int $productoId): int
    {
        return isset($this->lineas[$productoId]) ? $this->lineas[$productoId]->cantidad : 0;
    }

    /** @return array<int, LineaVenta> */
    public function lineas(): array
    {
        return array_values($this->lineas);
    }

    public function tieneLineas(): bool
    {
        return $this->lineas !== [];
    }

    public function limpiarPromociones(): void
    {
        foreach ($this->lineas as $linea) {
            $linea->limpiarDescuentos();
        }
        $this->descuentoVale = 0;
        $this->puntosGanados = 0;
    }

    public function agregarDescuentoVale(float $monto): void
    {
        $this->descuentoVale = Dinero::redondear($this->descuentoVale + $monto);
    }

    public function asignarCliente(?int $clienteId): void
    {
        $this->asegurarEnProceso();
        if ($this->puntosCanjeados > 0 && $clienteId !== $this->clienteId) {
            throw new ReglaNegocioException('No puede cambiar el cliente después de canjear sus puntos.');
        }
        $this->clienteId = $clienteId;
    }

    public function canjearPuntos(int $puntos, float $valorPunto): void
    {
        $this->asegurarEnProceso();
        if ($this->clienteId === null) {
            throw new ReglaNegocioException('Seleccione el cliente antes de canjear puntos.');
        }
        $maximo = (int) floor($this->totalAntesDePuntos() / $valorPunto) - $this->puntosCanjeados;
        if ($puntos <= 0) {
            throw new ReglaNegocioException('Indique una cantidad de puntos válida.');
        }
        if ($puntos > $maximo) {
            throw new ReglaNegocioException("Solo puede canjear hasta {$maximo} puntos en esta compra.");
        }
        $this->puntosCanjeados += $puntos;
        $this->descuentoPuntos = Dinero::redondear($this->puntosCanjeados * $valorPunto);
    }

    public function subtotal(): float
    {
        return Dinero::redondear(array_sum(array_map(fn (LineaVenta $l) => $l->importeBruto(), $this->lineas)));
    }

    public function descuentoLineas(): float
    {
        return Dinero::redondear(array_sum(array_map(fn (LineaVenta $l) => $l->descuento, $this->lineas)));
    }

    public function descuentoTotal(): float
    {
        return Dinero::redondear($this->subtotal() - $this->total());
    }

    private function totalAntesDePuntos(): float
    {
        $sumaLineas = array_sum(array_map(fn (LineaVenta $l) => $l->subtotal(), $this->lineas));

        return max(0, Dinero::redondear($sumaLineas - $this->descuentoVale));
    }

    public function total(): float
    {
        return max(0, Dinero::redondear($this->totalAntesDePuntos() - $this->descuentoPuntos));
    }

    public function marcarPagada(): void
    {
        $this->asegurarEnProceso();
        if (! $this->tieneLineas()) {
            throw new ReglaNegocioException('No hay productos en la venta.');
        }
        $this->estado = self::PAGADA;
    }

    public function marcarFacturada(): void
    {
        if ($this->estado !== self::PAGADA) {
            throw new ReglaNegocioException('Solo se puede facturar una venta pagada.');
        }
        $this->estado = self::FACTURADA;
    }
}
