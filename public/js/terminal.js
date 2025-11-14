function getRenderer(name, fallback = () => {}) {
  if (typeof window !== 'undefined' && typeof window[name] === 'function') return window[name];
  if (typeof global !== 'undefined' && typeof global[name] === 'function') return global[name];
  if (typeof module !== 'undefined' && module.exports && typeof module.exports[name] === 'function') return module.exports[name];
  try {
    const local = eval(name);
    if (typeof local === 'function') return local;
  } catch (e) {}
  return fallback;
}

let mostrarMensajesMicrofono = false
const mostrarMic = false; // mostrar microfono flotante
window._qrValidadoParaDevolucion = false;
const cantidadRecursosPorPagina = 3;

const micButton = document.getElementById('micStatusButton_debug');
if (micButton) {
  micButton.style.display = mostrarMic ? 'inline-block' : 'none';
}


let recognitionGlobalWasRunning = false;

function safeStopRecognitionGlobal() {
  try {
    if (recognitionGlobal && recognitionRunning) {
      actualizarEstadoMicrofono(false);
      recognitionGlobalWasRunning = true;
      if (typeof recognitionGlobal.abort === 'function') {
        recognitionGlobal.abort();
      } else if (typeof recognitionGlobal.stop === 'function') {
        recognitionGlobal.stop();
      }
      recognitionRunning = false;
      console.log('ℹ️ safeStopRecognitionGlobal: detenido (marcado)');
    } else {
      recognitionGlobalWasRunning = false;
    }
  } catch (e) {
    console.warn('safeStopRecognitionGlobal error', e);
    recognitionGlobalWasRunning = false;
  }
}

function safeStartRecognitionGlobal() {
  try {
    if (!('webkitSpeechRecognition' in window)) return;

    if (window.recognitionGlobalPaused) {
      console.log('⏸️ safeStartRecognitionGlobal: pausado, no se inicia');
      return;
    }

    if (recognitionRunning) {
      try {
        recognitionGlobal.stop(); // fuerza reinicio si quedó colgado
        recognitionRunning = false;
        console.log('safeStartRecognitionGlobal: reinicio forzado por estado inconsistente');
      } catch (e) {
        console.warn('safeStartRecognitionGlobal: stop falló en reinicio forzado', e);
      }
    }

    if (!recognitionGlobal) {
      iniciarReconocimientoGlobal();
      return;
    }

    try {
      recognitionGlobal.start();
      recognitionRunning = true;
      actualizarEstadoMicrofono(true);
      console.log('safeStartRecognitionGlobal: start solicitado');
    } catch (err) {
      const isAlreadyStarted = err && (err.name === 'InvalidStateError' || /recognition has already started/i.test(err.message || ''));
      if (isAlreadyStarted) {
        recognitionRunning = true;
        actualizarEstadoMicrofono(true);
        console.log('safeStartRecognitionGlobal: start ignorado, reconocimiento ya iniciado');
      } else {
        console.warn('safeStartRecognitionGlobal: start() falló', err);
        try {
          iniciarReconocimientoGlobal();
        } catch (e) {
          console.warn('safeStartRecognitionGlobal: reiniciar falló', e);
        }
      }
    }
  } catch (e) {
    console.warn('safeStartRecognitionGlobal: excepción', e);
  }
}


/*
function safeStopRecognitionGlobal() {
  try {
    if (recognitionGlobal && recognitionRunning) {
          actualizarEstadoMicrofono(false); // 👈 Aquí
      recognitionGlobalWasRunning = true;
      if (typeof recognitionGlobal.abort === 'function') {
        recognitionGlobal.abort();
      } else if (typeof recognitionGlobal.stop === 'function') {
        recognitionGlobal.stop();
      }
      recognitionRunning = false;
      console.log('ℹ️ safeStopRecognitionGlobal: detenido (marcado)');
    } else {
      recognitionGlobalWasRunning = false;
    }
  } catch (e) {
    console.warn('safeStopRecognitionGlobal error', e);
    recognitionGlobalWasRunning = false;
  }
}

function safeStartRecognitionGlobal() {
  try {
    if (!('webkitSpeechRecognition' in window)) return;
    if (recognitionRunning) {
      console.log('safeStartRecognitionGlobal: recognition ya corriendo; skip start');
      return;
    }
    if (!recognitionGlobal) {
      // intenta usar la rutina existente o crear uno nuevo (tu código de recreación)
      iniciarReconocimientoGlobal();
      return;
    }
    try {
      recognitionGlobal.start();
      console.log('safeStartRecognitionGlobal: start solicitado');
        actualizarEstadoMicrofono(true); // 👈 Aquí
    } catch (err) {
      // Ignorar error si el estado ya está started o si es InvalidStateError
      const isAlreadyStarted = err && (err.name === 'InvalidStateError' || /recognition has already started/i.test(err.message || ''));
      if (isAlreadyStarted) {
        console.log('safeStartRecognitionGlobal: start ignorado, reconocimiento ya iniciado');
        recognitionRunning = true;
            actualizarEstadoMicrofono(true); // 👈 Aquí también
      } else {
        console.warn('safeStartRecognitionGlobal: start() falló', err);
        // si falla por otro motivo, intentar recrear
        try { iniciarReconocimientoGlobal(); } catch(e){ console.warn('safeStartRecognitionGlobal: reiniciar falló', e); }
      }
    }
  } catch (e) {
    console.warn('safeStartRecognitionGlobal: excepción', e);
  }
}
*/
function actualizarEstadoMicrofono(activo = true) {
  const icon = document.getElementById('micStatusIcon_debug');
  const text = document.getElementById('micStatusText_debug');
  if (!icon || !text) return;

  if (activo) {
    icon.textContent = '🎤';
    text.textContent = 'Micrófono activo';
    text.className = 'badge text-bg-success';
  } else {
    icon.textContent = '🔄';
    text.textContent = 'Reiniciando...';
    text.className = 'badge text-bg-primary';
  }
}



let scanner;
let isScanning = false; // 👈 flag de estado

function esComandoVolver(limpio) {
  if (!limpio) return false;
  const s = normalizarTexto(String(limpio)).trim();

  return (
    s === 'volver' ||
    s === 'opcion volver' ||
    /\bvolver\b/.test(s) ||
    /\bopcion volver\b/.test(s)
  );
}



//modal de mensajes
function mostrarMensajeKiosco(mensaje, tipo = 'danger', duracion = 5000) {
  const step = getStepActivo();
  const tipoNormalizado = (tipo || '').toLowerCase();
  const mensajeLower = (mensaje || '').toLowerCase();

  // ❌ Ignorar mensajes de comandos reconocidos por voz
  if (mensaje && mensaje.trim().startsWith('🎤 Comando reconocido:')) {
    console.log('🔇 mostrarMensajeKiosco: mensaje omitido por ser comando reconocido');
    return;
  }

  // ✅ Detectar si el mensaje es crítico
  const mensajeCritico =
    mensajeLower.includes('clave inválida') ||
    mensajeLower.includes('usuario no habilitado') ||
    mensajeLower.includes('no se puede') ||
    mensajeLower.includes('error') ||
    mensajeLower.includes('rechazado');

  // ✅ Mostrar modal sin voz solo en step1 y si el mensaje es crítico
  if (step === 'step1' && (['danger', 'warning', 'error'].includes(tipoNormalizado) || mensajeCritico)) {
    mostrarModalKioscoSinVoz(mensaje, tipoNormalizado || 'danger');
    console.log('🛑 mostrarMensajeKiosco: modal sin voz activado en step1');
    return;
  }

  // ✅ Mostrar toast normal
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white bg-${tipoNormalizado || 'danger'} border-0 show`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');

  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${mensaje}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove('show');
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 500);
  }, duracion);
}




function mostrarModalKiosco(mensaje, tipo = 'danger') {
  const modalEl = document.getElementById('modal-mensaje-kiosco');
  const body = document.getElementById('modalMensajeKioscoBody');
  const cerrarBtn = document.getElementById('btnCerrarMensajeKiosco');

  if (!modalEl || !body || !cerrarBtn) {
    console.warn('⚠️ mostrarModalKiosco: elementos del DOM no encontrados');
    return;
  }

  body.textContent = mensaje;
  window.modalKioscoActivo = true;

  // 🔒 Pausar reconocimiento global ANTES de mostrar el modal
  try {
    recognitionGlobalPaused = true;
    recognitionGlobal?.abort();
    console.log('🛑 Reconocimiento global abortado por modal kiosco');
  } catch (e) {
    console.warn('⚠️ No se pudo abortar recognitionGlobal:', e);
  }

  // 🔧 Cierre defensivo del modalConfirmarSerie si está abierto
  try {
    const modalSerie = document.getElementById('modalConfirmarSerie'); // ✅ agregado

    if (modalSerie && modalSerie.classList.contains('show')) {
      const instanciaSerie = bootstrap.Modal.getInstance(modalSerie);
      if (instanciaSerie) instanciaSerie.hide();

      try {
        const recogSerie = modalSerie._recogInstance;
        if (recogSerie) {
          recogSerie.onresult = null;
          recogSerie.onerror = null;
          recogSerie.onend = null;
          recogSerie.stop?.();
          modalSerie._recogInstance = null;
          modalSerie._lastTranscript = null;
          console.log('🧹 Reconocimiento local del modal serie detenido');
        }
      } catch (e) {
        console.warn('⚠️ No se pudo detener recog del modal serie', e);
      }
    }
  } catch (e) {
    console.warn('⚠️ No se pudo cerrar modalConfirmarSerie desde mostrarModalKiosco', e);
  }

  const modal = new bootstrap.Modal(modalEl);
  let modalActionTaken = false;

  function cerrarModal() {
    if (modalActionTaken) return;
    modalActionTaken = true;
    modal.hide();
    cleanup();
    cerrarModalKiosco(); // ✅ delega cierre completo
  }

  function cleanup() {
    try {
      const recog = modalEl._recogInstance;
      if (recog) {
        recog.onresult = null;
        recog.onerror = null;
        recog.onend = null;
        recog.stop?.();
      }
    } catch (e) {
      console.warn('⚠️ Error al limpiar recog local del modal kiosco:', e);
    }
    modalEl._recogInstance = null;
    modalEl._lastTranscript = null;
  }

  cerrarBtn.onclick = cerrarModal;
  document.querySelectorAll('.btn-cerrar-modal').forEach(btn => {
    btn.onclick = cerrarModal;
  });

  try {
    if ('webkitSpeechRecognition' in window) {
      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = false;
      recog.interimResults = false;
      modalEl._lastTranscript = null;

      recog.onresult = function (event) {
        const texto = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
        if (modalActionTaken || modalEl._lastTranscript === texto) return;
        modalEl._lastTranscript = texto;

        if (texto.includes('cerrar') || texto.includes('entendido') || texto.includes('ok')) {
          cerrarModal();
          recog.stop();
        } else {
         // mostrarMensajeKiosco('No se reconoció el comando. Decí “cerrar” o “entendido”.', 'info');
        }
      };

      recog.onend = function () {
        if (!modalActionTaken && modalEl.classList.contains('show')) {
          setTimeout(() => {
            if (!modalActionTaken && modalEl.classList.contains('show')) {
              recog.start();
            }
          }, 300);
        }
      };

      recog.onerror = function (e) {
        if (e?.error !== 'aborted') console.warn('Error en reconocimiento modal kiosco:', e);
      };

      modalEl._recogInstance = recog;
      recog.start();
    }
  } catch (e) {
    console.warn('No se pudo iniciar reconocimiento modal kiosco:', e);
  }

  modal.show();
}


async function mostrarModalKioscoSinVoz(mensaje, tipo = 'success') {
  const modalEl = document.getElementById('modal-mensaje-kiosco');
  const body = document.getElementById('modalMensajeKioscoBody');
  const cerrarBtn = document.getElementById('btnCerrarMensajeKiosco');

  // 🛡️ Evitar duplicación si ya está activo
  if (window.modalKioscoActivo) {
    console.warn('⚠️ Modal ya activo, ignorando nueva apertura');
    return;
  }

  if (window.mostrarEmojisKiosco === false) {
    mensaje = mensaje.replace(/[\u{1F300}-\u{1FAFF}]/gu, '').trim();
  }

  body.textContent = mensaje;
  window.modalKioscoActivo = true;

  try {
    recognitionGlobalPaused = false;
  } catch (e) {
    console.warn('⚠️ No se pudo ajustar reconocimiento global:', e);
  }

  // 🛑 Detener escaneo QR si estamos en step13
  try {
    const stepActivo = document.querySelector('.step.active')?.id || getStepActivo();
    if (stepActivo === 'step13') {
      console.log('📴 Deteniendo escaneo QR en step13 por apertura de modal');
      await limpiarQRregistroRecursosStep13();
    }
  } catch (e) {
    console.warn('⚠️ No se pudo detener escaneo QR en step13:', e);
  }

  const modal = new bootstrap.Modal(modalEl);

  const reactivarSiStep13 = () => {
    const stepActivo = document.querySelector('.step.active')?.id || getStepActivo();
    if (stepActivo === 'step13') {
      console.log('📷 Reactivando escaneo QR en step13 tras cierre de modal');
      setTimeout(() => activarEscaneoQRstep13ConEspera(), 300);
    }
  };

  cerrarBtn.onclick = () => {
    cerrarModalKiosco();
    reactivarSiStep13();
  };

  document.querySelectorAll('.btn-cerrar-modal').forEach(btn => {
    btn.onclick = () => {
      cerrarModalKiosco();
      reactivarSiStep13();
    };
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
    console.log('🧹 Modal cerrado por backdrop o escape');
    cerrarModalKiosco();
    reactivarSiStep13();
  });

  modal.show();
  safeStartRecognitionGlobal();

  // 🧼 Eliminar backdrops duplicados si quedaron
  setTimeout(() => {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    if (backdrops.length > 1) {
      console.warn('🧼 Eliminando backdrops duplicados');
      backdrops.forEach((el, i) => { if (i > 0) el.remove(); });
    }
  }, 500);
}




function cerrarModalKiosco(callback) {
  const modalEl = document.getElementById('modal-mensaje-kiosco');
  if (!modalEl) return;

  // Ocultar modal visualmente
  modalEl.classList.remove('show');
  modalEl.style.display = 'none';

  // Resetear flag global
  window.modalKioscoActivo = false;

  // Limpiar reconocimiento local
  try {
    const recog = modalEl._recogInstance;
    if (recog) {
      recog.onresult = null;
      recog.onerror = null;
      recog.onend = null;
      recog.stop?.();
    }
  } catch (e) {
    console.warn('⚠️ Error al limpiar recog local del modal kiosco:', e);
  }
  modalEl._recogInstance = null;
  modalEl._lastTranscript = null;

  // Reactivar reconocimiento global
  try {
    recognitionGlobalPaused = false;
    safeStopRecognitionGlobal(); // por si quedó colgado
    safeStartRecognitionGlobal();
    console.log('🎤 Reconocimiento global reactivado tras cerrar modal kiosco');
  } catch (e) {
    console.warn('⚠️ No se pudo reiniciar reconocimiento global:', e);
  }

  // Reactivar escáner QR según el step activo
  try {
    const stepActivo = document.querySelector('.step.active')?.id || getStepActivo();
    if (stepActivo === 'step12') {
      console.log('📷 Reactivando escaneo QR login tras cerrar modal');
      activarEscaneoQRLogin();
    } else if (stepActivo === 'step13') {
      console.log('📷 Reactivando escaneo QR en step13 tras cierre de modal');
      setTimeout(() => activarEscaneoQRstep13ConEspera(), 300);
    }
  } catch (e) {
    console.warn('⚠️ No se pudo reactivar escaneo QR tras cerrar modal:', e);
  }

  // Ocultar backdrop manual si quedó visible
  const backdropManual = document.getElementById('backdrop-manual-kiosco');
  if (backdropManual) backdropManual.style.display = 'none';

  const backdropModal = document.querySelector('.modal-backdrop');
  if (backdropModal) {
    backdropModal.classList.remove('show');
    backdropModal.remove();
  }

  // ✅ Ejecutar callback si se pasó o si hay una global pendiente
  const cb = callback || window._callbackPostModalKiosco;
  if (typeof cb === 'function') {
    try {
      cb();
    } catch (e) {
      console.warn('⚠️ Error en callback post-modal:', e);
    }
    window._callbackPostModalKiosco = null;
  }
}



function quitarEmojis(texto) {
  return texto.replace(/([\u2700-\u27BF]|[\uE000-\uF8FF]|[\uD83C-\uDBFF\uDC00-\uDFFF])+/g, '');
}


//otras cosas
async function nextStep(n) {
  try {
    // Limpieza defensiva: detener recog locales en steps antes de cambiar
    try {
      document.querySelectorAll('.step').forEach(s => {
        try {
          if (s._recogInstance) {
            try { s._recogInstance.onresult = null; s._recogInstance.onerror = null; s._recogInstance.onend = null; } catch (e) {}
            try { if (typeof s._recogInstance.stop === 'function') s._recogInstance.stop(); } catch (e) {}
          }
        } catch (e) {}
        s._recogInstance = null;
        s._opening = false;
      });
    } catch (e) {
      console.warn('nextStep: limpieza recog locales falló', e);
    }

    // 🛑 Detener dictado de clave si está activo
    try {
      if (window._dictadoClaveActivo) {
        window._dictadoClaveActivo.onresult = null;
        window._dictadoClaveActivo.onerror = null;
        window._dictadoClaveActivo.onend = null;
        window._dictadoClaveActivo.stop?.();
        window._dictadoClaveActivo = null;
        console.log('🛑 Dictado de clave detenido en nextStep');
      }
    } catch (e) {
      console.warn('nextStep: error al detener dictado de clave', e);
    }

    // Cerrar modal de recursos si está abierto
    const modalEl = document.getElementById('modalRecursos');
    if (modalEl) {
      const modalInstance = (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function')
        ? bootstrap.Modal.getInstance(modalEl)
        : null;
      if (modalInstance && typeof modalInstance.hide === 'function') {
        try { modalInstance.hide(); } catch (e) { console.warn('nextStep: hide modalRecursos falló', e); }
      }
    }

    // Limpieza específica si estamos saliendo del step13
    try {
      const stepActual = document.querySelector('.step.active')?.id || getStepActivo();
      if (stepActual === 'step13') {
        console.log('🧹 Saliendo de step13, limpiando escáner QR');
        await limpiarQRregistroRecursosStep13?.();
        lastQRStep13 = null;
      }
    } catch (e) {
      console.warn('nextStep: limpieza de step13 falló', e);
    }

    // Detener escaneo QR
    try {
      detenerEscaneoQRregistroRecursos?.();
      cancelarEscaneoQRregistroRecursos?.();
      detenerEscaneoQRLogin?.();
      detenerEscaneoQRDevolucion?.();
      detenerEscaneoQRDevolucionSegura?.();
      console.log('🛑 Escaneo QR detenido en nextStep');
    } catch (e) {}

    // Ocultar todos los steps
    document.querySelectorAll('.step').forEach(s => {
      try { s.classList.remove('active'); s.classList.add('d-none'); } catch (e) {}
    });

    // Activar el step deseado
    const stepEl = document.getElementById('step' + n);
    if (stepEl) {
      actualizarVisibilidadBotonesPorStep('step' + n);
      stepEl.classList.remove('d-none');
      stepEl.classList.add('active');
    } else {
      console.warn('nextStep: step element not found:', 'step' + n);
    }

    // Acciones específicas por step
    try { if (n === 2) cargarMenuPrincipal?.(); } catch (e) { console.warn('nextStep: cargarMenuPrincipal falló', e); }
    try { if (n === 5) cargarCategorias?.(); } catch (e) { console.warn('nextStep: cargarCategorias falló', e); }

    // ✅ Reactivar reconocimiento global (si no lo maneja el step)
    reactivarReconocimientoGlobal?.();

    // Visibilidad de botones flotantes
    try {
      if (typeof window._nextStepWrappedVisibilityUpdater === 'function') {
        window._nextStepWrappedVisibilityUpdater('step' + n);
      } else {
        const ocultar = n === 1;
        const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
        const btnMenu2 = document.getElementById('boton-flotante-menu-principal');
        if (btnCerrar) btnCerrar.style.display = ocultar ? 'none' : 'inline-block';
        if (btnMenu2) btnMenu2.style.display = ocultar ? 'none' : 'inline-block';
      }
    } catch (e) {}

  } catch (err) {
    console.warn('nextStep: excepción general', err);
  }
}



function identificarTrabajador() {
  const clave = document.getElementById('clave').value;
  return new Promise((resolve) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/terminal/identificar', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = meta && meta.content ? meta.content : null;
    if (csrf) {
      xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
    }

    xhr.onload = function () {
      try {
        const res = JSON.parse(xhr.responseText);
        if (res.success) {
          localStorage.setItem('id_usuario', res.usuario.id);
          window.usuarioActual = res.usuario;   // ⚡ guardar usuario global
          window.nextStep(2);
          document.getElementById('saludo-trabajador').innerHTML = `
            <span class="saludo-texto">Hola ${res.usuario.name}</span>
            <img src="/images/hola.svg" alt="Saludo" class="icono-saludo">
          `;
        } else {
          getRenderer('mostrarModalKioscoSinVoz')(res.message || 'Error al identificar al trabajador', 'danger');
        }
        resolve(res);
      } catch (e) {
        getRenderer('mostrarModalKioscoSinVoz')('Error al identificar al trabajador', 'danger');
        resolve({ success: false, error: e });
      }
    };

    xhr.onerror = function () {
      getRenderer('mostrarModalKioscoSinVoz')('No se pudo conectar con el servidor. Verificá que esté activo.', 'danger');
      console.warn('🛑 Modal de red activado por xhr.onerror');
      resolve({ success: false, error: 'ERR_CONNECTION_REFUSED' });
    };

    xhr.send('clave=' + encodeURIComponent(clave));
  });
}


function simularEscaneo() {
  //alert("Simulación de escaneo QR");
  console.log('🧪 simularEscaneo: simulación activada, avanzando a step5');
  window.nextStep(5);
}

function cargarCategorias() {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', '/terminal/categorias', true);

  xhr.onload = function () {
    try {
      const categorias = JSON.parse(xhr.responseText);
      console.log('📁 cargarCategorias: categorías recibidas', categorias);
      const contenedor = document.getElementById('categoria-buttons');
      contenedor.innerHTML = '';

      const iconosCategoria = {
        'EPP': '/images/casco2.svg',
        'Herramienta': '/images/herramienta2.svg'
      };

      categorias.forEach((cat, index) => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-dark btn-lg d-flex justify-content-between align-items-center m-2';
        btn.dataset.categoriaId = cat.id;
        btn.onclick = () => seleccionarCategoria(cat.id);

        btn.innerHTML = `
          <span class="badge-opcion">Opción ${index + 1}</span>
          <span class="flex-grow-1 text-start d-flex align-items-center gap-2">
            ${iconosCategoria[cat.nombre_categoria] ? `<img src="${iconosCategoria[cat.nombre_categoria]}" alt="${cat.nombre_categoria}" class="icono-opcion">` : ''}
            <span>${cat.nombre_categoria}</span>
          </span>
        `;
        contenedor.appendChild(btn);
      });
    } catch (e) {
  getRenderer('mostrarModalKioscoSinVoz')('No se pudieron cargar las categorías', 'danger');
      console.log('No se pudieron cargar las categorías');
    }
  };

  xhr.send();
}

function cargarRecursos() {
  return new Promise((resolve) => {
    const id_usuario = window.localStorage.getItem('id_usuario');
    if (!id_usuario) {
      console.warn('⚠️ cargarRecursos: No hay id_usuario en localStorage');
      resolve();
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `/terminal/recursos-asignados/${id_usuario}`, true);

    xhr.onload = function () {
      try {
        const recursos = JSON.parse(xhr.responseText || '[]');
        const epp = [];
        const herramientas = [];

        recursos.forEach(r => {
          const tipo = r.tipo?.toLowerCase();
          const esEPP = tipo === 'epp' || (r.categoria && r.categoria.toLowerCase().includes('epp'));
          (esEPP ? epp : herramientas).push(r);
        });

        window.recursosEPP = epp;
        window.recursosHerramientas = herramientas;
        window.paginaEPPActual = 1;
        window.paginaHerramientasActual = 1;

        resolve();
      } catch (e) {
        console.error('❌ cargarRecursos: error procesando respuesta', e);
  getRenderer('mostrarModalKioscoSinVoz')('Error al cargar recursos asignados', 'danger');
        resolve();
      }
    };

    xhr.onerror = function () {
  getRenderer('mostrarModalKioscoSinVoz')('Error de red al cargar recursos asignados', 'danger');
      resolve();
    };

    xhr.send();
  });
}


// Función robusta para renderizar recursos
function mostrarRecursosAsignados(recursos, pagina = 1) {
  console.log('[mostrarRecursosAsignados] recursos recibidos:', recursos);
  console.log('[mostrarRecursosAsignados] página solicitada:', pagina);

  let contenedor = document.getElementById('contenedorRecursos');
  if (!contenedor) {
    console.warn('[mostrarRecursosAsignados] contenedor no encontrado, creando...');
    contenedor = document.createElement('div');
    contenedor.id = 'contenedorRecursos';
    document.body.appendChild(contenedor);
  }
  contenedor.innerHTML = '';

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  console.log('[mostrarRecursosAsignados] recursos visibles:', visibles);

  visibles.forEach((r, i) => {
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';

    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-primary btn-lg d-flex justify-content-between align-items-center mt-2';
    btn.dataset.detalleId = r.detalle_id;
    btn.dataset.opcionIndex = i + 1;
    btn.dataset.recurso = r.recurso || '';
    btn.dataset.serie = r.serie || '';

    btn.innerHTML = `
      <span class="badge-opcion">Opción ${i + 1}</span>
      <span class="flex-grow-1 text-start">Devolver</span>
    `;
    btn.onclick = () => mostrarStepDevolucionQR(r.serie, r.detalle_id);

    const html = `
      <div class="card-body">
        <h5 class="card-title mb-1">${r.recurso}</h5>
        <p class="card-text mb-1">Serie: <strong>${r.serie}</strong></p>
        <p class="card-text mb-1">Subcategoría: ${r.subcategoria}</p>
        <p class="card-text mb-1">📅 Prestado: ${r.fecha_prestamo}</p>
        <p class="card-text mb-1">📅 Devolución: ${r.fecha_devolucion ?? ''}</p>
      </div>
    `;
    card.innerHTML = html;
    card.querySelector('.card-body').appendChild(btn);
    contenedor.appendChild(card);

    console.log(`[mostrarRecursosAsignados] tarjeta ${i} generada con botón opción ${i + 1}`);
  });

  if (typeof window.renderPaginacionRecursos === 'function') {
    console.log('[mostrarRecursosAsignados] llamando renderPaginacionRecursos...');
    window.renderPaginacionRecursos(recursos, pagina, totalPaginas);
  } else {
    console.warn('[mostrarRecursosAsignados] renderPaginacionRecursos no está definida');
  }

  console.log('[mostrarRecursosAsignados] renderizado completo');
}


// ✅ Exponer para entorno de tests (JSDOM)
if (typeof window !== 'undefined') {
  window.mostrarRecursosAsignados = mostrarRecursosAsignados;
}

// ✅ Exportar para Jest (CommonJS)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    mostrarRecursosAsignados
  });
}


// ✅ Exponer para entorno de tests (JSDOM)
if (typeof window !== 'undefined') {
  window.mostrarRecursosAsignados = mostrarRecursosAsignados;
}

// ✅ Exportar para Jest (CommonJS)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    mostrarRecursosAsignados
  });
}


if (typeof window !== 'undefined') {
  window.mostrarRecursosAsignados = mostrarRecursosAsignados;
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    mostrarRecursosAsignados
  });
}


// ✅ Exponer para entorno de tests (JSDOM)
if (typeof window !== 'undefined') {
  window.mostrarRecursosAsignados = mostrarRecursosAsignados;
}

// ✅ Exportar para Jest (CommonJS)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    mostrarRecursosAsignados
  });
}




function renderPaginacionRecursos(recursos, paginaActual, totalPaginas) {
  const paginador = document.getElementById('paginadorRecursos');
  if (!paginador) return;
  paginador.innerHTML = '';
  for (let i = 1; i <= totalPaginas; i++) {
    // ...
  }
}


function renderTablaRecursos(tablaId, recursos, pagina = 1, paginadorId) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderTablaRecursos: safeStop falló', e); }

  const tabla = document.getElementById(tablaId);
  const paginador = document.getElementById(paginadorId);
  if (!tabla || !paginador) {
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  tabla.innerHTML = '';

  if (visibles.length === 0) {
    tabla.innerHTML = `<tr><td colspan="cantidadRecursosPorPagina" class="text-center">No tiene recursos asignados</td></tr>`;
    paginador.innerHTML = '';
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');

    btn.dataset.recurso = r.recurso || '';
    btn.dataset.serie = r.serie || '';

    btn.className = 'btn btn-sm btn-primary';
    btn.dataset.detalleId = r.detalle_id;
    btn.dataset.opcionIndex = index + 1;
    btn.innerHTML = `Opción ${index + 1}`;
    btn.onclick = () => mostrarStepDevolucionQR(r.serie, r.detalle_id);

    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${r.subcategoria || '-'} / ${r.recurso || '-'}</td>
      <td>${r.serie || '-'}</td>
      <td>${r.fecha_prestamo || '-'}</td>
      <td>${r.fecha_devolucion || '-'}</td>
      <td></td>
    `;
    row.children[4].appendChild(btn);
    tabla.appendChild(row);
  });

  // Mostrar paginas si hay mas de una
  actualizarVisibilidadPaginador(paginador, totalPaginas);


  paginador.innerHTML = '';
  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement('button');
    btn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    btn.textContent = "Pagina " + i;
    btn.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('paginador click stop failed', e); }
      setTimeout(() => getRenderer('renderTablaRecursos')(tablaId, recursos, i, paginadorId), 60);
    };
    paginador.appendChild(btn);
  }

  if (tablaId === 'tablaEPP') {
    window.paginaEPPActual = pagina;
  }
  if (tablaId === 'tablaHerramientas') {
    window.paginaHerramientasActual = pagina;
  }

  try {
    setTimeout(() => { safeStartRecognitionGlobal(); console.log('🎤 safeStart tras renderTablaRecursos'); }, 80);
  } catch (e) { console.warn('renderTablaRecursos safeStart failed', e); }
}

