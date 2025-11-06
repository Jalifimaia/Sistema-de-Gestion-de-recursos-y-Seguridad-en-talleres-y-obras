@extends('layouts.app')

@section('title', 'Checklist No Registrado')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-4">
    <a href="{{ route('controlEPP') }}" class="btn btn-outline-secondary me-3">
      ⬅️ Volver
    </a>
    <h2 class="h4 fw-bold mb-0">Checklist no registrado</h2>
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
                <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-outline-primary btn-sm">
                  Ver perfil
                </a>
                <a href="{{ route('checklist.epp', ['trabajador_id' => $usuario->id]) }}" class="btn btn-success btn-sm">
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
