@extends('layouts.app')

@section('title', 'Registro de Checklist Diario')

@section('content')
<div class="container py-4">
  <h2 class="h4 fw-bold mb-4">Registro de Checklist Diario</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Ups...</strong> Hay errores en el formulario:
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('checklist.epp.store') }}">
    @csrf

    <!-- Trabajador -->
    <div class="mb-3">
      <label for="trabajador_id" class="form-label">Trabajador</label>
      <select name="trabajador_id" id="trabajador_id" class="form-select @error('trabajador_id') is-invalid @enderror" required>
        <option value="">Seleccionar trabajador...</option>
        @foreach($trabajadores as $t)
          <option value="{{ $t->id }}" {{ old('trabajador_id') == $t->id ? 'selected' : '' }}>
            {{ $t->name }} ({{ $t->email }})
          </option>
        @endforeach
      </select>
      @error('trabajador_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <!-- Trabajo en altura -->
    <div class="mb-3 form-check">
      <input type="checkbox" name="es_en_altura" id="es_en_altura" class="form-check-input" value="1" {{ old('es_en_altura') ? 'checked' : '' }}>
      <label for="es_en_altura" class="form-check-label">¿Trabaja en altura hoy?</label>
    </div>

    <!-- EPP -->
    <div class="row g-3">
      @foreach(['anteojos', 'botas', 'chaleco', 'guantes', 'arnes'] as $epp)
      <div class="col-md-2">
        <div class="form-check">
          <input type="checkbox" name="{{ $epp }}" id="{{ $epp }}" class="form-check-input" value="1" {{ old($epp) ? 'checked' : '' }}>
          <label for="{{ $epp }}" class="form-check-label text-capitalize">{{ $epp }}</label>
        </div>
        @error($epp)
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
      @endforeach
    </div>

    <!-- Observaciones -->
    <div class="mt-3">
      <label for="observaciones" class="form-label">Observaciones</label>
      <textarea name="observaciones" id="observaciones" class="form-control">{{ old('observaciones') }}</textarea>
      @error('observaciones')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>

    <!-- Botón -->
    <div class="mt-4 text-end">
      <button type="submit" class="btn btn-primary">Registrar Checklist</button>
    </div>
  </form>
</div>
@endsection
