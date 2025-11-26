@extends('layouts.app')

@section('title', 'Registrar incidente')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('incidente.index') }}" class="btn btn-volver d-inline-flex align-items-center">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
      Registrar incidente
    </h4>
  </div>


    <form method="POST" action="{{ route('incidente.store') }}" id="formIncidente" novalidate>
        @csrf

        <!-- 🧠 DATOS DEL TRABAJADOR -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Trabajador Involucrado</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>DNI</label>
                        <div class="input-group">
                            <input type="text" id="dni_usuario" class="form-control" required placeholder="Ingrese aquí el DNI del trabajador involucrado" >
                            <button type="button" id="buscarUsuario" class="btn btn-secondary">Buscar</button>
                        </div>
                        <input type="hidden" name="id_usuario" id="id_usuario" required>
                        <label class="error-label text-danger mt-1 no-asterisk" style="display: none; font-size: 0.875rem;">Este campo es obligatorio</label>
                    </div>
                    <div class="col-md-6">
                        <label>Nombre completo</label>
                        <input type="text" id="nombre_usuario" class="form-control" readonly placeholder="Se completará automáticamente al buscar"  >
                    </div>
                </div>
            </div>
        </div>

        <!-- 🧰 DATOS DE LOS RECURSOS -->
        <div id="recursos-container">
  <div class="card mb-3 recurso-block">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <span>Recurso Involucrado</span>
      <button type="button" class="btn btn-sm btn-danger btn-eliminar-recurso" title="Eliminar este recurso">
        ✖
      </button>
    </div>
    <div class="card-body">

      <!-- Fila 1: Categoría y Subcategoría -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Categoría</label>
          <select name="recursos[0][id_categoria]" class="form-select categoria-select" required>
            <option value="">Seleccione la categoría</option>
            @foreach($categorias as $cat)
              @php
                $selectedCat = old('recursos.0.id_categoria') ?? ($incidente->recursos[0]->subcategoria->categoria->id ?? null);
              @endphp
              <option value="{{ $cat->id }}" {{ (string)$cat->id === (string)$selectedCat ? 'selected' : '' }}>
                {{ $cat->nombre_categoria }}
              </option>
            @endforeach
          </select>
          <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
        </div>

        <div class="col-md-6">
          <label class="form-label">Subcategoría</label>
          <select name="recursos[0][id_subcategoria]" class="form-select subcategoria-select" required>
            <option value="">Seleccione la subcategoría</option>
            @if(old('recursos.0.id_subcategoria'))
              <option value="{{ old('recursos.0.id_subcategoria') }}" selected>
                {{ collect($subcategorias)->firstWhere('id', old('recursos.0.id_subcategoria'))->nombre ?? 'Seleccionado' }}
              </option>
            @elseif(isset($incidente) && $incidente->recursos->count())
              @php $sc = $incidente->recursos[0]->subcategoria ?? null; @endphp
              @if($sc)
                <option value="{{ $sc->id }}" selected>{{ $sc->nombre }}</option>
              @endif
            @endif
          </select>
          <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
        </div>
      </div>

      <!-- Fila 2: Recurso y Serie -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Recurso</label>
          <select name="recursos[0][id_recurso]" class="form-select recurso-select" required>
            <option value="">Seleccione el recurso</option>
            @if(old('recursos.0.id_recurso'))
              <option value="{{ old('recursos.0.id_recurso') }}" selected>
                {{ collect($recursos)->firstWhere('id', old('recursos.0.id_recurso'))->nombre ?? 'Seleccionado' }}
              </option>
            @elseif(isset($incidente) && $incidente->recursos->count())
              @php $r = $incidente->recursos[0] ?? null; @endphp
              @if($r)
                <option value="{{ $r->id }}" selected>{{ $r->nombre }}</option>
              @endif
            @endif
          </select>
          <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
        </div>

        <div class="col-md-6">
          <label class="form-label">Serie del recurso</label>
          <select name="recursos[0][id_serie_recurso]" class="form-select serie-select" required>
            <option value="">Seleccione la serie</option>
            @if(old('recursos.0.id_serie_recurso'))
              <option value="{{ old('recursos.0.id_serie_recurso') }}" selected>
                {{ \App\Models\SerieRecurso::find(old('recursos.0.id_serie_recurso'))->nro_serie ?? 'Seleccionado' }}
              </option>
            @elseif(isset($incidente) && $incidente->recursos->count())
              @php $serieId = $incidente->recursos[0]->pivot->id_serie_recurso ?? null; @endphp
              @if($serieId)
                <option value="{{ $serieId }}" selected>
                  {{ \App\Models\SerieRecurso::find($serieId)->nro_serie ?? 'Seleccionado' }}
                </option>
              @endif
            @endif
          </select>
          <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
        </div>
      </div>

      <!-- Fila 3: Estado -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Estado</label>
          <select name="recursos[0][id_estado]" class="form-select estado-select" required>
            <option value="">Seleccione el estado</option>
            @foreach($estados as $estado)
              @php
                $selectedEstado = old('recursos.0.id_estado') ?? ($incidente->recursos[0]->pivot->id_estado ?? null);
              @endphp
              <option value="{{ $estado->id }}" {{ (string)$estado->id === (string)$selectedEstado ? 'selected' : '' }}>
                {{ $estado->nombre_estado }}
              </option>
            @endforeach
          </select>
          <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>
        </div>
      </div>

    </div>
  </div>
