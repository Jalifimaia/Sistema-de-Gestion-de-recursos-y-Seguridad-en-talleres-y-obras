@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')


<!--SEPARADOR-------------------------------------------- -->

<div class="container p-2">
  <div class="row align-items-stretch g-0">
    <!-- Caja grande izquierda -->
    <div class="col-12 col-md-8 col-sm-6 flex-column">
      <div class="border bg-light p-3">
        <h1>{{ $usuario->name }}</h1>
      </div>
      <div class="row g-0">


        <div class="d-flex flex-fill">


          <!-- Dos cajas pequeñas abajo -->
          <div class="col-12 col-md-6 flex-fill">
            <div class="border bg-light p-3">
              <p><strong>Email:</strong> {{ $usuario->email }}</p>
              <p><strong>Rol:</strong> {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</p>
              <p><strong>Último acceso:</strong> 
                {{ \Carbon\Carbon::parse($usuario->ultimo_acceso)->diffForHumans() ?? 'Nunca' }}
              </p>

              {{-- Estado actual --}}
              <p><strong>Estado:</strong>
                @if ($usuario->estado?->nombre === 'Alta')
                  <span class="badge bg-success">Activo (Alta)</span>
                @elseif ($usuario->estado?->nombre === 'Baja')
                  <span class="badge bg-danger">Inactivo (Baja)</span>
                @elseif ($usuario->estado?->nombre === 'stand by')
                  <span class="badge bg-warning text-dark">Stand by</span>
                @else
                  <span class="badge bg-secondary">Sin estado</span>
                @endif
              </p>
            </div>
          </div>

          <div class="col-12 col-md-6 flex-fill">
            <div class="border bg-light p-3">
              <p><strong>Creado por:</strong> {{ $usuario->creador?->name ?? 'Desconocido' }}</p>
              <p><strong>Modificado por:</strong> {{ $usuario->modificador?->name ?? 'Desconocido' }}</p>
              <p><strong>Creado el:</strong> {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}</p>  <!--parseo de dato a objeto carbon-->
              <p><strong>Modificado el:</strong> {{ $usuario->updated_at ? \Carbon\Carbon::parse($usuario->updated_at)->format('d/m/Y H:i') : 'N/A' }} </p>   <!--parseo de dato a objeto carbon-->
            </div>
          </div>
        

        </div>
      </div>


    </div>
      


    <!-- Caja alta derecha -->
    <div class="col-12 col-md-4 mb-3">
      <div class="border bg-light p-3">
        @if($usuario->codigo_qr)
        <div class="text-start d-flex flex-column justify-content-start align-items-center">
          <h5>Código QR de identificación</h5>
          {!! QrCode::size(200)->generate($usuario->codigo_qr) !!}
          <p class="text-muted small mt-2" id="codigo-qr-texto">{{ $usuario->codigo_qr }}</p>
          <button class="btn btn-outline-primary btn-sm mt-2" onclick="copiarCodigoQR()">📋 Copiar código</button>
        </div>
        @endif

        {{-- Toast Bootstrap --}}
        <div id="toastQR" class="toast align-items-center text-bg-success border-0" role="alert">
          <div class="d-flex justify-content-center">
            <div class="toast-body">
              ✅ Código QR copiado al portapapeles
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>      
      </div>
    </div>

  </div>
  <hr> <!--SEPARADOR-------------------------------------------- -->
</div>


<!-- NAV TABS -->
<ul class="nav nav-tabs d-flex justify-content-center mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab">Checklist</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Incidentes</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Préstamos</button>
  </li>
</ul>

<div class="tab-content" id="myTabContent">

  {{-- Checklist --}}
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
    <div class="p-3">
      <h4>Checklist del trabajador</h4>
      @if($usuario->checklists?->isNotEmpty())
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Anteojos</th>
              <th>Botas</th>
              <th>Chaleco</th>
              <th>Guantes</th>
              <th>Arnés</th>
              <th>Altura</th>
              <th>Observaciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuario->checklists as $c)
              <tr>
                <td>{{ $c->fecha instanceof \Illuminate\Support\Carbon ? $c->fecha->format('d/m/Y') : \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                <td>{{ $c->anteojos ? '✔' : '✘' }}</td>
                <td>{{ $c->botas ? '✔' : '✘' }}</td>
                <td>{{ $c->chaleco ? '✔' : '✘' }}</td>
                <td>{{ $c->guantes ? '✔' : '✘' }}</td>
                <td>{{ $c->arnes ? '✔' : '✘' }}</td>
                <td>{{ $c->es_en_altura ? 'Sí' : 'No' }}</td>
                <td>{{ $c->observaciones ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <p class="text-muted">No hay checklist registrados.</p>
      @endif
    </div>
  </div>

  {{-- Incidentes --}}
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
    <div class="p-3">
      <h4>Incidentes del trabajador</h4>
      @if($usuario->incidentes?->isNotEmpty())
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Descripción</th>
              <th>Recurso</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuario->incidentes as $i)
              <tr>
                <td>{{ \Carbon\Carbon::parse($i->fecha_incidente)->format('d/m/Y H:i') }}</td>
                <td>{{ $i->descripcion }}</td>
                <td>{{ $i->recurso->nombre ?? '-' }}</td>
                <td>{{ $i->estadoIncidente->nombre_estado ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <p class="text-muted">No hay incidentes registrados.</p>
      @endif
    </div>
  </div>

  {{-- Préstamos --}}
  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
    <div class="p-3">
      <h4>Préstamos del trabajador</h4>
      @if($usuario->prestamos?->isNotEmpty())
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Fecha préstamo</th>
              <th>Fecha devolución</th>
              <th>Recurso</th>
              <th>Nro Serie</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuario->prestamos as $p)
              @foreach($p->detallePrestamos as $d)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') }}</td>
                  <td>{{ $p->fecha_devolucion ? \Carbon\Carbon::parse($p->fecha_devolucion)->format('d/m/Y') : '-' }}</td>
                  <td>{{ $d->serieRecurso->recurso->nombre ?? '-' }}</td>
                  <td>{{ $d->serieRecurso->nro_serie ?? '-' }}</td>
                  <td>{{ $d->estadoPrestamo->nombre ?? '-' }}</td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      @else
        <p class="text-muted">No hay préstamos registrados.</p>
      @endif
    </div>
  </div>

</div>
<hr> <!--SEPARADOR-------------------------------------------- -->





<script>
function copiarCodigoQR() {
  const texto = document.getElementById('codigo-qr-texto').innerText;
  navigator.clipboard.writeText(texto).then(() => {
    const toastEl = document.getElementById('toastQR');
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  }).catch(err => {
    console.error('Error al copiar:', err);
    alert('❌ No se pudo copiar el código');
  });
}
</script>
    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
      <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
          ⬅️ Volver
       </a>
    </div>
@endsection


<style>



</style>
