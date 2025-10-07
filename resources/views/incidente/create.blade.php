@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar nuevo incidente</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('incidente.store') }}" method="POST">
        @csrf

        <!-- Recurso afectado -->
        <div class="mb-3">
            <label for="id_recurso" class="form-label">Recurso</label>
            <select name="id_recurso" id="id_recurso" class="form-select" required>
                @foreach($recursos as $recurso)
                    <option value="{{ $recurso->id }}">{{ $recurso->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Estado del incidente -->
        <div class="mb-3">
            <label for="id_estado_incidente" class="form-label">Estado del incidente</label>
            <select name="id_estado_incidente" id="id_estado_incidente" class="form-select" required>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                @endforeach
            </select>
        </div>

        <!-- Descripción general del incidente -->
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción general</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <!-- Fecha del incidente -->
        <div class="mb-3">
            <label for="fecha_incidente" class="form-label">Fecha del incidente</label>
            <input type="datetime-local" name="fecha_incidente" id="fecha_incidente" class="form-control" required>
        </div>

        <!-- Resolución (opcional) -->
        <div class="mb-3">
            <label for="resolucion" class="form-label">Resolución</label>
            <input type="text" name="resolucion" id="resolucion" class="form-control">
        </div>

        <!-- Detalle del incidente -->
        <div class="mb-3">
            <label for="detalle_descripcion" class="form-label">Detalle del incidente</label>
            <textarea name="detalle_descripcion" id="detalle_descripcion" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Registrar incidente</button>
    </form>
</div>
@endsection
