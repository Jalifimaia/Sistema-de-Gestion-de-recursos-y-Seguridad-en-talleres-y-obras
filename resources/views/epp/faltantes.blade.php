@extends('layouts.app')

@section('title', 'Recursos Faltantes')

@section('content')
<div class="container py-4">
  <h1 class="h4 fw-bold mb-4">🚨 Recursos Faltantes</h1>
  <p class="text-muted">Usuarios con rol de trabajador que no tienen todos los EPP obligatorios asignados según el checklist más reciente.</p>

  @if (count($faltantes))
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre del trabajador</th>
            <th>Faltantes de EPP</th>
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