// verificar si la paginación debe mostrarse
function actualizarVisibilidadPaginador(paginador, totalPaginas, claseOculta = 'd-none') {
  if (!paginador) return;
  if (totalPaginas <= 1) {
    paginador.classList.add(claseOculta);
  } else {
    paginador.classList.remove(claseOculta);
  }
}



async function devolverRecurso(detalleId) {
  if (!confirm('¿Confirmás que querés devolver este recurso?')) {
    return { success: false, reason: 'cancelled' };
  }

  try {
    const res = await fetch(`/terminal/devolver/${detalleId}`, {
      method: 'POST',
      headers: getHeadersSeguros()
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (data.success) {
      mostrarModalKioscoSinVoz('Recurso devuelto correctamente', 'success');
      cargarRecursos();
    } else {
      mostrarModalKioscoSinVoz(data.message || 'Error al devolver recurso', 'danger');
    }

    return data;
  } catch (err) {
    return manejarErrorFetch(err, 'Devolución de recurso');
  }
}




function confirmarDevolucionPorVoz(index) {
  console.log(`🎤 confirmarDevolucionPorVoz: pedido para opción ${index}`);

  // Limpieza de texto duplicado (por si se aplica antes)
  if (typeof index === 'string') {
    index = index.replace(/\b(\w+)\s+\1\b/g, '$1');
  }

  // Verificar que los botones están renderizados
  const botones = document.querySelectorAll('#tablaEPP button, #tablaHerramientas button, #contenedorRecursos button');
  if (botones.length === 0) {
    console.warn('⚠️ No hay botones renderizados aún, ignorando comando de voz');
    getRenderer('mostrarModalKioscoSinVoz')('Los recursos aún se están cargando. Intentá de nuevo en unos segundos.', 'warning');
    return;
  }

  const eppActivo = document.getElementById('tab-epp')?.getAttribute('aria-selected') === 'true';
  const herrActivo = document.getElementById('tab-herramientas')?.getAttribute('aria-selected') === 'true';

  let btn = null;
  if (eppActivo) {
    btn = document.querySelector(`#tablaEPP button[data-opcion-index="${index}"]`);
  } else if (herrActivo) {
    btn = document.querySelector(`#tablaHerramientas button[data-opcion-index="${index}"]`);
  } else {
    btn = document.querySelector(`#contenedorRecursos button[data-opcion-index="${index}"]`);
  }

  if (!btn) {
    console.warn(`❌ confirmarDevolucionPorVoz: no se encontró botón para opción ${index}`);
    getRenderer('mostrarModalKioscoSinVoz')(`No se encontró la opción ${index}. Verificá que esté visible.`, 'warning');
    return;
  }

  const detalleId = btn.dataset.detalleId;
  const serie = btn.dataset.serie || '';
  console.log(`➡️ confirmarDevolucionPorVoz: botón encontrado, detalleId=${detalleId}, serie=${serie}`);

  window._modalConfirmedByVoice = true;
  safeStopRecognitionGlobal();
  console.log('🛑 reconocimiento global pausado, mostrando modal confirmación');

  mostrarStepDevolucionQR(serie, detalleId);
}






function mostrarModalConfirmarDevolucion(detalleId, index = null) {
  const body = document.getElementById('modalConfirmarDevolucionBody');
  const modalEl = document.getElementById('modalConfirmarDevolucion');
  const aceptarBtn = document.getElementById('btnAceptarDevolucion');
  const cancelarBtn = document.getElementById('btnCancelarDevolucion');

  const btn = document.querySelector(`button[data-detalle-id="${detalleId}"]`)
            || (index ? document.querySelector(`#contenedorRecursos button[data-opcion-index="${index}"]`) : null)
            || (index ? document.querySelector(`#tablaEPP button[data-opcion-index="${index}"]`) : null)
            || (index ? document.querySelector(`#tablaHerramientas button[data-opcion-index="${index}"]`) : null);

  const recurso = btn?.dataset.recurso || 'recurso';
  const serie = btn?.dataset.serie || '';
  const texto = serie ? `¿Desea devolver la serie ${serie} de ${recurso}?` : `¿Desea devolver el recurso ${recurso}?`;
  if (body) body.textContent = texto;

  if (!modalEl) {
    if (confirm(texto)) {
      window.confirmationByVoice = false;
      return devolverRecurso(detalleId);
    }
    return;
  }

  if (modalEl._opening) return;
  modalEl._opening = true;
  console.log('🔔 mostrarModalConfirmarDevolucion: abriendo modal confirmacion para detalleId=', detalleId);

  recognitionGlobalPaused = true;
  safeStopRecognitionGlobal();

  let modalActionTaken = false;

  function cleanupModalRecognition() {
    try {
      const recog = modalEl._recogInstance;
      if (recog) {
        try { recog.onresult = null; } catch(e){}
        try { recog.onerror = null; } catch(e){}
        try { recog.stop(); } catch(e){}
      }
    } catch (e) {}
    modalEl._recogInstance = null;
  }

  function finishAndClose(callback) {
    if (modalActionTaken) return;
    modalActionTaken = true;
    try { modal.hide(); } catch (e) {}
    cleanupModalRecognition();
    if (typeof callback === 'function') callback();
  }

  function onAceptar() {
    console.log('🟢 mostrarModalConfirmarDevolucion: Aceptar pulsado');
    finishAndClose(() => {
      window.confirmationByVoice = !!window._modalConfirmedByVoice;
      window._modalConfirmedByVoice = false;
      devolverRecurso(detalleId);
    });
  }

  function onCancelar() {
    console.log('🔴 mostrarModalConfirmarDevolucion: Cancelar pulsado');
    finishAndClose(() => {
      window._modalConfirmedByVoice = false;
     // getRenderer('mostrarMensajeKiosco')('Devolución cancelada.', 'info');
    });
  }

  try { aceptarBtn && aceptarBtn.removeEventListener('click', onAceptar); } catch(e){}
  try { cancelarBtn && cancelarBtn.removeEventListener('click', onCancelar); } catch(e){}
  if (aceptarBtn) aceptarBtn.addEventListener('click', onAceptar);
  if (cancelarBtn) cancelarBtn.addEventListener('click', onCancelar);

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // Desactivar botones flotantes mientras el modal está activo
  const btnMenu = document.getElementById('boton-flotante-menu-principal');
  const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');

  if (btnMenu) {
    btnMenu.disabled = true;
    btnMenu.style.pointerEvents = 'none';
    //btnMenu.style.opacity = '0.5';
  }
  if (btnCerrar) {
    btnCerrar.disabled = true;
    btnCerrar.style.pointerEvents = 'none';
    //btnCerrar.style.opacity = '0.5';
  }


  try {
    if ('webkitSpeechRecognition' in window) {
      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = true;
      recog.interimResults = false;

      recog.onresult = function (event) {
        const textoRec = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
        console.log('🎤 Texto reconocido (modal devolución):', textoRec);
        if (modalActionTaken) return;
        if (textoRec.includes('acept') || textoRec.includes('confirm')) {
          window._modalConfirmedByVoice = true;
          onAceptar();
          try { recog.stop(); } catch(e) {}
        } else if (textoRec.includes('cancel')) {
          onCancelar();
          try { recog.stop(); } catch(e) {}
        }
      };

      recog.onerror = function (e) {
        console.warn('Reconocimiento modal devolucion falló', e);
      };

      modalEl._recogInstance = recog;
      try { recog.start(); console.log('🎤 reconocimiento local (modal devolucion) iniciado'); } catch (e) { console.warn('No se pudo iniciar recog modal', e); }
    }
  } catch (e) {
    console.warn('No se pudo crear reconocimiento modal', e);
  }

  // Handler seguro para cuando el modal se oculta
    // Handler seguro para cuando el modal se oculta
  const onHidden = () => {
    modalEl.removeEventListener('hidden.bs.modal', onHidden);

    // Reactivar botones flotantes al cerrar el modal
    if (btnMenu) {
      btnMenu.disabled = false;
      btnMenu.style.pointerEvents = 'auto';
      btnMenu.style.opacity = '1';
    }
    if (btnCerrar) {
      btnCerrar.disabled = false;
      btnCerrar.style.pointerEvents = 'auto';
      btnCerrar.style.opacity = '1';
    }


    // limpiar guardas/recog
    modalEl._opening = false;
    cleanupModalRecognition();

    // reactivar el reconocimiento global (intentamos siempre; safeStartIgnora errores y evita starts dobles)
    recognitionGlobalPaused = false;
    try {
      // intentamos reactivar, independientemente del flag, safeStart gestiona estados y recreación
      safeStartRecognitionGlobal();
      console.log('🎤 safeStartRecognitionGlobal llamado tras cerrar modal confirmacion');
    } catch (e) {
      console.warn('No se pudo reiniciar recognitionGlobal tras modal (ignored)', e);
    }

    // limpiar marca para la próxima operación
    recognitionGlobalWasRunning = false;
  };
  modalEl.addEventListener('hidden.bs.modal', onHidden);

  
  modalEl.addEventListener('hidden.bs.modal', onHidden);
}

// paso 9, paso 3 y paso 1 - manejo de error de los QR
const qrErrorBuffers = {};
const qrErrorTimers = {};

let scannerLogin = null;
let scannerRegistro = null;
let scannerRegistroStep13 = null;
let scannerDevolucion = null;
let isScanningLogin = false;
let isScanningRegistro = false;
let isScanningStep13 = false;
let isScanningDevolucion = false;


function manejarErrorEscaneoQR(errorMessage, contexto = 'QR') {
  const mensaje = String(errorMessage).trim();
  const key = contexto.toLowerCase();

  if (!qrErrorBuffers[key]) qrErrorBuffers[key] = [];
  if (!qrErrorBuffers[key].includes(mensaje)) {
    qrErrorBuffers[key].push(mensaje);
  }

  if (qrErrorTimers[key]) return;

  qrErrorTimers[key] = setTimeout(() => {
    if (qrErrorBuffers[key].length > 0) {
      console.warn(`❌ Error escaneo ${contexto}:`, qrErrorBuffers[key].join(' |'));
      // Si querés mostrarlo como toast en modo demo:
      // mostrarModalKioscoSinVoz(qrErrorBuffers[key].join(' |'), 'warning');
    }
    qrErrorBuffers[key] = [];
    qrErrorTimers[key] = null;
  }, 300);
}


// === paso 9: Devolución por QR ===

let serieEsperada = '';
let detalleIdActual = null;
window._modalErrorQR = null;

function mostrarStepDevolucionQR(serie, detalleId) {
  safeStopRecognitionGlobal();

  serieEsperada = serie;
  detalleIdActual = detalleId;
  window.modoActual = 'devolucion';

  const serieEl = document.getElementById('serieEsperadaQR');
  const feedbackEl = document.getElementById('qrFeedback');
  //const btnConfirmar = document.getElementById('btnConfirmarDevolucion');

  if (serieEl) serieEl.textContent = serie || '';
  if (feedbackEl) feedbackEl.textContent = '';
  /*if (btnConfirmar) {
    try { btnConfirmar.disabled = true; } catch (e) {}
  }*/

  nextStep(9);

  // 🔁 Reintento si el contenedor no está listo
  let intentos = 0;
  const intentarActivarCamara = () => {
    const qrContainer = document.getElementById('qr-reader-devolucion');
    const bounds = qrContainer?.getBoundingClientRect();
    if (!qrContainer || bounds?.width < 100 || bounds?.height < 100) {
      intentos++;
      if (intentos < 5) {
        setTimeout(intentarActivarCamara, 200);
      } else {
        console.warn('❌ Contenedor QR no tiene dimensiones válidas tras reintentos');
        mostrarModalKioscoSinVoz('No se pudo activar la cámara. Intente nuevamente.', 'danger');
      }
      return;
    }
    activarEscaneoDevolucionQR();
  };

  setTimeout(intentarActivarCamara, 250);
  activarReconocimientoDevolucionQR();
}



// --------------------------
// validarDevolucionQR (actualizada)
// --------------------------
async function validarDevolucionQR(qrCode, idUsuario) {
  const serieEsperada = document.getElementById('serieEsperadaQR')?.textContent?.trim() || '';

  try {
    const res = await fetch('/terminal/validar-qr-devolucion', {
      method: 'POST',
      headers: getHeadersSeguros(),
      body: JSON.stringify({ codigo_qr: qrCode, id_usuario: idUsuario, serie_esperada: serieEsperada })
    });

    const data = await res.json();
    console.log('📦 Respuesta completa de validación QR:', data);

    if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
    return data;
  } catch (err) {
    return manejarErrorFetch(err, 'Validación QR devolución');
  }
}





// --------------------------
// confirmarDevolucionQRActual (actualizada)
// --------------------------
async function confirmarDevolucionQRActual() {
  if (!detalleIdActual) {
    mostrarModalKioscoSinVoz('No se puede confirmar devolución: falta el recurso.', 'danger');
    return;
  }

  try {
    const res = await fetch('/terminal/devolver-recurso', {
      method: 'POST',
      headers: getHeadersSeguros(),
      body: JSON.stringify({ id_detalle: detalleIdActual })
    });

    const data = await res.json();

    if (data.success) {
      if (data.estado === 'ya_devuelto') return;

      const mensaje = `Recurso devuelto correctamente${data.recurso ? ': ' + data.recurso : ''}${data.serie ? ' - Serie ' + data.serie : ''}.`;
      window._devolucionCompletada = true;

      // ✅ Mostrar modal de éxito
      mostrarModalKioscoSinVoz(mensaje, 'success');

      // ✅ Guardar redirección como callback global
      window._callbackPostModalKiosco = () => {
        window.cargarRecursos().then(() => {
          const recursos = (ultimoTabElegido === 'herramientas') ? window.recursosHerramientas : window.recursosEPP;
          const totalPaginas = Math.ceil((recursos?.length || 0) / cantidadRecursosPorPagina);
          let paginaFinal = Math.min(ultimaPaginaElegida || 1, totalPaginas);

          while (paginaFinal > 1 && ((paginaFinal - 1) * cantidadRecursosPorPagina) >= recursos.length) {
            paginaFinal--;
          }

          if (ultimoTabElegido === 'herramientas') {
            window.paginaHerramientasActual = paginaFinal;
          } else {
            window.paginaEPPActual = paginaFinal;
          }

          abrirStepRecursos();
        });
      };

    } else {
      if (data.message) mostrarModalKioscoSinVoz(data.message, 'danger');
    }
  } catch (err) {
    manejarErrorFetch(err, 'Confirmar devolución QR');
  }

detalleIdActual = null;

}



function detenerEscaneoQRDevolucion() {
  const qrContainer = document.getElementById('qr-reader-devolucion');
  if (qrContainer && window.html5QrCodeDevolucion) {
    window.html5QrCodeDevolucion.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
    });
  }
}

