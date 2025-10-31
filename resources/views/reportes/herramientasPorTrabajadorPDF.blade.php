<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Herramientas por Trabajador</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Herramientas por Trabajador</h1>

    <p><strong>Desde:</strong> {{ $fecha_inicio ?? '—' }} &nbsp;&nbsp;
       <strong>Hasta:</strong> {{ $fecha_fin ?? '—' }}</p>

    <table>
        <thead>
            <tr>
                <th>Trabajador</th>
                <th>Herramienta</th>
                <th>Subcategoría</th>
                <th>N° de Serie</th>
                <th>Fecha de Adquisición</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($herramientas as $item)
                <tr>
                    <td>{{ $item->trabajador }}</td>
                    <td>{{ $item->herramienta }}</td>
                    <td>{{ $item->subcategoria ?? '—' }}</td>
                    <td>{{ $item->nro_serie ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->fecha_adquisicion)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <strong>Total de herramientas:</strong> {{ $total }}
    </div>
</body>
</html>
