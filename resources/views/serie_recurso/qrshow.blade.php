@extends('layouts.app')

@section('title', 'QR de Serie')

@section('content')
<div class="container py-4 text-center">
  <h3 class="mb-3">QR de la serie: {{ $serie->nro_serie }}</h3>
  <p><strong>Recurso:</strong> {{ $serie->recurso->nombre ?? 'Sin nombre' }}</p>

  @if($serie->codigo_qr)
    <div class="my-4">
      {!! QrCode::size(250)->generate($serie->codigo_qr) !!}
    </div>
    <a href="{{ route('series.qr.pdf', $serie->id) }}" class="btn btn-outline-secondary" target="_blank">
      📄 Exportar PDF
    </a>
  @else
    <div class="alert alert-warning">Esta serie no tiene código QR asignado.</div>
  @endif
</div>
@endsection
