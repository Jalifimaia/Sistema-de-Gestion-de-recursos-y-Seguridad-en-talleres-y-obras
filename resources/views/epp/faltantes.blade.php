@extends('layouts.app')

@section('title', 'Recursos Faltantes')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-4">
    <a href="{{ route('controlEPP') }}" class="btn btn-volver d-flex align-items-center me-3">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>
    <div class="d-flex align-items-center">
      <img src="{{ asset('images/atencion.svg') }}" alt="Atención" style="width: 28px; height: 28px;" class="me-2">
      <h4 class="fw-bold mb-0">Recursos Faltantes</h4>
    </div>

  </div>
  <p class="text-muted">Trabajadores que no tienen todos los EPP obligatorios asignados según el checklist diario más reciente.</p>

  @if (count($faltantes))
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>Nombre del trabajador</th>
            <th>EPP faltantes</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($faltantes as $usuarioId => $items)
            <tr>
              <td>{{ $usuarios[$usuarioId] ?? 'ID ' . $usuarioId }}</td>
              <td>
                @foreach ($items as $item)
                  <span class="badge bg-danger me-1">{{ $item }}</span>
                @endforeach
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="alert alert-success">✅ Todos los trabajadores tienen sus EPP asignados.</div>
  @endif
</div>
@endsection

@push('styles')
  <link href="{{ asset('css/faltantes.css') }}" rel="stylesheet">
@endpush
