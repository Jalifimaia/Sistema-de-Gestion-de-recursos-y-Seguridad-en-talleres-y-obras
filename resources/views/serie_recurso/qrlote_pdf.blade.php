<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Etiquetas QR</title>
  <style>
    body {
      font-family: sans-serif;
      margin: 10px;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr); /* 5 columnas por fila */
      gap: 10px; /* espacio entre celdas */
    }
    .item {
      border: 1px solid #ccc;
      padding: 6px;
      text-align: center;
      font-size: 12px;
    }
    .item img {
      width: 80px;
      height: 80px;
    }
  </style>
</head>
<body>
  <h3 style="text-align:center;">Etiquetas QR</h3>
  <div class="grid">
    @foreach($series as $serie)
      <div class="item">
        <strong>{{ $serie->nro_serie }}</strong><br>
        <small>{{ $serie->recurso->nombre ?? 'Sin nombre' }}</small><br>


        @if(!empty($qrBase64s[$serie->id]))
          <img src="data:image/png;base64,{{ $qrBase64s[$serie->id] }}" alt="QR">
        @else
          <span style="color:red;">QR no disponible</span>
        @endif
      </div>
    @endforeach
  </div>
</body>
</html>
