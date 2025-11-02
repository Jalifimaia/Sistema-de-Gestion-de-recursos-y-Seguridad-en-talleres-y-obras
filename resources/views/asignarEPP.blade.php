@extends('layouts.app')

@section('template_title')
    Asignar EPP
@endsection

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Asignar EPP a trabajador</h3>

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

        <select id="estado_filtro" class="form-select">
            <option value="" selected disabled>-- Filtrar por estado --</option>
            <option value="alta">Trabajadores en alta</option>
            <option value="standby">Trabajadores en stand by</option>
        </select>


        <!-- Trabajador -->
        <div class="mb-3">
            <label for="usuario_id" class="form-label">Trabajador</label>
            <select name="usuario_id" id="usuario_id" class="form-select select2" required>
                <option value="">-- Seleccionar trabajador --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}">
                        {{ $usuario->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- EPP ya asignado -->
        <div id="epp-asignado" class="alert alert-info d-none">
            <strong>EPP ya asignado:</strong>
            <ul id="epp-lista" class="mb-0"></ul>
        </div>

        <!-- Tipos de EPP -->
        @foreach (['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'] as $tipo)
            <div class="mb-3">
                <label for="{{ $tipo }}" class="form-label">{{ ucfirst($tipo) }}</label>
                <select name="{{ $tipo }}" id="{{ $tipo }}" class="form-select select2-epp" data-tipo="{{ $tipo }}">
                    <option value="">-- Seleccionar {{ $tipo }} disponible --</option>
                </select>
                <div class="text-danger small d-none" id="alert-{{ $tipo }}">Ya tiene {{ $tipo }} asignado</div>
            </div>
        @endforeach

        <!-- Fecha de asignación -->
        <div class="mb-3">
            <label for="fecha_asignacion" class="form-label">Fecha de asignación</label>
            <div class="input-group" onclick="this.querySelector('input').showPicker()" tabindex="0" role="button" onkeydown="if(event.key==='Enter'||event.key===' ') this.querySelector('input').showPicker()">
                <input type="date" name="fecha_asignacion" id="fecha_asignacion" class="form-control" required value="{{ now()->toDateString() }}">
            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectEstado = document.getElementById('estado_filtro');
    const selectTrabajador = document.getElementById('usuario_id');
    const eppBox = document.getElementById('epp-asignado');
    const eppList = document.getElementById('epp-lista');
    const tipos = ['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'];

    function cargarEPP(userId) {
        if (!userId) return;

        fetch(`/epp/asignados/${userId}`)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data)) return;

                const asignados = data.map(epp => epp.tipo.toLowerCase());
                eppList.innerHTML = data.length
                    ? data.map(epp => `<li>${epp.tipo}: ${epp.serie}</li>`).join('')
                    : '<li>No tiene EPP asignado</li>';
                eppBox.classList.remove('d-none');

                tipos.forEach(tipo => {
                    const select = document.getElementById(tipo);
                    const alerta = document.getElementById('alert-' + tipo);

                    if (asignados.includes(tipo)) {
                        alerta.classList.remove('d-none');
                        select.disabled = true;
                    } else {
                        alerta.classList.add('d-none');
                        select.disabled = false;
                    }

                    fetch(`/epp/disponibles/${tipo}`)
                        .then(res => res.json())
                        .then(opciones => {
                            select.innerHTML = `<option value="">-- Seleccionar ${tipo} disponible --</option>`;
                            opciones.forEach(epp => {
                                select.innerHTML += `<option value="${epp.id}">${epp.serie}</option>`;
                            });
                        });
                });
            });
    }

    selectTrabajador.addEventListener('change', function () {
        cargarEPP(this.value);
    });

    selectEstado.addEventListener('change', function () {
        const estado = this.value;
        if (!estado) return;

        fetch(`/trabajadores/por-estado/${estado}`)
            .then(res => res.json())
            .then(data => {
                selectTrabajador.innerHTML = '<option value="">-- Seleccionar trabajador --</option>';
                data.forEach(user => {
                    selectTrabajador.innerHTML += `<option value="${user.id}">${user.name}</option>`;
                });

                // Cargar EPP del primer trabajador automáticamente
                eppList.innerHTML = '';
                eppBox.classList.add('d-none');
                tipos.forEach(tipo => {
                    const select = document.getElementById(tipo);
                    select.innerHTML = `<option value="">-- Seleccionar ${tipo} disponible --</option>`;
                    select.disabled = false;
                    document.getElementById('alert-' + tipo).classList.add('d-none');
                });
            });
    });
});

</script>
@endpush

