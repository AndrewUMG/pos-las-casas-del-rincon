<?php

namespace App\Providers;

use App\Application\Caja\Ports\In as CajaIn;
use App\Application\Caja\Ports\Out as CajaOut;
use App\Application\Caja\Services\CajaService;
use App\Application\Catalogos\Ports\In as CatIn;
use App\Application\Catalogos\Ports\Out as CatOut;
use App\Application\Catalogos\Services\ClientesService;
use App\Application\Catalogos\Services\ProductosService;
use App\Application\Catalogos\Services\ProveedoresService;
use App\Application\Catalogos\Services\SincronizarCatalogoService;
use App\Application\Cobros\Ports\In as CobIn;
use App\Application\Cobros\Ports\Out\PagoRepository;
use App\Application\Cobros\Services\CobroService;
use App\Application\Devoluciones\Ports\In as DevIn;
use App\Application\Devoluciones\Ports\Out\DevolucionRepository;
use App\Application\Devoluciones\Services\DevolucionesService;
use App\Application\Facturacion\Ports\In as FacIn;
use App\Application\Facturacion\Ports\Out as FacOut;
use App\Application\Facturacion\Services\FacturacionService;
use App\Application\Inventario\Ports\In as InvIn;
use App\Application\Inventario\Ports\Out as InvOut;
use App\Application\Inventario\Services\InventarioService;
use App\Application\Promociones\Ports\In as PromIn;
use App\Application\Promociones\Ports\Out as PromOut;
use App\Application\Promociones\Services\PromocionesService;
use App\Application\Reportes\Ports\In as RepIn;
use App\Application\Reportes\Ports\Out\ReportesQueryPort;
use App\Application\Reportes\Services\ReportesService;
use App\Application\Seguridad\Ports\In as SegIn;
use App\Application\Seguridad\Ports\Out as SegOut;
use App\Application\Seguridad\Services\SeguridadService;
use App\Application\Shared\Ports as Shared;
use App\Application\Ventas\Ports\In as VenIn;
use App\Application\Ventas\Ports\Out as VenOut;
use App\Application\Ventas\Services\DespachoService;
use App\Infrastructure\External\CoreConsumoArchivoAdapter;
use App\Infrastructure\External\CoreConsumoHttpAdapter;
use App\Infrastructure\Integracion\CajaAdapter;
use App\Infrastructure\Integracion\InventarioAdapter;
use App\Infrastructure\Integracion\PromocionesAdapter;
use App\Infrastructure\Pdf\DomPdfAdapter;
use App\Infrastructure\Persistence\Consultas\ReportesSqlAdapter;
use App\Infrastructure\Persistence\Eloquent\Repositories as Repo;
use App\Infrastructure\Qr\SimpleQrCodeAdapter;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\LaravelSesionAdapter;
use App\Infrastructure\Shared\LaravelTransaccion;
use App\Infrastructure\Shared\SistemaReloj;
use Illuminate\Support\ServiceProvider;

/**
 * Ensamblaje de la arquitectura hexagonal: cada puerto se conecta con su adaptador
 * y cada caso de uso (puerto de entrada) con el servicio que lo implementa.
 */
class HexagonalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->puertosDeSalida();
        $this->casosDeUso();
    }

    private function puertosDeSalida(): void
    {
        $adaptadores = [
            // Compartidos
            Shared\Transaccion::class => LaravelTransaccion::class,
            Shared\Reloj::class => SistemaReloj::class,
            Shared\GeneradorPdfPort::class => DomPdfAdapter::class,
            Shared\InventarioPort::class => InventarioAdapter::class,
            Shared\CajaPort::class => CajaAdapter::class,
            // Seguridad
            SegOut\UsuarioRepository::class => Repo\EloquentUsuarioRepository::class,
            SegOut\RolRepository::class => Repo\EloquentRolRepository::class,
            SegOut\PasswordHasher::class => BcryptPasswordHasher::class,
            SegOut\SesionPort::class => LaravelSesionAdapter::class,
            // Catálogos
            CatOut\ClienteRepository::class => Repo\EloquentClienteRepository::class,
            CatOut\ProveedorRepository::class => Repo\EloquentProveedorRepository::class,
            CatOut\ProductoRepository::class => Repo\EloquentProductoRepository::class,
            // Inventario
            InvOut\CompraRepository::class => Repo\EloquentCompraRepository::class,
            InvOut\ExistenciaRepository::class => Repo\EloquentExistenciaRepository::class,
            InvOut\MovimientoRepository::class => Repo\EloquentMovimientoRepository::class,
            // Caja
            CajaOut\CajaRepository::class => Repo\EloquentCajaRepository::class,
            CajaOut\MovimientoCajaRepository::class => Repo\EloquentMovimientoCajaRepository::class,
            // Despacho
            VenOut\VentaRepository::class => Repo\EloquentVentaRepository::class,
            VenOut\MotorPromocionesPort::class => PromocionesAdapter::class,
            // Promociones
            PromOut\PromocionRepository::class => Repo\EloquentPromocionRepository::class,
            PromOut\CuponRepository::class => Repo\EloquentCuponRepository::class,
            PromOut\PuntosRepository::class => Repo\EloquentPuntosRepository::class,
            // Cobros
            PagoRepository::class => Repo\EloquentPagoRepository::class,
            // Facturación
            FacOut\FacturaRepository::class => Repo\EloquentFacturaRepository::class,
            FacOut\AsignarPromocionPort::class => PromocionesAdapter::class,
            FacOut\GeneradorQrPort::class => SimpleQrCodeAdapter::class,
            // Devoluciones
            DevolucionRepository::class => Repo\EloquentDevolucionRepository::class,
            // Reportes
            ReportesQueryPort::class => ReportesSqlAdapter::class,
        ];
        foreach ($adaptadores as $puerto => $adaptador) {
            $this->app->bind($puerto, $adaptador);
        }

        // Core de Empresas de Consumo Masivo: el adaptador se elige por configuración.
        $this->app->bind(CatOut\CatalogoCorePort::class, fn () => config('pos.core.driver') === 'http'
            ? new CoreConsumoHttpAdapter(config('pos.core.url'), config('pos.core.token'))
            : new CoreConsumoArchivoAdapter(config('pos.core.archivo')));
    }

    private function casosDeUso(): void
    {
        $servicios = [
            SeguridadService::class => [SegIn\IniciarSesionUseCase::class, SegIn\CerrarSesionUseCase::class,
                SegIn\GestionarUsuariosUseCase::class, SegIn\AsignarPermisosUseCase::class],
            ClientesService::class => [CatIn\GestionarClientesUseCase::class],
            ProveedoresService::class => [CatIn\GestionarProveedoresUseCase::class],
            ProductosService::class => [CatIn\GestionarProductosUseCase::class, CatIn\ActualizarPreciosUseCase::class],
            SincronizarCatalogoService::class => [CatIn\SincronizarCatalogoUseCase::class],
            InventarioService::class => [InvIn\RegistrarCompraUseCase::class, InvIn\ConsultarComprasUseCase::class,
                InvIn\ConsultarExistenciasUseCase::class, InvIn\ConsultarKardexUseCase::class, InvIn\AjustarExistenciasUseCase::class],
            CajaService::class => [CajaIn\AbrirCajaUseCase::class, CajaIn\RegistrarMovimientoUseCase::class,
                CajaIn\CerrarCajaUseCase::class, CajaIn\ConsultarCajaUseCase::class],
            DespachoService::class => [VenIn\IniciarVentaUseCase::class, VenIn\AgregarProductoUseCase::class,
                VenIn\QuitarLineaUseCase::class, VenIn\CalcularTotalesUseCase::class, VenIn\GestionarVentaUseCase::class],
            PromocionesService::class => [PromIn\ConfigurarPromocionUseCase::class, PromIn\ConsultarPromocionQrUseCase::class,
                PromIn\AplicarPromocionUseCase::class, PromIn\CanjearPuntosUseCase::class],
            CobroService::class => [CobIn\CobrarVentaUseCase::class, CobIn\CalcularCambioUseCase::class],
            FacturacionService::class => [FacIn\GenerarFacturaUseCase::class, FacIn\ImprimirFacturaUseCase::class],
            DevolucionesService::class => [DevIn\ProcesarDevolucionUseCase::class, DevIn\ConsultarFacturaUseCase::class],
            ReportesService::class => [RepIn\ReporteCorteCajaQuery::class, RepIn\ReporteDevolucionesQuery::class,
                RepIn\ReporteClientesQuery::class, RepIn\ReporteComprasQuery::class],
        ];
        foreach ($servicios as $servicio => $puertos) {
            foreach ($puertos as $puerto) {
                $this->app->bind($puerto, $servicio);
            }
        }

        // Parámetros de configuración del negocio.
        $this->app->when(PromocionesService::class)->needs('$valorPunto')->give(fn () => (float) config('pos.valor_punto'));
        $this->app->when(PromocionesService::class)->needs('$diasVigenciaCupon')->give(fn () => (int) config('pos.vigencia_cupon_dias'));
        $this->app->when([FacturacionService::class, DevolucionesService::class])->needs('$serie')->give(fn () => (string) config('pos.serie_factura'));
    }
}
