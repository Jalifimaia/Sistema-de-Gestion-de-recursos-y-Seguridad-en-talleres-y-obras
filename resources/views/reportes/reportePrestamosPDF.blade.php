<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Préstamos</title>
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
    <h2> Reporte de préstamos registrados</h2>

    @if($fecha_inicio || $fecha_fin)
        <h4>Rango seleccionado:
            @if($fecha_inicio) desde <strong>{{ $fecha_inicio }}</strong> @endif
            @if($fecha_fin) hasta <strong>{{ $fecha_fin }}</strong> @endif
        </h4>
    @endif

    <div class="resumen">
        <strong>Total de préstamos:</strong> {{ $total }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha préstamo</th>
                <th>Trabajador</th>
                <th>Estado</th>
                <th>Duración (Días)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prestamos as $p)
            <tr>
                <td>{{ $p->fecha_prestamo }}</td>
                <td>{{ $p->trabajador }}</td>
                <td>{{ $p->estado }}</td>
                <td>
                @if(empty($p->duracion) || $p->duracion === '-' || $p->duracion === '—')
                    <span class="badge bg-warning text-dark">Sin fecha de devolución</span>
                @else
                    {{ $p->duracion }}
                @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
