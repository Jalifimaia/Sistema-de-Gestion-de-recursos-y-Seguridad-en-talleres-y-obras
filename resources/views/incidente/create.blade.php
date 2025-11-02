@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-start mb-3">
  <a href="{{ route('incidente.index') }}" class="btn btn-outline-secondary">
    ⬅️ Volver
  </a>
</div>

<div class="container">
    <h2>Registrar nuevo incidente</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('incidente.store') }}">
        @csrf

        <!-- 🧍 DATOS DEL TRABAJADOR -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Datos del Trabajador</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>DNI del trabajador</label>
                        <div class="input-group">
                            <input type="text" id="dni_usuario" class="form-control" required placeholder="Ingrese aquí el DNI del trabajador involucrado" >
                            <button type="button" id="buscarUsuario" class="btn btn-secondary">Buscar</button>
                        </div>
                        <input type="hidden" name="id_usuario" id="id_usuario">
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
                <div class="card-header bg-success text-white">Datos del Recurso</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Categoría</label>
                            <select name="recursos[0][id_categoria]" class="form-select categoria-select" required>
                                <option value="">Seleccione</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre_categoria }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Subcategoría</label>
                            <select name="recursos[0][id_subcategoria]" class="form-select subcategoria-select" required></select>
                        </div>
                        <div class="col-md-3">
                            <label>Recurso</label>
                            <select name="recursos[0][id_recurso]" class="form-select recurso-select" required></select>
                        </div>
                        <div class="col-md-3">
                            <label>Serie del recurso</label>
                            <select name="recursos[0][id_serie_recurso]" class="form-select serie-select" required></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="agregar-recurso" class="btn btn-outline-primary mb-3">+ Agregar otro recurso</button>

        <!-- 🧾 DETALLE DEL INCIDENTE -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">Detalle del Incidente</div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Motivo del incidente</label>
                    <textarea name="descripcion" class="form-control" required placeholder="Ingrese aquí cuál fue el motivo del incidente" >{{ old('descripcion') }}</textarea>
                </div>
                <div class="mb-3">
                    <label>Fecha del incidente</label>
                    <input type="datetime-local" name="fecha_incidente" class="form-control" value="{{ old('fecha_incidente') }}" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100">Registrar incidente</button>
    </form>
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
// ---------- Datos de categorías, subcategorías, recursos y series ----------
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
    });

    sub.addEventListener('change', () => {
        llenarRecursos(sub.value, rec);
        ser.innerHTML = '<option value="">Seleccione</option>';
    });

    rec.addEventListener('change', () => {
        llenarSeries(rec.value, ser);
    });
}

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
            if(data.nombre && data.id) {
                document.getElementById('nombre_usuario').value = data.nombre;
                document.getElementById('id_usuario').value = data.id;
            } else {
                alert(data.error || "Usuario no encontrado");
                document.getElementById('nombre_usuario').value = '';
                document.getElementById('id_usuario').value = '';
            }
        });
});
</script>
@endsection
