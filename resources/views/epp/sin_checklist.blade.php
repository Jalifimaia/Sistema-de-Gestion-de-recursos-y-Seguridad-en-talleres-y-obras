@extends('layouts.app')

@section('title', 'Checklist sin registrar')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-4">
    <a href="{{ route('controlEPP') }}" class="btn btn-volver d-flex align-items-center me-3">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <div class="d-flex align-items-center">
      <img src="{{ asset('images/checknot.svg') }}" alt="Checklist no registrado" style="width: 28px; height: 28px;" class="me-2">
      <h4 class="fw-bold mb-0">Checklist sin registrar</h4>
    </div>
  </div>

  <p class="text-muted">Estos trabajadores no tienen checklist cargado hoy. Podés ingresar a su perfil para registrar o revisar.</p>

  @if ($sinChecklist->count())
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr class="text-orange">
            <th>Nombre</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($sinChecklist as $usuario)
            <tr>
              <td>{{ $usuario->name }}</td>
              <td>{{ $usuario->estado->nombre ?? 'Sin estado' }}</td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('usuarios.show', ['usuario' => $usuario->id, 'from' => 'sinChecklist']) }}" 
                     class="btn btn-verperfil btn-sm">
                    Ver perfil
                  </a>
                  <a href="{{ route('checklist.epp', ['trabajador_id' => $usuario->id, 'from' => 'sinChecklist']) }}" 
                     class="btn btn-success btn-sm">
                    Registrar checklist
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="alert alert-success">✅ Todos los trabajadores tienen checklist registrado hoy.</div>
  @endif
</div>

@endsection

@push('styles')
  <link href="{{ asset('css/sinChecklist.css') }}" rel="stylesheet">
@endpush
