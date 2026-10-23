<?php

namespace App\Infrastructure\Http\Controllers\Catalogos;

use App\Application\Catalogos\Ports\In\GestionarProductosUseCase;
use App\Application\Catalogos\Ports\In\GestionarProveedoresUseCase;
use Illuminate\Http\Request;

class ProductoController
{
    public function __construct(private GestionarProductosUseCase $productos, private GestionarProveedoresUseCase $proveedores)
    {
    }

    public function index(Request $request)
    {
        $familia = $request->integer('familia') ?: null;

        return view('catalogos.productos.index', [
            'productos' => $this->productos->listar($request->query('q'), $familia),
            'familias' => $this->productos->familias(), 'q' => $request->query('q'), 'familia' => $familia,
        ]);
    }

    public function create()
    {
        return $this->formulario(null);
    }

    public function store(Request $request)
    {
        $this->productos->guardar($this->validar($request));

        return redirect()->route('productos.index')->with('ok', 'Producto registrado. Su existencia aumentará al registrar compras.');
    }

    public function edit(int $producto)
    {
        return $this->formulario($this->productos->obtener($producto));
    }

    public function update(Request $request, int $producto)
    {
        $this->productos->guardar($this->validar($request), $producto);

        return redirect()->route('productos.index')->with('ok', 'Producto actualizado.');
    }

    public function destroy(int $producto)
    {
        $this->productos->desactivar($producto);

        return redirect()->route('productos.index')->with('ok', 'Producto desactivado.');
    }

    private function formulario($producto)
    {
        return view('catalogos.productos.form', [
            'producto' => $producto, 'familias' => $this->productos->familias(), 'proveedores' => $this->proveedores->listar(),
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'codigo' => 'required|string|max:40', 'nombre' => 'required|string|max:150', 'familia_id' => 'required|integer',
            'proveedor_id' => 'nullable|integer', 'precio' => 'required|numeric|min:0.01',
            'precio_mayoreo' => 'nullable|numeric|min:0.01', 'activo' => 'nullable|boolean',
        ]) + ['activo' => $request->boolean('activo')];
    }
}
