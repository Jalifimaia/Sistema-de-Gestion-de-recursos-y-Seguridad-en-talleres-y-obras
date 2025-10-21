@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-start mb-3">
  <a href="{{ route('incidente.index') }}" class="btn btn-outline-secondary">
    ⬅️ Volver
  </a>
</div>

<div class="container">
    <h2>Editar incidente</h2>

    <form method="POST" action="{{ route('incidente.update', $incidente->id) }}">
        @csrf
        @method('PUT')

        <!-- 🧍 Trabajador -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Datos del Trabajador</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Trabajador</label>
                        <input type="text" class="form-control" 
                               value="{{ $incidente->trabajador?->name }}" readonly>
                        <input type="hidden" name="id_trabajador" value="{{ $incidente->trabajador?->id }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 🧰 Recursos asociados -->
        <div id="recursos-container">
            @foreach($incidente->recursos as $i => $recurso)
            <div class="card mb-3 recurso-block">
                <div class="card-header bg-success text-white">Recurso asociado</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Categoría</label>
                            <input type="text" class="form-control" 
                                   value="{{ $recurso->subcategoria->categoria->nombre_categoria }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_categoria]" 
                                   value="{{ $recurso->subcategoria->categoria->id }}">
                        </div>
                        <div class="col-md-3">
                            <label>Subcategoría</label>
                            <input type="text" class="form-control" 
                                   value="{{ $recurso->subcategoria->nombre }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_subcategoria]" 
                                   value="{{ $recurso->subcategoria->id }}">
                        </div>
                        <div class="col-md-3">
                            <label>Recurso</label>
                            <input type="text" class="form-control" 
                                   value="{{ $recurso->nombre }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_recurso]" 
                                   value="{{ $recurso->id }}">
                        </div>
                        <div class="col-md-3">
                            <label>Serie del recurso</label>
                            <input type="text" class="form-control" 
                                   value="{{ $recurso->serieRecursos->firstWhere('id', $recurso->pivot->id_serie_recurso)?->nro_serie }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_serie_recurso]" 
                                   value="{{ $recurso->pivot->id_serie_recurso }}">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Estado del incidente -->
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
