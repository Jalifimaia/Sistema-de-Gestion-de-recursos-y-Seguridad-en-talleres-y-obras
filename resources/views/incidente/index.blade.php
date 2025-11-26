@extends('layouts.app')

@section('title', 'Incidentes')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <header class="row mb-2 align-items-center">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2 text-orange">
        <img src="{{ asset('images/list1.svg') }}" alt="Ícono lista" style="height: 35px;">
        Incidentes registrados
      </h1>
      <p class="text-muted small mb-2">Listado de incidentes registrados en el sistema</p>
    </div>
    <!--
    <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end mt-3">
      <strong id="today" class="valor-fecha text-nowrap">07/11/2023 09:20:17</strong>
    </div>
    -->
  </header>

  <!-- Botón registrar -->
  <div class="mb-3 text-start">
    <a href="{{ route('incidente.create') }}" class="btn btn-registrar-incidente">
    Registrar incidente
    </a>
  </div>

 <!-- Buscador y filtro -->
<div class="row mb-3 align-items-center g-2">
  <div class="d-flex flex-wrap align-items-center gap-3 mb-1">
    <!-- Buscador más corto -->
    <input
      type="text"
      id="buscadorIncidentes"
      class="form-control buscador-destacado buscador-con-icono"
      placeholder="Buscar por trabajador, motivo, estado o resolución...">

    <!-- Select de filtro más ancho -->
    <select id="filtroEstadoIncidente" class="form-select filtro-destacado">
      <option value="">Todos los estados</option>
      @foreach($estados as $id => $nombre)
        <option value="{{ $nombre }}">{{ $nombre }}</option>
      @endforeach
    </select>
  </div>
