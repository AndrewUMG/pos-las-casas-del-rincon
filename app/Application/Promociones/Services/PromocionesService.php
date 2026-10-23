<?php

namespace App\Application\Promociones\Services;

use App\Application\Promociones\Ports\In\AplicarPromocionUseCase;
use App\Application\Promociones\Ports\In\CanjearPuntosUseCase;
use App\Application\Promociones\Ports\In\ConfigurarPromocionUseCase;
use App\Application\Promociones\Ports\In\ConsultarPromocionQrUseCase;
use App\Application\Promociones\Ports\Out\CuponRepository;
use App\Application\Promociones\Ports\Out\PromocionRepository;
use App\Application\Promociones\Ports\Out\PuntosRepository;
use App\Application\Shared\Ports\Reloj;
use App\Domain\Promociones\Cupon;
use App\Domain\Promociones\MotorPromociones;
use App\Domain\Promociones\Promocion;
use App\Domain\Promociones\TipoPromocion;
use App\Domain\Shared\ReglaNegocioException;
use App\Domain\Ventas\Venta;

class PromocionesService implements ConfigurarPromocionUseCase, ConsultarPromocionQrUseCase, AplicarPromocionUseCase, CanjearPuntosUseCase
{
    private MotorPromociones $motor;

    public function __construct(
        private PromocionRepository $promociones,
        private CuponRepository $cupones,
        private PuntosRepository $puntos,
        private Reloj $reloj,
        private float $valorPunto,
        private int $diasVigenciaCupon,
    ) {
        $this->motor = new MotorPromociones;
    }

    // ------------------------------------------------------------------ Configuración

    public function listar(): array
    {
        return $this->promociones->listar();
    }

    public function obtener(int $id): Promocion
    {
        return $this->promociones->buscarPorId($id) ?? throw new ReglaNegocioException('La promoción no existe.');
    }

    public function guardar(array $datos, ?int $id = null): Promocion
    {
        $tipo = TipoPromocion::tryFrom($datos['tipo'] ?? '') ?? throw new ReglaNegocioException('Seleccione el tipo de promoción.');
        if (empty($datos['fecha_inicio']) || empty($datos['fecha_fin'])) {
            throw new ReglaNegocioException('Indique la vigencia de la promoción.');
        }

        return $this->promociones->guardar(new Promocion(
            $id, $datos['nombre'] ?? '', $tipo, $datos['modo'] ?? Promocion::AUTOMATICA,
            ! empty($datos['producto_id']) ? (int) $datos['producto_id'] : null,
            ! empty($datos['familia_id']) ? (int) $datos['familia_id'] : null,
            max(1, (int) ($datos['cantidad_minima'] ?? 1)), (float) ($datos['valor'] ?? 0), $datos['descripcion'] ?? null,
            new \DateTimeImmutable($datos['fecha_inicio']), new \DateTimeImmutable($datos['fecha_fin']),
            (bool) ($datos['activa'] ?? false),
        ));
    }

    public function cupones(): array
    {
        return $this->cupones->listar();
    }

    // ------------------------------------------------------------------ Aplicación en el despacho

    /** Promociones automáticas vigentes + las de los cupones QR canjeados en esta venta. */
    public function aplicar(Venta $venta): void
    {
        $promos = $this->promociones->vigentes(Promocion::AUTOMATICA, $this->reloj->ahora());
        if ($venta->id) {
            foreach ($this->cupones->canjeadosEnVenta($venta->id) as $cupon) {
                if ($p = $this->promociones->buscarPorId($cupon->promocionId)) {
                    $promos[] = $p;
                }
            }
        }
        $this->motor->aplicar($venta, $promos);
    }

