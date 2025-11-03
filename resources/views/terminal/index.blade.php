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
      <h2 class="mb-4 text-center">Identificar Trabajador</h2>

      <input type="text" id="dni" class="form-control form-control-lg mb-4" placeholder="Ingresar DNI">
      <small class="text-muted d-block text-start mb-3">Podes dictar tu DNI como por ejemplo : “20 583 321”</small>


      <button class="btn btn-primary btn-lg mb-3" onclick="identificarTrabajador()">Continuar</button>
      <button id="btnBorrarDNI" class="btn btn-danger btn-lg mb-3" onclick="borrarDNI()">Borrar DNI</button>


      <div class="text-center mt-4">
        <button class="btn btn-primary btn-lg" onclick="activarEscaneoQRLogin()">
           Iniciar sesión con QR
        </button>
      </div>
      
      <div id="qr-login-container" class="mt-3 text-center" style="display:none;">
        <div id="qr-login-reader" style="width:300px; margin:auto;"></div>
        <p class="text-muted small">Apuntá tu QR de identificación</p>
      </div>

      
    </div>

    <!-- Paso 2: Elegir acción -->
    <div id="step2" class="step">
      <h2 id="saludo-trabajador" class="mb-2 text-center">Hola </h2>
      <h4 class="mb-4 text-center">¿Qué querés hacer?</h4>
      <div id="menu-principal-buttons"></div>
    </div>


<!-- Paso 3: Escaneo QR -->
<div id="step3" class="step">
  <h2 id="titulo-step3" class="mb-4 text-center">Escanear Recurso</h2>
  <h5 id="texto-camara-activa" class="text-center mb-3 d-none">Cámara activa — escaneá el código QR</h5>
  <div id="qr-reader" class="rounded border shadow-sm" style="width: 100%; max-width: 400px; margin: auto;"></div>

  <div class="text-center mt-3">
    <button id="btn-escanear-qr" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-start m-2 w-100" onclick="activarEscaneoQRregistroRecursos()">
      <span class="badge-opcion">Opción 1</span>
      <span class="ms-2 flex-grow-1 text-start">Escanear QR</span>
    </button>

    <button id="btn-cancelar-qr" class="btn btn-outline-danger btn-lg d-none m-2 w-100" onclick="cancelarEscaneoQRregistroRecursos()">
      Cancelar escaneo
    </button>
  </div>

  <div class="text-center">
    <button class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-start m-2 w-100" onclick="detenerEscaneoQRregistroRecursos(5)">
      <span class="badge-opcion">Opción 2</span>
      <span class="ms-2 flex-grow-1 text-start">Solicitar manualmente</span>
    </button>

    <div class="text-start">
      <button class="btn btn-primary btn-lg mt-3" onclick="detenerEscaneoQRregistroRecursos(2)">Volver</button>
    </div>

  </div>
