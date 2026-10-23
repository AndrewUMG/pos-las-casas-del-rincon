<?php

use App\Infrastructure\Http\Controllers\Caja\CajaController;
use App\Infrastructure\Http\Controllers\Catalogos\ClienteController;
use App\Infrastructure\Http\Controllers\Catalogos\CoreController;
use App\Infrastructure\Http\Controllers\Catalogos\PrecioController;
use App\Infrastructure\Http\Controllers\Catalogos\ProductoController;
use App\Infrastructure\Http\Controllers\Catalogos\ProveedorController;
use App\Infrastructure\Http\Controllers\Devoluciones\DevolucionController;
use App\Infrastructure\Http\Controllers\InicioController;
use App\Infrastructure\Http\Controllers\Inventario\CompraController;
use App\Infrastructure\Http\Controllers\Inventario\InventarioController;
use App\Infrastructure\Http\Controllers\Promociones\PromocionController;
use App\Infrastructure\Http\Controllers\Promociones\PromocionQrController;
use App\Infrastructure\Http\Controllers\Reportes\ReporteController;
use App\Infrastructure\Http\Controllers\Seguridad\LoginController;
use App\Infrastructure\Http\Controllers\Seguridad\RolController;
use App\Infrastructure\Http\Controllers\Seguridad\UsuarioController;
use App\Infrastructure\Http\Controllers\Ventas\CobroController;
use App\Infrastructure\Http\Controllers\Ventas\DespachoController;
use App\Infrastructure\Http\Controllers\Ventas\FacturaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inicio');

// ---------------------------------------------------------------- Acceso
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'formulario'])->name('login');
    Route::post('/login', [LoginController::class, 'iniciar'])->name('login.iniciar');
});
Route::post('/logout', [LoginController::class, 'cerrar'])->middleware('auth')->name('logout');

// ---------------------------------------------------------------- Lectura pública del QR (celular del cliente)
Route::get('/promocion/{token}', [PromocionQrController::class, 'mostrar'])->name('promocion.qr');

Route::middleware('auth')->group(function () {
    Route::get('/inicio', InicioController::class)->name('inicio');

    // ------------------------------------------------------------ Punto de venta
    Route::middleware('permiso:pos')->group(function () {
        Route::get('/pos', [DespachoController::class, 'pantalla'])->name('pos.despacho');
        Route::post('/pos/iniciar', [DespachoController::class, 'iniciar'])->name('pos.iniciar');
        Route::get('/pos/productos', [DespachoController::class, 'buscarProductos'])->name('pos.productos');
        Route::post('/pos/{venta}/lineas', [DespachoController::class, 'agregar'])->name('pos.lineas.agregar');
        Route::delete('/pos/{venta}/lineas/{producto}', [DespachoController::class, 'quitar'])->name('pos.lineas.quitar');
        Route::post('/pos/{venta}/cliente', [DespachoController::class, 'cliente'])->name('pos.cliente');
        Route::post('/pos/{venta}/cupon', [DespachoController::class, 'cupon'])->name('pos.cupon');
        Route::post('/pos/{venta}/puntos', [DespachoController::class, 'puntos'])->name('pos.puntos');
        Route::post('/pos/{venta}/cancelar', [DespachoController::class, 'cancelar'])->name('pos.cancelar');

        Route::get('/pos/{venta}/cobro', [CobroController::class, 'formulario'])->name('pos.cobro');
        Route::post('/pos/{venta}/cobro', [CobroController::class, 'cobrar'])->name('pos.cobrar');

        Route::get('/pos/{venta}/factura', [FacturaController::class, 'formulario'])->name('pos.factura');
        Route::post('/pos/{venta}/factura', [FacturaController::class, 'generar'])->name('pos.facturar');
        Route::get('/facturas', [FacturaController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/{factura}', [FacturaController::class, 'mostrar'])->name('facturas.mostrar');
        Route::get('/facturas/{factura}/pdf', [FacturaController::class, 'pdf'])->name('facturas.pdf');
    });

    // ------------------------------------------------------------ Caja
    Route::middleware('permiso:caja')->group(function () {
        Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
        Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
        Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
        Route::get('/caja/{caja}', [CajaController::class, 'mostrar'])->name('caja.mostrar');
    });

    // ------------------------------------------------------------ Devoluciones
    Route::middleware('permiso:devoluciones')->group(function () {
        Route::get('/devoluciones', [DevolucionController::class, 'index'])->name('devoluciones.index');
        Route::get('/devoluciones/nueva', [DevolucionController::class, 'nueva'])->name('devoluciones.nueva');
        Route::post('/devoluciones', [DevolucionController::class, 'guardar'])->name('devoluciones.guardar');
        Route::get('/devoluciones/{devolucion}', [DevolucionController::class, 'mostrar'])->name('devoluciones.mostrar');
    });

    // ------------------------------------------------------------ Catálogos
    Route::middleware('permiso:catalogos')->group(function () {
        Route::resource('clientes', ClienteController::class)->except('show');
        Route::resource('proveedores', ProveedorController::class)->except('show')->parameters(['proveedores' => 'proveedor']);
        Route::resource('productos', ProductoController::class)->except('show');
        Route::get('/precios', [PrecioController::class, 'index'])->name('precios.index');
        Route::post('/precios', [PrecioController::class, 'actualizar'])->name('precios.actualizar');
        Route::get('/core', [CoreController::class, 'index'])->name('core.index');
        Route::post('/core/sincronizar', [CoreController::class, 'sincronizar'])->name('core.sincronizar');
    });

    // ------------------------------------------------------------ Inventario
    Route::middleware('permiso:inventario')->group(function () {
        Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
        Route::get('/compras/nueva', [CompraController::class, 'nueva'])->name('compras.nueva');
        Route::post('/compras', [CompraController::class, 'guardar'])->name('compras.guardar');
        Route::get('/compras/{compra}', [CompraController::class, 'mostrar'])->name('compras.mostrar');
        Route::get('/existencias', [InventarioController::class, 'existencias'])->name('inventario.existencias');
        Route::get('/kardex/{producto}', [InventarioController::class, 'kardex'])->name('inventario.kardex');
    });

    // ------------------------------------------------------------ Promociones
    Route::middleware('permiso:promociones')->group(function () {
        Route::resource('promociones', PromocionController::class)->except(['show', 'destroy'])->parameters(['promociones' => 'promocion']);
        Route::get('/cupones', [PromocionController::class, 'cupones'])->name('promociones.cupones');
    });

    // ------------------------------------------------------------ Reportes
    Route::middleware('permiso:reportes')->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/{tipo}', [ReporteController::class, 'mostrar'])
            ->whereIn('tipo', ['corte-caja', 'devoluciones', 'clientes', 'compras'])->name('reportes.mostrar');
    });

    // ------------------------------------------------------------ Administración
    Route::middleware('permiso:administracion')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
        Route::post('/roles/{rol}/permisos', [RolController::class, 'asignar'])->name('roles.asignar');
    });
});
