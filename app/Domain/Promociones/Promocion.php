<?php

namespace App\Domain\Promociones;

use App\Domain\Shared\ReglaNegocioException;
use App\Domain\Ventas\LineaVenta;

/**
 * Promoción del negocio.
 *  - modo "automatica": se aplica sola durante el despacho.
 *  - modo "qr": se entrega al cliente con el código QR de la factura y se canjea en su próxima compra.
 * El alcance se define por producto y/o familia.
 */
class Promocion
{
    public const AUTOMATICA = 'automatica';
    public const QR = 'qr';

    public function __construct(
        public readonly ?int $id,
        public string $nombre,
        public TipoPromocion $tipo,
        public string $modo,
        public ?int $productoId,
        public ?int $familiaId,
        public int $cantidadMinima,
        public float $valor,
        public ?string $descripcion,
        public \DateTimeImmutable $fechaInicio,
        public \DateTimeImmutable $fechaFin,
        public bool $activa = true,
        public ?string $productoNombre = null,
        public ?string $familiaNombre = null,
    ) {
        if (trim($nombre) === '') {
            throw new ReglaNegocioException('El nombre de la promoción es obligatorio.');
        }
        if (! in_array($modo, [self::AUTOMATICA, self::QR], true)) {
            throw new ReglaNegocioException('Modo de promoción inválido.');
        }
        if ($fechaFin < $fechaInicio) {
            throw new ReglaNegocioException('La fecha final no puede ser anterior a la inicial.');
        }
        if ($cantidadMinima < 1) {
            throw new ReglaNegocioException('La cantidad mínima debe ser al menos 1.');
        }
        match ($tipo) {
            TipoPromocion::ProductoGratuito, TipoPromocion::SegundoMitad => $productoId
                ?: throw new ReglaNegocioException('Indique el producto promocionado.'),
            TipoPromocion::Mayoreo => ($productoId || $familiaId)
                ?: throw new ReglaNegocioException('Indique el producto o la familia de la compra por mayoreo.'),
            TipoPromocion::ValeRegalo => ($valor > 0 && $modo === self::QR)
                ?: throw new ReglaNegocioException('El vale de regalo se entrega por QR y debe tener un monto mayor a cero.'),
            TipoPromocion::Puntos => ($valor > 0 && ($productoId || $familiaId))
                ?: throw new ReglaNegocioException('Indique la familia o producto y los puntos por unidad.'),
        };
    }

    public function vigente(\DateTimeImmutable $hoy): bool
    {
        $d = $hoy->format('Y-m-d');

        return $this->activa && $d >= $this->fechaInicio->format('Y-m-d') && $d <= $this->fechaFin->format('Y-m-d');
    }

    /** Línea dentro del alcance: familia si está definida, si no el producto. */
    public function enAlcance(LineaVenta $linea): bool
    {
        if ($this->familiaId !== null) {
            return $linea->familiaId === $this->familiaId;
        }

        return $linea->productoId === $this->productoId;
    }

    /** Línea que recibe el beneficio: el producto promocionado, o toda la familia si no hay producto. */
    public function esBeneficiada(LineaVenta $linea): bool
    {
        if ($this->productoId !== null) {
            return $linea->productoId === $this->productoId;
        }

        return $linea->familiaId === $this->familiaId;
    }

    /** Texto que se muestra al cliente cuando lee el QR. */
    public function detalle(): string
    {
        $obj = $this->productoNombre ?? ($this->familiaNombre ? 'productos de la familia '.$this->familiaNombre : 'productos seleccionados');

        return match ($this->tipo) {
            TipoPromocion::ProductoGratuito => "Recibe 1 {$obj} gratis".($this->cantidadMinima > 1 ? " al comprar {$this->cantidadMinima} unidades." : ' en tu próxima compra.'),
            TipoPromocion::ValeRegalo => 'Vale de regalo por Q '.number_format($this->valor, 2).' en tu próxima compra.',
            TipoPromocion::SegundoMitad => "Lleva el segundo {$obj} a mitad de precio.",
            TipoPromocion::Mayoreo => "Precio de mayoreo en {$obj} al comprar {$this->cantidadMinima} o más unidades de la misma clase.",
            TipoPromocion::Puntos => 'Gana '.rtrim(rtrim(number_format($this->valor, 2), '0'), '.')." puntos por cada unidad de {$obj} para pagar tu próxima compra.",
        };
    }
}
