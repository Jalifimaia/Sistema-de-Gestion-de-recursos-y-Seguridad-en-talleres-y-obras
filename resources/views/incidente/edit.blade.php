@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar incidente</h2>

    <form method="POST" action="{{ route('incidente.update', $incidente->id) }}">
        @csrf
        @method('PUT')

        <!-- Categoría (solo lectura) -->
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <input type="text" class="form-control" 
                   value="{{ $incidente->recurso->subcategoria->categoria->nombre_categoria ?? '' }}" 
                   readonly>
        </div>

        <!-- Subcategoría (solo lectura) -->
        <div class="mb-3">
            <label class="form-label">Subcategoría</label>
            <input type="text" class="form-control" 
                   value="{{ $incidente->recurso->subcategoria->nombre ?? '' }}" 
                   readonly>
        </div>

        <!-- Recurso (solo lectura) -->
        <div class="mb-3">
            <label class="form-label">Recurso</label>
            <input type="text" class="form-control" 
                   value="{{ $incidente->recurso->nombre ?? '' }}" 
                   readonly>
        </div>

        
    
        <!-- Estado del incidente (editable) -->
        <div class="mb-3">
            <label for="id_estado_incidente" class="form-label">Estado del incidente</label>
            <select name="id_estado_incidente" id="id_estado_incidente" class="form-select" required>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}" 
                        {{ $incidente->id_estado_incidente == $estado->id ? 'selected' : '' }}>
                        {{ $estado->nombre_estado }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Motivo / Descripción -->
        <div class="mb-3">
            <label for="descripcion" class="form-label">Motivo del incidente</label>
            <input type="text" name="descripcion" id="descripcion" class="form-control" 
                   value="{{ $incidente->descripcion }}" required>
        </div>

        <!-- Resolución -->
        <div class="mb-3">
            <label for="resolucion" class="form-label">Resolución</label>
            <input type="text" name="resolucion" id="resolucion" class="form-control" 
                   value="{{ $incidente->resolucion }}">
        </div>

        <!-- Fecha del incidente -->
        <div class="mb-3">
            <label for="fecha_incidente" class="form-label">Fecha del incidente</label>
            <input type="datetime-local" name="fecha_incidente" id="fecha_incidente" 
                   class="form-control" 
                   value="{{ \Carbon\Carbon::parse($incidente->fecha_incidente)->format('Y-m-d\TH:i') }}" 
                   required>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Actualizar incidente</button>
            <a href="{{ route('incidente.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@if(session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif

@endsection