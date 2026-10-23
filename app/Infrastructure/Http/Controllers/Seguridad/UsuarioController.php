<?php

namespace App\Infrastructure\Http\Controllers\Seguridad;

use App\Application\Seguridad\Ports\In\AsignarPermisosUseCase;
use App\Application\Seguridad\Ports\In\GestionarUsuariosUseCase;
use Illuminate\Http\Request;

class UsuarioController
{
    public function __construct(private GestionarUsuariosUseCase $usuarios, private AsignarPermisosUseCase $roles)
    {
    }

    public function index()
    {
        return view('seguridad.usuarios.index', ['usuarios' => $this->usuarios->listar()]);
    }

    public function create()
    {
        return view('seguridad.usuarios.form', ['usuario' => null, 'roles' => $this->roles->roles()]);
    }

    public function store(Request $request)
    {
        $this->usuarios->guardar($this->validar($request));

        return redirect()->route('usuarios.index')->with('ok', 'Usuario registrado.');
    }

    public function edit(int $usuario)
    {
        return view('seguridad.usuarios.form', ['usuario' => $this->usuarios->obtener($usuario), 'roles' => $this->roles->roles()]);
    }

    public function update(Request $request, int $usuario)
    {
        if ($usuario === $request->user()->id && ! $request->boolean('activo')) {
            return back()->with('error', 'No puede desactivar su propio usuario.');
        }
        $this->usuarios->guardar($this->validar($request), $usuario);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:120', 'usuario' => 'required|string|max:50|alpha_dash',
            'rol_id' => 'required|integer', 'password' => 'nullable|string|min:6|confirmed',
        ]) + ['activo' => $request->boolean('activo')];
    }
}
