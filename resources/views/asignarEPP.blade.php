@extends('layouts.app')

@section('template_title')
    Asignar EPP
@endsection

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Asignar EPP a trabajadores en stand by</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('epp.asignar.store') }}">

        @csrf

        <!-- Trabajador -->
        <div class="mb-3">
            <label for="usuario_id" class="form-label">Trabajador</label>
            <select name="usuario_id" class="form-select select2" required>
                <option value="">-- Seleccionar trabajador --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}">
                        {{ $usuario->name }} ({{ $usuario->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tipos de EPP -->
        @foreach (['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'] as $tipo)
            <div class="mb-3">
                <label for="{{ $tipo }}" class="form-label">{{ ucfirst($tipo) }}</label>
                <select name="{{ $tipo }}" class="form-select select2-epp" data-tipo="{{ $tipo }}" required></select>
            </div>
        @endforeach

        <!-- Fecha de asignación -->
        <div class="mb-3">
            <label for="fecha_asignacion" class="form-label">Fecha de asignación</label>
            <input type="date" name="fecha_asignacion" class="form-control" required value="{{ now()->toDateString() }}">
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('controlEPP') }}" class="btn btn-outline-secondary">⬅️ Volver</a>
            <button type="submit" class="btn btn-primary">Guardar asignación</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="{{ asset('js/asignar.js') }}"></script>
@endpush
