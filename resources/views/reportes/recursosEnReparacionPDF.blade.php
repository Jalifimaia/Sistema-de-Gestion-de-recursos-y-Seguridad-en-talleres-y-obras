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
            @if($fecha_inicio) desde <strong>{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</strong> @endif
            @if($fecha_fin) hasta <strong>{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</strong> @endif
        </h4>
    @endif

    <div class="resumen">
        <strong>Total en reparación:</strong> {{ $total ?? $recursos->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Recurso</th>
                <th>Número de serie</th>
                <th>Fecha adquisición</th>
                <th>Fecha marcado en reparación</th>
                <th>Costo unitario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recursos as $r)
            <tr>
                <td>
                    {{ $r->recurso ?? $r->nombre ?? '-' }}
                    [{{ $r->subcategoria ?? 'Sin subcategoría' }}]
                </td>
                <td>{{ $r->nro_serie }}</td>
                <td>
                    @if(!empty($r->fecha_adquisicion))
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $r->fecha_adquisicion, 'UTC')
                            ->setTimezone(config('app.timezone'))
                            ->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(!empty($r->estado_actualizado_en))
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $r->estado_actualizado_en, 'UTC')
                            ->setTimezone(config('app.timezone'))
                            ->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td>${{ number_format($r->costo_unitario, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="resumen">
        <strong>Total en reparación:</strong> {{ $recursos->count() }} recursos <br>
        <strong>Valor económico total:</strong> ${{ number_format($totalPerdido, 2, ',', '.') }}
    </div>

</body>
</html>
