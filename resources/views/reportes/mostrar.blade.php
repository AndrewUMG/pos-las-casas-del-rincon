@extends('layouts.app')
@section('titulo', $titulo)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 titulo-pagina mb-0">{{ $titulo }}</h1>
    <form method="get" class="d-flex gap-2 align-items-center d-print-none">
        <input type="date" name="desde" value="{{ $desde }}" class="form-control">
        <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
        <button class="btn btn-pos">Filtrar</button>
        <button name="formato" value="pdf" formtarget="_blank" class="btn btn-outline-secondary text-nowrap"><i class="bi bi-filetype-pdf"></i> PDF</button>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">Volver</a>
    </form>
</div>
<div class="card"><div class="card-body table-responsive">
    @include('reportes._contenido')
</div></div>
@endsection