// --------------------------
// detenerEscaneoQRDevolucionSegura (actualizada, idempotente)
// --------------------------
window._qrDevolucionStopping = false;

async function detenerEscaneoQRDevolucionSegura() {
  if (window._qrDevolucionStopping) {
    console.log('↩️ detenerEscaneoQRDevolucionSegura: ya en curso');
    return;
  }
  window._qrDevolucionStopping = true;

  try {
    console.log('🧹 detenerEscaneoQRDevolucionSegura: inicio');

    if (window._recogQRDevolucion) {
      try {
        if (typeof window._recogQRDevolucion.stop === 'function') {
          window._recogQRDevolucion.stop();
        }
      } catch (e) {
        console.warn('⚠️ Error al detener reconocimiento local', e);
      }
      window._recogQRDevolucion = null;
    }

    if (window.html5QrCodeDevolucion) {
      try {
        if (typeof window.html5QrCodeDevolucion.stop === 'function') {
          await window.html5QrCodeDevolucion.stop();
        }
      } catch (e) {
        console.warn('⚠️ detenerEscaneoQRDevolucionSegura: stop falló', e);
      }
      try {
        if (typeof window.html5QrCodeDevolucion.clear === 'function') {
          await window.html5QrCodeDevolucion.clear();
        }
      } catch (e) {
        console.warn('⚠️ detenerEscaneoQRDevolucionSegura: clear falló', e);
      }
      window.html5QrCodeDevolucion = null;
    }

    const qrContainer = document.getElementById('qr-reader-devolucion');
    if (qrContainer) {
      try { qrContainer.innerHTML = ''; } catch (e) {}
    }

    window._qrDevolucionActivo = false;
    window._qrDevolucionProcesando = false;
    window._qrValidadoParaDevolucion = false;

    console.log('🛑 Escaneo QR de devolución detenido (seguro)');
  } catch (e) {
    console.warn('⚠️ Error en detenerEscaneoQRDevolucionSegura', e);
  } finally {
    window._qrDevolucionStopping = false;
  }
}




function volverARecursosAsignadosDesdeDevolucionQR() {
 
 window._qrValidadoParaDevolucion = false;

  try {
    detenerEscaneoQRDevolucionSegura(); // 🔧 usa la versión segura
    nextStep(10);
    const btn = document.getElementById('btnVolverDevolucionQR');
    if (btn) btn.disabled = false; // por si quedó bloqueado
  } catch (e) {
    console.warn('⚠️ Error al ejecutar volver desde devolución QR', e);
  }
}

// Bind del botón de confirmación
// binding seguro: si el elemento existe, conectar; si no, lo intentamos en DOMContentLoaded
(function bindBtnConfirmarDevolucion() {
  const tryBind = () => {
    const btn = document.getElementById('btnConfirmarDevolucion');
    if (!btn) return false;
    if (btn._safeClickAttached) return true;
    try {
      btn.addEventListener('click', confirmarDevolucionQRActual);
      btn._safeClickAttached = true;
      console.log('✅ btnConfirmarDevolucion conectado de forma segura');
    } catch (e) {
      console.warn('bindBtnConfirmarDevolucion: addEventListener falló', e);
    }
    return !!btn._safeClickAttached;
  };

  // Intento inmediato (por si el script se carga después del DOM)
  if (tryBind()) return;

  // Si no está disponible aún, reintentar una vez cuando DOMContentLoaded ocurra
  document.addEventListener('DOMContentLoaded', () => {
    tryBind();
  }, { once: true });
})();


// --------------------------
// activarEscaneoDevolucionQR (actualizada)
// --------------------------
async function activarEscaneoDevolucionQR() {
  const contenedorId = 'qr-reader-devolucion';
  const qrContainer = document.getElementById(contenedorId);
  if (!qrContainer) {
    console.warn(`Contenedor QR no encontrado: ${contenedorId}`);
    mostrarModalKioscoSinVoz('No se encontró el área de escaneo.', 'danger');
    return;
  }

  qrContainer.classList.remove('qr-inactivo');

  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarModalKioscoSinVoz('⚠️ Usuario no identificado', 'danger');
    return;
  }

  if (window._qrDevolucionActivo) {
    console.warn('⚠️ Escáner QR ya activo, se evita duplicación');
    return;
  }

  await detenerEscaneoQRDevolucionSegura();

  try {
    window.html5QrCodeDevolucion = new Html5Qrcode(contenedorId);
  } catch (e) {
    console.error('Error creando Html5Qrcode:', e);
    mostrarModalKioscoSinVoz('No se pudo inicializar el escáner.', 'danger');
    return;
  }

  window._qrDevolucionActivo = true;
  window._qrDevolucionProcesando = false;

  try {
    await window.html5QrCodeDevolucion.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      async (decodedText) => {
        if (window._qrDevolucionProcesando) {
          console.warn('⚠️ Escaneo ignorado: ya se está procesando un QR');
          return;
        }
        window._qrDevolucionProcesando = true;

        console.log('🔎 QR detectado (decodedText):', decodedText);
        const res = await validarDevolucionQR(decodedText, idUsuario);
        console.log('📦 Respuesta de validación QR (handler):', res);

        // 🛡️ Si hubo error de red, no mostrar modal de QR inválido
        if (res.error) {
          console.warn('⛔ Error de red detectado, se omite modal de QR inválido');
          await detenerEscaneoQRDevolucionSegura();
          safeStopRecognitionGlobal();
          window._qrDevolucionProcesando = false;
          return;
        }


        if (!res.success || res.estado === 'qr_invalido') {
          await detenerEscaneoQRDevolucionSegura();
          safeStopRecognitionGlobal();
          window._qrDevolucionProcesando = false;

          const modalEl = document.getElementById('modalErrorQR');
          if (!modalEl) return;

          if (!window._modalErrorQR) {
            window._modalErrorQR = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
          }

          const body = document.getElementById('modalErrorQRBody');
          if (body) body.textContent = res.message || 'El QR no coincide con el recurso solicitado';

          window._modalErrorQR.show();

          const btnCerrar = document.getElementById('btnCerrarErrorQR');
          if (btnCerrar) {
            btnCerrar.removeEventListener('click', cerrarModalErrorQR);
            btnCerrar.addEventListener('click', cerrarModalErrorQR);
          }

          return;
        }

        if (res.success && res.coincide) {
          await detenerEscaneoQRDevolucionSegura();
          window._qrValidadoParaDevolucion = true;

          detalleIdActual = res.id_detalle;
          document.getElementById('qrFeedback').textContent = '';

          const modalEl = document.getElementById('modalConfirmarQR');
          if (!modalEl) return;

          if (modalEl.classList.contains('show')) {
            console.log('⚠️ modalConfirmarQR ya visible, se evita duplicación');
            return;
          }

          if (!window._modalConfirmarQR) {
            window._modalConfirmarQR = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
          }

          const body = document.getElementById('modalConfirmarQRBody');
          if (body) {
            const serie = document.getElementById('serieEsperadaQR')?.textContent || '';
            body.textContent = serie
              ? `¿Deseás confirmar la devolución de la serie ${serie}?`
              : '¿Deseás confirmar la devolución del recurso escaneado?';
          }

          window._modalConfirmarQR.show();

          const aceptar = document.getElementById('btnAceptarQR');
          const cancelar = document.getElementById('btnCancelarQR');

          const onAceptar = () => {
            try { window._modalConfirmarQR.hide(); } catch (e) {}
            confirmarDevolucionQRActual();
          };

          const onCancelar = () => {
            try { window._modalConfirmarQR.hide(); } catch (e) {}
            window._qrDevolucionProcesando = false;
            setTimeout(() => activarEscaneoDevolucionQR(), 250);
          };

          if (aceptar) {
            aceptar.replaceWith(aceptar.cloneNode(true));
            const nuevoAceptar = document.getElementById('btnAceptarQR');
            nuevoAceptar.disabled = false;
            nuevoAceptar.addEventListener('click', onAceptar);
          }

          if (cancelar) {
            cancelar.replaceWith(cancelar.cloneNode(true));
            const nuevoCancelar = document.getElementById('btnCancelarQR');
            nuevoCancelar.addEventListener('click', onCancelar);
          }
        }
      },
      (errorMessage) => {
        const msg = String(errorMessage || '');
        if (msg.includes('No MultiFormat Readers')) {
          console.debug('frame scan: no QR detected');
          return;
        }
        manejarErrorEscaneoQR(errorMessage, 'devolucion');
      }
    );

    console.log('📷 Escáner QR iniciado correctamente');
  } catch (err) {
    console.error('No se pudo iniciar escaneo devolución:', err);
    mostrarModalKioscoSinVoz('No se pudo activar la cámara para escanear QR', 'danger');
    window._qrDevolucionActivo = false;
    try { await detenerEscaneoQRDevolucionSegura(); } catch (e) {}
  }
}



function ExitoDevolucionQR(qrCodeMessage) {
  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarModalKioscoSinVoz('⚠️ Usuario no identificado', 'danger');
    return;
  }

  validarDevolucionQR(qrCodeMessage, idUsuario)
    .then(res => {
      if (res.success && res.coincide) {
        devolverRecurso(res.id_detalle);
      } else {
        mostrarModalKioscoSinVoz(res.message || 'QR no válido para devolución', 'warning');
      }
    })
    .catch(err => {
      console.error('Error validando QR:', err);
      mostrarModalKioscoSinVoz('Error al validar QR', 'danger');
    });
}

function activarReconocimientoDevolucionQR() {
  if (!('webkitSpeechRecognition' in window)) return;

  safeStopRecognitionGlobal();

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = false;

  recog.onresult = function (event) {
    const texto = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
    console.log('🎤 Texto reconocido (devolución QR):', texto);

    const btn = document.getElementById('btnConfirmarDevolucion');
    const modalVisible = document.getElementById('modalConfirmarQR')?.classList.contains('show');

    if (texto === 'confirmar' || texto === 'confirmar devolución') {
      if (window._qrValidadoParaDevolucion && btn && !btn.disabled && modalVisible) {
        console.log('🧠 confirmación por voz permitida');
        btn.click();
        recog.stop();
      } else {
        console.warn('⚠️ confirmación por voz bloqueada: condiciones no cumplidas');
        mostrarModalKioscoSinVoz('Aún no se detectó un QR válido para confirmar', 'warning');
      }
    } else if (texto === 'volver') {
      volverARecursosAsignadosDesdeDevolucionQR();
      recog.stop();
    } else if (texto === 'cerrar') {
      const btnCerrar = document.getElementById('btnCerrarErrorQR');
      if (btnCerrar) {
        btnCerrar.click();
        recog.stop();
      }
    }
  };

  recog.onerror = function (e) {
    console.warn('Reconocimiento devolución QR falló', e);
  };

  try {
    setTimeout(() => {
      recog.start();
      console.log('🎤 Reconocimiento voz activo en paso 9');
      window._recogQRDevolucion = recog;
    }, 300); // ✅ Delay para asegurar que el modal esté visible
  } catch (e) {
    console.warn('No se pudo iniciar reconocimiento QR', e);
  }
}


function cerrarModalErrorQR() {
  try {
    const modalEl = document.getElementById('modalErrorQR');
    if (!modalEl) return;

    // Usamos instancia única para evitar duplicados
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
  } catch (e) {
    console.warn('⚠️ No se pudo cerrar modalErrorQR correctamente', e);
  }

  // Reactivar escaneo y reconocimiento de voz
  setTimeout(() => {
    activarEscaneoDevolucionQR();
    safeStartRecognitionGlobal();
  }, 300);
}


const btnCerrar = document.getElementById('btnCerrarErrorQR');
if (btnCerrar) {
  btnCerrar.removeEventListener('click', cerrarModalErrorQR);
  btnCerrar.addEventListener('click', cerrarModalErrorQR);
}


// asegurar handlers básicos del modalConfirmarQR (idempotente)
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('modalConfirmarQR');
  const aceptar = document.getElementById('btnAceptarQR');
  const cancelar = document.getElementById('btnCancelarQR');

  if (!modalEl) return;
  // si el botón aceptar/cancelar ya están conectados en activarEscaneoDevolucionQR, los removeEventListener no harán daño
  if (aceptar && !aceptar._connected) {
    aceptar.addEventListener('click', () => { confirmarDevolucionQRActual(); });
    aceptar._connected = true;
  }
  if (cancelar && !cancelar._connected) {
    cancelar.addEventListener('click', () => {
      // reactivar el escaneo de devolución tras cancelar
      setTimeout(() => activarEscaneoDevolucionQR(), 250);
    });
    cancelar._connected = true;
  }
});

// defensivo: conectar botones del modalConfirmarQR si existen
// conectar modalConfirmarQR handlers de forma idempotente y segura
(function asegurarBindingsModalConfirmarQR() {
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalConfirmarQR');
    if (!modalEl) return;

    const aceptar = document.getElementById('btnAceptarQR');
    const cancelar = document.getElementById('btnCancelarQR');

    if (aceptar && !aceptar._connected) {
      aceptar.addEventListener('click', () => { confirmarDevolucionQRActual(); });
      aceptar._connected = true;
    }

    if (cancelar && !cancelar._connected) {
      cancelar.addEventListener('click', () => {
        setTimeout(() => activarEscaneoDevolucionQR(), 250);
      });
      cancelar._connected = true;
    }
  }, { once: true });
})();

// === Paso 3: Escaneo QR para registrar recursos ===

function activarEscaneoQRregistroRecursos() {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (!qrContainer || isScanningRegistro || scannerRegistro) {
    console.warn('⚠️ Escáner ya activo o contenedor no disponible');
    return;
  }

  qrContainer.innerHTML = '';
  btnEscanear?.classList.add('d-none');
  btnCancelar?.classList.remove('d-none');
  textoCamara?.classList.remove('d-none');

  scannerRegistro = new Html5Qrcode("qr-reader");
  isScanningRegistro = true;

  scannerRegistro.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 400, height: 400 } },
    qrCodeMessage => {
      console.log('QR detectado:', qrCodeMessage);
      limpiarQRregistroRecursos();
      registrarPorQRregistroRecursos(qrCodeMessage);
    },
    errorMessage => {
      manejarErrorEscaneoQR(errorMessage, 'registro');
    }
  ).catch(err => {
    console.error('Error al iniciar escaneo:', err);
    mostrarModalKioscoSinVoz('No se pudo activar la cámara para escanear QR', 'danger');
    limpiarQRregistroRecursos();
  });
}

function limpiarQRregistroRecursos() {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (scannerRegistro && isScanningRegistro) {
    scannerRegistro.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      btnCancelar?.classList.add('d-none');
      btnEscanear?.classList.remove('d-none');
      textoCamara?.classList.add('d-none');
      scannerRegistro = null;
      isScanningRegistro = false;
    });
  } else {
    qrContainer.innerHTML = '';
    btnCancelar?.classList.add('d-none');
    btnEscanear?.classList.remove('d-none');
    textoCamara?.classList.add('d-none');
    scannerRegistro = null;
    isScanningRegistro = false;
  }
}



function cancelarEscaneoQRregistroRecursos() {
  limpiarQRregistroRecursos();
}

async function registrarPorQRregistroRecursos(codigoQR) {
  const sesionOk = await verificarSesionActiva();
  if (!sesionOk) return { success: false };

  const id_usuario = localStorage.getItem('id_usuario');
  try {
    const res = await fetch('/terminal/registrar-por-qr', {
      method: 'POST',
      headers: getHeadersSeguros(),
      body: JSON.stringify({ codigo_qr: codigoQR, id_usuario })
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (data.success) {
      const mensaje = `Recurso registrado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`;
      mostrarModalKioscoSinVoz(mensaje, 'success');
      window.nextStep?.(3); // ← redirige al step3 después de éxito
      //////////
    } else {
      await limpiarQRregistroRecursosStep13();
      mostrarModalKioscoSinVoz(data.message || 'Error al registrar recurso por QR', 'danger');
    }

    return data;
  } catch (err) {
    manejarErrorFetch(err, 'Registro por QR');
    await limpiarQRregistroRecursosStep13();
    mostrarModalKioscoSinVoz('Error de red al registrar recurso por QR', 'danger');
    return { success: false };
  }
}




function detenerEscaneoQRregistroRecursos(next = null) {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (scanner && isScanning) {
    console.log('📴 detenerEscaneoQRregistroRecursos: deteniendo escaneo activo');
    scanner.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      if (btnCancelar) btnCancelar.classList.add('d-none');
      if (btnEscanear) btnEscanear.classList.remove('d-none');
      if (textoCamara) textoCamara.classList.add('d-none');
      isScanning = false;
      if (next) window.nextStep(next); // 👈 avanzar al paso cuando termina
      console.log('➡️ detenerEscaneoQRregistroRecursos: avanzando a step', next);
    });
  } else {
    qrContainer.innerHTML = '';
    if (btnCancelar) btnCancelar.classList.add('d-none');
    if (btnEscanear) btnEscanear.classList.remove('d-none');
    if (textoCamara) textoCamara.classList.add('d-none');
    isScanning = false;
    if (next) window.nextStep(next);
  }
}




// === Paso 13: 
let lastQRStep13 = null;

function activarEscaneoQRregistroRecursosStep13() {
  console.log('🟢 Intentando iniciar escáner en step13');

if (window.qrScannerActivoStep13) {
  console.warn('⚠️ Escáner ya activo, no se reinicia');
  return;
}
window.qrScannerActivoStep13 = true;


  const qrContainer = document.getElementById('qr-reader-step13');
  const btnEscanear = document.getElementById('btn-escanear-qr-step13');
  const btnCancelar = document.getElementById('btn-cancelar-qr-step13');
  const textoCamara = document.getElementById('texto-camara-activa-step13');

  if (!qrContainer || isScanningStep13 || scannerRegistroStep13) {
    console.warn('⚠️ Escáner ya activo o contenedor no disponible');
    return;
  }

  qrContainer.innerHTML = '';
  btnEscanear?.classList.add('d-none');
  btnCancelar?.classList.remove('d-none');
  textoCamara?.classList.remove('d-none');

  scannerRegistroStep13 = new Html5Qrcode("qr-reader-step13");
  isScanningStep13 = true;

  console.log('🚀 Iniciando escáner QR en step13');

  scannerRegistroStep13.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 400, height: 400 } },
    qrCodeMessage => {
      lastQRStep13 = qrCodeMessage;
      console.log('QR detectado (step13):', qrCodeMessage);
      limpiarQRregistroRecursosStep13().then(() => {
        registrarPorQRregistroRecursos(qrCodeMessage);
      });
    },
    errorMessage => {
      manejarErrorEscaneoQR(errorMessage, 'registro');
    }
  ).catch(err => {
    console.error('Error al iniciar escaneo (step13):', err);
    mostrarModalKioscoSinVoz('No se pudo activar la cámara para escanear QR', 'danger');
    limpiarQRregistroRecursosStep13();
  });
}

function limpiarQRregistroRecursosStep13() {
  window.qrScannerActivoStep13 = false;


  return new Promise(resolve => {
    const qrContainer = document.getElementById('qr-reader-step13');
    const btnEscanear = document.getElementById('btn-escanear-qr-step13');
    const btnCancelar = document.getElementById('btn-cancelar-qr-step13');
    const textoCamara = document.getElementById('texto-camara-activa-step13');

    if (scannerRegistroStep13 && isScanningStep13) {
      scannerRegistroStep13.stop().catch(() => {}).then(() => {
        qrContainer.innerHTML = '';
        btnCancelar?.classList.add('d-none');
        btnEscanear?.classList.remove('d-none');
        textoCamara?.classList.add('d-none');
        scannerRegistroStep13 = null;
        isScanningStep13 = false;
        resolve();
      });
    } else {
      qrContainer.innerHTML = '';
      btnCancelar?.classList.add('d-none');
      btnEscanear?.classList.remove('d-none');
      textoCamara?.classList.add('d-none');
      scannerRegistroStep13 = null;
      isScanningStep13 = false;
      resolve();
    }
  });
}


function cancelarEscaneoQRregistroRecursosStep13() {
  limpiarQRregistroRecursosStep13();
}



async function activarEscaneoQRstep13ConEspera() {
  // Limpieza defensiva antes de activar
  await limpiarQRregistroRecursosStep13?.();
  lastQRStep13 = null;

  nextStep(13);

  const intentarActivar = () => {
    const container = document.getElementById('qr-reader-step13');
    if (container) {
      console.log('📦 Contenedor QR step13 disponible, iniciando escáner');
      activarEscaneoQRregistroRecursosStep13();
    } else {
      console.warn('⏳ Esperando DOM para escáner step13...');
      setTimeout(intentarActivar, 100);
    }
  };

  setTimeout(intentarActivar, 300);
}



// === Paso 1: Escaneo QR para login o inicio de sesión === 
function activarEscaneoQRLogin() {
  const qrContainer = document.getElementById('qr-login-reader');

  if (!qrContainer || isScanningLogin || scannerLogin) {
    console.error('❌ activarEscaneoQRLogin: contenedor no disponible o escaneo ya activo');
    return;
  }

  qrContainer.innerHTML = '';
  scannerLogin = new Html5Qrcode("qr-login-reader");
  isScanningLogin = true;

  scannerLogin.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 250, height: 250 } },
    qrCodeMessage => {
      console.log('QR de login detectado:', qrCodeMessage);
      detenerEscaneoQRLogin();
      identificarPorQRLogin(qrCodeMessage);
    },
    errorMessage => {
      manejarErrorEscaneoQR(errorMessage, 'login');
    }
  ).catch(err => {
    console.error('No se pudo iniciar escaneo login:', err);
    mostrarModalKioscoSinVoz('No se pudo activar la cámara para escanear QR', 'danger');
    detenerEscaneoQRLogin();
  });
}

function detenerEscaneoQRLogin() {
  const qrContainer = document.getElementById('qr-login-reader');

  if (scannerLogin && isScanningLogin) {
    scannerLogin.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      scannerLogin = null;
      isScanningLogin = false;
      console.log('📴 detenerEscaneoQRLogin: escaneo login detenido');
    });
  } else {
    qrContainer.innerHTML = '';
    scannerLogin = null;
    isScanningLogin = false;
  }
}


