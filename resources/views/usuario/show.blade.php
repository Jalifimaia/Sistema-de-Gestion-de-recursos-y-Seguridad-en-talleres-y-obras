@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
<div class="container">
  <h2>{{ $usuario->name }}</h2>

  <p><strong>Email:</strong> {{ $usuario->email }}</p>
  <p><strong>Rol:</strong> {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</p>
  <p><strong>Último acceso:</strong> 
    {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->diffForHumans() : 'Nunca' }}
  </p>

  {{-- Estado actual --}}
  <p><strong>Estado:</strong>
    @if ($usuario->estado?->nombre === 'Alta')
      <span class="badge bg-success">Activo (Alta)</span>
    @elseif ($usuario->estado?->nombre === 'Baja')
      <span class="badge bg-danger">Inactivo (Baja)</span>
    @elseif ($usuario->estado?->nombre === 'stand by')
      <span class="badge bg-warning text-dark">Stand by</span>
    @else
      <span class="badge bg-secondary">Sin estado</span>
    @endif
  </p>

  <hr>

  <p><strong>Creado por:</strong> {{ $usuario->creador?->name ?? 'Desconocido' }}</p>
  <p><strong>Modificado por:</strong> {{ $usuario->modificador?->name ?? 'Desconocido' }}</p>
  <p><strong>Creado el:</strong> {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
  <p><strong>Modificado el:</strong> {{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>

  @if($usuario->codigo_qr)
  <div class="text-center my-4">
    <h5>Código QR de identificación</h5>
    {!! QrCode::size(200)->generate($usuario->codigo_qr) !!}
    <p class="text-muted small mt-2" id="codigo-qr-texto">{{ $usuario->codigo_qr }}</p>
    <button class="btn btn-outline-primary btn-sm mt-2" onclick="copiarCodigoQR()">📋 Copiar código</button>
  </div>
  @endif
</div>

{{-- Toast Bootstrap --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <div id="toastQR" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        ✅ Código QR copiado al portapapeles
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script>
function copiarCodigoQR() {
  const texto = document.getElementById('codigo-qr-texto').innerText;
  navigator.clipboard.writeText(texto).then(() => {
    const toastEl = document.getElementById('toastQR');
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  }).catch(err => {
    console.error('Error al copiar:', err);
    alert('❌ No se pudo copiar el código');
  });
}
</script>
@endsection
