<?php

namespace App\Domain\Caja;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Caja de un cajero: se abre con un monto inicial y se cierra con el corte de caja.
 */
class Caja
{
    public const ABIERTA = 'abierta';
    public const CERRADA = 'cerrada';

    public function __construct(
        public readonly ?int $id,
        public int $usuarioId,
        public \DateTimeImmutable $fechaApertura,
        public float $montoInicial,
        public string $estado = self::ABIERTA,
        public ?\DateTimeImmutable $fechaCierre = null,
        public ?float $montoEsperado = null,
        public ?float $montoContado = null,
        public ?float $diferencia = null,
    ) {
    }

    public static function abrir(int $usuarioId, float $montoInicial, \DateTimeImmutable $ahora): self
    {
        if ($montoInicial < 0) {
            throw new ReglaNegocioException('El monto inicial de la caja no puede ser negativo.');
        }

        return new self(null, $usuarioId, $ahora, Dinero::redondear($montoInicial));
    }

    public function estaAbierta(): bool
    {
        return $this->estado === self::ABIERTA;
    }

    public function asegurarAbierta(): void
    {
        if (! $this->estaAbierta()) {
            throw new ReglaNegocioException('La caja está cerrada; no admite nuevos movimientos.');
        }
    }

    /**
     * Corte de caja: esperado = monto inicial + ingresos en efectivo − egresos en efectivo.
     */
    public function cerrar(float $ingresosEfectivo, float $egresosEfectivo, float $montoContado, \DateTimeImmutable $ahora): void
    {
        $this->asegurarAbierta();
        if ($montoContado < 0) {
            throw new ReglaNegocioException('El monto contado no puede ser negativo.');
        }
        $this->montoEsperado = Dinero::redondear($this->montoInicial + $ingresosEfectivo - $egresosEfectivo);
        $this->montoContado = Dinero::redondear($montoContado);
        $this->diferencia = Dinero::redondear($this->montoContado - $this->montoEsperado);
        $this->fechaCierre = $ahora;
        $this->estado = self::CERRADA;
    }
}
