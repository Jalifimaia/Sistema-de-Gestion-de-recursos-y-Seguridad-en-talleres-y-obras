@extends('layouts.app')

@section('title', 'Series con código QR')

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
        <h3 class="mb-0 fw-bold">Series con código QR</h3>
      </div>
    </div>

    <!-- Botón imprimir lote -->
     <!-- SE DESACTIVO POR LAS DUDAS -->
    <!--<a href="{ route('series.qr.lote.pdf', ['page' => request('page', 1)]) }}"-->
    <a 
       class="btn btn-print d-flex align-items-center mt-2 mt-md-0" target="_blank">
      <img src="{{ asset('images/print.svg') }}" alt="Imprimir" class="me-2" style="width: 20px; height: 20px;">
      Imprimir QR en lote
    </a>
  </div>

  <!-- 🔍 Buscador por nro_serie -->
  <div class="input-group mb-3 mt-4">
    <input type="text" id="busquedaSerie" class="form-control buscador-con-icono"
           placeholder="Buscar por categoría, subcategoría, nombre del recurso o iniciales del número de serie...">
  </div>

  <!-- Contenedor dinámico -->
  <div id="seriesContainer">
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
                  <button class="btn btn-outline-dark btn-sm copiar-btn d-flex align-items-center btn-accion-compact"
                          data-codigo="{{ $serie->codigo_qr }}">
                    <img src="{{ asset('images/copiar.svg') }}" alt="Copiar" class="me-2" style="width: 18px; height: 18px;">
                    Copiar código
                  </button>

                  <a href="{{ route('series.qr.pdf', $serie->id) }}"
                     class="btn btn-pdf btn-sm d-flex align-items-center" target="_blank">
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
          {{ $series->links() }}
        </div>
      </div>
    @endif
  </div>

  <!-- Toast para copiar -->
  <div id="toastQR" class="toast position-fixed bottom-0 end-0 m-3 text-bg-success"
       role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">Código QR copiado al portapapeles</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/qr.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('busquedaSerie');
  const container = document.getElementById('seriesContainer');
  let timer;

  if (!input || !container) {
    console.error('No se encontró input o container');
    return;
  }

  const debounce = (fn, delay = 400) => {
    clearTimeout(timer);
    timer = setTimeout(fn, delay);
  };

  function cargarSeries(url) {
    console.log('fetching:', url);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(res => res.json())
      .then(data => {
        if (!data.html) {
          console.error('Respuesta sin html:', data);
          return;
        }
        container.innerHTML = data.html;
        engancharLinks();
        engancharCopiar();
      })
      .catch(err => console.error('Error fetch:', err));
  }

  function engancharLinks() {
    // Usar event delegation en el container en lugar de enganchar cada link individualmente
    const paginationContainer = container.querySelector('.pagination');
    if (!paginationContainer) return;

    // Remover listeners anteriores si existen
    const newPaginationContainer = paginationContainer.cloneNode(true);
    paginationContainer.parentNode.replaceChild(newPaginationContainer, paginationContainer);

    // Agregar listener al contenedor de paginación
    newPaginationContainer.addEventListener('click', (e) => {
      // Buscar el enlace clickeado o su padre si es un span/svg
      const link = e.target.closest('a');
      
      if (link && link.href) {
        e.preventDefault();
        e.stopPropagation();
        
        // Extraer el número de página del href original
        const urlObj = new URL(link.href);
        const page = urlObj.searchParams.get('page') || 1;
        
        // Construir URL hacia la ruta AJAX
        const searchValue = input.value.trim();
        let ajaxUrl = `/series/buscar?page=${page}`;
        
        if (searchValue) {
          ajaxUrl += `&search=${encodeURIComponent(searchValue)}`;
        }
        
        cargarSeries(ajaxUrl);
      }
    });
  }

  function engancharCopiar() {
    container.querySelectorAll('.copiar-btn').forEach(btn => {
      // Remover listeners anteriores
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);
      
      newBtn.addEventListener('click', async () => {
        const codigo = newBtn.getAttribute('data-codigo');
        if (!codigo) return;
        try {
          await navigator.clipboard.writeText(codigo);
          const toastEl = document.getElementById('toastQR');
          if (toastEl) new bootstrap.Toast(toastEl).show();
        } catch (err) {
          console.error('Error al copiar:', err);
        }
      });
    });
  }

  input.addEventListener('input', () => {
    debounce(() => {
      const valor = input.value.trim();
      const url = `/series/buscar?search=${encodeURIComponent(valor)}`;
      cargarSeries(url);
    }, 400);
  });

  // Inicial
  engancharLinks();
  engancharCopiar();
});
</script>
@endpush
