<table class="table table-sm table-bordered">
  <thead>
    <tr>
      <th>Fecha</th><th>Anteojos</th><th>Botas</th><th>Chaleco</th><th>Guantes</th><th>Arnés</th><th>Altura</th><th>Observaciones</th>
    </tr>
  </thead>
  <tbody>
    @forelse($items as $c)
      <tr>
        <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
        <td>{{ $c->anteojos ? 'Sí' : 'No' }}</td>
        <td>{{ $c->botas ? 'Sí' : 'No' }}</td>
        <td>{{ $c->chaleco ? 'Sí' : 'No' }}</td>
        <td>{{ $c->guantes ? 'Sí' : 'No' }}</td>
        <td>{{ $c->arnes ? 'Sí' : 'No' }}</td>
        <td>{{ $c->es_en_altura ? 'Sí' : 'No' }}</td>
        <td>{{ $c->observaciones ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="8" class="text-muted">No hay checklist registrados.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-2">
  {{ $items->links() }}
</div>