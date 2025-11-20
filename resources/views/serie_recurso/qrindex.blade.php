@extends('layouts.app')

@section('title', 'Series con QR')

@section('content')
<div class="container py-4">

<div class="d-flex align-items-center justify-content-between flex-wrap mb-4">
  <div class="d-flex align-items-center gap-3">
    <!-- Botón Volver -->
    <a href="{{ route('inventario.index') }}" class="btn btn-volver d-flex align-items-center">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <!-- Título con ícono -->
    <div class="d-flex align-items-center gap-2">
      <img src="{{ asset('images/qrr.svg') }}" alt="QR" style="width: 28px; height: 28px;">
      <h3 class="mb-0 fw-bold">Series con código QR</h3>
    </div>
  </div>

  <!-- Botón imprimir lote -->
  <a href="{{ route('series.qr.lote.pdf', ['page' => request('page', 1)]) }}"
     class="btn btn-print d-flex align-items-center mt-2 mt-md-0" target="_blank">
    <img src="{{ asset('images/print.svg') }}" alt="Imprimir" class="me-2" style="width: 20px; height: 20px;">
    Imprimir QR en lote
  </a>
</div>


<!-- 🔍 Buscador por nro_serie con botón -->
  <div class="input-group mb-3 mt-4">
    <input type="text" id="busquedaSerie" class="form-control" placeholder="Buscar por categoría, subcategoría, nombre del recurso o iniciales del número de serie...">
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

    <div class="mt-4 d-flex justify-content-center">
      {{ $series->links() }}
    </div>
  @endif
</div>
@endsection

@push('styles')
<link href="{{ asset('css/qr.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('busquedaSerie');
  if (!input) return;

  // Pre-cargar valor desde la URL
  const params = new URLSearchParams(window.location.search);
  input.value = params.get('search') || '';

  let timer;
  const debounce = (fn, delay = 400) => {
    clearTimeout(timer);
    timer = setTimeout(fn, delay);
  };

  input.addEventListener('input', () => {
    debounce(() => {
      const valor = input.value.trim();
      const url = new URL(window.location.href);
      const usp = url.searchParams;

      if (valor) {
        usp.set('search', valor);
        usp.delete('page'); // reiniciar a página 1
      } else {
        usp.delete('search');
        usp.delete('page');
      }

      window.location.search = usp.toString();
    }, 400);
  });
});
</script>
@endpush



