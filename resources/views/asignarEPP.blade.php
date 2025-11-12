@extends('layouts.app')

@section('template_title')
    Asignar EPP
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('controlEPP') }}" class="btn btn-volver d-inline-flex align-items-center">
            <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
            Volver
            </a>

            <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
            <img src="{{ asset('images/workerepp.svg') }}" alt="EPP" class="me-2 icono-volver">
            Asignación de EPP
            </h4>
        </div>
    </div>


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

   <form method="POST" action="{{ route('epp.asignar.store') }}">
  @csrf

  <!-- Card: Filtros de trabajador -->
  <div class="card mb-4">
    <div class="card-header bg-orange-soft">
      <h5 class="mb-0 fw-bold">Filtros de trabajador</h5>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <label for="estado_filtro" class="form-label">Estado</label>
        <select id="estado_filtro" class="form-select">
          <option value="" selected disabled>-- Filtrar por estado --</option>
          <option value="alta">Trabajadores en alta</option>
          <option value="standby">Trabajadores en stand by</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="usuario_id" class="form-label">Trabajador</label>
        <select name="usuario_id" id="usuario_id" class="form-select select2" required>
          <option value="">-- Seleccionar trabajador --</option>
          @foreach ($usuarios as $usuario)
            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
          @endforeach
        </select>
      </div>

      <div id="epp-asignado" class="alert alert-info d-none">
        <strong>EPP ya asignado:</strong>
        <ul id="epp-lista" class="mb-0"></ul>
      </div>
    </div>
  </div>

  <!-- Card: Asignación de EPP -->
  <div class="card mb-4">
    <div class="card-header bg-yellow-soft">
      <h5 class="mb-0 fw-bold">Asignación de elementos de protección</h5>
    </div>
    <div class="card-body">
      @foreach (['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'] as $tipo)
        <div class="mb-3">
          <label for="{{ $tipo }}" class="form-label">{{ ucfirst($tipo) }}</label>
          <select name="{{ $tipo }}" id="{{ $tipo }}" class="form-select select2-epp" data-tipo="{{ $tipo }}">
            <option value="">-- Seleccionar {{ $tipo }} disponible --</option>
          </select>
          <div class="text-danger small d-none" id="alert-{{ $tipo }}">Ya tiene {{ $tipo }} asignado</div>
        </div>
      @endforeach

      <div class="mb-3">
        <label for="fecha_asignacion" class="form-label">Fecha de asignación</label>
        <div class="input-group" onclick="this.querySelector('input').showPicker()" tabindex="0" role="button" onkeydown="if(event.key==='Enter'||event.key===' ') this.querySelector('input').showPicker()">
          <input type="date" name="fecha_asignacion" id="fecha_asignacion" class="form-control" required value="{{ now()->toDateString() }}">
        </div>
      </div>
    </div>
  </div>

  <!-- Botones -->
  <div class="text-center mt-4">
    <input type="hidden" name="todos_asignados" id="todos_asignados" value="0">
    <button type="submit" id="submitBtn" class="btn btn-guardar w-100 mb-5">
      Guardar asignación
    </button>
  </div>


</form>

</div>

<!-- Modal: EPP ya asignado -->
<div class="modal fade" id="modalEppCompleto" tabindex="-1" aria-labelledby="modalEppCompletoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="modalEppCompletoLabel">Asignación no permitida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Este trabajador ya tiene todos los EPP asignados. No es necesario asignar más.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('css/asignarEpp.css') }}" rel="stylesheet">
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectEstado = document.getElementById('estado_filtro');
    const selectTrabajador = document.getElementById('usuario_id');
    const eppBox = document.getElementById('epp-asignado');
    const eppList = document.getElementById('epp-lista');
    const tipos = ['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'];
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const hiddenTodos = document.getElementById('todos_asignados');

    function actualizarEstadoBoton() {
        const todosAsignados = tipos.every(tipo => {
            const select = document.getElementById(tipo);
            return select && select.disabled;
        });
        // marcar hidden para backend si está todo asignado
        hiddenTodos.value = todosAsignados ? '1' : '0';
        // deshabilitar botón visualmente para evitar intento de submit
        if (submitBtn) submitBtn.disabled = todosAsignados;
    }

    function cargarEPP(userId) {
        if (!userId) {
            // reset
            eppList.innerHTML = '';
            eppBox.classList.add('d-none');
            tipos.forEach(tipo => {
                const select = document.getElementById(tipo);
                if (select) {
                    select.innerHTML = `<option value="">-- Seleccionar ${tipo} disponible --</option>`;
                    select.disabled = false;
                }
                const alerta = document.getElementById('alert-' + tipo);
                if (alerta) alerta.classList.add('d-none');
            });
            actualizarEstadoBoton();
            return;
        }

        fetch(`/epp/asignados/${userId}`)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data)) {
                    //  resetear si no viene array
                    cargarEPP(null);
                    return;
                }

                const asignados = data.map(epp => (epp.tipo || '').toLowerCase());
                eppList.innerHTML = data.length
                    ? data.map(epp => `<li>${epp.tipo}: ${epp.serie}</li>`).join('')
                    : '<li>No tiene EPP asignado</li>';
                eppBox.classList.remove('d-none');

                tipos.forEach(tipo => {
                    const select = document.getElementById(tipo);
                    const alerta = document.getElementById('alert-' + tipo);

                    if (asignados.includes(tipo)) {
                        if (alerta) alerta.classList.remove('d-none');
                        if (select) select.disabled = true;
                    } else {
                        if (alerta) alerta.classList.add('d-none');
                        if (select) select.disabled = false;
                    }

                    // cargar opciones disponibles (si querés evitar cargas innecesarias podés condicionar)
                    if (select && !select.disabled) {
                        fetch(`/epp/disponibles/${tipo}`)
                            .then(res => res.json())
                            .then(opciones => {
                                select.innerHTML = `<option value="">-- Seleccionar ${tipo} disponible --</option>`;
                                opciones.forEach(epp => {
                                    select.innerHTML += `<option value="${epp.id}">${epp.serie}</option>`;
                                });
                            });
                    } else if (select) {
                        // si está deshabilitado, limpiamos opciones para evitar que se envíen valores
                        select.innerHTML = `<option value="">-- ${tipo} asignado --</option>`;
                    }
                });

                actualizarEstadoBoton();
            })
            .catch(() => {
                cargarEPP(null);
            });
    }

    selectTrabajador.addEventListener('change', function () {
        cargarEPP(this.value);
    });

    selectEstado.addEventListener('change', function () {
        const estado = this.value;
        if (!estado) return;

        fetch(`/trabajadores/por-estado/${estado}`)
            .then(res => res.json())
            .then(data => {
                selectTrabajador.innerHTML = '<option value="">-- Seleccionar trabajador --</option>';
                data.forEach(user => {
                    selectTrabajador.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                });

                // Resetear EPP (no seleccionar trabajador aún)
                cargarEPP(null);
            });
    });

    // Interceptar submit por seguridad (aun con botón deshabilitado)
    form.addEventListener('submit', function (e) {
        const todosAsignados = tipos.every(tipo => {
            const select = document.getElementById(tipo);
            return select && select.disabled;
        });

        if (todosAsignados) {
            // evitar envío y mostrar modal
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('modalEppCompleto'));
            if (modal) modal.show();
            // hidden ya actualizado por actualizarEstadoBoton()
        }
    });

    // Si la vista carga con un trabajador ya seleccionado, disparar carga
    const initialUser = selectTrabajador.value;
    if (initialUser) cargarEPP(initialUser);
});
</script>


@endpush

