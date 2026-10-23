<?php

namespace App\Infrastructure\Http\Controllers\Promociones;

use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use App\Application\Promociones\Ports\In\ConfigurarPromocionUseCase;
use App\Domain\Promociones\TipoPromocion;
use Illuminate\Http\Request;

/**
 * Configuración de promociones (administrador) y consulta de cupones QR emitidos.
 */
class PromocionController
{
    public function __construct(private ConfigurarPromocionUseCase $promociones, private GestionarProductosUseCase $productos)
    {
    }

    public function index()
    {
        return view('promociones.index', ['promociones' => $this->promociones->listar()]);
    }

    public function create()
    {
        return $this->formulario(null);
    }

    public function store(Request $request)
    {
        $this->promociones->guardar($this->validar($request));

        return redirect()->route('promociones.index')->with('ok', 'Promoción registrada.');
    }

    public function edit(int $promocion)
    {
        return $this->formulario($this->promociones->obtener($promocion));
    }

    public function update(Request $request, int $promocion)
    {
        $this->promociones->guardar($this->validar($request), $promocion);

        return redirect()->route('promociones.index')->with('ok', 'Promoción actualizada.');
    }

    public function cupones()
    {
        return view('promociones.cupones', ['cupones' => $this->promociones->cupones()]);
    }

    private function formulario($promocion)
    {
        return view('promociones.form', [
            'promocion' => $promocion, 'tipos' => TipoPromocion::cases(),
            'productos' => $this->productos->listar(null, null, true), 'familias' => $this->productos->familias(),
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:120', 'tipo' => 'required|string', 'modo' => 'required|in:automatica,qr',
            'producto_id' => 'nullable|integer', 'familia_id' => 'nullable|integer', 'cantidad_minima' => 'required|integer|min:1',
            'valor' => 'nullable|numeric|min:0', 'descripcion' => 'nullable|string|max:255',
            'fecha_inicio' => 'required|date', 'fecha_fin' => 'required|date',
        ]) + ['activa' => $request->boolean('activa')];
    }
}