</div>



        <div class="d-flex justify-content-end mb-3">
            <button type="button" id="agregar-recurso" class="btn btn-success">
                Agregar otro recurso
            </button>
        </div>

        <!-- 🧾 DETALLE DEL INCIDENTE -->
        <div class="card mb-3">
            <div class="card-header bg-detalle">Detalles del Incidente</div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Motivo del incidente</label>
                    <textarea name="descripcion" class="form-control"
                      required
                      maxlength="255"
                      placeholder="Ingrese el motivo (máximo 255 caracteres)">{{ old('descripcion') }}</textarea>
                    <label class="error-label text-danger mt-1 no-asterisk" style="display: none; font-size: 0.875rem;">Este campo es obligatorio</label>
                </div>
                <div class="mb-3">
                    <label>Fecha del incidente</label>
                    <input type="datetime-local"
                          id="fecha_incidente"
                          name="fecha_incidente"
                          class="form-control @error('fecha_incidente') is-invalid @enderror"
                          value="{{ old('fecha_incidente') }}"
                          required
                          aria-describedby="fechaError"
                          aria-invalid="{{ $errors->has('fecha_incidente') ? 'true' : 'false' }}"
                          max="{{ now()->format('Y-m-d\TH:i') }}">
                    <label class="error-label text-danger mt-1 no-asterisk" style="display:none;font-size:0.875rem;">Este campo es obligatorio</label>

                    @error('fecha_incidente')
                      <div id="fechaError" class="invalid-feedback d-block">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const fechaInput = document.getElementById("fecha_incidente");
    fechaInput.addEventListener("click", function() {
      // Forzar apertura del selector
      this.showPicker?.(); // método nativo en navegadores modernos
    });
  });
</script>


            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-reg px-4">Registrar incidente</button>
        </div>

    </form>

<!-- Modal de aviso (usuario no encontrado / rol inválido) -->
<div class="modal fade" id="usuarioAvisoModal" tabindex="-1" aria-labelledby="usuarioAvisoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-atencion">
        <h5 class="modal-title" id="usuarioAvisoModalLabel">Atención</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="usuarioAvisoModalBody">
        <!-- Mensaje dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de aviso para recursos -->
<div class="modal fade" id="modalAvisoRecursos" tabindex="-1" aria-labelledby="modalAvisoRecursosLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalAvisoRecursosLabel">Atención</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalAvisoRecursosBody">
        <!-- Mensaje dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

    <!-- Modal de confirmación -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">¡Incidente registrado!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <a href="{{ route('incidente.create') }}" class="btn btn-outline-success">
          + Registrar otro
        </a>
        <a href="{{ route('incidente.index') }}" class="btn btn-success">
          Ver incidentes
        </a>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
  });
</script>
@endpush
@endif


<script>
const categorias = @json($categorias);

// ---------- Funciones para llenar selects ----------
function llenarSubcategorias(categoriaId, subSelect) {
    subSelect.innerHTML = '<option value="">Seleccione</option>';
    const categoria = categorias.find(c => c.id == categoriaId);
    if(categoria && categoria.subcategorias) {
        categoria.subcategorias.forEach(sub => {
            subSelect.innerHTML += `<option value="${sub.id}">${sub.nombre}</option>`;
        });
    }
}

