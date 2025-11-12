<table class="table table-sm table-bordered">
  <thead><tr><th>Fecha</th><th>Descripción</th><th>Recurso</th><th>Estado</th></tr></thead>
  <tbody>
    @forelse($items as $i)
      <tr>
        <td>{{ \Carbon\Carbon::parse($i->fecha_incidente)->format('d/m/Y H:i') }}</td>
        <td>{{ $i->descripcion }}</td>
        <td>{{ $i->recurso->nombre ?? '-' }}</td>
        <td>{{ $i->estadoIncidente->nombre_estado ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-muted">No hay incidentes registrados.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-2">
  {{ $items->links() }}
</div>