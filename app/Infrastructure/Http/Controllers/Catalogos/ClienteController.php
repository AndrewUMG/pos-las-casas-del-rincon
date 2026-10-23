<?php

namespace App\Infrastructure\Http\Controllers\Catalogos;

use App\Application\Catalogos\Ports\In\GestionarClientesUseCase;
use Illuminate\Http\Request;

class ClienteController
{
    public function __construct(private GestionarClientesUseCase $clientes)
    {
    }

    public function index(Request $request)
    {
        return view('catalogos.clientes.index', ['clientes' => $this->clientes->listar($request->query('q')), 'q' => $request->query('q')]);
    }

    public function create()
    {
        return view('catalogos.clientes.form', ['cliente' => null]);
    }

    public function store(Request $request)
    {
        $this->clientes->guardar($this->validar($request));

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado.');
    }

    public function edit(int $cliente)
    {
        return view('catalogos.clientes.form', ['cliente' => $this->clientes->obtener($cliente)]);
    }

    public function update(Request $request, int $cliente)
    {
        $this->clientes->guardar($this->validar($request), $cliente);

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado.');
    }

    public function destroy(int $cliente)
    {
        $this->clientes->eliminar($cliente);

        return redirect()->route('clientes.index')->with('ok', 'Cliente eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nit' => 'required|string|max:20', 'nombre' => 'required|string|max:150', 'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30', 'email' => 'nullable|email|max:120',
        ]);
    }
}
