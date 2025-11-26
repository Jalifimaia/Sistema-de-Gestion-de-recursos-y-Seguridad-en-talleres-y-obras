<table class="table table-striped table-hover table-bordered custom-table table-naranja text-center">
  <thead class="table-orange">
    <tr>
      <th>Fecha préstamo</th>
      <th>Fecha devolución</th>
      <th>Recurso</th>
      <th>Nro Serie</th>
      <th>Estado</th>
    </tr>
  </thead>
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

<div class="mt-2 d-flex justify-content-end">
  @if ($items->hasPages())
    {!! $items->links('pagination::bootstrap-5') !!}
  @else
    <ul class="pagination">
      <li class="page-item active"><span class="page-link">1</span></li>
    </ul>
  @endif
</div>


@push('styles')
<link href="{{ asset('css/paginacionUsuarios.css') }}" rel="stylesheet">
@endpush