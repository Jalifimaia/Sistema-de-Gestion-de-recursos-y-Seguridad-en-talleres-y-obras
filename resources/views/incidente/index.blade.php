@extends('layouts.app')

@section('title', 'Incidentes')

@section('content')
<div class="container py-4">

    <!-- 🔶 Encabezado -->
    <header class="row mb-4 align-items-center">
      <div class="col-md-8">
        <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2 text-orange">
          <img src="{{ asset('images/list1.svg') }}" alt="Ícono lista" style="height: 35px;">
          Incidentes registrados
        </h1>
        <p class="text-muted small">Listado de incidentes registrados en el sistema</p>
      </div>

      <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end mt-3">
        <strong id="today" class="valor-fecha text-nowrap">07/11/2023 09:20:17</strong>
      </div>
    </header>


    <div class="mb-3 text-start">
      <a href="{{ route('incidente.create') }}" class="btn btn-registrar-incidente">
        + Registrar nuevo incidente
      </a>
    </div>


    @if(session('success'))
        <div id="alertaEstado" class="alert alert-success alert-dismissible fade show" role="alert">
            <span id="mensajeAlertaEstado">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-naranja align-middle mb-0">
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
                              <button class="btn btn-detalles" data-bs-toggle="modal" data-bs-target="#modalIncidente{{ $incidente->id }}">
                                <img src="{{ asset('images/detalles.svg') }}" alt="Detalles" width="16" height="16" class="me-1">
                                Ver detalles
                              </button>

                              @if($incidente->estadoIncidente?->nombre_estado === 'Resuelto')
                                <button class="btn btn-bloqueado" data-bs-toggle="modal" data-bs-target="#modalBloqueado{{ $incidente->id }}">
                                  <i class="bi bi-lock"></i>
                                </button>
                              @else
                                <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-editar">
                                  <i class="bi bi-pencil me-1"></i> Editar
                                </a>
                              @endif
                        </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
          <table class="table table-bordered table-sm">
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
              @php
                $estadoRecursoResuelto = \App\Models\Estado::where('nombre_estado','Resuelto')->first();
              @endphp

              @foreach($incidente->recursos as $recurso)
                @php
                  $estadoRecurso = \App\Models\Estado::find($recurso->pivot->id_estado);
                @endphp
                <tr>
                  <td>{{ $recurso->subcategoria?->categoria?->nombre_categoria ?? '-' }}</td>
                  <td>{{ $recurso->subcategoria?->nombre ?? '-' }}</td>
                  <td>{{ $recurso->nombre ?? '-' }}</td>
                  <td>{{ $recurso->serieRecursos->firstWhere('id', $recurso->pivot->id_serie_recurso)?->nro_serie ?? '-' }}</td>
                  <td>{{ $estados[$recurso->pivot->id_estado] ?? 'Sin estado' }}</td>
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

@endforeach
@endsection

@push('scripts')
  {{-- Cargar archivo externo que formatea la fecha en zona Buenos Aires --}}
  <script src="{{ asset('js/formatoFecha.js') }}" defer></script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
      const alerta = document.getElementById('alertaEstado');
      if (alerta) {
          setTimeout(() => {
              alerta.classList.add('fade');
              alerta.classList.remove('show');
              alerta.addEventListener('transitionend', () => {
                  alerta.remove();
              }, { once: true });
          }, 5000);
      }

      const today = new Date();
    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();
    const hora = String(today.getHours()).padStart(2, '0');
    const minutos = String(today.getMinutes()).padStart(2, '0');
    document.getElementById('today').textContent = `${dia}/${mes}/${año} ${hora}:${minutos}`;
  });
  </script>
@endpush

@push('styles')
<link href="{{ asset('css/incidentes.css') }}" rel="stylesheet">
@endpush

