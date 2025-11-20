@extends('layouts.app')

@section('title', 'Checklist No Registrado')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-4">
    <a href="{{ route('controlEPP') }}" class="btn btn-volver d-flex align-items-center me-3">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <div class="d-flex align-items-center">
      <img src="{{ asset('images/checknot.svg') }}" alt="Checklist no registrado" style="width: 28px; height: 28px;" class="me-2">
      <h4 class="fw-bold mb-0">Checklist no registrado</h4>
    </div>

  </div>
  <p class="text-muted">Estos trabajadores no tienen checklist cargado hoy. Podés ingresar a su perfil para registrar o revisar.</p>

  @if ($sinChecklist->count())
    <div class="row g-3">
      @foreach ($sinChecklist as $usuario)
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title">{{ $usuario->name }}</h5>
              <p class="card-text text-muted mb-2">
                <strong>Estado:</strong> {{ $usuario->estado->nombre ?? 'Sin estado' }}
              </p>
              <div class="d-flex justify-content-between">
           <a href="{{ route('usuarios.show', ['usuario' => $usuario->id, 'from' => 'sinChecklist']) }}" 
   class="btn btn-verperfil btn-sm">
  Ver perfil
</a>

         <a href="{{ route('checklist.epp', ['trabajador_id' => $usuario->id, 'from' => 'sinChecklist']) }}" 
   class="btn btn-success btn-sm">
  Registrar checklist
</a>

              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="alert alert-success">✅ Todos los trabajadores tienen checklist registrado hoy.</div>
  @endif
</div>
@endsection

@push('styles')
  <link href="{{ asset('css/sinChecklist.css') }}" rel="stylesheet">
@endpush
