@extends('layouts.app')

@section('title', 'Imprimir QR en lote')

@section('content')
<div class="container py-4">
  <h3 class="mb-4">🖨️ Imprimir etiquetas QR</h3>

  {{-- Filtros --}}
  <form method="GET" action="{{ route('series.qr.lote') }}" class="mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="desde">Desde fecha</label>
        <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
      </div>
      <div class="col-md-3">
        <label for="hasta">Hasta fecha</label>
        <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
      </div>
      <div class="col-md-3">
        <label for="recurso_id">Recurso</label>
        <select name="recurso_id" id="recurso_id" class="form-select">
          <option value="">Todos</option>
          @foreach(\App\Models\Recurso::all() as $recurso)
            <option value="{{ $recurso->id }}" @selected(request('recurso_id') == $recurso->id)>
              {{ $recurso->nombre }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label for="subcategoria_id">Subcategoría</label>
        <select name="subcategoria_id" id="subcategoria_id" class="form-select">
          <option value="">Todas</option>
          @foreach(\App\Models\Subcategoria::all() as $sub)
            <option value="{{ $sub->id }}" @selected(request('subcategoria_id') == $sub->id)>
              {{ $sub->nombre }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-12 text-end">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('series.qr.lote.pdf', request()->query()) }}" class="btn btn-outline-secondary" target="_blank">
          📄 Exportar PDF con filtros
        </a>
      </div>
    </div>
  </form>

  {{-- Etiquetas QR --}}
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
    @foreach($series as $serie)
      <div class="col">
        <div class="border p-2 text-center">
          <strong>{{ $serie->nro_serie }}</strong><br>
          <small>{{ $serie->recurso->nombre ?? 'Sin nombre' }}</small><br>
          <small>{{ $serie->created_at->format('d/m/Y') }}</small>
          <div class="mt-2">
            {!! QrCode::size(100)->generate($serie->codigo_qr) !!}
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
