<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Herramientas y EPP</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/terminal.css') }}">
</head>
<body>

  <div class="container-kiosk">

    <!-- contenedor de mensajes -->
    <div id="mensaje-kiosco" class="alert alert-warning text-center d-none" role="alert">
      <span id="mensaje-kiosco-texto"></span>
      <button type="button" class="btn-close float-end" onclick="document.getElementById('mensaje-kiosco').classList.add('d-none')"></button>
    </div>

  <!-- Paso 0: Pantalla de bienvenida -->
  <div id="step0" class="step active">
   <h2 class="mb-4 text-center">
  Te damos la bienvenida a<br>SafeStock
</h2>


    <p class="text-center text-muted mb-4" id="textoBienvenida">
      Una terminal guiada por voz para gestionar tus herramientas.
    </p>

    <div class="d-flex flex-column align-items-center gap-3">
      <button class="btn btn-primary btn-lg d-flex align-items-center gap-2" onclick="nextStep(1)">
        <span>Continuar</span>
      </button>



    </div>
  </div>

  <!-- Modal del Asistente -->
  <div class="modal fade" id="modalAsistente" tabindex="-1" aria-labelledby="modalAsistenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-asistente-ancho">

      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="modalAsistenteLabel">¿En qué puedo ayudarte?</h5>
          <button type="button" class="btn-close" onclick="cerrarModalAsistente()" aria-label="Cerrar"></button>

        </div>

        <div class="modal-body d-flex flex-column gap-3">
          <button class="btn btn-outline-dark" onclick="leerAsistenteTexto(1)">¿Cómo puedo usar el sistema?</button>
          <button class="btn btn-outline-dark" onclick="leerAsistenteTexto(2)">¿Cómo ingreso al sistema?</button>
          <button class="btn btn-outline-dark" onclick="leerAsistenteTexto(3)">¿Qué puedo hacer desde el menú principal?</button>
          <button class="btn btn-outline-dark" onclick="leerAsistenteTexto(4)">¿Cómo devuelvo una herramienta?</button>


          <!-- Contenedor animado del subtítulo -->
          <div class="subtitulo-wrapper">
            <div id="asistenteSubtitulo"></div>
          </div>

          <button class="btn btn-outline-danger mt-3 btn-cerrar-modal" onclick="cerrarModalAsistente()">Cerrar</button>

        </div>


      </div>
    </div>
  </div>


    <!-- Paso 1: Identificar trabajador -->
    <div id="step1" class="step">

      <h2 class="mb-4 text-center titulo-identificar">
        <img src="{{ asset('images/casco.svg') }}" alt="Casco de seguridad" class="icono-casco">
        Identificar Trabajador
      </h2>

      <input type="text" id="clave" class="form-control form-control-lg mb-4" placeholder="Ingresar clave">
      <p class="text-muted d-block text-start mb-3">Podes dictar tu clave diciendo <b>"INGRESAR CLAVE"</b></p>


      <div class="d-flex flex-wrap justify-content-start gap-3 mb-3">
        <button class="btn btn-primary btn-lg boton-continuar" onclick="identificarTrabajador()">
          Continuar
          <img src="{{ asset('images/continuar.svg') }}" alt="Flecha continuar" class="icono-flecha">
        </button>

        <button id="btnBorrarClave" class="btn btn-danger btn-lg boton-borrar" onclick="BorrarClave()">
          <img src="{{ asset('images/cruz.svg') }}" alt="Cruz borrar" class="icono-cruz">
          Borrar clave
        </button>

        <button class="btn btn-primary btn-lg boton-qr-personalizado d-flex align-items-center gap-2 ms-auto" onclick="abrirStepQRLogin()">
          <img src="images/qr.svg" alt="Ícono QR" class="icono-qr">
          <span>Iniciar sesión con QR</span>
        </button>
      </div>
      
    </div>

    <!-- Paso 12: Inicio de sesión con QR -->
    <div id="step12" class="step d-none">
      <h2 class="mb-4 text-center titulo-identificar">
        Inicio de sesión con QR
      </h2>

      <div id="qr-login-reader" style="width:300px; margin:auto;"></div>

      <div class="text-center mt-4">
        <button class="btn btn-outline-danger btn-lg texto-cancelar mt-2" onclick="cancelarEscaneoQRLogin()">
          <span>Cancelar escaneo</span>
        </button>
      </div>
    </div>




    
    <!-- Paso 2: Elegir acción -->
    <div id="step2" class="step">
      <h2 id="saludo-trabajador" class="saludo-trabajador">
        <span class="saludo-texto">Hola Micaela</span>
        <img src="{{ asset('images/hola.svg') }}" alt="Saludo" class="icono-saludo">
      </h2>

      <h4 class="mb-4 text-center">¿Qué querés hacer?</h4>
      <div id="menu-principal-buttons"></div>
    </div>


    <!-- Paso 3: Escaneo QR -->
    <div id="step3" class="step">
      <h2 id="titulo-step3" class="mb-4 text-center">Escanear Recurso</h2>
      <h5 id="texto-camara-activa" class="text-center mb-3 d-none d-flex justify-content-center align-items-center gap-2">
        <img src="/images/camara.svg" alt="Cámara activa" class="icono-opcion">
        <span>Cámara activa — escaneá el código QR</span>
      </h5>

      <div id="qr-reader" class="rounded border shadow-sm" style="width: 100%; max-width: 300px; margin: auto;"></div>

      <div class="text-center mt-3">
                <button id="btn-escanear-qr" class="btn btn-outline-dark btn-lg d-flex herramienta-en-mano align-items-center justify-content-start m-2 w-100"
  onclick="nextStep(13); activarEscaneoQRregistroRecursosStep13()">

          <span class="badge-opcion">Opción 1</span>
          <span class="ms-2 flex-grow-1 text-start d-flex align-items-center gap-2">
            <img src="{{ asset('images/qr2.svg') }}" alt="QR" class="icono-opcion">
            <span>Escanear QR</span>
          </span>
        </button>



   
      </div>

      <div class="text-center">
        <button id="herramienta-en-mano-solicitar" class="btn btn-outline-dark btn-lg d-flex herramienta-en-mano align-items-center justify-content-start m-2 w-100" onclick="detenerEscaneoQRregistroRecursos(5)">
          <span class="badge-opcion">Opción 2</span>
          <span class="ms-2 flex-grow-1 text-start d-flex align-items-center gap-2">
            <img src="{{ asset('images/hand2.svg') }}" alt="Mano" class="icono-opcion">
            <span>Solicitar manualmente</span>
          </span>
        </button>

        <div class="text-start">
          <button class="btn btn-primary btn-lg mt-3 d-flex align-items-center gap-2 texto-volver" onclick="detenerEscaneoQRregistroRecursos(2)">
            <img src="{{ asset('images/volver.svg') }}" alt="Volver" class="icono-opcion">
            <span>Volver</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Paso 13: Registro por QR -->
    <div id="step13" class="step d-none">
      <h2 class="mb-4 text-center">Registro por QR</h2>

      <div id="qr-reader-step13" class="rounded border shadow-sm" style="width: 100%; max-width: 300px; margin: auto;"></div>

      <div class="text-center mt-3">

       <button id="btn-cancelar-qr-step13" class="btn btn-outline-danger btn-lg texto-cancelar d-none mt-2"
          onclick="cancelarEscaneoQRregistroRecursosStep13(); nextStep(3)">
          Cancelar escaneo
        </button>

      </div>

     
    </div>



    <!-- Paso 5: Categoría -->
    <div id="step5" class="step">
      <h2 class="mb-4 text-center">Seleccionar Categoría</h2>
      <div id="categoria-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3 texto-volver d-flex align-items-center gap-2" onclick="volverDesdeStep5()">
        <img src="{{ asset('images/volver.svg') }}" alt="Volver" class="icono-opcion">
        <span>Volver</span>
      </button>  
    </div>

    <!-- Paso 6: Subcategoría -->
    <div id="step6" class="step">
      <h2 class="mb-4 text-center">Seleccionar Subcategoría</h2>
      <div id="subcategoria-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3 texto-volver d-flex align-items-center gap-2" onclick="nextStep(5)">
        <img src="{{ asset('images/volver.svg') }}" alt="Volver" class="icono-opcion">
        <span>Volver</span>
      </button>

      <div id="subcategoria-buttons"></div>
      <div id="paginadorSubcategorias" class="d-flex justify-content-center mt-3"></div>
    </div>

    <!-- Paso 7: Recurso -->
    <div id="step7" class="step">
      <h2 class="mb-4 text-center">Seleccioná el recurso</h2>
      <div id="recurso-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3 texto-volver d-flex align-items-center gap-2" onclick="nextStep(6)">
        <img src="{{ asset('images/volver.svg') }}" alt="Volver" class="icono-opcion">
        <span>Volver</span>
      </button>

      
      <div id="recurso-buttons"></div>
      <div id="paginadorRecursos" class="d-flex justify-content-center mt-3"></div>
    </div>

    <!-- Paso 8: Serie -->
    <div id="step8" class="step">
      <h2 class="mb-4 text-center">Seleccioná la serie disponible</h2>
      <div id="serie-buttons"></div>
      <button class="btn btn-primary btn-lg mt-3 texto-volver d-flex align-items-center gap-2" onclick="nextStep(7)">
        <img src="{{ asset('images/volver.svg') }}" alt="Volver" class="icono-opcion">
        <span>Volver</span>
      </button>
      <div id="serie-buttons"></div>
      <div id="paginadorSeries" class="d-flex justify-content-center mt-3"></div>

    </div>

  <!-- Paso 9: Devolución con escaneo QR -->
    <div id="step9" class="step d-none">
      <h3 class="mb-3 text-center">Devolución de recurso</h3>
      <p class="text-center">Muestre el QR del recurso con serie <strong id="serieEsperadaQR"></strong></p>

      <!-- Contenedor del escáner QR -->
      <div class="d-flex justify-content-center">
        <div id="qr-reader-devolucion" style="width: 300px; height: 225px; margin: auto;"></div>

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
        <button class="btn btn-outline-danger btn-lg texto-cancelar mt-2" onclick="volverARecursosAsignadosDesdeDevolucionQR()">
          <span>Cancelar escaneo</span>
        </button>
      </div>
    </div>