function llenarRecursos(subcategoriaId, recursoSelect) {
    recursoSelect.innerHTML = '<option value="">Seleccione</option>';
    categorias.forEach(c => {
        const sub = c.subcategorias.find(s => s.id == subcategoriaId);
        if(sub && sub.recursos) {
            sub.recursos.forEach(r => {
                recursoSelect.innerHTML += `<option value="${r.id}">${r.nombre}</option>`;
            });
        }
    });
}

function llenarSeries(recursoId, serieSelect) {
    serieSelect.innerHTML = '<option value="">Seleccione</option>';
    categorias.forEach(c => {
        c.subcategorias.forEach(sub => {
            const rec = sub.recursos.find(r => r.id == recursoId);
            if(rec && rec.serie_recursos) {
                rec.serie_recursos.forEach(s => {
                    serieSelect.innerHTML += `<option value="${s.id}">${s.nro_serie}</option>`;
                });
            }
        });
    });
}

// ---------- Inicializar selects de un bloque ----------
function initSelects(block) {
    
    const cat = block.querySelector('.categoria-select');
    const sub = block.querySelector('.subcategoria-select');
    const rec = block.querySelector('.recurso-select');
    const ser = block.querySelector('.serie-select');

    cat.addEventListener('change', () => {
        llenarSubcategorias(cat.value, sub);
        rec.innerHTML = '<option value="">Seleccione</option>';
        ser.innerHTML = '<option value="">Seleccione</option>';
        ocultarError(cat);
    });

    sub.addEventListener('change', () => {
        llenarRecursos(sub.value, rec);
        ser.innerHTML = '<option value="">Seleccione</option>';
        ocultarError(sub);
    });

    rec.addEventListener('change', () => {
        llenarSeries(rec.value, ser);
        ocultarError(rec);
    });

    ser.addEventListener('change', () => ocultarError(ser));
    
    const estado = block.querySelector('.estado-select');
    if (estado) {
        estado.addEventListener('change', () => ocultarError(estado));
    }
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('btn-eliminar-recurso')) {
    const bloque = e.target.closest('.recurso-block');
    const total = document.querySelectorAll('.recurso-block').length;

    if (total > 1) {
      bloque.remove();
    } else {
      mostrarModalAvisoRecursos();
    }
  }
});


// Inicializar primer bloque
document.querySelectorAll('.recurso-block').forEach(initSelects);

// ---------- Agregar nuevo recurso ----------
let recursoIndex = 1;
document.getElementById('agregar-recurso').addEventListener('click', function() {
    const container = document.getElementById('recursos-container');
    const newBlock = document.querySelector('.recurso-block').cloneNode(true);

    // Resetear selects y actualizar nombre
    newBlock.querySelectorAll('select').forEach(sel => {
        sel.value = '';
        const name = sel.getAttribute('name');
        const newName = name.replace(/\d+/, recursoIndex);
        sel.setAttribute('name', newName);
        sel.classList.remove('is-invalid');
    });
    
    // Ocultar errores en el nuevo bloque
    newBlock.querySelectorAll('.error-label').forEach(label => {
        label.style.display = 'none';
    });

    container.appendChild(newBlock);
    initSelects(newBlock);
    recursoIndex++;
});

// ---------- Buscar usuario por DNI ----------
document.getElementById('buscarUsuario').addEventListener('click', function() {
    const dni = document.getElementById('dni_usuario').value.replace(/\./g,'');
    fetch(`/ajax/incidente/buscar-usuario/${dni}`)
        .then(res => res.json())
        .then(data => {
            if (data.nombre && data.id) {
            document.getElementById('nombre_usuario').value = data.nombre;
            document.getElementById('id_usuario').value = data.id;
            ocultarError(document.getElementById('id_usuario'));
            } else {
            // mostrar modal con mensaje y limpiar campos
            mostrarModalAvisoUsuario(data.error || 'Usuario no encontrado', 'warning');
            document.getElementById('nombre_usuario').value = '';
            document.getElementById('id_usuario').value = '';
            }

        });
});

// ---------- Validación del formulario ----------
function mostrarError(campo) {
    campo.classList.add('is-invalid');
    const errorLabel = campo.closest('.col-md-6, .col-md-3, .mb-3')?.querySelector('.error-label');
    if (errorLabel) {
        errorLabel.style.display = 'block';
    }
}

