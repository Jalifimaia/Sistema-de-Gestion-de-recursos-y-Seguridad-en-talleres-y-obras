@extends('layouts.app')

@section('title', 'Registrar préstamo')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-orange text-white text-center">
      <h4 class="mb-0 fw-bold">Registrar préstamo</h4>
    </div>
    <div class="card-body bg-white">

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('prestamos.store') }}">
        @csrf

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Fecha de Préstamo</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::today()->format('d/m/Y') }}" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Fecha de Devolución</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::tomorrow()->format('d/m/Y') }}" disabled>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="id_trabajador" class="form-label">Trabajador</label>
            <div class="d-flex gap-2">
              <select id="id_trabajador" name="id_trabajador_select" class="form-select" required>
                <option value="" selected disabled>Seleccione un trabajador</option>
                @foreach($trabajadores as $t)
                  <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
              </select>

              {{-- Hidden que realmente se enviará al servidor --}}
              <input type="hidden" id="id_trabajador_hidden" name="id_trabajador" value="">
            </div>
          </div>
        </div>

        <input type="hidden" name="estado" value="2">

        <div class="row mb-3">
          <div class="col-md-4">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" class="form-select" required>
              <option selected disabled>Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre_categoria }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" class="form-select" required>
              <option selected disabled>Seleccione una subcategoría</option>
            </select>
          </div>

          <div class="col-md-4">
            <label for="recurso" class="form-label">Recurso</label>
            <select id="recurso" class="form-select" required>
              <option selected disabled>Seleccione un recurso</option>
            </select>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-10">
            <label for="serie" class="form-label">Serie del Recurso</label>
            <select id="serie" class="form-select" required>
              <option selected disabled>Seleccione una serie</option>
            </select>
          </div>
          <div class="col-md-2 text-end">
            <button type="button" class="btn btn-success w-100 mt-4" id="agregar">Agregar</button>
          </div>
        </div>

        <h5 style="display:none" class="mb-3">Recursos seleccionados</h5>

        <div style="display:none" id="contenedorSeries" class="row g-3">
          {{-- tarjetas dinámicas creadas por JS con hidden name="series[]" --}}
        </div>

        <div class="row mt-4">
          <div class="col-md-6">
            <a href="{{ route('prestamos.index') }}" class="btn btn-volver w-100 d-inline-flex align-items-center justify-content-center">
              <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
              Volver
            </a>
          </div>
          <div class="col-md-6">
            <button style="display:none" type="submit" class="btn btn-guardar w-100">Guardar Préstamo</button>
          </div>
        </div>

      </form>
    </div>
  </div>

{{-- Modal recurso agregado --}}
@if(session('success'))
<div class="modal fade" id="modalRecursoAgregado" tabindex="-1" aria-labelledby="modalRecursoAgregadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoAgregadoLabel">Recurso agregado</h5>
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


<div class="modal fade" id="modalSerieInvalida" tabindex="-1" aria-labelledby="modalSerieInvalidaLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark py-2">
        <h6 class="modal-title" id="modalSerieInvalidaLabel">Serie inválida</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        Seleccioná una serie válida antes de continuar.
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-primary w-100" data-bs-dismiss="modal">Entendido</button>
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

  {{-- Script para sincronizar select -> hidden y mostrar modal de éxito (si aplica) --}}
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
  });
  </script>
@endpush


@push('styles')
<link href="{{ asset('css/agregarPrestamo.css') }}" rel="stylesheet">
@endpush