<!-- Paso 10: Recursos asignados -->
<div id="step10" class="step d-none">
  <h2 class="mb-4 text-center d-flex justify-content-center align-items-center gap-2">
    <img src="/images/herramienta3.svg" alt="Recursos" class="icono-opcion">
    <span>Recursos asignados</span>
  </h2>

  <div class="d-flex justify-content-center mb-3">
    <button class="btn btn-primary me-2 d-flex align-items-center gap-2 active" id="tab-epp-step" type="button" aria-selected="true">
      <img src="/images/casco3.svg" alt="EPP" class="icono-opcion">
      <span>EPP</span>
    </button>
    <button class="btn btn-primary d-flex align-items-center gap-2" id="tab-herramientas-step" type="button" aria-selected="false">
      <img src="/images/tool.svg" alt="Herramientas" class="icono-opcion">
      <span>Herramientas</span>
    </button>
  </div>

  <div id="recursosTabContentStep" class="tab-content">
    <div id="panel-epp-step" class="tab-pane show active">
      <div id="recursos-asignados-epp" class="mb-3"></div>
      <div id="paginadorEPP-step" class="d-flex justify-content-center flex-wrap mt-2"></div>
    </div>
    <div id="panel-herramientas-step" class="tab-pane">
      <div id="recursos-asignados-herramientas" class="mb-3"></div>
      <div id="paginadorHerramientas-step" class="d-flex justify-content-center flex-wrap mt-2"></div>
    </div>
  </div>

  <div class="text-center mt-3">
    <button id="btnVolverStepRecursos" class="btn btn-primary texto-volver d-flex align-items-center gap-2">
      <img src="/images/volver.svg" alt="Volver" class="icono-opcion">
      <span>Volver</span>
    </button>
  </div>