async function identificarPorQRLogin(codigoQR) {
  try {
    const res = await fetch('/terminal/identificar-qr', {
      method: 'POST',
      headers: getHeadersSeguros(),
      body: JSON.stringify({ codigo_qr: codigoQR })
    });

    const data = await res.json();
    console.log('Respuesta login QR:', data);

    if (data.success) {
      localStorage.setItem('id_usuario', data.usuario.id);
      window.usuarioActual = data.usuario;   // ⚡ guardar usuario global
      window.nextStep?.(2);
      document.getElementById('saludo-trabajador').textContent = `Hola ${data.usuario.name}`;
    } else {
      mostrarModalKioscoSinVoz(data.message || 'Error al identificar por QR', 'danger');
    }
  } catch (err) {
    manejarErrorFetch(err, 'Login por QR');
  }
}


//step 12: abrir escaneo QR login
window.abrirStepQRLogin = function () {
  console.log('🟢 abrirStepQRLogin: llamado');

  safeStopRecognitionGlobal?.();

  // 👇 Ocultar botones antes de cambiar de step
  actualizarVisibilidadBotonesPorStep('step12');

  nextStep(12);
  activarEscaneoQRLogin();
};


window.cancelarEscaneoQRLogin = function () {
  console.log('🔴 cancelarEscaneoQRLogin: llamado');
  detenerEscaneoQRLogin();
  safeStartRecognitionGlobal?.();
  nextStep(1);
};



// Función para botón Volver en step3
function volverAInicio() {
  localStorage.removeItem('id_usuario');
  console.log('volverAInicio: sesión limpiada');
  nextStep(1);
  const claveInput = document.getElementById('clave');
  if (claveInput) claveInput.value = '';
  reactivarReconocimientoGlobal(); // ✅ blindado
}


/*
function getActiveRecursosTab() {
  const tabEPP = document.getElementById('tab-epp');
  const tabHerr = document.getElementById('tab-herramientas');
  if (tabEPP?.getAttribute('aria-selected') === 'true') return 'epp';
  if (tabHerr?.getAttribute('aria-selected') === 'true') return 'herramientas';
  return null;
}*/


function seleccionarCategoria(categoriaId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/subcategorias-disponibles/${categoriaId}`, true);

  xhr.onload = function () {
    try {
      const subcategorias = JSON.parse(xhr.responseText);
      console.log('📁 seleccionarCategoria: subcategorías recibidas', subcategorias);
      window.subcategoriasActuales = subcategorias.filter(s => s.disponibles > 0);
  getRenderer('renderSubcategoriasPaginadas')(window.subcategoriasActuales, 1);
      window.nextStep(6);
    } catch (e) {
  getRenderer('mostrarModalKioscoSinVoz')('No se pudieron cargar las subcategorías', 'danger');
      console.log('❌ No se pudieron cargar las subcategorías');
    }
  };

  xhr.send();
}

function renderSubcategoriasPaginadas(subcategorias, pagina = 1) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderSubcategoriasPaginadas: safeStop failed', e); }

  const contenedor = document.getElementById('subcategoria-buttons');
  const paginador = document.getElementById('paginadorSubcategorias');
  if (!contenedor || !paginador) {
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }
  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(subcategorias.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = subcategorias.slice(inicio, inicio + porPagina);

  visibles.forEach((s, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-dark btn-lg d-flex justify-content-between align-items-center m-2';
    btn.dataset.subcategoriaId = s.id;

    btn.innerHTML = `
      <span class="badge-opcion">Opción ${index + 1}</span>
      <span class="flex-grow-1 text-start">${s.nombre}</span>
      <span class="badge-disponibles">${s.disponibles} disponibles</span>
    `;
    contenedor.appendChild(btn);
  });

  // Mostrar paginas si hay mas de una
  actualizarVisibilidadPaginador(paginador, totalPaginas);


  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = "Pagina " + i;
    pagBtn.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('pag sub stop failed', e); }
      setTimeout(() => getRenderer('renderSubcategoriasPaginadas')(subcategorias, i), 60);
    };
    paginador.appendChild(pagBtn);
  }

  window.paginaSubcategoriasActual = pagina;

  try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) { console.warn('renderSubcategorias safeStart failed', e); }
}


function seleccionarSubcategoria(subcategoriaId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/recursos-disponibles/${subcategoriaId}`, true);

  xhr.onload = function () {
    try {
      const recursos = JSON.parse(xhr.responseText);
      console.log('📦 seleccionarSubcategoria: recursos recibidos', recursos);
      window.recursosActuales = recursos.filter(r => r.disponibles > 0);
  getRenderer('renderRecursosPaginados')(window.recursosActuales, 1);
      window.nextStep(7);
    } catch (e) {
  getRenderer('mostrarModalKioscoSinVoz')('No se pudieron cargar los recursos', 'danger');
      console.log('❌ No se pudieron cargar los recursos', e);
    }
  };

  xhr.send();
}

function renderRecursosPaginados(recursos, pagina = 1) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderRecursosPaginados: safeStop failed', e); }

  const contenedor = document.getElementById('recurso-buttons');
  const paginador = document.getElementById('paginadorRecursos');
  if (!contenedor || !paginador) {
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }
  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-dark btn-lg d-flex justify-content-between align-items-center m-2';
    btn.dataset.recursoId = r.id;

    btn.innerHTML = `
      <span class="badge-opcion">Opción ${index + 1}</span>
      <span class="flex-grow-1 text-start">${r.nombre}</span>
      <span class="badge-disponibles">${r.disponibles} disponibles</span>
    `;
    contenedor.appendChild(btn);
  });

  // Mostrar paginas si hay mas de una
  actualizarVisibilidadPaginador(paginador, totalPaginas);


  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = "Pagina " + i;
    pagBtn.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('pag recursos stop failed', e); }
      setTimeout(() => getRenderer('renderRecursosPaginados')(recursos, i), 60);
    };
    paginador.appendChild(pagBtn);
  }

  window.paginaRecursosActual = pagina;

  try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) { console.warn('renderRecursosPaginados safeStart failed', e); }
}

function seleccionarRecurso(recursoId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/series/${recursoId}`, true);

  xhr.onload = function () {
    try {
      const series = JSON.parse(xhr.responseText);
      console.log('🔢 seleccionarRecurso: series recibidas', series);
      window.seriesActuales = series;
  getRenderer('renderSeriesPaginadas')(series, 1);
      window.nextStep(8);
    } catch (e) {
  getRenderer('mostrarModalKioscoSinVoz')('No se pudieron cargar las series', 'danger');
      console.log('❌ No se pudieron cargar las series', e);
    }
  };

  xhr.onerror = function () {
  getRenderer('mostrarModalKioscoSinVoz')('Error de red al cargar las series', 'danger');
  };

  xhr.send();
}

function renderSeriesPaginadas(series, pagina = 1) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderSeriesPaginadas: safeStop failed', e); }

  const contenedor = document.getElementById('serie-buttons');
  const paginador = document.getElementById('paginadorSeries');
  if (!contenedor || !paginador) {
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }
  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(series.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = series.slice(inicio, inicio + porPagina);

  visibles.forEach((s, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-dark btn-lg d-flex justify-content-between align-items-center m-2';
    btn.dataset.serieId = s.id;

    const textoSerie = s.nro_serie || s.codigo || `Serie ${s.id}`;
    btn.innerHTML = `
      <span class="badge-opcion">Opción ${index + 1}</span>
      <span class="flex-grow-1 text-start">${textoSerie}</span>
    `;

    contenedor.appendChild(btn);
  });

  // Mostrar paginas si hay mas de una
  actualizarVisibilidadPaginador(paginador, totalPaginas);


  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = "Pagina " + i;
    pagBtn.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('pag series stop failed', e); }
      setTimeout(() => getRenderer('renderSeriesPaginadas')(series, i), 60);
    };
    paginador.appendChild(pagBtn);
  }

  window.paginaSeriesActual = pagina;

  try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) { console.warn('renderSeriesPaginadas safeStart failed', e); }
}

function confirmarSerieModal(serieId, serieTexto = '', options = {}, botonSerie = null) {
  botonSerie = botonSerie || window.botonSerieSeleccionada || null;

  const registrar = options.registrarSerie || window.registrarSerie;
  const mostrarMensaje = options.mostrarMensajeKiosco || getRenderer('mostrarMensajeKiosco');

  const body = document.getElementById('modalConfirmarSerieBody');
  if (body) body.textContent = `¿Confirmás que querés solicitar el recurso "${serieTexto}"?`;

  const modalEl = document.getElementById('modalConfirmarSerie');
  if (!modalEl) {
    if (confirm(`¿Confirmás que querés solicitar el recurso "${serieTexto}"?`)) {
      if (typeof registrar === 'function') registrar(serieId, botonSerie);
    }
    return;
  }

  const modal = new bootstrap.Modal(modalEl);
  const aceptarBtn = document.getElementById('btnAceptarSerie');
  const cancelarBtn = document.getElementById('btnCancelarSerie');

  let modalActionTaken = false;

  function cleanup() {
    try {
      const existing = modalEl._recogInstance;
      if (existing) {
        try { existing.onresult = null; } catch (e) {}
        try { existing.onerror = null; } catch (e) {}
        try { existing.onend = null; } catch (e) {}
        try { if (typeof existing.stop === 'function') existing.stop(); } catch (e) {}
      }
    } catch (e) {}
    modalEl._recogInstance = null;
    modalEl._lastTranscript = null;
  }

  function onAceptar() {
    if (modalActionTaken) return;
    modalActionTaken = true;
    modal.hide();
    cleanup();
    if (typeof registrar === 'function') registrar(serieId, botonSerie);
  }

  function onCancelar() {
    if (modalActionTaken) return;
    modalActionTaken = true;
    modal.hide();
    cleanup();
   // if (typeof mostrarMensaje === 'function') mostrarMensaje('Solicitud cancelada.', 'info');
  }

  try {
    if (aceptarBtn) {
      aceptarBtn.removeEventListener('click', onAceptar);
      aceptarBtn.addEventListener('click', onAceptar);
    }
    if (cancelarBtn) {
      cancelarBtn.removeEventListener('click', onCancelar);
      cancelarBtn.addEventListener('click', onCancelar);
    }
  } catch (e) {}

  try {
    recognitionGlobalPaused = true;
    if (recognitionGlobal && typeof recognitionGlobal.abort === 'function') {
      recognitionGlobal.abort();
      console.log('🛑 Recognition global abortado y marcado como pausado');
    }
  } catch (e) { console.warn('⚠️ No se pudo abortar recognitionGlobal:', e); }

  // === Inicio: reconocimiento local robusto para confirmarSerieModal ===
  try {
    if ('webkitSpeechRecognition' in window) {
      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = false; // evitar reinicios automáticos y races con el global
      recog.interimResults = false;

      // inicializadores locales en el elemento modal
      modalEl._lastTranscript = null;

      recog.onresult = function (event) {
        const texto = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
        console.log('🎤 Texto reconocido (modal serie):', texto);

        if (modalActionTaken) return;

        // Evitar repeticiones exactas
        if (modalEl._lastTranscript === texto) {
          console.log('🔁 Texto repetido, ignorado:', texto);
          return;
        }
        modalEl._lastTranscript = texto;

        // Comandos válidos
        if (texto.includes('aceptar') || texto.includes('confirm')) {
          try { aceptarBtn?.click(); } catch (e) { onAceptar(); }
          try { recog.stop(); } catch (e) {}
          return;
        }

        if (texto.includes('cancelar') || texto === 'no') {
          try { cancelarBtn?.click(); } catch (e) { onCancelar(); }
          try { recog.stop(); } catch (e) {}
          return;
        }

        // Comando no reconocido: feedback y no forzar stop/start aquí
        console.log('🗣️ Comando no reconocido en modal serie:', texto);
        if (typeof mostrarMensaje === 'function') {
        //  mostrarMensaje('No se reconoció el comando. Decí “aceptar” o “cancelar”.', 'info');
        }
        // No hacemos stop/start; onend decidirá si reiniciar
      };

      // onend solo reintenta reiniciar si el modal sigue abierto, no se tomó acción y el global no está corriendo
      recog.onend = function () {
        try {
          if (modalActionTaken) return;
          if (!modalEl || !modalEl.classList || !modalEl.classList.contains('show')) return;
          if (recognitionRunning) {
            console.log('ℹ️ onend: recognition global corriendo, no reinicio recog modal');
            return;
          }
          try {
            recog.start();
            console.log('🔁 reconocimiento local (modal serie) reiniciado desde onend');
          } catch (err) {
            console.warn('⚠️ No se pudo reiniciar recog local desde onend (ignored):', err);
          }
        } catch (e) {
          console.warn('onend (modal serie) excep:', e);
        }
      };

      recog.onerror = function (e) {
        if (e?.error === 'aborted') {
          console.log('ℹ️ Reconocimiento modal abortado (intencional/conflicto)');
          return;
        }
        console.warn('Reconocimiento de voz modal falló', e);
      };

      modalEl._recogInstance = recog;

      try {
        recog.start();
        console.log('🔔 reconocimiento local (modal serie) iniciado (no-continuous)');
      } catch (e) {
        console.warn('No se pudo iniciar reconocimiento del modal:', e);
      }
    }
  } catch (e) {
    console.warn('No se pudo crear reconocimiento del modal', e);
  }
  // === Fin: reconocimiento local robusto para confirmarSerieModal ===

  const onHidden = () => {
    modalEl.removeEventListener('hidden.bs.modal', onHidden);
    modalEl._opening = false;
    cleanup();
    window.botonSerieSeleccionada = null;
    recognitionGlobalPaused = false;

    // reactivar el recognition global de forma segura usando la helper que evita starts dobles
    try {
      safeStartRecognitionGlobal();
      console.log('🎤 safeStartRecognitionGlobal llamado tras cerrar modal serie');
    } catch (e) {
      console.warn('No se pudo reiniciar recognitionGlobal:', e);
    }
  };
  modalEl.addEventListener('hidden.bs.modal', onHidden);

  modal.show();
}




async function registrarSerie(serieId, boton = null) {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!serieId || !id_usuario) {
    mostrarModalKioscoSinVoz('⚠️ Serie o usuario inválido', 'danger');
    return { success: false };
  }

  try {
    const res = await fetch(`/terminal/prestamos/${id_usuario}`, {
      method: 'POST',
      headers: getHeadersSeguros(),
      body: JSON.stringify({ series: [serieId] })
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (data.success) {
      const mensaje = `✅ Recurso asignado correctamente${data.recurso ? ': ' + data.recurso : ''}${data.serie ? ' - Serie ' + data.serie : ''}.`;
      mostrarModalKioscoSinVoz(mensaje, 'success');

      if (boton instanceof HTMLElement) {
        boton.innerHTML = `<span class="flex-grow-1 text-start">✅ Recurso asignado</span>`;
        boton.disabled = true;
        boton.classList.remove('btn-outline-success');
        boton.classList.add('btn-success');
      }

      return { success: true, data };
    } else {
      mostrarModalKioscoSinVoz(data.message || 'Error al registrar recurso', 'danger');
      return { success: false, data };
    }
  } catch (err) {
    return manejarErrorFetch(err, 'Registro de serie');
  }
}


document.addEventListener('DOMContentLoaded', () => {
  // Inicializar escáner QR de devolución de forma defensiva
  try {
    if (typeof Html5Qrcode !== 'undefined') {
      try {
        const qrScanner = new Html5Qrcode("qr-reader-devolucion");
        qrScanner.start(
          { facingMode: "environment" },
          { fps: 10, qrbox: 250 },
          ExitoDevolucionQR
        );
      } catch (e) {
        console.warn('QR devolucion init falló (start):', e);
      }
    } else {
      console.warn('Html5Qrcode no disponible en este contexto');
    }
  } catch (e) {
    console.warn('QR devolucion init general falló:', e);
  }

  // --- Listener seguro y único para el botón "Menu principal" ---
  (function bindSafeMenuPrincipal() {
    const btn = document.getElementById('boton-flotante-menu-principal');
    if (!btn) return;

    // Limpiar handlers inseguros previos
    try {
      // eliminar onclick directo si existiera
      btn.onclick = null;
      // si previamente guardamos un handler, eliminarlo
      if (btn._safeMenuHandler) {
        try { btn.removeEventListener('click', btn._safeMenuHandler, true); } catch (e) {}
        btn._safeMenuHandler = null;
        btn._safeMenuListenerAttached = false;
      }
    } catch (e) {
      console.warn('bindSafeMenuPrincipal: limpieza previa falló', e);
    }

    // Handler seguro
    const handler = function (e) {
      try {
        const stepActivo = document.querySelector('.step.active')?.id || getStepActivo();
        const idUsuario = window.localStorage.getItem('id_usuario');

        // Si estamos en step1 y no hay usuario identificado, bloquear navegación
        if ((stepActivo === 'step1' || stepActivo === '1') && !idUsuario) {
          e.stopImmediatePropagation();
          e.preventDefault();
          getRenderer('mostrarModalKioscoSinVoz')('Debés identificarte antes de abrir el Menú principal', 'warning');
          return;
        }

        // Permitido: detener scanners y abrir menú
        try { detenerEscaneoQRDevolucionSegura(); } catch (err) { console.warn('stop escaneo previo falló', err); }
        if (typeof window.cargarMenuPrincipal === 'function') window.cargarMenuPrincipal();
        if (typeof window.nextStep === 'function') window.nextStep(2);

        // reactivar reconocimiento global con pequeño delay
        setTimeout(() => {
          try { safeStartRecognitionGlobal(); } catch (err) { /* ignore */ }
        }, 120);
      } catch (err) {
        console.warn('bindSafeMenuPrincipal handler error', err);
      }
    };

    // Guardar referencias para evitar múltiples attachments
    btn._safeMenuHandler = handler;
    btn._safeMenuListenerAttached = true;

    // Usar listener en captura para interceptar antes que handlers en bubbling
    btn.addEventListener('click', handler, true);
  })();

  // --- Botón Borrar clave (idempotente) ---
  try {
    const btnBorrar = document.getElementById('btnBorrarClave');
    const claveInput = document.getElementById('clave');
    if (btnBorrar && claveInput && !btnBorrar._borrarAttached) {
      btnBorrar.addEventListener('click', () => {
        claveInput.value = '';
        //claveInput.focus();
       // getRenderer('mostrarMensajeKiosco')('clave borrada', 'info');
      });
      btnBorrar._borrarAttached = true;
    }
  } catch (e) {
    console.warn('Error conectando btnBorrarClave', e);
  }

  // --- Botón Aceptar Cerrar Sesión (idempotente) ---
  try {
    const btnAceptarCerrarSesion = document.getElementById('btnAceptarCerrarSesion');
    if (btnAceptarCerrarSesion && !btnAceptarCerrarSesion._cerrarAttached) {
      btnAceptarCerrarSesion.addEventListener('click', () => {
        try { detenerEscaneoQRDevolucionSegura(); } catch (e) { console.warn('detenerEscaneo en cerrar sesion falló', e); }
        try { volverAInicio(); } catch (e) { console.warn('volverAInicio falló', e); }
      });
      btnAceptarCerrarSesion._cerrarAttached = true;
    }
  } catch (e) {
    console.warn('Error conectando btnAceptarCerrarSesion', e);
  }

  // --- Estado inicial defensivo: asegurar que en step1 el botón no permita acción ---
  try {
    const btnMenu = document.getElementById('boton-flotante-menu-principal');
    const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
    const activo = document.querySelector('.step.active')?.id || getStepActivo();
    const enStep1 = (activo === 'step1' || activo === '1' || document.getElementById('step1')?.classList.contains('active'));

    if (btnMenu) {
      if (enStep1) {
        btnMenu.disabled = true;
        btnMenu.setAttribute('aria-disabled', 'true');
        btnMenu.style.pointerEvents = 'none';
        //btnMenu.style.opacity = '0.5';
      } else {
        btnMenu.disabled = false;
        btnMenu.removeAttribute('aria-disabled');
        btnMenu.style.pointerEvents = 'auto';
        btnMenu.style.opacity = '1';
      }
    }

    if (btnCerrar) {
      if (enStep1) {
        btnCerrar.disabled = true;
        btnCerrar.setAttribute('aria-disabled', 'true');
        btnCerrar.style.pointerEvents = 'none';
       // btnCerrar.style.opacity = '0.5';
      } else {
        btnCerrar.disabled = false;
        btnCerrar.removeAttribute('aria-disabled');
        btnCerrar.style.pointerEvents = 'auto';
        btnCerrar.style.opacity = '1';
      }
    }
  } catch (e) {
    console.warn('Error aplicando estado inicial a botones flotantes', e);
  }

  // --- Reaplicar estado defensivo tras cargas tardías / posibles re-creaciones ---
  // Si nextStep existe, envolverlo para reaplicar la verificación luego de cada cambio de step
  try {
    if (typeof window.nextStep === 'function' && !window._nextStepWrappedForMenuProtection) {
      const origNext = window.nextStep;
      window.nextStep = function (n) {
        try { origNext(n); } catch (e) { console.warn('wrapped nextStep original falló', e); }
        // reaplicar estado con pequeño delay para evitar races
          try {
            const stepId = typeof n === 'number' ? 'step' + n : n;
            actualizarVisibilidadBotonesPorStep(stepId);
          } catch (e) {
            console.warn('Reaplicación visibilidad falló', e);
          }

      };
      window._nextStepWrappedForMenuProtection = true;
    }
  } catch (e) {
    console.warn('No se pudo wrappear nextStep para protección adicional', e);
  }
});


function BorrarClave() {
  const claveInput = document.getElementById('clave');
  if (claveInput) {
    claveInput.value = '';
    //claveInput.focus();
    //getRenderer('mostrarMensajeKiosco')('clave borrada', 'info');
  }
}

const recursosTabs = document.getElementById('recursosTabs');
if (recursosTabs) {
  recursosTabs.addEventListener('shown.bs.tab', function (event) {
    const tabId = event.target.id;
    try { safeStopRecognitionGlobal(); } catch (e) { console.warn('recursosTabs shown stop failed', e); }

    if (tabId === 'tab-epp') {
      getRenderer('renderTablaRecursos')('tablaEPP', window.recursosEPP || [], window.paginaEPPActual || 1, 'paginadorEPP');
    } else if (tabId === 'tab-herramientas') {
      getRenderer('renderTablaRecursos')('tablaHerramientas', window.recursosHerramientas || [], window.paginaHerramientasActual || 1, 'paginadorHerramientas');
    }

    // Reiniciar micrófono tras re-render del tab con pequeño delay
    try {
      setTimeout(() => { safeStartRecognitionGlobal(); console.log('🎤 safeStart tras cambiar tab recursos'); }, 120);
    } catch (e) { console.warn('recursosTabs safeStart failed', e); }
  });
}

// Delegación para subcategorías
const _subcatButtons = document.getElementById('subcategoria-buttons');
if (_subcatButtons) {
  _subcatButtons.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-subcategoria-id]');
    if (btn) seleccionarSubcategoria(btn.dataset.subcategoriaId);
  });
}

// Delegación para recursos
const _recursoButtons = document.getElementById('recurso-buttons');
if (_recursoButtons) {
  _recursoButtons.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-recurso-id]');
    if (btn) seleccionarRecurso(btn.dataset.recursoId);
  });
}

// Delegación para series (abre modal de confirmación)
const _serieButtons = document.getElementById('serie-buttons');
if (_serieButtons) {
  _serieButtons.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-serie-id]');
    if (!btn) return;
    const serieTextoEl = btn.querySelector('.flex-grow-1');
    const serieTexto = serieTextoEl ? serieTextoEl.textContent.trim() : btn.textContent.trim();
    window.botonSerieSeleccionada = btn;
  confirmarSerieModal(btn.dataset.serieId, serieTexto, { registrarSerie, mostrarMensajeKiosco: getRenderer('mostrarMensajeKiosco') }, btn);
  });
}


// 👇 nuevo: target de retorno para step5
let step5ReturnTarget = 2; // default: menú principal

function setModoEscaneo(modo) {
  const titulo = document.getElementById('titulo-step3');
  if (modo === 'manual') {
    console.log('🔄 setModoEscaneo: modo manual activado');
    titulo.innerHTML = `
      <img src="/images/trabajadorHerramienta.svg" alt="Herramienta" class="icono-herramienta">
      Tengo la herramienta en mano
    `;
    detenerEscaneoQRregistroRecursos();
    step5ReturnTarget = 3;
  } else {
    console.log('🔄 setModoEscaneo: modo escaneo QR activado');
    titulo.textContent = '📷 Escanear Recurso';
    activarEscaneoQRregistroRecursos();
    // escaneo QR no cambia el target de step5
  }
  window.nextStep(3);
}

function cargarMenuPrincipal() {
  const contenedor = document.getElementById('menu-principal-buttons');
  contenedor.innerHTML = '';

  const opciones = [
    {
      id: 1,
      texto: "Tengo la herramienta en mano",
      accion: () => setModoEscaneo('manual'),
      clase: "btn-outline-dark",
      icono: "/images/trabajadorHerramienta.svg"
    },
    {
      id: 2,
      texto: "Quiero solicitar una herramienta",
      accion: async () => {
        const sesionOk = await verificarSesionActiva();
        if (!sesionOk) return;

        const id_usuario = localStorage.getItem('id_usuario');
        try {
          const res = await fetch('/terminal/solicitar', {
            method: 'POST',
            headers: getHeadersSeguros(),
            body: JSON.stringify({ id_usuario })
          });

          const data = await res.json();
          if (!data.success) {
            mostrarModalKioscoSinVoz(data.message || 'No se puede solicitar herramientas', 'warning');
            return;
          }

          step5ReturnTarget = 2;
          window.nextStep(5);
        } catch (err) {
          manejarErrorFetch(err, 'Solicitud de herramienta');
        }
      },
      clase: "btn-outline-dark",
      icono: "/images/herramienta2.svg"
    },
    {
      id: 3,
      texto: "Ver recursos asignados",
      accion: () => {
        window.cargarRecursos().then(() => abrirStepRecursos());
      },
      clase: "btn-outline-dark",
      icono: "/images/list.svg"
    }
  ];

  opciones.forEach(op => {
    const btn = document.createElement('button');
    btn.className = `btn ${op.clase} btn-lg d-flex align-items-center justify-content-start m-2 w-100`;
    btn.innerHTML = `
      <span class="badge-opcion">Opción ${op.id}</span>
      <span class="ms-2 flex-grow-1 text-start d-flex align-items-center gap-2">
        ${op.icono ? `<img src="${op.icono}" alt="Icono" class="icono-opcion">` : ''}
        ${op.texto}
      </span>
    `;
    btn.onclick = op.accion;
    contenedor.appendChild(btn);
  });
}


// 👇 nuevo: función para botón Volver en step5
function volverDesdeStep5() {
  window.nextStep(step5ReturnTarget);
}

let ultimoTabElegido = 'epp';
let ultimaPaginaElegida = 1;
window.ignoreVoiceOptionsEPP = true;

// RECURSOS ASIGNADOS - STEP 10
function abrirStepRecursos() {
  const stepId = 'step10';
  let stepEl = document.getElementById(stepId);

  if (!stepEl) {
    stepEl = document.createElement('div');
    stepEl.id = stepId;
    stepEl.className = 'step d-none';

    const rutaCasco = '/images/casco3.svg';
    const rutaHerramienta = '/images/tool.svg';

    stepEl.innerHTML = `
      <h2 class="mb-4 text-center d-flex justify-content-center align-items-center gap-2">
        <img src="/images/herramienta3.svg" alt="Recursos" class="icono-opcion">
        <span>Recursos asignados</span>
      </h2>

      <div class="d-flex justify-content-center mb-3">
        <button class="btn btn-primary me-2 d-flex align-items-center gap-2 active" id="tab-epp-step" type="button" aria-selected="true">
          <img src="${rutaCasco}" alt="EPP" class="icono-opcion">
          <span>Ver EPP</span>
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2" id="tab-herramientas-step" type="button" aria-selected="false">
          <img src="${rutaHerramienta}" alt="Herramientas" class="icono-opcion">
          <span>Ver herramientas</span>
        </button>
      </div>

      <div id="recursosTabContentStep" class="tab-content">
        <div id="panel-epp-step" class="tab-pane show active">
          <div id="recursos-asignados-epp" class="mb-3"></div>
          <div id="paginadorEPP-step" class="d-flex flex-wrap justify-content-center mt-3"></div>
        </div>

        <div id="panel-herramientas-step" class="tab-pane">
          <div id="recursos-asignados-herramientas" class="mb-3"></div>
          <div id="paginadorHerramientas-step" class="d-flex flex-wrap justify-content-center mt-3"></div>
        </div>
      </div>

      <div class="text-center mt-3">
        <button id="btnVolverStepRecursos" class="btn btn-primary texto-volver d-flex align-items-center gap-2">
          <img src="/images/volver.svg" alt="Volver" class="icono-opcion">
          <span>Volver</span>
        </button>
      </div>
    `;

    document.querySelector('.container-kiosk')?.appendChild(stepEl);
  }

  if (stepEl._opening) return;
  stepEl._opening = true;

  recognitionGlobalPaused = true;
  try { safeStopRecognitionGlobal(); } catch (e) {}
  try { nextStep(10); } catch (e) {}

  try {
    const tabEPP = document.getElementById('tab-epp-step');
    const tabHerr = document.getElementById('tab-herramientas-step');
    const panelEPP = document.getElementById('panel-epp-step');
    const panelHerr = document.getElementById('panel-herramientas-step');

    const tab = ultimoTabElegido === 'herramientas' ? 'herramientas' : 'epp';

    if (tab === 'herramientas') {
      tabHerr?.classList.add('active');
      tabHerr?.setAttribute('aria-selected', 'true');
      tabEPP?.classList.remove('active');
      tabEPP?.setAttribute('aria-selected', 'false');
      panelHerr?.classList.add('show', 'active');
      panelEPP?.classList.remove('show', 'active');
    } else {
      tabEPP?.classList.add('active');
      tabEPP?.setAttribute('aria-selected', 'true');
      tabHerr?.classList.remove('active');
      tabHerr?.setAttribute('aria-selected', 'false');
      panelEPP?.classList.add('show', 'active');
      panelHerr?.classList.remove('show', 'active');
    }

// ⚡ Cargar EPP desde backend
const usuarioId = window.usuarioActual?.id;
if (usuarioId) {
  fetch(`/terminal/epp-asignados/${usuarioId}`)
    .then(r => r.json())
    .then(data => {
      window.recursosEPP = data;
      if (Array.isArray(data) && data.length > 0) {
        renderRecursosAsignados(data, 1, 'recursos-asignados-epp', 'paginadorEPP-step', true);
      } else {
        document.getElementById('recursos-asignados-epp').innerHTML =
          `<div class="text-center text-muted">No tiene EPP asignado</div>`;
      }
    })
    .catch(err => {
      console.error('Error cargando EPP asignados', err);
      document.getElementById('recursos-asignados-epp').innerHTML =
        `<div class="text-center text-muted">Error al cargar EPP</div>`;
    });
} else {
  document.getElementById('recursos-asignados-epp').innerHTML =
    `<div class="text-center text-muted">Usuario no identificado</div>`;
}


    // ⚡ Herramientas (ya las tenías)
    if (window.recursosHerramientas) {
      renderRecursosAsignados(window.recursosHerramientas, window.paginaHerramientasActual || 1, 'recursos-asignados-herramientas', 'paginadorHerramientas-step');
    }
  } catch (e) { console.warn('abrirStepRecursos: preparar UI falló', e); }

  // Listeners de UI
  try {
    const btnVolver = document.getElementById('btnVolverStepRecursos');
    if (btnVolver && !btnVolver._connected) {
      btnVolver.addEventListener('click', () => {
        recognitionGlobalPaused = false;
        safeStartRecognitionGlobal();
        nextStep(2);
      });
      btnVolver._connected = true;
    }

    const tabEPPBtn = document.getElementById('tab-epp-step');
    const tabHerrBtn = document.getElementById('tab-herramientas-step');
    if (tabEPPBtn && !tabEPPBtn._connected) {
      tabEPPBtn.addEventListener('click', () => {
        document.getElementById('panel-epp-step')?.classList.add('show', 'active');
        document.getElementById('panel-herramientas-step')?.classList.remove('show', 'active');
        tabEPPBtn.classList.add('active'); tabEPPBtn.setAttribute('aria-selected', 'true');
        tabHerrBtn.classList.remove('active'); tabHerrBtn.setAttribute('aria-selected', 'false');
        safeStartRecognitionGlobal();
        ultimoTabElegido = 'epp';
      });
      tabEPPBtn._connected = true;
    }
    if (tabHerrBtn && !tabHerrBtn._connected) {
      tabHerrBtn.addEventListener('click', () => {
        document.getElementById('panel-herramientas-step')?.classList.add('show', 'active');
        document.getElementById('panel-epp-step')?.classList.remove('show', 'active');
        tabHerrBtn.classList.add('active'); tabHerrBtn.setAttribute('aria-selected', 'true');
        tabEPPBtn.classList.remove('active'); tabEPPBtn.setAttribute('aria-selected', 'false');
        safeStartRecognitionGlobal();
        ultimoTabElegido = 'herramientas';
      });
      tabHerrBtn._connected = true;
    }
  } catch (e) { console.warn('abrirStepRecursos: conectar listeners falló', e); }

  stepEl._opening = false;
}



function renderRecursosAsignados(recursos, pagina = 1, contenedorId, paginadorId, esEpp = false) {
  try { safeStopRecognitionGlobal(); } catch (e) {}

  const contenedor = document.getElementById(contenedorId);
  const paginador = document.getElementById(paginadorId);
  if (!contenedor || !paginador) return;

  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn-resumen';
    btn.dataset.detalleId = r.detalle_id;
    btn.dataset.serie = r.serie || '';
    btn.dataset.recurso = r.recurso || '';
    btn.dataset.opcionIndex = index + 1;

    if (!esEpp) {
      btn.onclick = () => mostrarStepDevolucionQR(r.serie, r.detalle_id);
    } else {
      btn.disabled = true; // no clickeable
    }

    btn.innerHTML = `
      <div class="d-flex flex-row justify-content-between align-items-center w-100">
        ${!esEpp ? `<span class="badge-opcion">Opción ${index + 1}</span>` : ''}
        <div class="d-flex flex-column text-start" style="flex: 1; min-width: 0;">
          <span>${r.recurso || '-'}</span>
          <span class="text-muted">${r.serie || '-'}</span>
        </div>
        <div class="d-flex flex-column text-end" style="flex-shrink: 0;">
          <span class="text-muted">${esEpp ? 'Asignación' : 'Devolución'}</span>
          <span>${esEpp ? (r.fecha_asignacion || '-') : (r.fecha_devolucion || '-')}</span>
        </div>
      </div>
    `;

    contenedor.appendChild(btn);
  });

  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = `Página ${i}`;
    pagBtn.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) {}
      ultimaPaginaElegida = i;
      setTimeout(() => renderRecursosAsignados(recursos, i, contenedorId, paginadorId, esEpp), 60);
    };
    paginador.appendChild(pagBtn);
  }

  if (contenedorId === 'recursos-asignados-epp') window.paginaEPPActual = pagina;
  if (contenedorId === 'recursos-asignados-herramientas') window.paginaHerramientasActual = pagina;

  try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
}


/*
function renderTablaRecursosStep(tablaId, recursos = [], pagina = 1, paginadorId) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderTablaRecursosStep: safeStop failed', e); }

  const tabla = document.getElementById(tablaId);
  const paginador = document.getElementById(paginadorId);
  if (!tabla || !paginador) {
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }

  const porPagina = cantidadRecursosPorPagina;
  const totalPaginas = Math.max(1, Math.ceil((recursos || []).length / porPagina));
  const inicio = (pagina - 1) * porPagina;
  const visibles = (recursos || []).slice(inicio, inicio + porPagina);

  tabla.innerHTML = '';
  if (visibles.length === 0) {
    tabla.innerHTML = `<tr><td colspan="${porPagina}" class="text-center">No tiene recursos asignados</td></tr>`;
    paginador.innerHTML = '';
    try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
    return;
  }

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm btn-primary';
    btn.dataset.detalleId = r.detalle_id;
    btn.dataset.serie = r.serie || '';
    btn.dataset.recurso = r.recurso || '';
    btn.dataset.opcionIndex = index + 1;
    btn.innerHTML = `Opción ${index + 1}`;
    btn.onclick = () => mostrarStepDevolucionQR(r.serie, r.detalle_id);

    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${r.subcategoria || '-'} / ${r.recurso || '-'}</td>
      <td>${r.serie || '-'}</td>
      <td>${r.fecha_prestamo || '-'}</td>
      <td>${r.fecha_devolucion || '-'}</td>
      <td></td>
    `;
    row.children[4].appendChild(btn);
    tabla.appendChild(row);
  });

  // paginador
  paginador.innerHTML = '';
  for (let i = 1; i <= totalPaginas; i++) {
    const b = document.createElement('button');
    b.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    b.textContent = `Página ${i}`;
    b.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) {}
      ultimaPaginaElegida = i;
      setTimeout(() => renderTablaRecursosStep(tablaId, recursos, i, paginadorId), 60);
    };
    paginador.appendChild(b);
  }

  if (tablaId === 'tablaEPP-step') window.paginaEPPActual = pagina;
  if (tablaId === 'tablaHerramientas-step') window.paginaHerramientasActual = pagina;

  // ✅ Emitir evento para sincronización con reconocimiento por voz
  document.dispatchEvent(new CustomEvent('tablaRecursosRenderizada', {
    detail: { tablaId, pagina }
  }));

  try { setTimeout(() => safeStartRecognitionGlobal(), 80); } catch (e) {}
}
*/

