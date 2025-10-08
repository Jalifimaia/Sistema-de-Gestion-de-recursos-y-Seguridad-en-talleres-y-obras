    <div class="d-flex justify-content-center gap-2 mb-4">
    <a class="btn btn-outline-primary {{ request()->is('supervisor/checklist-epp') ? 'active' : '' }}" href="{{ url('supervisor/checklist-epp') }}">Checklist EPP</a>

    <a class="btn btn-outline-primary {{ request()->is('supervisor/control-herramientas') ? 'active' : '' }}" href="{{ url('supervisor/control-herramientas') }}">Control Herramientas</a>

    <a class="btn btn-outline-primary {{ request()->is('supervisor/registrar-incidente') ? 'active' : '' }}" href="{{ url('supervisor/registrar-incidente') }}">Registrar Incidente</a>

    <a class="btn btn-outline-primary {{ request()->is('supervisor/reportes') ? 'active' : '' }}" href="{{ url('supervisor/reportes') }}">Reportes</a>
    </div> 