</div>

  </div>

  <!-- Modal de confirmacion unico de serie -->
<div id="modalResultadoRegistro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tituloModalRegistro" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalRegistro">Confirmación</h5>
        <button type="button" class="btn-close" id="btnCerrarResultadoRegistro" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalResultadoRegistroBody">
        ¿Confirmás que querés solicitar el recurso?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnAceptarResultadoRegistro">Aceptar</button>
      </div>
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

  <!-- Modal de error de devolucion por QR -->
  <div class="modal fade" id="modalErrorQR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title">Error de escaneo</h5>
        </div>
        <div class="modal-body">
          <p id="modalErrorQRBody">El QR no coincide con el recurso solicitado</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-danger" id="btnCerrarErrorQR" data-bs-dismiss="modal">Cerrar</button>
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

  <!-- Modal de mensajes de error -->
  <div id="modal-mensaje-kiosco" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tituloModalKiosco" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content text-dark">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModalKiosco">Atención</h5>
          <button type="button" class="btn-close btn-cerrar-modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalMensajeKioscoBody">Mensaje dinámico</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-cerrar-modal" id="btnCerrarMensajeKiosco">Cerrar</button>
        </div>
      </div>
    </div>
  </div>




  <!--Microfono flotante-->
  <button id="microfono_flotante" class="mic-float-latido" title="Dictar por voz">
    <i class="fa-solid fa-microphone"></i>
  </button>

  <span class="mic-label">¡Podés dictar tu DNI!</span>

<!-- Botón flotante del Asistente -->
<button id="asistente_flotante" class="asistente-float" onclick="abrirModalAsistente()" title="Asistente de SafeStock">
  Ayuda
</button>


<!-- Microfono flotante  (indicador de microfono activo, debug)-->
<div id="micStatusButton_debug" style="
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
  <span id="micStatusIcon_debug">🎤</span>
  <span id="micStatusText_debug" class="badge text-bg-success">Micrófono activo</span>
</div>

<!-- Botón fijo inferior izquierda (Menu principal) -->
<button id="boton-flotante-menu-principal" type="button" title="Menu principal" aria-label="Menu principal" class="align-items-center gap-2" style="display: none">
  <img src="{{ asset('images/menu.svg') }}" alt="Menú" class="icono-opcion">
  <span>Menu principal</span>
</button>

<!-- Botón fijo inferior derecha (Cerrar sesión) -->
<button id="boton-flotante-cerrar-sesion" type="button" title="Cerrar sesión" aria-label="Cerrar sesión" class="align-items-center gap-2">
  <img src="{{ asset('images/out.svg') }}" alt="Cerrar sesión" class="icono-opcion">
  <span>Cerrar sesión</span>
</button>

  <!-- Contenedor de Toasts -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="{{ asset('js/terminal.js') }}"></script>
  <script src="{{ asset('js/terminal_microfono.js') }}"></script>


</body>
</html>