function confirmarDevolucionPorVozStep10(index) {
  console.log(`🎤 confirmarDevolucionPorVozStep10: opción ${index}`);

  const eppActivo = document.getElementById('tab-epp-step')?.classList.contains('active');
  const herrActivo = document.getElementById('tab-herramientas-step')?.classList.contains('active');

  // 🚫 Si estamos en EPP y el flag está activo, ignoramos el comando
  if (eppActivo && window.ignoreVoiceOptionsEPP) {
    console.log('🎤 Ignorando comando Opción N en tab EPP');
    return;
  }

  const contenedorId = eppActivo
    ? 'recursos-asignados-epp'
    : herrActivo
    ? 'recursos-asignados-herramientas'
    : null;

  if (!contenedorId) {
    console.warn('❌ No se pudo determinar el contenedor activo');
    return;
  }

  const btn = document.querySelector(`#${contenedorId} button[data-opcion-index="${index}"]`);
  if (!btn) {
    console.warn(`❌ Opción ${index} no encontrada en ${contenedorId}`);
    getRenderer('mostrarModalKioscoSinVoz')(`No se encontró la opción ${index}. Verificá que esté visible.`, 'warning');
    return;
  }

  const detalleId = btn.dataset.detalleId;
  const serie = btn.dataset.serie || '';

  if (!detalleId) {
    console.warn(`❌ El botón opción ${index} no tiene detalleId`);
    getRenderer('mostrarModalKioscoSinVoz')(`El recurso no tiene un identificador válido.`, 'warning');
    return;
  }

  console.log(`➡️ confirmarDevolucionPorVozStep10: botón encontrado, detalleId=${detalleId}, serie=${serie}`);

  window._modalConfirmedByVoice = true;
  try { safeStopRecognitionGlobal(); } catch (e) {}
  mostrarStepDevolucionQR(serie, detalleId);
}



function handleStep10Pagina(numero, intentos = 0) {
  if (!Number.isFinite(numero) || numero < 1) {
    getRenderer('mostrarModalKioscoSinVoz')('Número de página no reconocido', 'warning');
    return;
  }

  const eppActivo = document.getElementById('tab-epp-step')?.classList.contains('active');
  const herrActivo = document.getElementById('tab-herramientas-step')?.classList.contains('active');

  const recursos = eppActivo
    ? window.recursosEPP
    : herrActivo
    ? window.recursosHerramientas
    : null;

  if (!Array.isArray(recursos)) {
    if (intentos < 5) {
      console.warn('⏳ Recursos aún no disponibles, reintentando...');
      setTimeout(() => handleStep10Pagina(numero, intentos + 1), 200);
    } else {
      getRenderer('mostrarModalKioscoSinVoz')('No se detectó el tab activo', 'warning');
    }
    return;
  }

  const total = Math.max(1, Math.ceil(recursos.length / cantidadRecursosPorPagina));
  if (numero > total) {
    getRenderer('mostrarModalKioscoSinVoz')('Número de página inválido', 'warning');
    return;
  }

  const contenedorId = eppActivo ? 'recursos-asignados-epp' : 'recursos-asignados-herramientas';
const paginadorId = eppActivo ? 'paginadorEPP-step' : 'paginadorHerramientas-step';
renderRecursosAsignados(recursos, numero, contenedorId, paginadorId);

}

// Detección permisiva para cambio de tabs EPP <-> Herramientas
function matchTabCambio(texto) {
  if (!texto) return null;
  const s = normalizarTexto(String(texto)).trim();

  // triggers más permisivos para EPP
  const eppTriggers = [
    'epp', 'ver epp', 'mostrar epp', 'cambiar epp',
    'equipo', 'equipo proteccion', 'equipo proteccion personal',
    'proteccion', 'proteccion personal', 'equipo de proteccion'
  ];

  // triggers para herramientas (formas y errores comunes)
  const herrTriggers = [
    'herramienta', 'herramientas', 'ver herramienta', 'ver herramientas',
    'mostrar herramienta', 'mostrar herramientas',
    'cambiar herramienta', 'cambiar herramientas', 'ver herramientas',
    'ver herramienta(s)?', 'herramient'
  ];

  // comprobaciones por inclusión (permite frases largas y errores parciales)
  for (const t of eppTriggers) {
    if (s.includes(t)) return 'epp';
  }
  for (const t of herrTriggers) {
    if (s.includes(t)) return 'herramientas';
  }

  // tokens aislados: si dicen solo "e p p" o "h e r r"
  const tokens = s.split(/\s+/).filter(Boolean);
  if (tokens.length === 3 && tokens.join('') === 'epp') return 'epp';
  if (tokens.length <= 4 && tokens.join('').startsWith('herramient')) return 'herramientas';

  return null;
}

// 🔧 Normalizar texto (quita acentos)
function normalizarTexto(str) {
  console.log('🔤 normalizarTexto: texto original →', str);
  
  return str
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}



// 🔍 Detectar qué step está activo
function getStepActivo() {
  const steps = document.querySelectorAll('.step');
  for (let s of steps) {
    if (s.classList.contains('active')) {
      console.log('🔍 getStepActivo: step activo detectado →', s.id);
      return s.id; // ej: "step2"
    }
  }
  return null;
}

// === Reconocimiento de voz global ===
let recognitionGlobal;
let recognitionRunning = false;
let recognitionGlobalPaused = false; // <- nueva bandera

function iniciarReconocimientoGlobal() {
  if (!('webkitSpeechRecognition' in window)) {
    console.warn('⚠️ Tu navegador no soporta reconocimiento de voz');
    window.mostrarMensajeKiosco('⚠️ Tu navegador no soporta reconocimiento de voz', 'warning');
    return;
  }

  recognitionGlobal = new webkitSpeechRecognition();
  recognitionGlobal.lang = 'es-ES';
  recognitionGlobal.continuous = true;
  recognitionGlobal.interimResults = false;

  recognitionGlobal.onstart = () => {
    recognitionRunning = true;
    console.log("🎤 Micrófono global activo");

   // if (mostrarMensajesMicrofono)
   //   window.mostrarMensajeKiosco('Micrófono activo: podés dar comandos por voz', 'info');
  };

  recognitionGlobal.onerror = (event) => {
    // Si abortamos intencionalmente, event.error === 'aborted'. No lo tratamos como fallo.
    if (event.error === "aborted") {
      console.log("ℹ️ Reconocimiento abortado intencionalmente");
      return;
    }
    console.warn('Error en reconocimiento global de voz:', event.error);
  };

  recognitionGlobal.onresult = (event) => {
    const texto = event.results[event.results.length - 1][0].transcript.toLowerCase().trim();
    const limpio = normalizarTexto(texto);
    console.log("👉 Reconocido:", limpio, "| Step activo:", getStepActivo());
    procesarComandoVoz(limpio);
  };

 recognitionGlobal.onend = () => {
  recognitionRunning = false;
  console.log("ℹ️ recognitionGlobal onend");
  // Si est\u00E1 pausado, no reiniciamos. Si no está pausado, delegamos a safeStartRecognitionGlobal (que comprueba estados)
  if (!recognitionGlobalPaused) {
    try {
      safeStartRecognitionGlobal();
    } catch (e) {
      console.warn('onend: safeStartRecognitionGlobal falló', e);
    }
  } else {
    console.log("ℹ️ Reconocimiento global pausado, no se reinicia");
  }
};


  try {
    recognitionGlobal.start();
  } catch (e) {
    console.warn('No se pudo iniciar recognitionGlobal:', e);
  }
}


