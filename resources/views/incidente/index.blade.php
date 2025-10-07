@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Incidentes registrados</h1>

    <a href="{{ route('incidente.create') }}" class="btn btn-primary mb-3">
        Registrar nuevo incidente
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Recurso</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Supervisor</th>
                <th>Rol</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <tbody>
            @foreach($incidentes as $i)
                <tr>
                    <td>{{ $i->id }}</td>
                    <td>
                        {{ $i->detalle->serieRecurso->recurso->nombre ?? '—' }}
                        <br>
                        <small>Serie: {{ $i->detalle->serieRecurso->numero_serie ?? '—' }}</small>
                    </td>
                    <td>{{ $i->descripcion }}</td>
                    <td><span class="badge bg-secondary">{{ $i->estadoIncidente->nombre_estado ?? '—' }}</span></td>
                    <td>{{ $i->supervisor->name ?? '—' }}</td>
                    <td>{{ $i->supervisor->rol->nombre ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->fecha_incidente)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
