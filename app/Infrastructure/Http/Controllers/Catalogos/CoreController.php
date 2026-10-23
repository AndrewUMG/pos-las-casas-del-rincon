<?php

namespace App\Infrastructure\Http\Controllers\Catalogos;

use App\Application\Catalogos\Ports\In\SincronizarCatalogoUseCase;

/**
 * Integración con el Core de Empresas de Consumo Masivo (existente).
 */
class CoreController
{
    public function index(SincronizarCatalogoUseCase $core)
    {
        return view('catalogos.core', ['origen' => $core->origen()]);
    }

    public function sincronizar(SincronizarCatalogoUseCase $core)
    {
        $r = $core->sincronizar();

        return back()->with('ok', "Sincronización completa: {$r['creados']} producto(s) nuevo(s), {$r['actualizados']} actualizado(s) y {$r['proveedores']} proveedor(es) nuevo(s).");
    }
}
