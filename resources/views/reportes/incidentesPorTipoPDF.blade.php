<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Incidentes por Tipo de Recurso</title>
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
    <h2>Reporte de incidentes por tipo de recurso</h2>

    @if($fecha_inicio || $fecha_fin)
        <h4>Rango seleccionado:
            @if($fecha_inicio) desde <strong>{{ $fecha_inicio }}</strong> @endif
            @if($fecha_fin) hasta <strong>{{ $fecha_fin }}</strong> @endif
        </h4>
    @endif

    <div class="resumen">
        <strong>Total de incidentes:</strong> {{ $total }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo de recurso</th>
                <th>Cantidad de incidentes</th>
                <th>Última fecha de incidente</th>
                <th>Valor económico total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidentes as $item)
            <tr>
                <td>{{ $item->nombre_categoria }}</td>
                <td>{{ $item->cantidad_incidentes }}</td>
                <td>{{ $item->ultima_fecha }}</td>
                <td>${{ number_format($item->costo_total_incidentes, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="resumen">
        <strong>Total de incidentes:</strong> {{ $total }} <br>
        <strong>Impacto económico total:</strong> ${{ number_format($totalEconomico, 2, ',', '.') }}
    </div>


</body>
</html>
