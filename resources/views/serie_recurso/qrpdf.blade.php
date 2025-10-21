<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Etiqueta QR</title>
  <style>
    body { font-family: sans-serif; text-align: center; padding: 20px; }
    .qr { margin-top: 20px; }
    svg { width: 150px; height: 150px; }
  </style>
</head>
<body>
  <h3>{{ $serie->nro_serie }}</h3>
  <p><strong>Recurso:</strong> {{ $serie->recurso->nombre ?? 'Sin nombre' }}</p>
  
<div class="qr">
  <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR" width="150" height="150">
</div>


  <p style="margin-top: 10px;">{{ $serie->codigo_qr }}</p>
</body>
</html>
