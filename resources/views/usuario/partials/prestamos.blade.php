<table class="table table-sm table-bordered">
  <thead><tr><th>Fecha préstamo</th><th>Fecha devolución</th><th>Recurso</th><th>Nro Serie</th><th>Estado</th></tr></thead>
  <tbody>
    @forelse($items as $p)
      @foreach($p->detallePrestamos as $d)
        <tr>
          <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') }}</td>
          <td>{{ $p->fecha_devolucion ? \Carbon\Carbon::parse($p->fecha_devolucion)->format('d/m/Y') : '-' }}</td>
          <td>{{ $d->serieRecurso->recurso->nombre ?? '-' }}</td>
          <td>{{ $d->serieRecurso->nro_serie ?? '-' }}</td>
          <td>{{ $d->estadoPrestamo->nombre ?? '-' }}</td>
        </tr>
      @endforeach
    @empty
      <tr><td colspan="5" class="text-muted">No hay préstamos registrados.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-2">
  {{ $items->links() }}
</div>