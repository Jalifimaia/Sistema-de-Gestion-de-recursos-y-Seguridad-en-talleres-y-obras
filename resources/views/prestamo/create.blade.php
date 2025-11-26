@extends('layouts.app')

@section('title', 'Registrar préstamo')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-reg text-white text-center">
      <h4 class="mb-0">Registrar préstamo</h4>
    </div>
    <div class="card-body bg-white">

      <form method="POST" action="{{ route('prestamos.store') }}">
        @csrf

        <!-- Fila 1: Fechas -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Fecha de préstamo</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::today()->format('d/m/Y') }}" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Fecha de devolución</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::tomorrow()->format('d/m/Y') }}" disabled>
          </div>
        </div>

        <!-- Fila 2: Categoría y Subcategoría -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" class="form-select" required>
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre_categoria }}</option>
              @endforeach
            </select>
            <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
          </div>
          <div class="col-md-6">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" class="form-select" required>
              <option value="" selected disabled>Seleccione una subcategoría</option>
            </select>
            <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
          </div>
        </div>

        <!-- Fila 3: Recurso y Serie -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="recurso" class="form-label">Recurso</label>
            <select id="recurso" class="form-select" required>
              <option value="" selected disabled>Seleccione un recurso</option>
            </select>
            <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
          </div>
          <div class="col-md-6">
            <label for="serie" class="form-label">Serie del recurso</label>
            <select id="serie" class="form-select" required>
              <option value="" selected disabled>Seleccione una serie</option>
            </select>
            <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
          </div>
        </div>

        <!-- Fila 4: Trabajador -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="id_trabajador" class="form-label">Trabajador</label>
            <select id="id_trabajador" name="id_trabajador_select" class="form-select" required>
              <option value="" selected disabled>Seleccione al trabajador</option>
              @foreach($trabajadores as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
              @endforeach
            </select>
            <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
            <input type="hidden" id="id_trabajador_hidden" name="id_trabajador" value="">
          </div>
        </div>

        <h5 style="display:none" class="mb-3">Recursos seleccionados</h5>
        <div style="display:none" id="contenedorSeries" class="row g-3">
          {{-- tarjetas dinámicas creadas por JS con hidden name="series[]" --}}
        </div>

        <!-- Fila 5: Botones Volver, Agregar y Guardar -->
        <div class="row mt-4 align-items-end">
          <!-- Volver a la izquierda -->
          <div class="col-md-6 d-flex justify-content-start">
            <a href="{{ route('prestamos.index') }}" class="btn btn-volver d-inline-flex align-items-center">
              <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
              Volver
            </a>
          </div>

          <!-- Agregar y Guardar al extremo derecho -->
          <div class="col-md-6 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-ag" id="agregar">Guardar préstamo</button>
            <button style="display:none" type="submit" class="btn btn-guardar">Guardar préstamo</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>



{{-- Modal recurso agregado --}}
@if(session('success'))
<div class="modal fade" id="modalRecursoAgregado" tabindex="-1" aria-labelledby="modalRecursoAgregadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoAgregadoLabel">Prestamo registrado correctamente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btnAceptarModal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
@endif


{{-- Modal recurso vencido --}}
@if(session('error'))
<div class="modal fade" id="modalRecursoVencido" tabindex="-1" aria-labelledby="modalRecursoVencidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalRecursoVencidoLabel">No se pudo registrar el préstamo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('error') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
@endif



<div class="modal fade" id="modalSerieInvalida" tabindex="-1" aria-labelledby="modalSerieInvalidaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalSerieInvalidaLabel">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Seleccioná una serie válida antes de continuar.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalErrorCampos" tabindex="-1" aria-labelledby="modalErrorCamposLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErrorCamposLabel">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Faltan campos por completar. Por favor, revisá el formulario.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modalConfirmarCambioTrabajador" tabindex="-1" aria-labelledby="modalConfirmarCambioTrabajadorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="modalConfirmarCambioTrabajadorLabel">Confirmar cambio de trabajador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Al cambiar al trabajador eliminará los recursos agregados. ¿Desea continuar?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-action="cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" data-action="confirm">Sí, cambiar</button>
      </div>
    </div>
  </div>

  {{-- Modal préstamo guardado --}}
  @if(session('success'))
  <div class="modal fade" id="modalPrestamoGuardado" tabindex="-1" aria-labelledby="modalPrestamoGuardadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalPrestamoGuardadoLabel">🎉 Préstamo guardado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          {{ session('success') }}
        </div>
        <div class="modal-footer">
          <a href="{{ route('prestamos.index') }}" class="btn btn-outline-primary">Ver préstamos</a>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
  @endif

</div>
@endsection
@push('scripts')
  {{-- Incluimos el script principal --}}
  <script src="{{ asset('js/prestamo.js') }}"></script>

  {{-- Script para sincronizar select -> hidden y mostrar modales de éxito/error --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const trabajadorSelect = document.getElementById('id_trabajador');
    const trabajadorHidden = document.getElementById('id_trabajador_hidden');
    const agregarBtn = document.getElementById('agregar');

    function syncAndEnable() {
      if (!trabajadorSelect || !trabajadorHidden) return;
      trabajadorHidden.value = trabajadorSelect.value || '';
      // Disparamos change en el hidden por compatibilidad con prestamo.js
      trabajadorHidden.dispatchEvent(new Event('change', { bubbles: true }));
      // Forzamos re-evaluación directa y habilitamos el botón si corresponde
      if (agregarBtn) {
        agregarBtn.disabled = !(trabajadorHidden.value && trabajadorHidden.value !== '');
      }
    }

    if (trabajadorSelect && trabajadorHidden) {
      trabajadorSelect.addEventListener('change', () => {
        syncAndEnable();
      });
      syncAndEnable();
    }

    // Mostrar modal de éxito si viene en session
    @if(session('success'))
      const modalPrestamo = document.getElementById('modalRecursoAgregado');
      if (modalPrestamo && typeof bootstrap !== 'undefined') {
        const inst = new bootstrap.Modal(modalPrestamo);
        inst.show();

        // Redirigir al index al aceptar o cerrar
        const btnAceptar = modalPrestamo.querySelector('#btnAceptarModal');
        if (btnAceptar) {
          btnAceptar.addEventListener('click', () => {
            window.location.href = "{{ route('prestamos.index') }}";
          });
        }
        modalPrestamo.addEventListener('hidden.bs.modal', () => {
          window.location.href = "{{ route('prestamos.index') }}";
        }, { once: true });
      } else if ('{{ session("success") }}') {
        alert('{{ session("success") }}');
        window.location.href = "{{ route('prestamos.index') }}";
      }
    @endif

    // Mostrar modal de error si viene en session
    @if(session('error'))
      const modalError = document.getElementById('modalRecursoVencido');
      if (modalError && typeof bootstrap !== 'undefined') {
        const instError = new bootstrap.Modal(modalError);
        instError.show();

        const btnAceptarError = modalError.querySelector('#btnAceptarErrorModal');
        if (btnAceptarError) {
          btnAceptarError.addEventListener('click', () => {
            window.location.href = "{{ route('prestamos.index') }}";
          });
        }
        modalError.addEventListener('hidden.bs.modal', () => {
          window.location.href = "{{ route('prestamos.index') }}";
        }, { once: true });
      } else if ('{{ session("error") }}') {
        alert('{{ session("error") }}');
        window.location.href = "{{ route('prestamos.index') }}";
      }
    @endif

  });
  </script>
@endpush



@push('styles')
<link href="{{ asset('css/agregarPrestamo.css') }}" rel="stylesheet">

<style>
 label::after {
 content: " *";
 color: red;
}

label.no-asterisk::after {
 content: ""; /* anula el asterisco */
}
</style>
@endpush