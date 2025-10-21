@extends('layouts.app')

@section('title', 'Series con QR')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">📦 Series con código QR</h3>

  <div class="mb-3 text-end">
    <a href="{{ route('series.qr.lote.pdf') }}" class="btn btn-outline-primary" target="_blank">
      🖨️ Imprimir QR en lote
    </a>
  </div>

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
                  <strong>Recurso:</strong> {{ $serie->recurso->nombre ?? 'Sin nombre' }}
                </p>
                @if($serie->codigo_qr)
                  <div class="text-center mt-3">
                    {!! QrCode::size(150)->generate($serie->codigo_qr) !!}
                  </div>
                @endif
              </div>
              <div class="mt-4 text-center">
                <button class="btn btn-outline-dark btn-sm me-2 copiar-btn" 
                        data-codigo="{{ $serie->codigo_qr }}">
                  📋 Copiar código
                </button>

                <a href="{{ route('series.qr.pdf', $serie->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                  📄 Exportar PDF
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.copiar-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const codigo = btn.getAttribute('data-codigo');
        navigator.clipboard.writeText(codigo).then(() => {
          const original = btn.innerHTML;
          btn.innerHTML = '✅ Copiado';
          setTimeout(() => {
            btn.innerHTML = original;
          }, 1500);
        }).catch(() => {
          alert('No se pudo copiar el código.');
        });
      });
    });
  });
</script>
@endsection