</div>



  @if(session('success'))
    <div id="alertaEstado" class="alert alert-success alert-dismissible fade show" role="alert">
      <span id="mensajeAlertaEstado">{{ session('success') }}</span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  @endif

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0 text-center" id="tablaIncidentes">
          <thead>
            <tr>
              <th>Trabajador</th>
              <th>Motivo</th>
              <th>Estado</th>
              <th>Resolución</th>
              <th>Fecha del incidente</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($incidentes as $incidente)
              <tr>
                <td>{{ $incidente->trabajador?->name ?? '-' }}</td>
                <td>{{ $incidente->descripcion ?? '-' }}</td>
                <td>{{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}</td>
                <td>{{ $incidente->resolucion ? $incidente->resolucion : 'No hay resolución' }}</td>
                <td>
                  {{ $incidente->fecha_incidente
                    ? \Carbon\Carbon::parse($incidente->fecha_incidente, config('app.timezone'))->format('d/m/Y H:i')
                    : '-' }}
                </td>
                <td>
                  <div class="grupo-acciones d-flex gap-2">
                  <button
                    class="btn btn-detalles btn-primary btn-ver-series"
                    data-bs-toggle="modal"
                    data-bs-target="#modalIncidente{{ $incidente->id }}"
                    title="Ver detalles del incidente">
                     <i class="bi bi-eye "></i>
                  </button>

                  @if($incidente->estadoIncidente?->nombre_estado === 'Resuelto')
                    <button class="btn btn-bloqueado" data-bs-toggle="modal" data-bs-target="#modalBloqueado{{ $incidente->id }}">
                      <i class="bi bi-lock"></i>
                    </button>
                  @else
                    <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-editar" title="Editar incidente">
                      <i class="bi bi-pencil me-1"></i>
                    </a>
                  @endif
                </div>

                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small" id="infoPaginacionIncidentes">
            Mostrando {{ $incidentes->firstItem() }} a {{ $incidentes->lastItem() }} de {{ $incidentes->total() }} incidentes
          </div>
          <div class="ms-auto">
            {{ $incidentes->links() }}
          </div>
        </div>
      </div>

</div>

<!-- Modales de detalle -->
@foreach($incidentes as $incidente)
  <div class="modal fade" id="modalIncidente{{ $incidente->id }}" tabindex="-1" aria-labelledby="modalIncidenteLabel{{ $incidente->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-orange text-white">
          <h5 class="modal-title" id="modalIncidenteLabel{{ $incidente->id }}">Detalles del incidente #{{ $incidente->id }}</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <p>
            <strong>Trabajador:</strong>
            {{ $incidente->trabajador?->name ?? '-' }}
            @if($incidente->trabajador?->dni)
              <small class="text-muted">[DNI: {{ $incidente->trabajador->dni }}]</small>
            @else
              <small class="text-muted">([DNI: no disponible])</small>
            @endif
          </p>

          <p><strong>Motivo:</strong> {{ $incidente->descripcion ?? '-' }}</p>
          <p><strong>Estado del incidente:</strong> {{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}</p>
          <p><strong>Resolución:</strong> {{ $incidente->resolucion ? $incidente->resolucion : 'No hay resolución' }}</p>
          <p><strong>Fecha del incidente:</strong>
            {{ $incidente->fecha_incidente
              ? \Carbon\Carbon::parse($incidente->fecha_incidente, config('app.timezone'))->format('d/m/Y H:i')
              : '-' }}
          </p>

          <p><strong>Última modificación del incidente:</strong>
            {{ $incidente->fecha_modificacion
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_modificacion, 'UTC')
                    ->setTimezone('America/Argentina/Buenos_Aires')
                    ->format('d/m/Y H:i')
                : 'No hay modificaciones' }}
          </p>

          <p><strong>Fecha de resolución del incidente:</strong>
            {{ $incidente->fecha_cierre_incidente
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_cierre_incidente, 'UTC')
                    ->setTimezone('America/Argentina/Buenos_Aires')
                    ->format('d/m/Y H:i')
                : 'No hay fecha de resolución' }}
          </p>

          <hr>
          <h6 class="text-orange">Recursos asociados</h6>

          <div class="table-responsive">
            <table class="table table-naranja table-sm">
              <thead class="table-light">
                <tr>
                  <th>Categoría</th>
                  <th>Subcategoría</th>
                  <th>Recurso</th>
                  <th>Serie</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                @foreach($incidente->recursos as $recurso)
                  @php
                    // Preferencia: usar series precargadas con with('recursos.serieRecursos')
                    $serie = $recurso->serieRecursos->firstWhere('id', $recurso->pivot->id_serie_recurso);
                    // Fallback: buscar por ID si no está en la colección
                    if (!$serie && $recurso->pivot?->id_serie_recurso) {
                      $serie = \App\Models\SerieRecurso::find($recurso->pivot->id_serie_recurso);
                    }
                    $nroSerie = $serie?->nro_serie ?? '-';

                    // Estado del recurso desde pivote (usar array $estados si lo pasás)
                    $estadoNombre = \App\Models\Estado::find($recurso->pivot->id_estado)?->nombre_estado ?? 'Sin estado';
                  @endphp
                  <tr>
                    <td>{{ $recurso->subcategoria?->categoria?->nombre_categoria ?? '-' }}</td>
                    <td>{{ $recurso->subcategoria?->nombre ?? '-' }}</td>
                    <td>{{ $recurso->nombre ?? '-' }}</td>
                    <td>{{ $nroSerie }}</td>
                    <td>{{ $estadoNombre }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

<!-- Modales de bloqueo -->
@foreach($incidentes as $incidente)
  @if($incidente->estadoIncidente?->nombre_estado === 'Resuelto')
    <div class="modal fade" id="modalBloqueado{{ $incidente->id }}" tabindex="-1" aria-labelledby="modalBloqueadoLabel{{ $incidente->id }}" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-orange text-light">
            <h5 class="modal-title" id="modalBloqueadoLabel{{ $incidente->id }}">Incidente bloqueado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            El incidente <strong>#{{ $incidente->id }}</strong> ya está marcado como <strong>Resuelto</strong> y no puede ser editado.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  @endif
@endforeach
@endsection

@push('scripts')
  <script src="{{ asset('js/formatoFecha.js') }}" defer></script>


<script src="{{ asset('js/ordenamiento-tabla.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  
  // 💡 Necesario para llamar aplicarZebra()
  const tablaIncidentes = document.querySelector('table.table-naranja'); 

  // ✅ IDs CORRECTOS del HTML
  const buscador = document.getElementById('buscadorIncidentes');
  const filtro = document.getElementById('filtroEstadoIncidente');
  const tbody = document.querySelector('#tablaIncidentes tbody');

  if (!tbody) return;

  function aplicarFiltros() {
    const textoBuscador = buscador?.value.toLowerCase().trim() || '';
    const valorFiltro = filtro?.value.toLowerCase() || '';

    const filas = Array.from(tbody.querySelectorAll('tr'));
    
    filas.forEach(fila => {
      const celdas = Array.from(fila.querySelectorAll('td'));
      
      // Columnas: 0=Trabajador, 1=Motivo, 2=Estado, 3=Resolución, 4=Fecha
      const textoFila = celdas.map(td => td.textContent.toLowerCase().trim()).join(' ');
      const estadoActual = celdas[2]?.textContent.trim().toLowerCase() || '';
      
      const coincideBuscador = textoFila.includes(textoBuscador);
      const coincideFiltro = valorFiltro === '' || estadoActual === valorFiltro;

      if (coincideBuscador && coincideFiltro) {
        fila.style.display = 'table-row';
      } else {
        fila.style.display = 'none';
      }
    });

    // 💡 SOLUCIÓN ZEBRA: Aplicar después de filtrar
    if (tablaIncidentes && tablaIncidentes.aplicarZebra) {
      tablaIncidentes.aplicarZebra();
    }
  }
  
  if (buscador) buscador.addEventListener('input', aplicarFiltros);
  if (filtro) filtro.addEventListener('change', aplicarFiltros);

  // Aplicar zebra inicial
  if (tablaIncidentes && tablaIncidentes.aplicarZebra) {
    tablaIncidentes.aplicarZebra();
  }
});
</script>

@endpush

@push('styles')
  <link href="{{ asset('css/incidentes.css') }}" rel="stylesheet">
@endpush

