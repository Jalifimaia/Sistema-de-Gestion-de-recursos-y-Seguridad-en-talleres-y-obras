@extends('layouts.app')

@section('template_title')
    Agregar Serie a {{ $recurso->nombre }}
@endsection

@section('content')
<div class="container">
    <h3>Agregar Serie para: {{ $recurso->nombre }}</h3>

    <form method="POST" action="{{ route('serie_recurso.store', $recurso->id) }}">
        @csrf

        <div class="mb-3">
            <label for="nro_serie" class="form-label">Número de Serie</label>
            <input type="text" name="nro_serie" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="talle" class="form-label">Talle (opcional)</label>
            <input type="text" name="talle" class="form-control">
        </div>

        <div class="mb-3">
            <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
            <input type="date" name="fecha_adquisicion" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Guardar Serie</button>
        <a href="{{ route('recursos.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