// 👉 Arranca automáticamente al cargar la página
window.addEventListener('load', () => {
  iniciarReconocimientoGlobal();
  const claveInput = document.getElementById('clave');
  //if (claveInput) //claveInput.focus();
});


// === Reconocimiento manual para otros steps ===
let recognition;

function iniciarReconocimientoVoz() {
  if (!('webkitSpeechRecognition' in window)) {
    console.warn('⚠️ Tu navegador no soporta reconocimiento de voz');
    window.mostrarMensajeKiosco('⚠️ Tu navegador no soporta reconocimiento de voz', 'warning');
    return;
  }

  recognition = new webkitSpeechRecognition();
  recognition.lang = 'es-ES';
  recognition.continuous = false;
  recognition.interimResults = false;

  recognition.onresult = (event) => {
    const texto = event.results[0][0].transcript.toLowerCase().trim();
    const limpio = normalizarTexto(texto);
    procesarComandoVoz(limpio);
  };

  recognition.start();
  console.log('🎤 iniciarReconocimientoVoz: reconocimiento iniciado');
}

// matchOpcion: si se pasa 'numero' devuelve el número (Number) cuando coincide, otherwise false
function matchOpcion(limpio, numero, ...palabrasClave) {
  const palabra = Object.keys(MAPA_NUMEROS).find(k => MAPA_NUMEROS[k] === numero);

  console.log('🎯 matchOpcion: evaluando coincidencia para opción', numero);

  // Coincidencias explícitas
  if (limpio.includes(`opcion ${numero}`) || limpio.includes(`opción ${numero}`)) return numero;
  if (palabra && (limpio.includes(`opcion ${palabra}`) || limpio.includes(`opción ${palabra}`))) return numero;

  // Coincidencia exacta con solo el número o palabra
  if (limpio === `${numero}` || limpio === palabra) return numero;

  // Coincidencia por token aislado
  const tokens = limpio.split(/\s+/);
  for (const token of tokens) {
    const n = numeroDesdeToken(token);
    if (n === numero) return numero;
  }

  // Palabras clave adicionales
  if (palabrasClave.length && palabrasClave.some(p => limpio.includes(p))) return numero;

  return false;
}



function matchTextoBoton(limpio, btn) {
  if (!btn || !btn.textContent) return false;
  const textoBtn = normalizarTexto(btn.textContent);
  // eliminar prefijos tipo "opcion 1" y normalizar espacios y guiones
  const texto = textoBtn.replace(/opcion\s*\d+/i, '').replace(/[\s-]/g, '').trim();
  const comando = normalizarTexto(limpio).replace(/[\s-]/g, '').trim();
  console.log('🎯 matchTextoBoton: comparando comando vs botón', comando, texto);
  return texto.includes(comando) || comando.includes(texto);
}


// Conversión palabras -> número (siempre disponible antes de usarlo)
const MAPA_NUMEROS = {
  uno: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5,
  seis: 6, siete: 7, ocho: 8, nueve: 9, diez: 10,
  once: 11, doce: 12, trece: 13, catorce: 14, quince: 15,
  dieciseis: 16, diecisiete: 17, dieciocho: 18, diecinueve: 19, veinte: 20
};

// helper ya definido previamente (si no está, pegalo antes de procesar comandos)
function numeroDesdeToken(token) {
  if (!token && token !== 0) return NaN;
  const n = parseInt(token, 10);
  if (!isNaN(n)) return n;
  const normal = normalizarTexto(String(token || '')).replace(/\s+/g, '');
  return MAPA_NUMEROS[normal] || NaN;
}

// --- Modal Cerrar Sesion: creación segura (si ya existe en HTML, lo usa) ---
function asegurarModalCerrarSesion() {
  let modalEl = document.getElementById('modalCerrarSesion');
  if (!modalEl) {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
      <div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="z-index:2147483650;">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalCerrarSesionLabel">Confirmación de cierre de sesión</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalCerrarSesionBody">
              ¿Desea cerrar sesión?
            </div>
            <div class="modal-footer">
              <button id="btnCancelarCerrarSesion" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button id="btnAceptarCerrarSesion" type="button" class="btn btn-danger">Aceptar</button>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(wrapper);
    modalEl = document.getElementById('modalCerrarSesion');
  }
  return modalEl;
}

// --- Acción que ejecuta el cierre real de sesión (sin UI) ---
function ejecutarCerrarSesion() {
  try {
    recognitionGlobalPaused = true;
    safeStopRecognitionGlobal();
  } catch (e) {
    console.warn('⚠️ ejecutarCerrarSesion: safeStopRecognitionGlobal falló', e);
  }

  try {
    localStorage.removeItem('id_usuario');
    console.log('🔓 Sesión cerrada (ejecutarCerrarSesion), volviendo a step1');
    BorrarClave();
  } catch (e) {
    console.warn('⚠️ ejecutarCerrarSesion: error limpiando localStorage', e);
  }

  try {
    nextStep(1);
  } catch (e) {
    console.warn('⚠️ ejecutarCerrarSesion: nextStep(1) falló', e);
  }

  reactivarReconocimientoGlobal(); // ✅ blindado
}


// --- Mostrar modal y conectar botones (idempotente) ---
function mostrarModalCerrarSesion() {
  const modalEl = asegurarModalCerrarSesion();
  if (!modalEl || modalEl._opening) return;
  modalEl._opening = true;

  recognitionGlobalPaused = true;
  try { safeStopRecognitionGlobal(); } catch (e) {}

  const aceptarBtn = modalEl.querySelector('#btnAceptarCerrarSesion');
  const cancelarBtn = modalEl.querySelector('#btnCancelarCerrarSesion');

  function onAceptar() {
    try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
    modalEl._opening = false;
    ejecutarCerrarSesion();
  }

  function onCancelar() {
    try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
    modalEl._opening = false;
    reactivarReconocimientoGlobal(); // ✅ blindado
  }

  aceptarBtn?.removeEventListener('click', onAceptar);
  cancelarBtn?.removeEventListener('click', onCancelar);
  aceptarBtn?.addEventListener('click', onAceptar);
  cancelarBtn?.addEventListener('click', onCancelar);

  try {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  } catch (e) {
    if (confirm('¿Desea cerrar sesión?')) {
      onAceptar();
    } else {
      onCancelar();
    }
  }

  // 🎤 Reconocimiento local dentro del modal
  try {
    if ('webkitSpeechRecognition' in window) {
      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = true;
      recog.interimResults = false;

      recog.onresult = function (event) {
        const textoRec = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
        console.log('🎤 Texto reconocido (modal cerrar sesión):', textoRec);
        if (modalEl._actionTaken) return;

        if (textoRec.includes('acept') || textoRec.includes('confirm')) {
          modalEl._actionTaken = true;
          console.log('🟢 cerrar sesión: voz reconocida como aceptar');
          try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
          ejecutarCerrarSesion();
        } else if (textoRec.includes('cancel')) {
          modalEl._actionTaken = true;
          console.log('🔴 cerrar sesión: voz reconocida como cancelar');
          try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
          reactivarReconocimientoGlobal();
        } else {
          console.log('⚠️ cerrar sesión: voz reconocida pero no válida → ignorada');
          try {
            recog.stop();
            setTimeout(() => {
              try {
                recog.start();
                console.log('🔁 reconocimiento local (modal cerrar sesión) reiniciado tras comando no válido');
              } catch (err) {
                if (err.name === 'InvalidStateError') {
                  console.log('⚠️ recog.start() ignorado: ya estaba iniciado');
                } else {
                  console.warn('⚠️ recog.start() falló:', err);
                }
              }
            }, 200);
          } catch (e) {
            console.warn('⚠️ recog.stop() falló antes de reiniciar:', e);
          }
        }
      };

      recog.onerror = function (e) {
        console.warn('Reconocimiento modal cerrar sesión falló', e);
      };

      modalEl._recogInstance = recog;
      recog.start();
      console.log('🎤 reconocimiento local (modal cerrar sesión) iniciado');
    }
  } catch (e) {
    console.warn('No se pudo crear reconocimiento modal cerrar sesión', e);
  }

  // 🧼 Limpieza al cerrar el modal
  const onHidden = () => {
    modalEl.removeEventListener('hidden.bs.modal', onHidden);
    modalEl._opening = false;
    try {
      const recog = modalEl._recogInstance;
      if (recog) {
        recog.onresult = null;
        recog.onerror = null;
        recog.stop?.();
      }
    } catch (e) {
      console.warn('No se pudo limpiar recog modal cerrar sesión', e);
    }
    modalEl._recogInstance = null;
    modalEl._actionTaken = false;
    reactivarReconocimientoGlobal(); // ✅ blindado
  };
  modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
}


// --- Asegurar y conectar botones flotantes y comportamiento (idempotente) ---
function asegurarYConectarBotonesFlotantes() {
  // wrapper (no bloqueante)
  let wrapper = document.getElementById('floating-controls');
  if (!wrapper) {
    wrapper = document.createElement('div');
    wrapper.id = 'floating-controls';
    wrapper.style.pointerEvents = 'none';
    document.body.appendChild(wrapper);
  }

  // botón Cerrar Sesión
  let btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
  if (!btnCerrar) {
    btnCerrar = document.createElement('button');
    btnCerrar.id = 'boton-flotante-cerrar-sesion';
    btnCerrar.type = 'button';
    btnCerrar.title = 'Cerrar sesión';
    btnCerrar.style.position = 'fixed';
    btnCerrar.style.top = '12px';
    btnCerrar.style.right = '12px';
    btnCerrar.style.zIndex = '2147483647';
    btnCerrar.style.background = '#dc3545';
    btnCerrar.style.color = '#fff';
    btnCerrar.style.border = 'none';
    btnCerrar.style.padding = '10px 14px';
    btnCerrar.style.borderRadius = '6px';
    btnCerrar.style.boxShadow = '0 4px 10px rgba(0,0,0,0.15)';
    btnCerrar.style.fontSize = '14px';
    btnCerrar.style.cursor = 'pointer';
    btnCerrar.textContent = 'Cerrar sesión';
    btnCerrar.setAttribute('aria-label', 'Cerrar sesión');

    // 👇 Ocultar por defecto
    btnCerrar.style.display = 'none';
    btnCerrar.style.pointerEvents = 'none';
    //btnCerrar.style.opacity = '0.5';
    btnCerrar.disabled = true;
    btnCerrar.setAttribute('aria-disabled', 'true');

    wrapper.appendChild(btnCerrar);
  }

  // botón Menú Principal
  let btnMenu = document.getElementById('boton-flotante-menu-principal');
  if (!btnMenu) {
    btnMenu = document.createElement('button');
    btnMenu.id = 'boton-flotante-menu-principal';
    btnMenu.type = 'button';
    btnMenu.title = 'Menú principal';
    btnMenu.style.position = 'fixed';
    btnMenu.style.bottom = '18px';
    btnMenu.style.left = '50%';
    btnMenu.style.transform = 'translateX(-50%)';
    btnMenu.style.zIndex = '2147483646';
    btnMenu.style.background = '#0d6efd';
    btnMenu.style.color = '#fff';
    btnMenu.style.border = 'none';
    btnMenu.style.padding = '10px 16px';
    btnMenu.style.borderRadius = '8px';
    btnMenu.style.boxShadow = '0 4px 10px rgba(0,0,0,0.12)';
    btnMenu.style.fontSize = '15px';
    btnMenu.style.cursor = 'pointer';
    btnMenu.textContent = 'Menú principal';
    btnMenu.setAttribute('aria-label', 'Menú principal');

    // 👇 Ocultar por defecto
    btnMenu.style.display = 'none';
    btnMenu.style.pointerEvents = 'none';
    //btnMenu.style.opacity = '0.5';
    btnMenu.disabled = true;
    btnMenu.setAttribute('aria-disabled', 'true');

    wrapper.appendChild(btnMenu);
  }

  // listeners (idempotentes)
  if (!btnCerrar._listenerAttached) {
    btnCerrar.addEventListener('click', () => {
      console.log('🔒 Cerrar sesión: botón flotante pulsado');
      mostrarModalCerrarSesion();
    });
    btnCerrar._listenerAttached = true;
  }

  if (!btnMenu._listenerAttached) {
    btnMenu.addEventListener('click', () => {
      console.log('📋 Menú principal: botón pulsado');
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('⚠️ Menú principal: safeStop falló', e); }
      try {
        window.nextStep && window.nextStep(2);
        try { cargarMenuPrincipal && cargarMenuPrincipal(); } catch (e) {}
        console.log('➡️ Navegando a step2 (¿Qué querés hacer?)');
      } catch (e) { console.warn('⚠️ Menú principal: nextStep(2) falló', e); }
      try { setTimeout(() => { safeStartRecognitionGlobal(); console.log('🎤 recognitionGlobal: intento reinicio tras ir a menú principal'); }, 120); } catch(e){}
    });
    btnMenu._listenerAttached = true;
  }

  return { btnCerrar, btnMenu };
}


// --- Control de visibilidad: ocultar en step1 ---
// --- Control de visibilidad: ocultar en step1 ---
function actualizarVisibilidadBotonesPorStep(stepId) {
  console.log('🔍 actualizando visibilidad para', stepId);

  const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
  const btnMenu = document.getElementById('boton-flotante-menu-principal');
  if (!btnCerrar || !btnMenu) return;

  const step = typeof stepId === 'number' ? 'step' + stepId : String(stepId);

  if (step === 'step1' || stepId === '1' || step === 'step12' || step === 'step0') {
    // 🔒 Ocultar completamente en login
    btnCerrar.style.display = 'none';
    btnMenu.style.display = 'none';
    btnCerrar.disabled = true;
    btnMenu.disabled = true;
    btnCerrar.setAttribute('aria-disabled', 'true');
    btnMenu.setAttribute('aria-disabled', 'true');
    btnCerrar.style.pointerEvents = 'none';
    btnMenu.style.pointerEvents = 'none';
    btnMenu.classList.remove('boton-menu-deshabilitado');
    console.log('👀 Botones ocultos (step1)');
  } else if (step === 'step2' || step === '2') {
    // 🟡 Mostrar pero deshabilitado en menú principal
    btnCerrar.style.display = 'inline-block';
    btnMenu.style.display = 'inline-block';
    btnCerrar.disabled = false;
    btnMenu.disabled = true;
    btnCerrar.removeAttribute('aria-disabled');
    btnMenu.setAttribute('aria-disabled', 'true');
    btnCerrar.style.pointerEvents = 'auto';
    btnMenu.style.pointerEvents = 'none';
    btnMenu.classList.add('boton-menu-deshabilitado');
    console.log('👀 Botón menú deshabilitado (step2)');
  } else {
    // ✅ Activos en los demás steps
    btnCerrar.style.display = 'inline-block';
    btnMenu.style.display = 'inline-block';
    btnCerrar.disabled = false;
    btnMenu.disabled = false;
    btnCerrar.removeAttribute('aria-disabled');
    btnMenu.removeAttribute('aria-disabled');
    btnCerrar.style.pointerEvents = 'auto';
    btnMenu.style.pointerEvents = 'auto';
    btnMenu.classList.remove('boton-menu-deshabilitado');
    console.log('👀 Botones visibles y activos');
  }
}


// --- DOMContentLoaded actualizado: inicia QR devolucion, crea botones y wrap nextStep ---
document.addEventListener('DOMContentLoaded', () => {
  // Inicializar escáner QR de devolución (como antes)
  try {
    const qrScanner = new Html5Qrcode("qr-reader-devolucion");
    qrScanner.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      ExitoDevolucionQR
    );
    console.log('📷 QR devolucion: escáner iniciado (DOMContentLoaded)');
  } catch (e) {
    console.warn('⚠️ QR devolucion: no se pudo iniciar escáner en DOMContentLoaded', e);
  }

  // Asegurar modal y botones
  asegurarModalCerrarSesion();
  asegurarYConectarBotonesFlotantes();

  // Wrap nextStep para mantener visibilidad sin romper lógica existente
  try {
    const originalNextStep = window.nextStep && typeof window.nextStep === 'function' ? window.nextStep : null;
    if (originalNextStep) {
      window.nextStep = function (n) {
        try { originalNextStep(n); } catch (e) { console.warn('⚠️ nextStep (original) falló desde wrapper', e); }
        const stepId = typeof n === 'number' ? 'step' + n : String(n);
        setTimeout(() => actualizarVisibilidadBotonesPorStep(stepId), 40);
      };
      console.log('🔧 nextStep envuelto para controlar visibilidad de botones flotantes');
    } else {
      setTimeout(() => {
        const current = getStepActivo();
        actualizarVisibilidadBotonesPorStep(current);
      }, 60);
    }
  } catch (e) {
    console.warn('⚠️ No se pudo wrappear nextStep', e);
  }

  // Aplicar visibilidad inicial
  try {
    const current = getStepActivo();
    actualizarVisibilidadBotonesPorStep(current);
  } catch (e) {
    console.warn('⚠️ No se pudo determinar step activo para visibilidad inicial', e);
  }
});

// Defensive guards: evitar errores si elementos no existen o librerías no cargadas
(function safeBindings() {
  // Guardar referencias seguras para elementos que se usan fuera de DOMContentLoaded
  if (typeof document !== 'undefined') {
    // btnConfirmarDevolucion se usa en varios sitios; aseguramos binding seguro
    const btnConfirmar = document.getElementById('btnConfirmarDevolucion');
    if (btnConfirmar && !btnConfirmar._safeClickAttached) {
      try {
        btnConfirmar.addEventListener('click', confirmarDevolucionQRActual);
        btnConfirmar._safeClickAttached = true;
        console.log('✅ safeBindings: btnConfirmarDevolucion conectado de forma segura');
      } catch (e) {
        console.warn('⚠️ safeBindings: no se pudo conectar btnConfirmarDevolucion', e);
      }
    }
  }

  // Asegurar existencia de bootstrap antes de usarlo en cualquier lugar inicial
  if (typeof window !== 'undefined' && typeof window.bootstrap === 'undefined') {
    console.log('ℹ️ safeBindings: bootstrap no disponible todavía');
  }
})();

// Patch idempotente para asegurar que los botones del modal "Cerrar sesión" reaccionen
(function asegurarConexionModalCerrarSesion() {
  function info(...args){ console.log('🔧 modal-patch:', ...args); }
  function warn(...args){ console.warn('🔧 modal-patch:', ...args); }

  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
      const modalEl = document.getElementById('modalCerrarSesion');
      if (!modalEl) { warn('modalCerrarSesion no encontrado en el DOM'); return; }
      const aceptarBtn = modalEl.querySelector('#btnAceptarCerrarSesion');
      const cancelarBtn = modalEl.querySelector('#btnCancelarCerrarSesion');

      if (!aceptarBtn) warn('btnAceptarCerrarSesion no encontrado');
      if (!cancelarBtn) warn('btnCancelarCerrarSesion no encontrado');

      function onAceptar() {
        info('Aceptar pulsado (patch). Ejecutando cerrar sesión.');
        try { ejecutarCerrarSesion && ejecutarCerrarSesion(); } catch(e){ console.warn(e); }
      }
      function onCancelar() {
        info('Cancelar pulsado (patch). Cerrando modal y reanudando reconocimiento.');
        try { const inst = bootstrap.Modal.getInstance(modalEl); inst && inst.hide && inst.hide(); } catch(e){}
        recognitionGlobalPaused = false;
        try { safeStartRecognitionGlobal && safeStartRecognitionGlobal(); } catch (e) { warn('safeStartRecognitionGlobal fallo:', e); }
      }

      try { aceptarBtn && aceptarBtn.removeEventListener('click', onAceptar); } catch(e){}
      try { cancelarBtn && cancelarBtn.removeEventListener('click', onCancelar); } catch(e){}
      if (aceptarBtn) aceptarBtn.addEventListener('click', onAceptar);
      if (cancelarBtn) cancelarBtn.addEventListener('click', onCancelar);

      info('Patch conectado: aceptar:', !!aceptarBtn, 'cancelar:', !!cancelarBtn, 'bootstrap:', typeof window.bootstrap === 'object');
    }, 50);
  }, { once: true });
})();



