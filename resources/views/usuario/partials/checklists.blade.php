<table class="table table-striped table-hover table-bordered custom-table table-naranja text-center">
  <thead class="table-orange">
    <tr>
      <th>Fecha</th>
      <th>Lentes</th>
      <th>Botas</th>
      <th>Chaleco</th>
      <th>Guantes</th>
      <th>Arnés</th>
      <th>Altura</th>
      <th>Observaciones</th>
    </tr>
  </thead>
  <tbody>
    @forelse($items as $c)
      <tr>
        <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
        <td>
          @if($c->lentes)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td>
          @if($c->botas)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td>
          @if($c->chaleco)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td>
          @if($c->guantes)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td>
          @if($c->arnes)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td>
          @if($c->es_en_altura)
            <img src="{{ asset('images/checkCheck.svg') }}" alt="Sí" class="icon-epp icon-check">
          @else
            <img src="{{ asset('images/crossCross.svg') }}" alt="No" class="icon-epp">
          @endif
        </td>
        <td style="min-width: 200px;">{{ $c->observaciones ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="8" class="text-muted">No hay checklist registrados.</td></tr>
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