function ocultarError(campo) {
    campo.classList.remove('is-invalid');
    const errorLabel = campo.closest('.col-md-6, .col-md-3, .mb-3')?.querySelector('.error-label');
    if (errorLabel) {
        errorLabel.style.display = 'none';
    }
}

function validarFormulario() {
    let esValido = true;
    
    // Ocultar todos los errores primero
    document.querySelectorAll('.error-label').forEach(label => {
        label.style.display = 'none';
    });
    document.querySelectorAll('.is-invalid').forEach(campo => {
        campo.classList.remove('is-invalid');
    });
    
    // Validar usuario
    const idUsuario = document.getElementById('id_usuario');
    if (!idUsuario.value) {
        mostrarError(document.getElementById('dni_usuario'));
        esValido = false;
    }
    
    // Validar recursos
    document.querySelectorAll('.recurso-block').forEach(block => {
        const campos = [
            block.querySelector('.categoria-select'),
            block.querySelector('.subcategoria-select'),
            block.querySelector('.recurso-select'),
            block.querySelector('.serie-select'),
            block.querySelector('.estado-select')
        ];
        
        campos.forEach(campo => {
            if (campo && !campo.value) {
                mostrarError(campo);
                esValido = false;
            }
        });
    });
    
    // Validar descripción
    const descripcion = document.querySelector('textarea[name="descripcion"]');
    if (!descripcion.value.trim()) {
        mostrarError(descripcion);
        esValido = false;
    }
    
    // Validar fecha
    const fecha = document.querySelector('input[name="fecha_incidente"]');
    if (!fecha.value) {
        mostrarError(fecha);
        esValido = false;
    }
    
    return esValido;
}

// Evento submit del formulario
document.getElementById('formIncidente').addEventListener('submit', function(e) {
    if (!validarFormulario()) {
        e.preventDefault();
        // Scroll al primer error
        const primerError = document.querySelector('.is-invalid');
        if (primerError) {
            primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            primerError.focus();
        }
    }
});

// Ocultar errores al escribir/cambiar
document.querySelector('textarea[name="descripcion"]').addEventListener('input', function() {
    ocultarError(this);
});

document.querySelector('input[name="fecha_incidente"]').addEventListener('change', function() {
    ocultarError(this);
});

document.getElementById('dni_usuario').addEventListener('input', function() {
    ocultarError(this);
});

// Mostrar modal de aviso con mensaje dinámico
function mostrarModalAvisoUsuario(mensaje, tipo = 'danger') {
  try {
    const modalEl = document.getElementById('usuarioAvisoModal');
    const body = document.getElementById('usuarioAvisoModalBody');
    if (!modalEl || !body) {
      // fallback visible si modal no existe
      alert(mensaje);
      return;
    }

    body.textContent = mensaje;

    // ajustar estilos según tipo (danger, warning, info, success)
    const header = modalEl.querySelector('.modal-header');
    header.classList.remove('bg-danger','bg-warning','bg-info','bg-success','text-white');
    if (tipo === 'warning') header.classList.add('bg-warning','text-dark');
    else if (tipo === 'info') header.classList.add('bg-info','text-white');
    else if (tipo === 'success') header.classList.add('bg-success','text-white');
    else header.classList.add('bg-danger','text-white');

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  } catch (e) {
    console.warn('mostrarModalAvisoUsuario error', e);
    alert(mensaje);
  }
  
}
function mostrarModalAvisoRecursos(mensaje = 'Debe haber al menos un recurso cargado.') {
  const body = document.getElementById('modalAvisoRecursosBody');
  if (body) body.textContent = mensaje;

  const modal = new bootstrap.Modal(document.getElementById('modalAvisoRecursos'));
  modal.show();
}

document.addEventListener("DOMContentLoaded", function() {
    const fechaInput = document.getElementById("fecha_incidente");
    fechaInput.addEventListener("click", function() {
      // Forzar apertura del selector
      this.showPicker?.(); // método nativo en navegadores modernos
    });
  });

</script>
@endsection

@push('styles')
<link href="{{ asset('css/agregarIncidente.css') }}" rel="stylesheet">
<style>
    .is-invalid {
        /*border-color: #dc3545 !important;*/
    }
    .error-label {
        display: block;
        font-size: 0.875rem;
    }

 label::after {
 content: " *";
 color: red;
}

label.no-asterisk::after {
 content: ""; /* anula el asterisco */
}

</style>


@endpush