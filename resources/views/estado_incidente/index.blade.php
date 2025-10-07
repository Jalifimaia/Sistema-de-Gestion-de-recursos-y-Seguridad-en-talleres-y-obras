@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Estados de incidente</h1>

    <a href="{{ route('estado_incidente.create') }}" class="btn btn-primary mb-3">Nuevo estado</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('incidente.create') }}" class="btn btn-primary mb-3">
    Registrar nuevo incidente
    </a>


    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estados as $estado)
                <tr>
                    <td>{{ $estado->id }}</td>
                    <td>{{ $estado->nombre_estado }}</td>
                    <td>
                        <a href="{{ route('estado_incidente.edit', $estado->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('estado_incidente.destroy', $estado->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