    public function canjearCupon(Venta $venta, string $token): Promocion
    {
        $venta->asegurarEnProceso();
        $cupon = $this->cupones->buscarPorToken(self::normalizarToken($token))
            ?? throw new ReglaNegocioException('El código QR no corresponde a ningún cupón.');
        $cupon->canjear($venta->id, $this->reloj->ahora());
        $promo = $this->obtener($cupon->promocionId);
        if (! $promo->activa) {
            throw new ReglaNegocioException('La promoción de este cupón ya no está activa.');
        }
        $this->cupones->guardar($cupon);

        return $promo;
    }

    public function liberarCupones(int $ventaId): void
    {
        foreach ($this->cupones->canjeadosEnVenta($ventaId) as $cupon) {
            $cupon->liberar();
            $this->cupones->guardar($cupon);
        }
    }

    /** Al facturar se asigna al QR una de las promociones vigentes en modo QR. */
    public function emitirCupon(int $facturaId, ?int $clienteId): ?array
    {
        $disponibles = $this->promociones->vigentes(Promocion::QR, $this->reloj->ahora());
        if ($disponibles === []) {
            return null;
        }
        $promo = $disponibles[array_rand($disponibles)];
        $cupon = $this->cupones->guardar(Cupon::emitir($promo->id, $facturaId, $clienteId, $this->reloj->ahora(), $this->diasVigenciaCupon));

        return ['token' => $cupon->token, 'promocion' => $promo->tipo->etiqueta(), 'detalle' => $promo->detalle(),
            'vence' => $cupon->fechaVencimiento->format('d/m/Y')];
    }

    public function cuponDeFactura(int $facturaId): ?array
    {
        $cupon = $this->cupones->deFactura($facturaId);
        if (! $cupon) {
            return null;
        }
        $promo = $this->obtener($cupon->promocionId);

        return ['token' => $cupon->token, 'promocion' => $promo->tipo->etiqueta(), 'detalle' => $promo->detalle(),
            'vence' => $cupon->fechaVencimiento->format('d/m/Y'), 'estado' => $cupon->estado];
    }

    // ------------------------------------------------------------------ Lectura del QR

    public function consultar(string $token): ?array
    {
        $cupon = $this->cupones->buscarPorToken(self::normalizarToken($token));
        if (! $cupon) {
            return null;
        }
        $promo = $this->obtener($cupon->promocionId);
        $vencido = $cupon->vencido($this->reloj->ahora());

        return [
            'token' => $cupon->token,
            'tipo' => $promo->tipo->value,
            'tipo_etiqueta' => $promo->tipo->etiqueta(),
            'nombre' => $promo->nombre,
            'detalle' => $promo->detalle(),
            'descripcion' => $promo->descripcion,
            'vence' => $cupon->fechaVencimiento->format('d/m/Y'),
            'estado' => $vencido && $cupon->estado === Cupon::EMITIDO ? 'vencido' : $cupon->estado,
        ];
    }

    // ------------------------------------------------------------------ Puntos

    public function canjear(Venta $venta, int $puntos): void
    {
        if ($venta->clienteId === null) {
            throw new ReglaNegocioException('Seleccione el cliente antes de canjear puntos.');
        }
        $saldo = $this->puntos->saldo($venta->clienteId);
        if ($puntos > $saldo) {
            throw new ReglaNegocioException("El cliente solo tiene {$saldo} puntos disponibles.");
        }
        $venta->canjearPuntos($puntos, $this->valorPunto);
        $this->puntos->ajustar($venta->clienteId, -$puntos);
    }

    public function devolver(int $clienteId, int $puntos): void
    {
        if ($puntos > 0) {
            $this->puntos->ajustar($clienteId, $puntos);
        }
    }

    public function acreditar(int $clienteId, int $puntos): void
    {
        if ($puntos > 0) {
            $this->puntos->ajustar($clienteId, $puntos);
        }
    }

    /** El lector de QR entrega la URL completa; el token es el último segmento. */
    private static function normalizarToken(string $token): string
    {
        $token = trim($token);
        if (str_contains($token, '/')) {
            $token = basename(parse_url($token, PHP_URL_PATH) ?: $token);
        }

        return strtoupper($token);
    }
}
