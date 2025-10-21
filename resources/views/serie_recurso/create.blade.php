@extends('layouts.app')

@section('template_title')
    Agregar Serie a {{ $recurso->nombre }}
@endsection

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Agregar Serie para: {{ $recurso->nombre }}</h3>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('serie_recurso.storeMultiple') }}">
        @csrf

        <!-- Campo oculto para enviar el id del recurso -->
        <input type="hidden" name="id_recurso" value="{{ $recurso->id }}">

        <div class="mb-3">
            <label for="nro_serie" class="form-label">Prefijo de Serie</label>
            <input type="text" name="nro_serie" id="nro_serie" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad de series</label>
            <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
        </div>

        <div id="campoTalle" class="mb-3">
            <label for="talle" class="form-label">Talle</label>
            <input type="text" name="talle" id="talle" class="form-control" placeholder="Ej: M, L, XL">
        </div>

        <div class="mb-3">
            <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
            <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
        </div>

        <div class="mb-3">
            <label for="id_estado" class="form-label">Estado</label>
            <select name="id_estado" id="id_estado" class="form-select">
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Guardar Serie</button>
            <a href="{{ route('inventario') }}" class="btn btn-secondary">Volver</a>
        </div>
    </form>
</div>

<!-- Modal: serie guardada -->
@if(session('success'))
<div class="modal fade" id="modalSerieGuardada" tabindex="-1" aria-labelledby="modalSerieGuardadaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalSerieGuardadaLabel">Serie guardada</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <a href="{{ route('inventario') }}" class="btn btn-outline-success">Volver al inventario</a>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Mostrar modal de éxito si existe en DOM
  const modalEl = document.getElementById('modalSerieGuardada');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    new bootstrap.Modal(modalEl).show();
  }

  // (Opcional) ejemplo sencillo para ocultar campo talle según categoría
  try {
    const categoriaRecurso = "{{ strtolower($recurso->categoria->nombre_categoria ?? '') }}";
    const campoTalle = document.getElementById('campoTalle');
    if (categoriaRecurso && !['epp','ropa','vestimenta'].includes(categoriaRecurso)) {
      // ocultar talle para categorías que no aplican
      campoTalle.style.display = 'none';
    }
  } catch(e) {
    console.debug('No se aplicó lógica de talle:', e);
  }
});
</script>
@endpush
