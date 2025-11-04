@extends('layouts.app')

@section('title', 'Series con QR')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">📦 Series con código QR</h3>

  <!-- 🔍 Buscador por nro_serie con botón -->
  <div class="input-group mb-3">
    <input type="text" id="busquedaSerie" class="form-control" placeholder="🔍 Buscar por iniciales del número de serie...">
    <button id="btnBuscarSerie" class="btn btn-primary">Buscar</button>
  </div>

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
                  <strong>Recurso:</strong> {{ $serie->recurso->nombre ?? 'Sin nombre' }}<br>
                  <strong>Código:</strong> {{ $serie->serie_recurso_codigo ?? '—' }}
                </p>

                @if($serie->codigo_qr)
                  <div class="text-center mt-3">
                    {!! QrCode::size(100)->generate($serie->codigo_qr) !!}
                  </div>
                @endif
              </div>

              <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">
                <button class="btn btn-outline-dark btn-sm copiar-btn" 
                        data-codigo="{{ $serie->codigo_qr }}">
                  📋 Copiar código
                </button>

                <a href="{{ route('series.qr.pdf', $serie->id) }}" 
                   class="btn btn-outline-secondary btn-sm" target="_blank">
                  📄 Exportar PDF
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
    console.log('✅ Script cargado');

    // 📋 Copiar código QR
    document.querySelectorAll('.copiar-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const codigo = btn.getAttribute('data-codigo');
        console.log('📋 Copiando:', codigo);

        navigator.clipboard.writeText(codigo).then(() => {
          const original = btn.innerHTML;
          btn.innerHTML = '✅ Copiado';
          setTimeout(() => {
            btn.innerHTML = original;
          }, 1500);
        }).catch(err => {
          console.error('❌ Error al copiar:', err);
          alert('No se pudo copiar el código.');
        });
      });
    });

    // 🔍 Buscar solo al presionar Enter o botón
    const input = document.getElementById('busquedaSerie');
    const boton = document.getElementById('btnBuscarSerie');
    
    input.value = new URLSearchParams(window.location.search).get('search') || '';

    const buscar = () => {
    const valor = input.value.trim();
    const baseUrl = window.location.pathname;

    if (valor) {
      window.location.href = `${baseUrl}?search=${encodeURIComponent(valor)}`;
    } else {
      window.location.href = baseUrl; // 🔄 sin filtro, muestra todo
    }
  };


    input.addEventListener('keypress', e => {
      if (e.key === 'Enter') buscar();
    });

    boton.addEventListener('click', buscar);
  });



</script>
@endpush

