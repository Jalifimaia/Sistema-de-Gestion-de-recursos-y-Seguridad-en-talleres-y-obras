@if($series->isEmpty())
  <div class="alert alert-warning">No hay series registradas.</div>
@else
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    @foreach($series as $serie)   
      <div class="col">
        <div class="card shadow-sm h-100">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <h5 class="card-title">{{ $serie->nro_serie }}</h5>
              <p class="card-text">
                <strong>Recurso:</strong> 
                {{ $serie->recurso->nombre ?? 'Sin nombre' }}
                [{{ $serie->recurso->subcategoria->nombre ?? 'Sin subcategoría' }}]
              </p>

              @if($serie->codigo_qr)
                <div class="text-center mt-3">
                  {!! QrCode::size(100)->generate($serie->codigo_qr) !!}
                </div>
              @endif
            </div>

            <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">
              <button class="btn btn-outline-dark btn-sm copiar-btn d-flex align-items-center" data-codigo="{{ $serie->codigo_qr }}">
                <img src="{{ asset('images/copiar.svg') }}" alt="Copiar" class="me-2" style="width: 18px; height: 18px;">
                Copiar código
              </button>

              <a href="{{ route('series.qr.pdf', $serie->id) }}" class="btn btn-pdf btn-sm d-flex align-items-center" target="_blank">
                <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2" style="width: 18px; height: 18px;">
                Exportar PDF
              </a>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4 d-flex justify-content-between align-items-center">
    <div class="text-muted small">
      Mostrando {{ $series->firstItem() }} a {{ $series->lastItem() }} de {{ $series->total() }} series
    </div>
    <div>
      {{ $series->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endif