function parsearClavePorVoz(texto) {
  if (!texto) return '';

  const mapa = {
    cero: '0',
    uno: '1', dos: '2', tres: '3', cuatro: '4', cinco: '5',
    seis: '6', siete: '7', ocho: '8', nueve: '9',
    diez: '10', once: '11', doce: '12', trece: '13', catorce: '14', quince: '15',
    dieciseis: '16', diecisiete: '17', dieciocho: '18', diecinueve: '19',
    veinte: '20', veintiuno: '21', veintidos: '22', veintitres: '23', veinticuatro: '24',
    veinticinco: '25', veintiseis: '26', veintisiete: '27', veintiocho: '28', veintinueve: '29',
    treinta: '30', cuarenta: '40', cincuenta: '50', sesenta: '60',
    setenta: '70', ochenta: '80', noventa: '90',
    mil: '000',
    cuarentaidos: '42', // tolerancia a errores de reconocimiento
    cuarentaitres: '43',
    cincuentayuno: '51',
    cuarentayuno: '41',
    cuarentaycuatro: '44',
    cincuentaydos: '52',
    cincuentaytres: '53',
    sesentayseis: '66',
    setentaysiete: '77',
    ochentayocho: '88',
    noventaynueve: '99',

    treintayuno: '31',
    treintaydos: '32',
    treintaytres: '33',
    treintaycuatro: '34',
    treintaycinco: '35',
    treintayseis: '36',
    treintaysiete: '37',
    treintayocho: '38',
    treintaynueve: '39',

    cuarentaycinco: '45',
    cuarentayseis: '46',
    cuarentaysiete: '47',
    cuarentayocho: '48',
    cuarentaynueve: '49',

    cincuentaycuatro: '54',
    cincuentaycinco: '55',
    cincuentayseis: '56',
    cincuentaysiete: '57',
    cincuentayocho: '58',
    cincuentaynueve: '59',

    sesentayuno: '61',
    sesentaydos: '62',
    sesentaytres: '63',
    sesentaycuatro: '64',
    sesentaycinco: '65',
    sesentaysiete: '67',
    sesentayocho: '68',
    sesentaynueve: '69',

    setentayuno: '71',
    setentaydos: '72',
    setentaytres: '73',
    setentaycuatro: '74',
    setentaycinco: '75',
    setentayseis: '76',
    setentayocho: '78',
    setentaynueve: '79',

    ochentayuno: '81',
    ochentaydos: '82',
    ochentaytres: '83',
    ochentaycuatro: '84',
    ochentaycinco: '85',
    ochentayseis: '86',
    ochentaysiete: '87',
    ochentaynueve: '89',

    noventayuno: '91',
    noventaydos: '92',
    noventaytres: '93',
    noventaycuatro: '94',
    noventaycinco: '95',
    noventayseis: '96',
    noventaysiete: '97',
    noventayocho: '98',
    cien: '100',


  // ... ya existentes ...
  noventasiete: '97',
  noventaocho: '98',
  noventanueve: '99',
  treintauno: '31',
  treintados: '32',
  treintatres: '33',
  treintacuatro: '34',
  treintacinco: '35',
  treintaseis: '36',
  treintasiete: '37',
  treintaocho: '38',
  treintanueve: '39',
  cuarentauno: '41',
  cuarentados: '42',
  // ... y así hasta noventanueve
  // ... tu mapa actual ...
  // Treinta
  treintauno: '31',
  treintados: '32',
  treintatres: '33',
  treintacuatro: '34',
  treintacinco: '35',
  treintaseis: '36',
  treintasiete: '37',
  treintaocho: '38',
  treintanueve: '39',
  // Cuarenta
  cuarentauno: '41',
  cuarentados: '42',
  cuarentatres: '43',
  cuarentacuatro: '44',
  cuarentacinco: '45',
  cuarentaseis: '46',
  cuarentasiete: '47',
  cuarentaocho: '48',
  cuarentanueve: '49',
  // Cincuenta
  cincuentauno: '51',
  cincuentados: '52',
  cincuentatres: '53',
  cincuentacuatro: '54',
  cincuentacinco: '55',
  cincuentaseis: '56',
  cincuentasiete: '57',
  cincuentaocho: '58',
  cincuentanueve: '59',
  // Sesenta
  sesentauno: '61',
  sesentados: '62',
  sesentatres: '63',
  sesentacuatro: '64',
  sesentacinco: '65',
  sesentaseis: '66',
  sesentasiete: '67',
  sesentaocho: '68',
  sesentanueve: '69',
  // Setenta
  setentauno: '71',
  setentados: '72',
  setentatres: '73',
  setentacuatro: '74',
  setentacinco: '75',
  setentaseis: '76',
  setentasiete: '77',
  setentaocho: '78',
  setentanueve: '79',
  // Ochenta
  ochentauno: '81',
  ochentados: '82',
  ochentatres: '83',
  ochentacuatro: '84',
  ochentacinco: '85',
  ochentaseis: '86',
  ochentasiete: '87',
  ochentaocho: '88',
  ochentanueve: '89',
  // Noventa
  noventauno: '91',
  noventados: '92',
  noventatres: '93',
  noventacuatro: '94',
  noventacinco: '95',
  noventaseis: '96',
  noventasiete: '97',
  noventaocho: '98',
  noventanueve: '99'

  };

  const conectoresIgnorados = new Set([
    'del', 'de', 'la', 'el', 'los', 'las',
    'eh', 'por', 'favor', 'gracias', 'porfavor',
    'hola', 'soy', 'clave', 'para', 'es',
    'mi', 'un', 'una', 'usuario', 'nombre', 'identificador',
    'dame', 'decime', 'quiero', 'necesito',
    'mostrar', 'mostrarme', 'ingresar', 'ingrese',
    'comando', 'codigo', 'contraseña', 'como',
    'contraseña', 'contrasena', 'contrasenia'

  ]);



  const normalizar = str =>
    str.toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[.,/\\-]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

  texto = normalizar(texto)
    .replace(/\bveinti\s+uno\b/g, 'veintiuno')
    .replace(/\bveinti\s+dos\b/g, 'veintidos')
    .replace(/\bveinti\s+tres\b/g, 'veintitres')
    .replace(/\bveinti\s+cuatro\b/g, 'veinticuatro')
    .replace(/\bveinti\s+cinco\b/g, 'veinticinco')
    .replace(/\bveinti\s+seis\b/g, 'veintiseis')
    .replace(/\bveinti\s+siete\b/g, 'veintisiete')
    .replace(/\bveinti\s+ocho\b/g, 'veintiocho')
    .replace(/\bveinti\s+nueve\b/g, 'veintinueve');


    ///

// 🔽 INSERTÁ ACÁ el bloque de limpieza de frase inicial
const frasesInicioClave = [
  'ingresa clave', 'ingresar clave', 'clave es',
  'mi clave es', 'clave de usuario es', 'la clave es',
  'por favor ingresa la clave', 'por favor ingresar clave'
];

const fraseValida = frasesInicioClave.find(frase => texto.startsWith(frase));
if (!fraseValida) return ''; // ❌ No se dijo la frase requerida

texto = texto.replace(fraseValida, '').trim(); // ✅ Limpiar la frase inicial

for (const frase of frasesInicioClave) {
  if (texto.startsWith(frase)) {
    texto = texto.replace(frase, '').trim();
    break;
  }
}


  const tokens = texto.split(' ');
  let numero = '';
  let candidatos = [];

  for (let i = 0; i < tokens.length; i++) {
    const t = tokens[i];

    if (/^\d+$/.test(t)) {
      numero += t;
      continue;
    }

    const v = mapa[t];
    if (v !== undefined) {
      // decena + unidad
      if (parseInt(v) >= 30 && parseInt(v) % 10 === 0 && i + 1 < tokens.length) {
        const next = tokens[i + 1];
        if (next === 'y' && i + 2 < tokens.length && mapa[tokens[i + 2]]) {
          numero += String(parseInt(v) + parseInt(mapa[tokens[i + 2]]));
          i += 2;
          continue;
        } else if (mapa[next]) {
          numero += String(parseInt(v) + parseInt(mapa[next]));
          i++;
          continue;
        }
      }

      numero += v;
      continue;
    }

    // palabra no numérica ni reconocida → candidata a nombre
    if (!conectoresIgnorados.has(t)) {
      candidatos.push({ palabra: t, index: i });
    }
  }

  // elegir nombre más confiable: primer candidato antes del número
  const centro = tokens.findIndex(t => mapa[t] || /^\d+$/.test(t));
  const candidatosAntes = candidatos.filter(c => c.index < centro);
  const mejor = candidatosAntes.length > 0 ? candidatosAntes[0] : candidatos[0];
  if (!mejor || !numero) return '';

  // validación: evitar números excesivos
  if (numero.length > 6 || parseInt(numero) > 999999) return '';

  return (mejor.palabra + numero).toLowerCase();

}

// Export CommonJS para tests
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { parsearClavePorVoz };
}

// input de dictado de clave por voz para el inicio de sesion
function activarModoDictadoClave() {
  if (!('webkitSpeechRecognition' in window)) return;

  try {
    recognitionGlobalPaused = true;
    recognitionGlobal?.abort();
    console.log('🛑 Reconocimiento global pausado por dictado de clave');
  } catch (e) {
    console.warn('⚠️ No se pudo abortar reconocimiento global:', e);
  }

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = false;

  const claveInput = document.getElementById('clave');
  if (!claveInput) return;

  // ✅ Forzar focus con reintento
  if (!document.activeElement.isSameNode(claveInput)) {
    claveInput.focus();
    setTimeout(() => {
      if (!document.activeElement.isSameNode(claveInput)) {
        console.warn('⚠️ Focus no aplicado correctamente, reintentando');
        claveInput.focus();
      }
    }, 100);
  }

  claveInput.classList.add('dictado-activo');

  let ultimoFragmento = '';

  recog.onresult = function (event) {
    let texto = '';
    for (let i = event.resultIndex; i < event.results.length; ++i) {
      if (!event.results[i].isFinal) continue;
      texto += event.results[i][0].transcript;
    }

    console.log('🔤 dictadoClave: texto original →', texto);

    texto = texto.toLowerCase()
      .normalize("NFD").replace(/[\u0300-\u036f]/g, '')
      .replace(/[^\w\s]/g, '')
      .replace(/\s+/g, ' ')
      .trim();

    console.log('🔤 dictadoClave: texto normalizado →', texto);

    // 🛑 Salida por voz del modo dictado
    if (texto.includes('terminar dictado') || texto.includes('modo comandos')) {
      recog.stop();
      window._dictadoClaveActivo = null;
      recognitionGlobalPaused = false;
      safeStartRecognitionGlobal();
     // getRenderer('mostrarMensajeKiosco')('Modo dictado desactivado', 'info');
      return;
    }

    // ✅ Comandos críticos durante dictado
    if (texto.includes('continuar')) {
      identificarTrabajador();
      return;
    }

    if (texto.includes('borrar')) {
      claveInput.value = '';
      //getRenderer('mostrarMensajeKiosco')('clave borrada por voz', 'info');
      return;
    }

    if (texto.includes('iniciar sesion con qr') || texto === 'qr') {
      activarEscaneoQRLogin();
      //getRenderer('mostrarMensajeKiosco')('Escaneo QR activado por voz', 'info');
      return;
    }

    // 🧠 Evitar repetir fragmentos idénticos
   /* if (texto === ultimoFragmento) {
      console.log('🔁 dictadoClave: fragmento idéntico al anterior, ignorado');
      return;
    }*/
    ultimoFragmento = texto;

    // 🔢 Convertir palabras numéricas a dígitos
    const tokens = texto.split(/(\d+|[a-z]+)/);
    const mapa = {
      cero: '0', uno: '1', dos: '2', tres: '3', cuatro: '4', cinco: '5',
      seis: '6', siete: '7', ocho: '8', nueve: '9'
    };

    let resultado = '';
    for (const t of tokens) {
      if (!t) continue;
      resultado += mapa[t] || t;
    }

    // 🔗 Acumular en el input sin espacios
    claveInput.value = (claveInput.value || '') + resultado.replace(/\s+/g, '');
    console.log('📝 dictadoClave: input actualizado →', claveInput.value);
  };

  recog.onerror = function (e) {
    console.warn('⚠️ Error en dictado de clave:', e);
  };

  recog.onend = function () {
    console.log('ℹ️ Dictado de clave finalizado');

    if (window._dictadoClaveActivo === recog) {
      window._dictadoClaveActivo = null;
      recognitionGlobalPaused = false;
      safeStartRecognitionGlobal();
      console.log('🎤 Reconocimiento global reactivado tras fin de dictado');
    }

    claveInput.classList.remove('dictado-activo');
    claveInput.blur();
  };

  recog.start();
  window._dictadoClaveActivo = recog;
}


function reactivarReconocimientoGlobal() {
  try {
    // 🛑 Detener dictado de clave si sigue activo
    if (window._dictadoClaveActivo) {
      window._dictadoClaveActivo.onresult = null;
      window._dictadoClaveActivo.onerror = null;
      window._dictadoClaveActivo.onend = null;
      window._dictadoClaveActivo.stop?.();
      window._dictadoClaveActivo = null;
      console.log('🛑 Dictado de clave detenido');
    }

    // 🛑 Detener recog local de modal si existe
    const recogModal = document.querySelector('.modal.show')?._recogInstance;
    if (recogModal) {
      recogModal.onresult = null;
      recogModal.onerror = null;
      recogModal.onend = null;
      recogModal.stop?.();
      console.log('🛑 Reconocimiento local de modal detenido');
    }

    // ✅ Reactivar global
    recognitionGlobalPaused = false;
    safeStartRecognitionGlobal();
    console.log('🎤 Reconocimiento global reactivado');
  } catch (e) {
    console.warn('⚠️ reactivarReconocimientoGlobal: error al reactivar', e);
  }
}



/*TTS - TEXTO A VOZ - ASISTENTE DE SAFESTOCK*/

window.usandoAsistente = false;
window.cierreManualAsistente = false;
window.modalAsistenteCerrando = false;
window.bloqueoEcoTTS = false;

function abrirModalAsistente() {
  const modalEl = document.getElementById('modalAsistente');
  if (!modalEl) return;
  if (modalEl.classList.contains('show')) return;

  window.modalKioscoActivo = true;
  window.usandoAsistente = true; // ✅ Activar flag

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

function cerrarModalAsistente() {
  const modalEl = document.getElementById('modalAsistente');
  if (!modalEl) return;

  // ✅ Evitar bucle si ya se está cerrando
  if (window.modalAsistenteCerrando) return;
  window.modalAsistenteCerrando = true;

  window.modalKioscoActivo = false;
  window.usandoAsistente = false;
  window.cierreManualAsistente = true;

  try {
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance && modalEl.classList.contains('show')) {
      modalInstance.hide(); // solo si no fue disparado por Bootstrap
    }
  } catch (e) {}

  try {
    window.speechSynthesis.cancel();
  } catch (e) {}

  // ✅ Reset de flags TTS y eco
  window.ttsEnCurso = false;
  window.textoUltimoTTS = '';
  window.timestampUltimoTTS = 0;
  window.bloqueoEcoTTS = false;

  const wrapper = document.querySelector('.subtitulo-wrapper');
  const subtituloEl = document.getElementById('asistenteSubtitulo');
  if (wrapper && subtituloEl) {
    wrapper.classList.remove('visible');
    setTimeout(() => {
      subtituloEl.innerHTML = '';
    }, 300);
  }

  document.getElementById('microfono_flotante')?.classList.remove('mic-muted');

  // ✅ Quitar hover visual de cualquier botón activo del asistente
  document.querySelectorAll('#modalAsistente .btn-hover-simulada').forEach(btn => {
    btn.classList.remove('btn-hover-simulada');
  });

  // ✅ Reactivar reconocimiento si el TTS fue interrumpido
  setTimeout(() => {
    if (!window.ttsEnCurso) {
      try {
        safeStartRecognitionGlobal();
        console.log('🎤 Reconocimiento reactivado tras cierre manual del asistente');
      } catch (e) {
        console.warn('safeStartRecognitionGlobal falló tras cierre manual:', e);
      }
    }
  }, 300);

  setTimeout(() => {
    document.querySelectorAll('.modal.show').forEach(el => el.classList.remove('show'));
    window.modalAsistenteCerrando = false; // ✅ Reset
  }, 100);
}



/* ==========================================
   leerAsistenteTexto(opcion) (versión segura)
   ========================================== */
function leerAsistenteTexto(opcion) {
  let texto = '';
  switch (opcion) {
    case 1:
      texto = 'Podés usar el sistema mediante voz, al leer el nombre de los botones como "opción 1" o "página 2".';
      break;
    case 2:
      texto = 'Podés ingresar al sistema escribiendo tu clave o escaneando tu código QR personal. También podés dictar tu clave por voz diciendo "ingresar clave".';
      break;
    case 3:
      texto = 'Podés solicitar herramientas, registrar recursos que ya tenés en mano, o ver los recursos que tenés asignados actualmente.';
      break;
    case 4:
      texto = 'Para devolver una herramienta, seleccioná el recurso asignado y escaneá el código QR de la serie correspondiente. El sistema validará la devolución automáticamente.';
      break;
    default:
      texto = '';
  }

  const modalEl = document.getElementById('modalAsistente');
  const modalVisible = !!modalEl && modalEl.classList.contains('show');
  const wrapper = document.querySelector('.subtitulo-wrapper');
  const subtituloEl = document.getElementById('asistenteSubtitulo');
  const mic = document.getElementById('microfono_flotante');

 const reproducir = () => {
  if (!subtituloEl || !wrapper) {
    console.warn('⚠️ asistenteSubtitulo o wrapper no encontrado en el DOM');
    return;
  }

  // ✅ Limpiar hover de todos los botones antes de aplicar el nuevo
  document.querySelectorAll('#modalAsistente .btn-hover-simulada').forEach(btn => {
    btn.classList.remove('btn-hover-simulada');
  });

  const boton = document.querySelector(`#modalAsistente button[onclick="leerAsistenteTexto(${opcion})"]`);
  if (boton) boton.classList.add('btn-hover-simulada');

  // Preparar subtítulos
  const palabras = texto.split(' ').filter(Boolean);
  subtituloEl.innerHTML = palabras.map((p, i) => `<span id="palabra-${i}">${p}</span>`).join(' ');
  wrapper.classList.add('visible');

  if (mic) {
    mic.classList.add('mic-muted');
    microfono_flotante?.classList.remove('pulsing');
  }

  window.bloqueoEcoTTS = true;
  window.textoUltimoTTS = texto;
  window.timestampUltimoTTS = Date.now();
  window.ttsEnCurso = true;
  window.cierreManualAsistente = false;

  const utterance = new SpeechSynthesisUtterance(texto);
  utterance.lang = 'es-ES';
  utterance.rate = 1;
  utterance.pitch = 1;
  utterance.volume = 1;

  let palabraIndex = 0;
  utterance.onboundary = (event) => {
    const isWordBoundary = (event.name && event.name === 'word') || typeof event.charIndex === 'number';
    if (!isWordBoundary) return;
    const span = document.getElementById(`palabra-${palabraIndex}`);
    if (span) {
      span.style.backgroundColor = '#ffeeba';
      span.style.borderRadius = '4px';
    }
    const prev = document.getElementById(`palabra-${palabraIndex - 1}`);
    if (prev) prev.style.backgroundColor = '';
    palabraIndex++;
  };

  utterance.onend = () => {
    window.ttsEnCurso = false;

    if (mic) mic.classList.remove('mic-muted');
    wrapper.classList.remove('visible');
    setTimeout(() => {
      if (subtituloEl) subtituloEl.innerHTML = '';
    }, 300);

    // ✅ Quitar hover visual del botón
    if (boton) boton.classList.remove('btn-hover-simulada');

    const sigueVisible = !!modalEl && modalEl.classList.contains('show');
    if (sigueVisible && !window.cierreManualAsistente) {
      setTimeout(() => {
        if (!window.ttsEnCurso) {
          try {
            safeStartRecognitionGlobal();
          } catch (e) {
            console.warn('safeStartRecognitionGlobal falló tras TTS:', e);
          }
        }
      }, 300);
    } else {
      console.log('🎤 Reconocimiento no reactivado: modal cerrado durante TTS');
    }

    setTimeout(() => {
      window.bloqueoEcoTTS = false;
    }, 2000);

    window.cierreManualAsistente = false;
  };

  try {
    window.speechSynthesis.cancel();
  } catch (e) {
    console.warn('No se pudo cancelar speechSynthesis previo:', e);
  }
  window.speechSynthesis.speak(utterance);
};


  if (!modalVisible) {
    if (!!modalEl) {
      window.modalKioscoActivo = true;
      window.usandoAsistente = true;
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      modalEl.addEventListener('shown.bs.modal', () => {
        reproducir();
      }, { once: true });
    } else {
      reproducir();
    }
  } else {
    reproducir();
  }
}



/* COMANDOS DE VOZ */

function calcularSimilitudSemantica(a, b) {
  if (!a || !b) return 0;

  const simplificar = (str) => str
    .normalize("NFD").replace(/[\u0300-\u036f]/g, '') // quitar acentos
    .replace(/[^\w\s]/g, '') // quitar signos
    .replace(/\b(podes|podras|podras|podre|puedo|puede|podria)\b/g, 'poder')
    .replace(/\b(tenes|tiene|tengo|tendra|tendras|tuvo|tenia)\b/g, 'tener')
    .replace(/\b(solicitar|solicita|solicito|solicite)\b/g, 'solicitar')
    .replace(/\b(ver|veo|vio|vea|veas)\b/g, 'ver')
    .replace(/\b(registrar|registro|registra|registre)\b/g, 'registrar')
    .replace(/\b(asignados|asignado|asignar)\b/g, 'asignar')
    .toLowerCase();

  const tokensA = simplificar(a).split(/\s+/);
  const tokensB = simplificar(b).split(/\s+/);
  const interseccion = tokensA.filter(t => tokensB.includes(t));
  const union = new Set([...tokensA, ...tokensB]);
  return interseccion.length / union.size;
}


