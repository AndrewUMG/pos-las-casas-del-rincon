@extends('layouts.app')
@section('titulo', 'Reportes')

@section('content')
<h1 class="h3 titulo-pagina mb-3">Reportes</h1>
@php($iconos = ['corte-caja' => 'bi-cash-stack', 'devoluciones' => 'bi-arrow-return-left', 'clientes' => 'bi-people', 'compras' => 'bi-cart-check'])
<div class="row g-3">
    @foreach ($titulos as $tipo => $titulo)
        <div class="col-md-6">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2"><span class="kpi-icono"><i class="bi {{ $iconos[$tipo] }}"></i></span><h2 class="h5 mb-0">{{ $titulo }}</h2></div>
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
