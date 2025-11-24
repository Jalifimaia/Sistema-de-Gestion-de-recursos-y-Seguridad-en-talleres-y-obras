<table class="table table-striped table-hover table-bordered custom-table table-naranja">
  <thead class="table-orange">
    <tr>
      <th>Fecha</th>
      <th>Descripción</th>
      <th>Recursos</th>
      <th>Estado</th>
    </tr>
  </thead>
  <tbody>
    @forelse($items as $i)
      <tr>
        <td>{{ \Carbon\Carbon::parse($i->fecha_incidente)->format('d/m/Y H:i') }}</td>
        <td>{{ $i->descripcion }}</td>
        <td>
          @forelse($i->recursos as $r)
            {{ $r->nombre }}
            (Serie: {{ $r->serieRecursos->first()->nro_serie ?? '-' }},
            Estado: {{ $r->pivot->id_estado ?? '-' }})<br>
          @empty
            -
          @endforelse
        </td>
        <td>{{ $i->estadoIncidente->nombre_estado ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-muted">No hay incidentes registrados.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-2 d-flex justify-content-end">
  {{ $items->links() }}
</div>
