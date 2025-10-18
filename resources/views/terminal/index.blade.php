<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Herramientas y EPP</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/terminal.css') }}">
</head>
<body>

  <div class="container-kiosk">

    <!-- contenedor de mensajes -->
    <div id="mensaje-kiosco" class="alert alert-warning text-center d-none" role="alert">
      <span id="mensaje-kiosco-texto"></span>
      <button type="button" class="btn-close float-end" onclick="document.getElementById('mensaje-kiosco').classList.add('d-none')"></button>
    </div>

    <!-- Paso 1: Identificar trabajador -->
    <div id="step1" class="step active">
      <h2 class="mb-4 text-center">👷 Identificar Trabajador</h2>
      <input type="text" id="dni" class="form-control form-control-lg mb-4" placeholder="Ingresar DNI">
      <button class="btn btn-primary btn-lg" onclick="identificarTrabajador()">Continuar</button>
    </div>

    <!-- Paso 2: Elegir acción -->
    <div id="step2" class="step">
      <h2 id="saludo-trabajador" class="mb-2 text-center">Hola 👷</h2>
      <h4 class="mb-4 text-center">¿Qué querés hacer?</h4>
      <button class="btn btn-outline-success btn-lg" onclick="setModoEscaneo('manual')">📦 Tengo la herramienta en mano</button>
      <button class="btn btn-outline-primary btn-lg" onclick="nextStep(5)">🛠️ Quiero solicitar una herramienta</button>
      <button class="btn btn-info btn-lg" onclick="cargarRecursos()" data-bs-toggle="modal" data-bs-target="#modalRecursos">📋 Ver recursos asignados</button>
      <button class="btn btn-secondary btn-lg" onclick="nextStep(1)">🔙 Volver</button>
    </div>

    <!-- Paso 3: Escaneo QR -->
    <div id="step3" class="step">
      <h2 id="titulo-step3" class="mb-4 text-center">📷 Escanear Recurso</h2>

      <h5 id="texto-camara-activa" class="text-center mb-3 d-none">📡 Cámara activa — escaneá el código QR</h5>

      <div id="qr-reader" class="rounded border shadow-sm" style="width: 100%; max-width: 400px; margin: auto;"></div>

      <div class="text-center mt-3">
        <button id="btn-escanear-qr" class="btn btn-outline-primary btn-lg" onclick="activarEscaneoQR()">📡 Escanear QR</button>
        <button id="btn-cancelar-qr" class="btn btn-outline-danger btn-lg d-none" onclick="cancelarEscaneoQR()">❌ Cancelar escaneo</button>
      </div>

      <p class="text-center mt-4">Si no tiene QR, podés solicitar la herramienta manualmente.</p>
      <div class="text-center">
        <button class="btn btn-outline-dark btn-lg" onclick="detenerEscaneoQR(5)">Solicitar manualmente</button>
        <button class="btn btn-secondary btn-lg" onclick="detenerEscaneoQR(2)">🔙 Volver</button>

      </div>
    </div>

    <!-- Paso 5: Categoría -->
    <div id="step5" class="step">
      <h2 class="mb-4 text-center">📦 Seleccionar Categoría</h2>
      <div id="categoria-buttons"></div>
      <button class="btn btn-secondary btn-lg" onclick="nextStep(2)">🔙 Volver</button>
    </div>

    <!-- Paso 6: Subcategoría -->
    <div id="step6" class="step">
      <h2 class="mb-4 text-center">🔧 Seleccionar Subcategoría</h2>
      <div id="subcategoria-buttons"></div>
      <button class="btn btn-secondary btn-lg mt-3" onclick="nextStep(5)">🔙 Volver</button>
    </div>

    <!-- Paso 7: Recurso -->
    <div id="step7" class="step">
      <h2 class="mb-4 text-center">🛠️ Seleccioná el recurso</h2>
      <div id="recurso-buttons"></div>
      <button class="btn btn-secondary btn-lg mt-3" onclick="nextStep(6)">🔙 Volver</button>
    </div>

    <!-- Paso 8: Serie -->
    <div id="step8" class="step">
      <h2 class="mb-4 text-center">🔢 Seleccioná la serie disponible</h2>
      <div id="serie-buttons"></div>
      <button class="btn btn-secondary btn-lg mt-3" onclick="nextStep(7)">🔙 Volver</button>
    </div>

  </div>

  <!-- Modal -->
  <div class="modal fade" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">🧰 Recursos asignados</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th>Subcategoría / Recurso</th>
                  <th>Serie</th>
                  <th>Fecha de préstamo</th>
                  <th>Fecha de devolución</th>
                </tr>
              </thead>
              <tbody id="tablaRecursos"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/terminal-debug.js') }}"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>

</body>
</html>
