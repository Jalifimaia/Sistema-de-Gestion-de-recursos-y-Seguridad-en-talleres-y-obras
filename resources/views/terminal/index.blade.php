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
      <button class="btn btn-primary btn-lg mb-3" onclick="identificarTrabajador()">Continuar</button>

      <div class="text-center mt-4">
        <button class="btn btn-outline-primary btn-lg" onclick="activarEscaneoQRLogin()">
          📷 Iniciar sesión con QR
        </button>
      </div>
      
      <div id="qr-login-container" class="mt-3 text-center" style="display:none;">
        <div id="qr-login-reader" style="width:300px; margin:auto;"></div>
        <p class="text-muted small">Apuntá tu QR de identificación</p>
      </div>

      
    </div>

    <!-- Paso 2: Elegir acción -->
    <div id="step2" class="step">
      <h2 id="saludo-trabajador" class="mb-2 text-center">Hola 👷</h2>
      <h4 class="mb-4 text-center">¿Qué querés hacer?</h4>
      <div id="menu-principal-buttons"></div>
    </div>


<!-- Paso 3: Escaneo QR -->
<div id="step3" class="step">
  <h2 id="titulo-step3" class="mb-4 text-center">📷 Escanear Recurso</h2>
  <h5 id="texto-camara-activa" class="text-center mb-3 d-none">📡 Cámara activa — escaneá el código QR</h5>
  <div id="qr-reader" class="rounded border shadow-sm" style="width: 100%; max-width: 400px; margin: auto;"></div>

  <div class="text-center mt-3">
    <button id="btn-escanear-qr" class="btn btn-outline-primary btn-lg d-flex align-items-center justify-content-start m-2 w-100" onclick="activarEscaneoQR()">
      <span class="badge-opcion">Opción 1</span>
      <span class="ms-2 flex-grow-1 text-start">📡 Escanear QR</span>
    </button>

    <button id="btn-cancelar-qr" class="btn btn-outline-danger btn-lg d-none m-2 w-100" onclick="cancelarEscaneoQR()">
      ❌ Cancelar escaneo
    </button>
  </div>

  <p class="text-center mt-4">Si no tiene QR, podés solicitar la herramienta manualmente.</p>
  <div class="text-center">
    <button class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-start m-2 w-100" onclick="detenerEscaneoQR(5)">
      <span class="badge-opcion">Opción 2</span>
      <span class="ms-2 flex-grow-1 text-start">Solicitar manualmente</span>
    </button>

    <button class="btn btn-secondary btn-lg d-flex align-items-center justify-content-start m-2 w-100" onclick="detenerEscaneoQR(2)">
      <span class="badge-opcion">Opción 3</span>
      <span class="ms-2 flex-grow-1 text-start">🔙 Volver</span>
    </button>
  </div>
</div>


    <!-- Paso 5: Categoría -->
    <div id="step5" class="step">
      <h2 class="mb-4 text-center">📦 Seleccionar Categoría</h2>
      <div id="categoria-buttons"></div>
      <button class="btn btn-secondary btn-lg" onclick="volverDesdeStep5()">🔙 Volver</button>

      
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
  
 <!-- Modal -->
  <!-- Modal -->
  <div class="modal fade" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRecursosLabel">🧰 Recursos asignados</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <ul class="nav nav-tabs mb-3" id="recursosTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-epp" data-bs-toggle="tab" data-bs-target="#panel-epp" type="button" role="tab">🦺 EPP</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-herramientas" data-bs-toggle="tab" data-bs-target="#panel-herramientas" type="button" role="tab">🔧 Herramientas</button>
            </li>
           
          </ul>

          <div class="tab-content" id="recursosTabContent">
            <!-- Tabla EPP -->
            <div class="tab-pane fade show active" id="panel-epp" role="tabpanel" aria-labelledby="tab-epp">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Subcategoría / Recurso</th>
                      <th>Serie</th>
                      <th>Fecha de préstamo</th>
                      <th>Fecha de devolución</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="tablaEPP"></tbody>
                </table>
              </div>
              <div id="paginadorEPP" class="d-flex flex-wrap justify-content-center mt-3"></div>
            </div>

            <!-- Tabla Herramientas -->
            <div class="tab-pane fade" id="panel-herramientas" role="tabpanel" aria-labelledby="tab-herramientas">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Subcategoría / Recurso</th>
                      <th>Serie</th>
                      <th>Fecha de préstamo</th>
                      <th>Fecha de devolución</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="tablaHerramientas"></tbody>
                </table>
              </div>
              <div id="paginadorHerramientas" class="d-flex flex-wrap justify-content-center mt-3"></div>
            </div>


          </div>
        </div>
      </div>
    </div>
  </div>



<!-- Contenedor de Toasts -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/terminal.js') }}"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>

</body>
</html>