function procesarComandoVoz(rawTexto) {
  try {
    if (!rawTexto || typeof rawTexto !== 'string') return;
    const texto = String(rawTexto || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log("👉 Reconocido (raw):", rawTexto, "| normalizado:", limpio, "| Step activo:", getStepActivo());

    // ✅ Bloqueo temporal tras TTS
    if (window.bloqueoEcoTTS) {
      console.warn('🚫 Ignorado: bloqueo temporal tras TTS (bloqueoEcoTTS activo)');
      return;
    }

    // Protección temprana contra eco TTS: si TTS activo, ignorar todo
    if (window.ttsEnCurso) {
      console.log('🚫 Ignorado: TTS en curso, posible eco');
      return;
    }

    // Datos TTS guardados (si existen)
    const textoTTS = String(window.textoUltimoTTS || '').toLowerCase();
    const tiempoTTS = Number(window.timestampUltimoTTS || 0);
    const ahora = Date.now();

    const textoTTSNorm = textoTTS ? normalizarTexto(textoTTS) : '';
    const tiempoReciente = (ahora - tiempoTTS < 3000);
    const similitud = textoTTSNorm && limpio ? calcularSimilitudSemantica(textoTTSNorm, limpio) : 0;

    if (tiempoReciente && similitud > 0.85) {
      console.warn('🚫 Ignorado por eco TTS (similitud alta):', { limpio, textoTTSNorm, similitud });
      return;
    }

    // === Comandos de voz para el Asistente ===
    const modalAsistente = document.getElementById('modalAsistente');
    const modalAsistenteVisible = !!modalAsistente && modalAsistente.classList.contains('show');

   if (modalAsistenteVisible) {
  if (/\b(como usar|como puedo usar|usar sistema|ayuda sistema)\b/.test(limpio)) {
    leerAsistenteTexto(1);
    return;
  }

  if (/\b(como ingreso|como me identifico|como entrar)\b/.test(limpio)) {
    leerAsistenteTexto(2);
    return;
  }

  if (/\b(que puedo hacer|menu principal|opciones disponibles)\b/.test(limpio)) {
    leerAsistenteTexto(3);
    return;
  }

  if (/\b(como devuelvo|como devolver|como devuelve|devolver herramienta|entregar herramienta|devolver recurso|como entregar)\b/.test(limpio)) {
    leerAsistenteTexto(4);
    return;
  }

  // ✅ Comando para cerrar el asistente
  if (/\b(cerrar|cerrar asistente|salir|terminar ayuda)\b/.test(limpio)) {
    cerrarModalAsistente();
    return;
  }
}


    // Bloqueo general si el asistente está activo
    if (window.usandoAsistente) {
      console.log('🚫 Comando ignorado: asistente activo');
      return;
    }

    // Comando global para abrir el asistente (solo si no hay ningún modal visible)
    const modalEl = document.getElementById('modalAsistente');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    const modalVisible = modalEl?.classList.contains('show') && !!modalInstance;

    const algunModalVisible = window.modalKioscoActivo || document.querySelectorAll('.modal.show').length > 0;

if (!algunModalVisible && /\b(ayuda|asistente|tengo dudas)\b/.test(limpio)) {
      abrirModalAsistente();
      return;
    }


    // Si el kiosco está mostrando un modal kiosco forzado, priorizamos su cierre por voz
    if (window.modalKioscoActivo) {
      if (/\b(cerrar)\b/.test(limpio)) {
        console.log('🎤 Cierre por voz de modal activo:', limpio);
        const modalAs = document.getElementById('modalAsistente');
        if (modalAs?.classList.contains('show')) {
          cerrarModalAsistente();
        } else {
          cerrarModalKiosco();
        }
      } else {
        console.log('🧪 algunModalVisible:', algunModalVisible);

        console.log('🚫 Comando bloqueado por modal activo:', limpio);
      }
      return;
    }

    // Si hay modal de error QR visible priorizamos su cierre
    const modalErrorQR = document.getElementById('modalErrorQR');
    const modalErrorVisible = !!modalErrorQR && modalErrorQR.classList.contains('show');
    if (modalErrorVisible) {
      if (/\b(cerrar|cerrar error|cerrar modal|cerrar qr)\b/.test(limpio)) {
        console.log('🎤 Comando de voz: cerrar modal error QR');
        cerrarModalErrorQR();
      } else {
        console.log('⚠️ Modal de error QR abierto, comando ignorado:', limpio);
      }
      return;
    }

    // Resto de lógica por pasos / botones globales
    const step = getStepActivo();

    // Botones globales de menú principal y cerrar sesión
    

    // Comando de voz para avanzar desde la pantalla de bienvenida (step0)
    if (step === 'step0') {
      if (/\b(continuar)\b/.test(limpio)) {
        console.log('🎤 Comando de voz: avanzar desde step0');
        nextStep(1);
        return;
      }
    }

    if (step !== 'step0' && step !== 'step1' && step !== 'step12') {
      if (/\b(cerrar sesión|cerrar sesion)\b/.test(limpio)) {
        console.log('🔐 Comando de voz detectado: cerrar sesión');
        mostrarModalCerrarSesion();
        return;
      }
    }

    if (step !== 'step0' && step !== 'step1' && step !== 'step2' && step !== 'step12') {
      if (/\b(menu principal)\b/.test(limpio)) {
        recognitionGlobalPaused = false;
        safeStartRecognitionGlobal();
        nextStep(2);
        return;
      }
    }

    // Si estamos en step10 (pantalla de recursos asignados) manejamos comandos allí
    if (step === 'step10') {

      if (esComandoVolver(limpio) || /\b(v|b)ol(v|b)er\b/.test(limpio)) {
      recognitionGlobalPaused = false;
      safeStartRecognitionGlobal();
      nextStep(2);
    //  getRenderer('mostrarMensajeKiosco')('Volviendo al menú principal', 'info');
      return;
    }


      // cambio de tab por voz
      const tabPorStep = matchTabCambio(limpio);
      if (tabPorStep === 'epp') {
        document.getElementById('tab-epp-step')?.click();
        //getRenderer('mostrarMensajeKiosco')('✅ Mostrando EPP', 'success');
        return;
      }
      if (tabPorStep === 'herramientas') {
        document.getElementById('tab-herramientas-step')?.click();
       // getRenderer('mostrarMensajeKiosco')('✅ Mostrando Herramientas', 'success');
        return;
      }

      // Devolución por voz: "opcion N"
      const mOp = limpio.match(/opcion\s*(\d{1,2})/i);
      if (mOp) {
        const index = parseInt(mOp[1], 10);
        if (!isNaN(index)) {
          confirmarDevolucionPorVozStep10(index);
        } else {
          getRenderer('mostrarModalKioscoSinVoz')('Opción no reconocida', 'warning');
        }
        return;
      }

      // Paginación "pagina N"
      const mp = limpio.match(/^pagina\s*(\d{1,2})$/i);
      if (mp) {
        const numero = parseInt(mp[1], 10);
        if (!isNaN(numero)) handleStep10Pagina(numero);
        else getRenderer('mostrarModalKioscoSinVoz')('Número de página no reconocido', 'warning');
        return;
      }

      console.log('⚠️ step10: comando no reconocido', limpio);
      return;
    }



    // Comandos globales cuando no estamos bloqueados por modales ni step10
    if (recognitionGlobalPaused) {
      console.log('⚠️ Reconocimiento global pausado, ignorando comando:', limpio);
      return;
    }

    

    // === Step1: Login ===
    if (step === 'step1') {
  // 🧠 Intento de ingreso por voz usando frase activadora
  const clave = parsearClavePorVoz(rawTexto);
  if (clave) {
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = clave;
      //claveInput.focus();
     // getRenderer('mostrarMensajeKiosco')(`🎤 Clave reconocida: ${clave}`, 'success');
      // Opcional: avanzar automáticamente
      // nextStep();
    }
    return;
  }

  if (/\b(ingresar clave| clave)\b/.test(limpio)) {
  const claveInput = document.getElementById('clave');
  if (claveInput) {
    claveInput.focus();
    claveInput.value = ''; // opcional: limpiar antes de dictar
    activarModoDictadoClave(); // 🔧 función que vamos a crear
    //getRenderer('mostrarMensajeKiosco')('🎤 Modo dictado de clave activado', 'info');
  }
  return;
}


  // 🧹 Comando para borrar el campo clave
  if (/\b(borrar|borrar clave|borrar todo)\b/.test(limpio)) {
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = '';
      claveInput.focus();
      //getRenderer('mostrarMensajeKiosco')('clave borrada por voz', 'info');
    }
    return;
  }

  // ▶️ Comando para continuar login manualmente
  if (/\b(continuar)\b/.test(limpio)) {
    console.log('🎤 Comando de voz: Continuar login');
    identificarTrabajador(); // tu función actual para validar y avanzar
    return;
  }

  // ▶️ Comando QR
if (/\b(qr|iniciar sesion con QR)\b/.test(limpio)) {
  console.log('🎤 Comando de voz: Iniciar sesión con QR');
  abrirStepQRLogin(); // cambia al step12 y activa escaneo
  return;
}


  // 🧩 Fallback: si se dictó solo números sin frase activadora
  if (!/^[a-zA-Z]/.test(limpio) && /^\d/.test(limpio)) {
    // Si querés permitir ingreso de clave por bloques sin activadora
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = limpio.replace(/\s+/g, '');
      //claveInput.focus();
      //getRenderer('mostrarMensajeKiosco')('clave dictado por voz', 'info');
    }
    return;
  }
}


    // === Step2: Menú principal y navegación ===
    if (step === 'step2') {
      // normalizar repeticiones
      const textoSimple = limpio.replace(/\b(\w+)\s+\1\b/g, '$1');

      // Si modalRecursos estuviera abierto (en el viejo enfoque) no lo procesamos aquí,
      // pero ahora preferimos abrir step10 desde menú con la opción correspondiente.
      if (matchOpcion(textoSimple, 1, "herramienta en mano")) {
        //window.mostrarMensajeKiosco('🎤 Comando reconocido: Herramienta en mano', 'success');
        setModoEscaneo('manual');
        return;
      }

      if (matchOpcion(textoSimple, 2, "solicitar herramienta", "quiero solicitar", "pedir herramienta")) {
        //window.mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
        step5ReturnTarget = 2;
        window.nextStep(5);
        return;
      }

      if (matchOpcion(textoSimple, 3, "ver recursos", "recursos asignados", "mostrar recursos")) {
       // window.mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
        window.cargarRecursos().then(() => abrirStepRecursos());
        return;
      }

      // paginación por tab si corresponde (comandos "pagina EPP 2", etc.)
      const matchPaginaEPP = textoSimple.match(/^pagina\s*epp\s*(\d{1,2})$/i);
      const matchPaginaHerr = textoSimple.match(/^pagina\s*herramientas\s*(\d{1,2})$/i);
      if (matchPaginaEPP) {
        const numero = parseInt(matchPaginaEPP[1], 10);
        const total = Math.ceil((window.recursosEPP?.length || 0) / cantidadRecursosPorPagina);
        if (numero >= 1 && numero <= total) renderTablaRecursos('tablaEPP', window.recursosEPP, numero, 'paginadorEPP');
        else window.mostrarModalKioscoSinVoz('Número de página inválido para EPP', 'warning');
        return;
      }
      if (matchPaginaHerr) {
        const numero = parseInt(matchPaginaHerr[1], 10);
        const total = Math.ceil((window.recursosHerramientas?.length || 0) / cantidadRecursosPorPagina);
        if (numero >= 1 && numero <= total) renderTablaRecursos('tablaHerramientas', window.recursosHerramientas, numero, 'paginadorHerramientas');
        else window.mostrarModalKioscoSinVoz('Número de página inválido para herramientas', 'warning');
        return;
      }

      console.log("⚠️ Step2: No se reconoció comando válido");
      return;
    }

    // === Step3: Escaneo QR ===
    if (step === 'step3') {

      if (limpio.includes("cancelar")) {
        cancelarEscaneoQRregistroRecursos();
        return;
      }

      if (matchOpcion(limpio, 1, "escanear", "qr", "escanear qr", "registrar por qr")) {
        activarEscaneoQRstep13ConEspera();
        return;
      }

      if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
        step5ReturnTarget = 3;
        detenerEscaneoQRregistroRecursos(5);
        return;
      }

      if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
        detenerEscaneoQRregistroRecursos(2);
        return;
      }

      console.log("⚠️ Step3: No se reconoció ningún comando válido");
      return;
    }


    // === Step5, Step6, Step7, Step8 handling (botones + paginación) ===
    // Delegamos a bloques ya implementados en tu código original
    if (step === 'step5') {
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "opcion volver")) {
        //window.mostrarMensajeKiosco(step5ReturnTarget === 3 ? '🎤 Comando reconocido: Volver a "Tengo la herramienta en mano"' : '🎤 Comando reconocido: Volver al menú principal', 'success');
        window.nextStep(step5ReturnTarget);
        return;
      }
      const botonesCat = document.querySelectorAll('#categoria-buttons button');
      for (let i = 0; i < botonesCat.length; i++) {
        const btn = botonesCat[i];
        if (matchOpcion(limpio, i + 1) || matchTextoBoton(limpio, btn)) { btn.click(); return; }
      }
      console.log("⚠️ Step5: Procesada entrada (si hubo coincidencias)");
      return;
    }

    if (step === 'step6') {
      const matchPaginaSub = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
      if (matchPaginaSub && Array.isArray(window.subcategoriasActuales)) {
        const token = matchPaginaSub[1];
        const numero = numeroDesdeToken(token);
        if (!isNaN(numero) && numero >= 1) {
          const totalPaginas = Math.max(1, Math.ceil(window.subcategoriasActuales.length / cantidadRecursosPorPagina));
          if (numero > totalPaginas) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
          renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
          return;
        }
      }
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "opcion volver")) { /*window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a categorías', 'success');*/ window.nextStep(5); return; }
      const botonesSub = document.querySelectorAll('#subcategoria-buttons button');
      for (let i = 0; i < botonesSub.length; i++) { const btn = botonesSub[i]; if (matchOpcion(limpio, i + 1) || matchTextoBoton(limpio, btn)) { btn.click(); return; } }
      console.log("⚠️ Step6: Procesada entrada (si hubo coincidencias)");
      return;
    }

    if (step === 'step7') {
      const matchPaginaRec = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
      if (matchPaginaRec && Array.isArray(window.recursosActuales)) {
        const token = matchPaginaRec[1];
        const numero = numeroDesdeToken(token);
        if (!isNaN(numero) && numero >= 1) {
          const totalPaginas = Math.max(1, Math.ceil(window.recursosActuales.length / cantidadRecursosPorPagina));
          if (numero > totalPaginas) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
          renderRecursosPaginados(window.recursosActuales, numero);
          return;
        }
      }
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) { /*window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a subcategorías', 'success');*/ window.nextStep(6); return; }
      const botonesRec = document.querySelectorAll('#recurso-buttons button');
      botonesRec.forEach((btn, index) => { try { if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) { btn.click(); } } catch (e) { console.warn('Error al procesar botón recurso', e); } });
      console.log("⚠️ Step7: Procesada entrada (si hubo coincidencias)");
      return;
    }

    if (step === 'step8') {
      const matchPaginaSer = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
      if (matchPaginaSer && Array.isArray(window.seriesActuales)) {
        const token = matchPaginaSer[1];
        const numero = numeroDesdeToken(token);
        if (!isNaN(numero) && numero >= 1) {
          const totalPaginas = Math.max(1, Math.ceil(window.seriesActuales.length / cantidadRecursosPorPagina));
          if (numero > totalPaginas) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
          renderSeriesPaginadas(window.seriesActuales, numero);
          return;
        }
      }

    if (/\b(cerrar)\b/.test(limpio)) {
      const modalEl = document.getElementById('modal-mensaje-kiosco');
      if (modalEl && modalEl.classList.contains('show')) {
        cerrarModalKiosco();
        return;
      }
    }

      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) { /*window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a recursos', 'success');*/ window.nextStep(7); return; }
      const botonesSeries = document.querySelectorAll('#serie-buttons button');
      botonesSeries.forEach((btn, index) => { try { if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) { btn.click(); } } catch (e) { console.warn('Error al procesar botón serie', e); } });
      console.log("⚠️ Step8: Procesada entrada (si hubo coincidencias)");
      return;
    }

    // === Step9: Devolución por QR ===
    if (step === 'step9') {
      if (/\b(confirmar|firmar|devolucion)\b/.test(limpio)) {
        const modalVisible = document.getElementById('modalConfirmarQR')?.classList.contains('show');

        console.log('🧠 Voz: confirmar detectado');
        console.log('🔍 _qrValidadoParaDevolucion:', window._qrValidadoParaDevolucion);
        console.log('🔍 modalVisible:', modalVisible);

        if (window._qrValidadoParaDevolucion && modalVisible) {
          try {
            window._modalConfirmarQR?.hide();
          } catch (e) {}

          confirmarDevolucionQRActual();
          return;
        }

        getRenderer('mostrarModalKioscoSinVoz')('Aún no se detectó un QR válido para confirmar', 'warning');
        return;
      }

      if (/\b(cancelar|cancelar escaneo)\b/.test(limpio)) {
        const modalError = document.getElementById('modalErrorQR');
        const modalConfirm = document.getElementById('modalConfirmarQR');

        if (modalError?.classList.contains('show')) {
          document.getElementById('btnCerrarErrorQR')?.click();
          return;
        }

        if (modalConfirm?.classList.contains('show')) {
          document.getElementById('btnCancelarQR')?.click();
          return;
        }

        volverARecursosAsignadosDesdeDevolucionQR();
        return;
      }
    }

    // === Step12: Inicio de sesión con QR ===
    if (step === 'step12') {
      if (/\b(cancelar|cancelar qr|cancelar inicio|cancelar inicio de sesión|cancelar inicio de sesión con qr)\b/.test(limpio)) {
        console.log('🎤 Comando de voz: Cancelar inicio de sesión con QR');
        cancelarEscaneoQRLogin(); // tu función actual para cerrar escáner y volver a step1
        return;
      }

      console.log('⚠️ Step12: comando no reconocido', limpio);
      return;
    }

    // === Step13: Registro por QR ===
    if (step === 'step13') {
      if (limpio.includes("cancelar")) {
        cancelarEscaneoQRregistroRecursosStep13();
        nextStep(3);
        return;
      }

      console.log("⚠️ Step13: No se reconoció ningún comando válido");
      return;
    }

    // === Paginación y navegación globales (fallback) ===
    const matchPaginaAny = limpio.match(/^pagina\s*(número\s*)?(\d{1,2}|[a-záéíóúñ]+)$/i);

    if (matchPaginaAny) {
      const token = matchPaginaAny[1];
      const numero = numeroDesdeToken(token);
      if (isNaN(numero) || numero < 1) { window.mostrarModalKioscoSinVoz('Número de página no reconocido', 'warning'); return; }

      if (step === 'step6' && Array.isArray(window.subcategoriasActuales)) {
        const total = Math.max(1, Math.ceil(window.subcategoriasActuales.length / cantidadRecursosPorPagina));
        if (numero > total) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
        renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
        return;
      }
      if (step === 'step7' && Array.isArray(window.recursosActuales)) {
        const total = Math.max(1, Math.ceil(window.recursosActuales.length / cantidadRecursosPorPagina));
        if (numero > total) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
        renderRecursosPaginados(window.recursosActuales, numero);
        return;
      }
      if (step === 'step8' && Array.isArray(window.seriesActuales)) {
        const total = Math.max(1, Math.ceil(window.seriesActuales.length / cantidadRecursosPorPagina));
        if (numero > total) { window.mostrarModalKioscoSinVoz('Número de página inválido', 'warning'); return; }
        renderSeriesPaginadas(window.seriesActuales, numero);
        return;
      }

      console.log('⚠️ matchPaginaAny: comando página detectado pero no aplicable en step', step);
      return;
    }

    // Comando global: cerrar modalRecursos antiguo compat (si sigue existiendo)
    const modalRec = document.getElementById('modalRecursos');
    if (modalRec && modalRec.classList.contains('show')) {
      if (matchOpcion(limpio, 0, "volver", "cerrar", "cerrar recursos")) {
        console.log("✅ Comando global: Cerrar modal de recursos asignados");
        try { bootstrap.Modal.getInstance(modalRec)?.hide(); } catch (e) {}
        //window.mostrarMensajeKiosco('🎤 Comando reconocido: Cerrar recursos asignados', 'success');
        return;
      }
    }

    console.log("⚠️ procesarComandoVoz: comando no reconocido en ningún step");
  } catch (err) {
    console.warn('procesarComandoVoz: excepción', err);
  }
}

/*Actualizacion de los tokens*/
function getHeadersSeguros() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  const csrf = meta?.content;
  const headers = { 'Content-Type': 'application/json' };
  if (csrf) headers['X-CSRF-TOKEN'] = csrf;
  return headers;
}

function refrescarTokenCSRF() {
  return fetch('/csrf-token')
    .then(res => res.json())
    .then(data => {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta && data.token) {
        meta.setAttribute('content', data.token);
        return data.token;
      }
      throw new Error('No se pudo actualizar el token CSRF');
    });
}

async function verificarSesionActiva() {
  const id_usuario = localStorage.getItem('id_usuario');
  let csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  if (!id_usuario) {
    mostrarModalKioscoSinVoz('⚠️ No hay trabajador identificado', 'danger');
    return false;
  }

  if (!csrf) {
    try {
      csrf = await refrescarTokenCSRF();
    } catch (e) {
      mostrarModalKioscoSinVoz('⚠️ No se pudo recuperar el token CSRF. Refrescar la página.', 'danger');
      return false;
    }
  }

  return true;
}

function manejarErrorFetch(err, contexto = 'Error de red') {
  const mensaje = typeof err === 'string' ? err :
    err?.message?.includes('419') ? '⚠️ Sesión expirada. Refrescar la página.' :
    err?.message?.includes('500') ? '⛔ Error interno del servidor.' :
    `${contexto}. Verificá conexión o sesión.`;

  console.error(`❌ ${contexto}:`, err);
  mostrarModalKioscoSinVoz(mensaje, 'danger');

  // 🛠️ Reactivar escáner si estamos en step9
  try {
    const stepActivo = document.querySelector('.step.active')?.id || getStepActivo();
    if (stepActivo === 'step9') {
      setTimeout(() => activarEscaneoDevolucionQR(), 500);
    }
  } catch (e) {
    console.warn('⚠️ No se pudo reactivar escáner tras error de red:', e);
  }

  return { success: false, error: err };
}


document.addEventListener('DOMContentLoaded', () => {
  console.log('🟢 Terminal cargada: iniciando ping de sesión');

  // 🔄 Mantener sesión activa y renovar token CSRF cada 1 minuto
  setInterval(() => {
    fetch('/csrf-token')
      .then(res => res.json())
      .then(data => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && data.token) {
          meta.setAttribute('content', data.token);
          console.log('🔄 Token CSRF renovado automáticamente');
        }
      })
      .catch(err => {
        console.warn('⚠️ Falló el ping de sesión:', err);
      });
  }, 10 * 1000); // cada 10 segundos
});


/* ==========================================
   Listeners y cleanup seguros (copiá tal cual)
   ========================================== */

// Listener: si se abre cualquier modal distinto al asistente, cerramos asistente para evitar conflictos
document.addEventListener('show.bs.modal', (event) => {
  const modalAsistente = document.getElementById('modalAsistente');
  if (!modalAsistente) return;
  // Comprobación en tiempo real: si otro modal se abre y el asistente está visible, cerrarlo
  const asistVisible = modalAsistente.classList.contains('show');
  if (event.target && event.target.id !== 'modalAsistente' && asistVisible) {
    console.log('🧠 Otro modal abierto, cerrando asistente para evitar conflicto');
    cerrarModalAsistente();
  }
});

// Listener seguro para hide del modal (solo si existe)
// Listener seguro para hide del modal (solo si existe)
(function attachModalHideHandler() {
  const modalEl = document.getElementById('modalAsistente');
  if (!modalEl) return;

  modalEl.addEventListener('hide.bs.modal', () => {
    cerrarModalAsistente();
  });
})();



// Cleanup adicional en beforeunload: cancelar TTS y resetear flags críticos
window.addEventListener('beforeunload', () => {
  try { window.speechSynthesis.cancel(); } catch (e) {}
  window.ttsEnCurso = false;
  window.textoUltimoTTS = '';
  window.timestampUltimoTTS = 0;
});