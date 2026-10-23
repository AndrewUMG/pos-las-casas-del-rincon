@extends('layouts.app')
@section('titulo', 'Reportes')

@section('content')
<h1 class="h3 titulo-pagina mb-3"><i class="bi bi-bar-chart"></i> Reportes</h1>
@php($iconos = ['corte-caja' => 'bi-cash-stack', 'devoluciones' => 'bi-arrow-return-left', 'clientes' => 'bi-people', 'compras' => 'bi-cart-check'])
<div class="row g-3">
    @foreach ($titulos as $tipo => $titulo)
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <h2 class="h5"><i class="bi {{ $iconos[$tipo] }} text-pos"></i> {{ $titulo }}</h2>
                <form method="get" action="{{ route('reportes.mostrar', $tipo) }}" class="row g-2 align-items-end mt-1">
                    <div class="col-sm-4"><label class="form-label small">Desde</label>
                        <input type="date" name="desde" class="form-control" value="{{ $tipo === 'corte-caja' ? now()->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d') }}"></div>
                    <div class="col-sm-4"><label class="form-label small">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                    <div class="col-sm-4 d-flex gap-1">
                        <button class="btn btn-pos flex-fill">Ver</button>
                        <button name="formato" value="pdf" formtarget="_blank" class="btn btn-outline-secondary" title="PDF"><i class="bi bi-filetype-pdf"></i></button>
                    </div>
                </form>
            </div></div>
        </div>
    @endforeach
</div>
@endsection
