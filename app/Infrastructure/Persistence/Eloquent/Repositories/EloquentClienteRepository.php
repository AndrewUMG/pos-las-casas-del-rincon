<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Catalogos\Ports\Out\ClienteRepository;
use App\Domain\Catalogos\Cliente;
use App\Domain\Shared\ReglaNegocioException;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteModel;
use Illuminate\Database\QueryException;

class EloquentClienteRepository implements ClienteRepository
{
    public function buscarPorId(int $id): ?Cliente
    {
        $m = ClienteModel::find($id);

        return $m ? Mapeador::cliente($m) : null;
    }

    public function buscarPorNit(string $nit): ?Cliente
    {
        $m = ClienteModel::where('nit', strtoupper(trim($nit)))->first();

        return $m ? Mapeador::cliente($m) : null;
    }

    public function listar(?string $buscar = null): array
    {
        return ClienteModel::when($buscar, fn ($q) => $q->where(fn ($w) => $w
            ->where('nombre', 'ilike', "%{$buscar}%")->orWhere('nit', 'ilike', "%{$buscar}%")))
            ->orderBy('nombre')->limit(300)->get()->map(fn ($m) => Mapeador::cliente($m))->all();
    }

    public function guardar(Cliente $c): Cliente
    {
        $m = Mapeador::persistir(ClienteModel::class, $c->id, [
            'nit' => $c->nit, 'nombre' => $c->nombre, 'direccion' => $c->direccion,
            'telefono' => $c->telefono, 'email' => $c->email, 'puntos' => $c->puntos,
        ]);

        return Mapeador::cliente($m->fresh());
    }

    public function eliminar(int $id): void
    {
        try {
            ClienteModel::whereKey($id)->delete();
        } catch (QueryException) {
            throw new ReglaNegocioException('No se puede eliminar: el cliente tiene ventas registradas.');
        }
    }
}