</div>


    <!-- Paso 5: Categoría -->
    <div id="step5" class="step">
      <h2 class="mb-4 text-center">Seleccionar Categoría</h2>
      <div id="categoria-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3" onclick="volverDesdeStep5()">Volver</button>

      
    </div>

    <!-- Paso 6: Subcategoría -->
    <div id="step6" class="step">
      <h2 class="mb-4 text-center">Seleccionar Subcategoría</h2>
      <div id="subcategoria-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3" onclick="nextStep(5)">Volver</button>
      
      <div id="subcategoria-buttons"></div>
      <div id="paginadorSubcategorias" class="d-flex justify-content-center mt-3"></div>
    </div>

    <!-- Paso 7: Recurso -->
    <div id="step7" class="step">
      <h2 class="mb-4 text-center">Seleccioná el recurso</h2>
      <div id="recurso-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3" onclick="nextStep(6)">Volver</button>
      
      <div id="recurso-buttons"></div>
      <div id="paginadorRecursos" class="d-flex justify-content-center mt-3"></div>
    </div>

    <!-- Paso 8: Serie -->
    <div id="step8" class="step">
      <h2 class="mb-4 text-center">Seleccioná la serie disponible</h2>
      <div id="serie-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3" onclick="nextStep(7)">Volver</button>
      <div id="serie-buttons"></div>
      <div id="paginadorSeries" class="d-flex justify-content-center mt-3"></div>

    </div>

  <!-- Paso 9: Devolución con escaneo QR -->
    <div id="step9" class="step d-none">
      <h3 class="mb-3 text-center">Devolución de recurso</h3>
      <p class="text-center">Muestre el QR del recurso con serie <strong id="serieEsperadaQR"></strong></p>

      <!-- Contenedor del escáner QR -->
      <div class="d-flex justify-content-center">
        <div id="qr-reader-devolucion" style="width: 300px; height: 300px; margin: auto;"></div>

      </div>

      <!-- Indicador de cámara activa -->
      <div id="texto-camara-activa-devolucion" class="text-muted text-center mt-2 d-none">Cámara activa</div>

      <!-- Botón para cancelar escaneo -->
      <div class="text-center mt-2">
        <button id="btn-cancelar-qr" class="btn btn-outline-primary d-none" onclick="cancelarEscaneoQRregistroRecursos()">Cancelar escaneo</button>
      </div>

      <!-- Feedback del QR -->
      <div id="qrFeedback" class="mt-3 text-center fw-bold text-danger"></div>

      <!-- Botones de acción -->
      <div class="text-center mt-4">
        <button id="btnVolverDevolucionQR" class="btn btn-primary ms-2" onclick="volverARecursosAsignadosDesdeDevolucionQR()">Volver</button>
      </div>
    </div>


  </div>

  <!-- Modal de confirmacion final de devolucion por QR -->
  <div class="modal fade" id="modalConfirmarQR" tabindex="-1" aria-labelledby="modalConfirmarQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarQRLabel">Confirmar devolución</h5>
        </div>
        <div class="modal-body" id="modalConfirmarQRBody">
          ¿Deseás confirmar la devolución del recurso escaneado?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="btnCancelarQR" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnAceptarQR">Confirmar devolución</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal de recursos asignados -->
  <div class="modal fade" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5 class="modal-title" id="modalRecursosLabel">Recursos asignados</h5>
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        </div>
        <div class="modal-body">

        <ul class="nav nav-tabs mb-3" id="recursosTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="btn btn-primary me-2" id="tab-epp" data-bs-toggle="tab" data-bs-target="#panel-epp" type="button" role="tab" aria-selected="true">Ver EPP</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="btn btn-primary" id="tab-herramientas" data-bs-toggle="tab" data-bs-target="#panel-herramientas" type="button" role="tab" aria-selected="false">Ver herramientas</button>
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
                      <th>Devolver</th>
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
                      <th>Devolver</th>
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



  <!-- Modal de confirmacion de serie de recurso -->
  <div class="modal fade" id="modalConfirmarSerie" tabindex="-1" aria-labelledby="modalConfirmarSerieLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarSerieLabel">Confirmar solicitud</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalConfirmarSerieBody">
          ¿Confirmás que querés solicitar este recurso?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="btnCancelarSerie">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnAceptarSerie">Aceptar</button>
        </div>
      </div>
    </div>
  </div>


<!-- Microfono flotante -->
<div id="micStatusButton" style="
  position: fixed;
  bottom: 16px;
  left: 16px;
  z-index: 9999;
  background-color: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 0 6px rgba(0,0,0,0.2);
  padding: 8px 12px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  pointer-events: none;
  display:none;
">
  <span id="micStatusIcon">🎤</span>
  <span id="micStatusText" class="badge text-bg-success">Micrófono activo</span>
</div>

<!-- Botón fijo inferior izquierda (Menu principal) -->
<button id="boton-flotante-menu-principal" type="button" title="Menu principal" aria-label="Menu principal" style="
  position: fixed;
  bottom: 18px;
  left: 18px;
  z-index: 1040;
  background: #0d6efd;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  font-size: 15px;
  cursor: pointer;
  display:none;
">Menu principal</button>

<!-- Botón fijo inferior derecha (Cerrar sesión) -->
<button id="boton-flotante-cerrar-sesion" type="button" title="Cerrar sesión" aria-label="Cerrar sesión" style="
  position: fixed;
  bottom: 18px;
  right: 18px;
  z-index: 1040;
  background: #dc3545;
  color: #fff;
  border: none;
  padding: 10px 14px;
  border-radius: 6px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  font-size: 14px;
  cursor: pointer;
  display:none;
">Cerrar sesión</button>

  <!-- Contenedor de Toasts -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="{{ asset('js/terminal.js') }}"></script>


</body>
</html>
