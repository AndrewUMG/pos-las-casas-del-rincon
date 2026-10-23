<?php

namespace App\Infrastructure\Http\Controllers\Catalogos;

use App\Application\Catalogos\Ports\In\GestionarProveedoresUseCase;
use Illuminate\Http\Request;

class ProveedorController
{
    public function __construct(private GestionarProveedoresUseCase $proveedores)
    {
    }

    public function index(Request $request)
    {
        return view('catalogos.proveedores.index', ['proveedores' => $this->proveedores->listar($request->query('q')), 'q' => $request->query('q')]);
    }

    public function create()
    {
        return view('catalogos.proveedores.form', ['proveedor' => null]);
    }

    public function store(Request $request)
    {
        $this->proveedores->guardar($this->validar($request));

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor registrado.');
    }

    public function edit(int $proveedor)
    {
        return view('catalogos.proveedores.form', ['proveedor' => $this->proveedores->obtener($proveedor)]);
    }

    public function update(Request $request, int $proveedor)
    {
        $this->proveedores->guardar($this->validar($request), $proveedor);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado.');
    }

    public function destroy(int $proveedor)
    {
        $this->proveedores->eliminar($proveedor);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nit' => 'required|string|max:20', 'nombre' => 'required|string|max:150', 'contacto' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:30', 'direccion' => 'nullable|string|max:255',
        ]);
    }
}
