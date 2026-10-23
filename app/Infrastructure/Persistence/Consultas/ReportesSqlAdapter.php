<?php

namespace App\Infrastructure\Persistence\Consultas;

use App\Application\Reportes\Ports\Out\ReportesQueryPort;
use App\Domain\Devoluciones\MotivoDevolucion;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de solo lectura para los reportes (Query Builder sobre PostgreSQL).
 */
class ReportesSqlAdapter implements ReportesQueryPort
{
    /** Cierre del día: cajas del período con sus ventas por método de pago y el corte. */
    public function corteCaja(string $desde, string $hasta): array
    {
        $mov = DB::table('movimientos_caja')
            ->selectRaw("caja_id,
                sum(case when tipo='ingreso' and metodo_pago='efectivo' then monto else 0 end) as efectivo,
                sum(case when tipo='ingreso' and metodo_pago='credito' then monto else 0 end) as credito,
                sum(case when tipo='ingreso' and metodo_pago='debito' then monto else 0 end) as debito,
                sum(case when tipo='egreso' then monto else 0 end) as egresos,
                sum(case when tipo='ingreso' then 1 else 0 end) as num_ventas")
            ->groupBy('caja_id');

        $cajas = DB::table('cajas')
            ->join('usuarios', 'usuarios.id', '=', 'cajas.usuario_id')
            ->leftJoinSub($mov, 'm', 'm.caja_id', '=', 'cajas.id')
            ->whereRaw('cajas.fecha_apertura::date between ? and ?', [$desde, $hasta])
            ->orderBy('cajas.fecha_apertura')
            ->select('cajas.*', 'usuarios.nombre as cajero', DB::raw('coalesce(m.efectivo,0) as efectivo'),
                DB::raw('coalesce(m.credito,0) as credito'), DB::raw('coalesce(m.debito,0) as debito'),
                DB::raw('coalesce(m.egresos,0) as egresos'), DB::raw('coalesce(m.num_ventas,0) as num_ventas'))
            ->get()->map(fn ($c) => [
                'id' => $c->id, 'cajero' => $c->cajero,
                'apertura' => date('d/m/Y H:i', strtotime($c->fecha_apertura)),
                'cierre' => $c->fecha_cierre ? date('d/m/Y H:i', strtotime($c->fecha_cierre)) : null,
                'estado' => $c->estado, 'monto_inicial' => (float) $c->monto_inicial,
                'efectivo' => (float) $c->efectivo, 'credito' => (float) $c->credito, 'debito' => (float) $c->debito,
                'ventas' => round($c->efectivo + $c->credito + $c->debito, 2), 'egresos' => (float) $c->egresos,
                'num_ventas' => (int) $c->num_ventas,
                'esperado' => $c->monto_esperado !== null ? (float) $c->monto_esperado
                    : round($c->monto_inicial + $c->efectivo - $c->egresos, 2),
                'contado' => $c->monto_contado !== null ? (float) $c->monto_contado : null,
                'diferencia' => $c->diferencia !== null ? (float) $c->diferencia : null,
            ])->all();

        $totales = ['efectivo' => 0, 'credito' => 0, 'debito' => 0, 'ventas' => 0, 'egresos' => 0, 'num_ventas' => 0];
        foreach ($cajas as $c) {
            foreach ($totales as $k => $_) {
                $totales[$k] += $c[$k];
            }
        }

        return ['cajas' => $cajas, 'totales' => array_map(fn ($v) => is_float($v) ? round($v, 2) : $v, $totales)];
    }

    public function devoluciones(string $desde, string $hasta): array
    {
        $filas = DB::table('devoluciones as d')
            ->join('facturas as f', 'f.id', '=', 'd.factura_id')
            ->join('usuarios as u', 'u.id', '=', 'd.usuario_id')
            ->whereRaw('d.fecha::date between ? and ?', [$desde, $hasta])
            ->orderBy('d.fecha')
            ->select('d.id', 'd.fecha', 'd.motivo', 'd.total', 'd.observacion', 'f.serie', 'f.numero', 'f.nombre as cliente', 'u.nombre as usuario')
            ->get();

        $detalle = DB::table('detalle_devoluciones as dd')->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->whereIn('dd.devolucion_id', $filas->pluck('id'))
            ->select('dd.devolucion_id', 'p.nombre', 'dd.cantidad')->get()->groupBy('devolucion_id');

        $lista = $filas->map(fn ($d) => [
            'id' => $d->id, 'fecha' => date('d/m/Y H:i', strtotime($d->fecha)),
            'factura' => $d->serie.'-'.str_pad((string) $d->numero, 6, '0', STR_PAD_LEFT), 'cliente' => $d->cliente,
            'motivo' => MotivoDevolucion::from($d->motivo)->etiqueta(), 'usuario' => $d->usuario,
            'productos' => ($detalle[$d->id] ?? collect())->map(fn ($x) => "{$x->cantidad} × {$x->nombre}")->implode(', '),
            'observacion' => $d->observacion, 'total' => (float) $d->total,
        ])->all();

        $porMotivo = [];
        foreach ($lista as $d) {
            $porMotivo[$d['motivo']] = round(($porMotivo[$d['motivo']] ?? 0) + $d['total'], 2);
        }

        return ['devoluciones' => $lista, 'por_motivo' => $porMotivo, 'total' => round(array_sum(array_column($lista, 'total')), 2)];
    }

    public function clientes(string $desde, string $hasta): array
    {
        $compras = DB::table('facturas')
            ->whereRaw('fecha::date between ? and ?', [$desde, $hasta])
            ->selectRaw('nit, count(*) as facturas, sum(total) as total, max(fecha) as ultima')->groupBy('nit');

        $clientes = DB::table('clientes as c')
            ->leftJoinSub($compras, 'f', 'f.nit', '=', 'c.nit')
            ->orderByRaw('coalesce(f.total,0) desc')->orderBy('c.nombre')
            ->select('c.*', DB::raw('coalesce(f.facturas,0) as facturas'), DB::raw('coalesce(f.total,0) as total'), 'f.ultima')
            ->get()->map(fn ($c) => [
                'nit' => $c->nit, 'nombre' => $c->nombre, 'telefono' => $c->telefono, 'email' => $c->email,
                'puntos' => (int) $c->puntos, 'facturas' => (int) $c->facturas, 'total' => (float) $c->total,
                'ultima' => $c->ultima ? date('d/m/Y', strtotime($c->ultima)) : null,
            ])->all();

        return ['clientes' => $clientes, 'total' => round(array_sum(array_column($clientes, 'total')), 2),
            'con_compras' => count(array_filter($clientes, fn ($c) => $c['facturas'] > 0))];
    }

    public function compras(string $desde, string $hasta): array
    {
        $compras = DB::table('compras as c')
            ->join('proveedores as p', 'p.id', '=', 'c.proveedor_id')
            ->whereBetween('c.fecha', [$desde, $hasta])->orderBy('c.fecha')
            ->select('c.id', 'c.fecha', 'c.numero_documento', 'c.total', 'p.nombre as proveedor')->get()
            ->map(fn ($c) => ['id' => $c->id, 'fecha' => date('d/m/Y', strtotime($c->fecha)), 'documento' => $c->numero_documento,
                'proveedor' => $c->proveedor, 'total' => (float) $c->total])->all();

        $productos = DB::table('detalle_compras as d')
            ->join('compras as c', 'c.id', '=', 'd.compra_id')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->join('familias as f', 'f.id', '=', 'p.familia_id')
            ->whereBetween('c.fecha', [$desde, $hasta])
            ->groupBy('p.id', 'p.codigo', 'p.nombre', 'f.nombre', 'p.existencia')
            ->orderBy('f.nombre')->orderBy('p.nombre')
            ->selectRaw('p.codigo, p.nombre, f.nombre as familia, p.existencia, sum(d.cantidad) as cantidad,
                sum(d.subtotal) as total, sum(d.subtotal)/sum(d.cantidad) as costo_promedio')
            ->get()->map(fn ($r) => ['codigo' => $r->codigo, 'nombre' => $r->nombre, 'familia' => $r->familia,
                'cantidad' => (int) $r->cantidad, 'costo_promedio' => round((float) $r->costo_promedio, 2),
                'total' => (float) $r->total, 'existencia' => (int) $r->existencia])->all();

        return ['compras' => $compras, 'productos' => $productos, 'total' => round(array_sum(array_column($compras, 'total')), 2)];
    }
}
