<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Recursos en Reparación</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2, h4 { text-align: center; margin-bottom: 10px; }
        .resumen { margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <h2>Reporte de recursos en reparación</h2>

    @if($fecha_inicio || $fecha_fin)
        <h4>Rango seleccionado:
            @if($fecha_inicio) desde <strong>{{ $fecha_inicio }}</strong> @endif
            @if($fecha_fin) hasta <strong>{{ $fecha_fin }}</strong> @endif
        </h4>
    @endif

    <div class="resumen">
        <strong>Total en reparación:</strong> {{ $total }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Recurso</th>
                <th>Número de serie</th>
                <th>Fecha adquisición</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recursos as $r)
            <tr>
                <td>{{ $r->nombre }}</td>
                <td>{{ $r->nro_serie }}</td>
                <td>{{ $r->fecha_adquisicion }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
