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

const micButton = document.getElementById('micStatusButton');
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
    

  if (document.getElementById('modalCerrarSesion')?.classList.contains('show')) {
  console.log('⛔️ safeStartRecognitionGlobal: bloqueado por modalCerrarSesion activo');
  return;
}

  const step = getStepActivo();
if (['step2','step3','step5','step6','step7','step9','step10'].includes(step)) {
  console.log(`⛔️ safeStartRecognitionGlobal: bloqueado en ${step}`);
  return;
}


  if (window.recognitionGlobalPaused) {
    console.log('⏸️ safeStartRecognitionGlobal: pausado, no se inicia');
    return;
  }

  if (getStepActivo() === 'step10') {
  console.log('⛔️ safeStartRecognitionGlobal: bloqueado en step10');
  return;
}


  // Limpieza defensiva: evitar reinicio si ya está corriendo
  if (recognitionRunning) {
    console.log('⏸️ safeStartRecognitionGlobal: ya corriendo, no se reinicia');
    return;
  }
  
  try {
    if (!('webkitSpeechRecognition' in window)) return;

    // 🚫 Si está pausado, no iniciar
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

function reactivarReconocimientoActual() {
  const step = getStepActivo();
  if (esStepConReconocimientoLocal(step)) {
    const fnName = `iniciarReconocimientoLocal${step.charAt(4).toUpperCase()}${step.slice(5)}`;
    const fn = window[fnName];
    if (typeof fn === 'function') {
      fn();
      console.log(`🎤 Reconocimiento local reactivado en ${step}`);
    } else {
      console.warn(`⚠️ No se encontró recogedor local para ${step}`);
    }
  } else {
    safeStartRecognitionGlobal();
    console.log('🎤 Reconocimiento global reactivado');
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
  const icon = document.getElementById('micStatusIcon');
  const text = document.getElementById('micStatusText');
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
  mensaje = quitarEmojis(mensaje); // 🧹 Limpiar emojis

  // 🔁 Redirigir ciertos mensajes a modal
  const mensajeModalForzado = [
    'Recurso asignado correctamente'
  ];

  if (tipo === 'warning' || tipo === 'danger' || mensajeModalForzado.includes(mensaje.trim())) {
    
    if (window.modalKioscoActivo && mensaje.trim() === 'Recurso asignado correctamente') {
  console.log('⏸️ Modal ya activo con mismo mensaje, se evita duplicación');
  return;
}

    mostrarModalKiosco(mensaje, tipo);
    return;
  }

  const container = document.getElementById('toast-container');
  if (!container) return;

  const toasts = container.querySelectorAll('.toast');
  for (const toast of toasts) {
    const body = toast.querySelector('.toast-body');
    if (body && body.textContent.trim() === mensaje.trim()) {
      return;
    }
  }

  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white bg-${tipo} border-0 show`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');
  toast.style.marginBottom = '0.5rem';

  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${mensaje}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

  body.textContent = mensaje;
  window.modalKioscoActivo = true;

  try {
    recognitionGlobalPaused = true;
    recognitionGlobal?.abort();
    console.log('🛑 Reconocimiento global abortado por modal kiosco');
  } catch (e) {
    console.warn('⚠️ No se pudo abortar recognitionGlobal:', e);
  }

  try {
    const modalSerie = document.getElementById('modalConfirmarSerie');
    if (modalSerie?.classList.contains('show')) {
      bootstrap.Modal.getInstance(modalSerie)?.hide();
      console.log('🔁 modalConfirmarSerie cerrado para mostrar mensaje kiosco');
    }
  } catch (e) {
    console.warn('⚠️ No se pudo cerrar modalConfirmarSerie desde mostrarModalKiosco', e);
  }

  if (modalEl.classList.contains('show')) {
    console.log('⏸️ Modal kiosco ya visible, se evita duplicación');
    return;
  }

  const modal = new bootstrap.Modal(modalEl);
  let modalActionTaken = false;
  let ultimoTexto = null;

  function cerrarModal() {
    if (modalActionTaken) return;
    modalActionTaken = true;
    modal.hide();
    cleanup();
    cerrarModalKiosco();
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
  if (modalActionTaken || texto === ultimoTexto) return;
  ultimoTexto = texto;

  // 🛑 Evitar bucle si el mensaje ya está activo y es el mismo
  if (window.modalKioscoActivo && body?.textContent?.trim() === 'Recurso asignado correctamente') {
    console.log('⏸️ Modal kiosco ya activo con mismo mensaje, ignorando comando de voz');
    return;
  }

  if (texto.includes('cerrar') || texto.includes('entendido') || texto.includes('ok')) {
    cerrarModal();
    try { recog.stop(); } catch (e) {}
  } else {
    modalEl._recogIntentosFallidos = (modalEl._recogIntentosFallidos || 0) + 1;
    console.log('⚠️ Comando no reconocido en modal kiosco:', texto);

    if (modalEl._recogIntentosFallidos >= 3) {
      console.log('⏹️ Intentos fallidos superados, cerrando modal automáticamente');
      cerrarModal();
    }
    // No llamamos mostrarMensajeKiosco para evitar bucle
  }
};



      recog.onerror = function (e) {
        console.warn('Reconocimiento modal kiosco falló', e);
        if (e?.error === 'aborted') return;

        try { recog.stop(); } catch (err) {}

        setTimeout(() => {
          if (!modalActionTaken && modalEl.classList.contains('show')) {
            try {
              recog.start();
              console.log('🔁 recog modal reiniciado tras error');
            } catch (err) {
              if (err.name === 'InvalidStateError') {
                console.log('⚠️ recog.start() ignorado: ya estaba iniciado');
              } else {
                console.warn('⚠️ recog.start() falló:', err);
              }
            }
          }
        }, 300);
      };

      recog.onend = function () {
        if (!modalActionTaken && modalEl.classList.contains('show')) {
          try {
            recog.start();
            console.log('🔁 recog modal reiniciado tras onend');
          } catch (err) {
            console.warn('⚠️ recog.start() falló en onend:', err);
          }
        }
      };

      modalEl._recogInstance = recog;
      modalEl._recogIntentosFallidos = 0;

      recog.start();
      console.log('🎤 reconocimiento local (modal kiosco) iniciado');
    }
  } catch (e) {
    console.warn('No se pudo iniciar reconocimiento modal kiosco:', e);
  }

  if (getStepActivo() === 'step9') {
    window._recogStep9PausadoPorModal = true;
    try {
      if (window._recogStep9) {
        window._recogStep9.onresult = null;
        window._recogStep9.onerror = null;
        window._recogStep9.onend = null;
        window._recogStep9.stop?.();
        window._recogStep9 = null;
        console.log('🛑 Reconocimiento local step9 pausado por mostrarModalKiosco');
      }
    } catch (e) {
      console.warn('⚠️ No se pudo pausar recog step9 desde mostrarModalKiosco', e);
    }
  }

  modal.show();
}



function cerrarModalKiosco() {
  const modalEl = document.getElementById('modal-mensaje-kiosco');
  if (!modalEl) return;

  modalEl.classList.remove('show');
  modalEl.style.display = 'none';
  window.modalKioscoActivo = false;
  window._modalKioscoErrorMostrado = false;

  // Reactivar escaneo QR si estamos en step9
  if (getStepActivo() === 'step9') {
    try {
      activarEscaneoDevolucionQR();
      console.log('📷 Escáner QR reactivado tras cierre de modal');
    } catch (e) {
      console.warn('⚠️ No se pudo reactivar escáner QR en step9:', e);
    }

    const btn = document.getElementById('btnConfirmarDevolucion');
    if (btn && !btn.disabled) {
      window._recogStep9PausadoPorModal = false;
      iniciarReconocimientoLocalStep9();
      console.log('🎤 Reconocimiento local step9 reactivado tras cierre de modal kiosco');
    } else {
      console.log('⏸️ No se reactiva recog step9: botón sigue deshabilitado');
    }
  }

  // Limpiar reconocimiento local del modal
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

  // ✅ Reactivar reconocimiento según el step
  try {
    recognitionGlobalPaused = false;
    safeStopRecognitionGlobal(); // por si quedó colgado

    const step = getStepActivo();
    if (step === 'step10') {
      abrirStepRecursos(true); // 🔁 fuerza reinicio completo
      console.log('🔁 step10 reactivado tras cerrar modal kiosco');
    } else if (esStepConReconocimientoLocal(step)) {
      const fnName = `iniciarReconocimientoLocal${step.charAt(4).toUpperCase()}${step.slice(5)}`;
      const fn = window[fnName];
      if (typeof fn === 'function') {
        fn();
        console.log(`🎤 Reconocimiento local ${step} reactivado tras cerrar modal kiosco`);
      } else {
        console.warn(`⚠️ No se encontró recogedor local para ${step}`);
      }
    } else {
      safeStartRecognitionGlobal();
      console.log('🎤 Reconocimiento global reactivado tras cerrar modal kiosco');
    }
  } catch (e) {
    console.warn('⚠️ No se pudo reiniciar reconocimiento tras cerrar modal kiosco', e);
  }

  // Ocultar backdrop manual si lo usás
  const backdropManual = document.getElementById('backdrop-manual-kiosco');
  if (backdropManual) backdropManual.style.display = 'none';

  document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
}



function quitarEmojis(texto) {
  return texto.replace(/([\u2700-\u27BF]|[\uE000-\uF8FF]|[\uD83C-\uDBFF\uDC00-\uDFFF])+/g, '');
}


//otras cosas
function nextStep(n) {
  try {
    // 🧹 Limpiar recog locales de todos los steps
    try {
      document.querySelectorAll('.step').forEach(s => {
        try {
          if (s._recogInstance) {
            s._recogInstance.onresult = null;
            s._recogInstance.onerror = null;
            s._recogInstance.onend = null;
            s._recogInstance.stop?.();
          }
        } catch (e) {}
        s._recogInstance = null;
        s._opening = false;
      });
    } catch (e) {
      console.warn('nextStep: limpieza recog locales falló', e);
    }

    // 🧹 Limpiar recog local de step2 si estaba activo
    try {
      if (window._recogStep2) {
        window._recogStep2.onresult = null;
        window._recogStep2.onerror = null;
        window._recogStep2.onend = null;
        window._recogStep2.stop?.();
        window._recogStep2 = null;
      }
    } catch (e) {
      console.warn('nextStep: limpieza recog step2 falló', e);
    }

    // 🛑 Detener escaneos QR
    try {
      detenerEscaneoQRregistroRecursos();
      cancelarEscaneoQRregistroRecursos();
      detenerEscaneoQRLogin();
      detenerEscaneoQRDevolucion();
      detenerEscaneoQRDevolucionSegura?.();
    } catch (e) {}

    // 🧹 Limpiar recog local de step3 si estaba activo
    try {
      if (window._recogStep3) {
        window._recogStep3.onresult = null;
        window._recogStep3.onerror = null;
        window._recogStep3.onend = null;
        window._recogStep3.stop?.();
        window._recogStep3 = null;
      }
    } catch (e) {
      console.warn('nextStep: limpieza recog step3 falló', e);
    }

    // 🧹 Limpiar recog local de step5 si estaba activo
try {
  if (window._recogStep5) {
    window._recogStep5.onresult = null;
    window._recogStep5.onerror = null;
    window._recogStep5.onend = null;
    window._recogStep5.stop?.();
    window._recogStep5 = null;
  }
} catch (e) {
  console.warn('nextStep: limpieza recog step5 falló', e);
}

try {
  if (window._recogStep6) {
    window._recogStep6.onresult = null;
    window._recogStep6.onerror = null;
    window._recogStep6.onend = null;
    window._recogStep6.stop?.();
    window._recogStep6 = null;
  }
} catch (e) {
  console.warn('nextStep: limpieza recog step6 falló', e);
}

try {
  if (window._recogStep7) {
    window._recogStep7.onresult = null;
    window._recogStep7.onerror = null;
    window._recogStep7.onend = null;
    window._recogStep7.stop?.();
    window._recogStep7 = null;
  }
} catch (e) {
  console.warn('nextStep: limpieza recog step7 falló', e);
}


try {
  if (window._recogStep9) {
    window._recogStep9.onresult = null;
    window._recogStep9.onerror = null;
    window._recogStep9.onend = null;
    window._recogStep9.stop?.();
    window._recogStep9 = null;
  }
} catch (e) {
  console.warn('nextStep: limpieza recog step9 falló', e);
}

    // Ocultar todos los steps
    document.querySelectorAll('.step').forEach(s => {
      s.classList.remove('active');
      s.classList.add('d-none');
    });

    // Activar el nuevo step
    const stepEl = document.getElementById('step' + n);
    if (stepEl && stepEl.classList) {
      stepEl.classList.remove('d-none');
      stepEl.classList.add('active');
    } else {
      console.warn('nextStep: step element not found:', 'step' + n);
    }

    // Botones flotantes
    const btnMenu = document.getElementById('boton-flotante-menu-principal');
    if (btnMenu) {
      if (n === 2) {
        btnMenu.disabled = true;
        btnMenu.style.pointerEvents = 'none';
        btnMenu.style.opacity = '0.5';
      } else {
        btnMenu.disabled = false;
        btnMenu.style.pointerEvents = 'auto';
        btnMenu.style.opacity = '1';
      }
    }

    // Acciones específicas por step
    if (n === 2) {
      cargarMenuPrincipal();
      pausarReconocimientoGlobal();
  iniciarReconocimientoLocalStep2();
     // recognitionGlobalPaused = true;
     // safeStopRecognitionGlobal();
     // iniciarReconocimientoLocalStep2(); // 👈 nuevo
    }

    if (n === 3) {
      recognitionGlobalPaused = true;
      safeStopRecognitionGlobal();
      iniciarReconocimientoLocalStep3();
    }


    if (n === 5) {
      cargarCategorias();
      pausarReconocimientoGlobal();
      iniciarReconocimientoLocalStep5();
    }

if (n === 6) {
  pausarReconocimientoGlobal();
  iniciarReconocimientoLocalStep6();
}

if (n === 7) {
  pausarReconocimientoGlobal();
  iniciarReconocimientoLocalStep7();
}


if (n === 9) {
  pausarReconocimientoGlobal();
  iniciarReconocimientoLocalStep9();
}

    // Reiniciar reconocimiento global si no es step2
    if (n !== 2  && n !== 3 && n !== 5 && n !== 6 && n !== 7 && n !== 9) {
      recognitionGlobalPaused = false;
      safeStopRecognitionGlobal();
      setTimeout(() => {
        safeStartRecognitionGlobal();
        console.log('🎤 Reconocimiento reiniciado tras cambio de step');
      }, 300);
    }

    // Visibilidad de botones flotantes
    try {
      if (typeof window._nextStepWrappedVisibilityUpdater === 'function') {
        window._nextStepWrappedVisibilityUpdater('step' + n);
      } else {
        const ocultar = (n === 1 || 'step' + n === 'step1');
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
          window.nextStep(2);
          document.getElementById('saludo-trabajador').innerHTML = `
            <span class="saludo-texto">Hola ${res.usuario.name}</span>
            <img src="/images/hola.svg" alt="Saludo" class="icono-saludo">
          `;
        } else {
      getRenderer('mostrarMensajeKiosco')(res.message || 'Error al identificar al trabajador', 'danger');
        }
        resolve(res);
      } catch (e) {
      getRenderer('mostrarMensajeKiosco')('Error al identificar al trabajador', 'danger');
        resolve({ success: false, error: e });
      }
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
  getRenderer('mostrarMensajeKiosco')('No se pudieron cargar las categorías', 'danger');
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
  getRenderer('mostrarMensajeKiosco')('Error al cargar recursos asignados', 'danger');
        resolve();
      }
    };

    xhr.onerror = function () {
  getRenderer('mostrarMensajeKiosco')('Error de red al cargar recursos asignados', 'danger');
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

  const porPagina = 5;
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

  const porPagina = 5;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  tabla.innerHTML = '';

  if (visibles.length === 0) {
    tabla.innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
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



function devolverRecurso(detalleId) {
  if (!confirm('¿Confirmás que querés devolver este recurso?')) {
    return Promise.resolve({ success: false, reason: 'cancelled' });
  }

  return fetch(`/terminal/devolver/${detalleId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(res => {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  })
  .then(data => {
    if (data.success) {
      mostrarMensajeKiosco('✅ Recurso devuelto correctamente', 'success');
      cargarRecursos(); // actualiza el modal
    } else {
      mostrarMensajeKiosco(data.message || 'Error al devolver recurso', 'danger');
    }
    return data;
  })
  .catch(err => {
    mostrarMensajeKiosco('Error de red al devolver recurso', 'danger');
    return { success: false, error: err };
  });
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
    getRenderer('mostrarMensajeKiosco')('Los recursos aún se están cargando. Intentá de nuevo en unos segundos.', 'warning');
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
    getRenderer('mostrarMensajeKiosco')(`No se encontró la opción ${index}. Verificá que esté visible.`, 'warning');
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
      getRenderer('mostrarMensajeKiosco')('Devolución cancelada.', 'info');
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
    btnMenu.style.opacity = '0.5';
  }
  if (btnCerrar) {
    btnCerrar.disabled = true;
    btnCerrar.style.pointerEvents = 'none';
    btnCerrar.style.opacity = '0.5';
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
  const btnConfirmar = document.getElementById('btnConfirmarDevolucion');

  if (serieEl) serieEl.textContent = serie || '';
  if (feedbackEl) feedbackEl.textContent = '';
  if (btnConfirmar) {
    try { btnConfirmar.disabled = true; } catch (e) {}
  }

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
        mostrarMensajeKiosco('No se pudo activar la cámara. Intente nuevamente.', 'danger');
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
function validarDevolucionQR(qrCode, idUsuario) {
  const serieEsperada = document.getElementById('serieEsperadaQR')?.textContent?.trim() || '';

  return fetch('/terminal/validar-qr-devolucion', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      codigo_qr: qrCode,
      id_usuario: idUsuario,
      serie_esperada: serieEsperada
    })
  })
  .then(async res => {
    const data = await res.json();

    // Debug: ver qué llega desde el backend
    console.log('📦 Respuesta completa de validación QR:', data);

    if (!res.ok) {
      return {
        success: false,
        message: data?.message || `Error HTTP ${res.status}`
      };
    }

    return data;
  })
  .catch(err => {
    console.error('Error de red en fetch:', err);
    return {
      success: false,
      message: 'Error de red al validar el QR'
    };
  });
}




// --------------------------
// confirmarDevolucionQRActual (actualizada)
// --------------------------
function confirmarDevolucionQRActual() {
  if (!detalleIdActual) {
    mostrarMensajeKiosco('No se puede confirmar devolución: falta el recurso.', 'danger');
    return;
  }

fetch('/terminal/devolver-recurso', {
  method: 'POST',
  headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
    body: JSON.stringify({ id_detalle: detalleIdActual })
})
  .then(res => res.json())
.then(data => {
  if (data.success) {
      // Si el backend indica que ya fue devuelto, no mostramos nada
      if (data.estado === 'ya_devuelto') {
        console.log('ℹ️ Recurso ya estaba devuelto, se omite mensaje');
        return;
      }

      mostrarMensajeKiosco('✅ Recurso devuelto correctamente.', 'success');
      window._devolucionCompletada = true;
      nextStep(2); // volver al menú principal o recursos asignados
  } else {
      // Si no hay mensaje, no mostramos nada
      if (!data.message) {
        console.log('⚠️ Respuesta sin mensaje, se omite toast de error');
        return;
      }

      mostrarMensajeKiosco(data.message || '❌ Error al devolver recurso.', 'danger');
  }
})
.catch(err => {
    mostrarMensajeKiosco('❌ Error de red al devolver recurso.', 'danger');
    console.error('Error en confirmarDevolucionQRActual:', err);
});


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
  // Evitar reentradas simultáneas
  if (window._qrDevolucionStopping) {
    console.log('↩️ detenerEscaneoQRDevolucionSegura: ya en curso');
    return;
  }
  window._qrDevolucionStopping = true;

  try {
    console.log('🧹 detenerEscaneoQRDevolucionSegura: inicio');

    // Detener reconocimiento de voz local si existe
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

    // Detener y limpiar html5QrCodeDevolucion si existe
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

    // Limpiar DOM del contenedor
    const qrContainer = document.getElementById('qr-reader-devolucion');
    if (qrContainer) {
      try { qrContainer.innerHTML = ''; } catch (e) { /* ignore */ }
    }

    // Resetar flags
    window._qrDevolucionActivo = false;
    console.log('🛑 Escaneo QR de devolución detenido (seguro)');

  } catch (e) {
    console.warn('⚠️ Error en detenerEscaneoQRDevolucionSegura', e);
  } finally {
    window._qrDevolucionStopping = false;
  }
}




function volverARecursosAsignadosDesdeDevolucionQR() {
  try {
    detenerEscaneoQRDevolucionSegura(); // 🔧 usa la versión segura
    nextStep(10);
    const btn = document.getElementById('btnVolverDevolucionQR');
    if (btn) btn.disabled = false; // por si quedó bloqueado
  } catch (e) {
    console.warn('⚠️ Error al ejecutar volver desde devolución QR', e);
  }

setTimeout(() => {
  if (getStepActivo() === 'step10') {
    console.log('🔁 Reiniciando reconocimiento local step10 tras volver desde step9');
    iniciarReconocimientoLocalStep10();
  }
}, 100);

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
    mostrarMensajeKiosco('No se encontró el área de escaneo.', 'danger');
    return;
  }

  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarMensajeKiosco('⚠️ Usuario no identificado', 'danger');
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
    mostrarMensajeKiosco('No se pudo inicializar el escáner.', 'danger');
    return;
  }

  window._qrDevolucionActivo = true;

  try {
    await window.html5QrCodeDevolucion.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      async (decodedText) => {
        console.log('🔎 QR detectado (decodedText):', decodedText);
        const res = await validarDevolucionQR(decodedText, idUsuario);
        console.log('📦 Respuesta de validación QR (handler):', res);

        if (!res.success || res.estado === 'qr_invalido') {
          await detenerEscaneoQRDevolucionSegura();
          safeStopRecognitionGlobal();

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
          detalleIdActual = res.id_detalle;
          document.getElementById('qrFeedback').textContent = '';
          mostrarMensajeKiosco('✅ QR válido. Confirma la devolución en pantalla.', 'success');

          const modalEl = document.getElementById('modalConfirmarQR');
          if (modalEl) {
            const body = document.getElementById('modalConfirmarQRBody');
            if (body) {
              const serie = document.getElementById('serieEsperadaQR')?.textContent || '';
              body.textContent = serie ? `¿Deseás confirmar la devolución de la serie ${serie}?` : '¿Deseás confirmar la devolución del recurso escaneado?';
            }

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            const aceptar = document.getElementById('btnAceptarQR');
            const cancelar = document.getElementById('btnCancelarQR');

            const onAceptar = () => { try { modal.hide(); } catch (e){}; confirmarDevolucionQRActual(); };
            const onCancelar = () => { try { modal.hide(); } catch (e){}; setTimeout(() => activarEscaneoDevolucionQR(), 250); };

            if (aceptar) {
              try { aceptar.removeEventListener('click', onAceptar); } catch(e){}
              aceptar.addEventListener('click', onAceptar);
            }
            if (cancelar) {
              try { cancelar.removeEventListener('click', onCancelar); } catch(e){}
              cancelar.addEventListener('click', onCancelar);
            }
          } else {
            confirmarDevolucionQRActual();
          }
        }
      },
      (errorMessage) => {
        const msg = String(errorMessage || '');
        if (msg.includes('No MultiFormat Readers')) {
          console.debug('frame scan: no QR detected');
          return;
        }
        console.warn('Error escaneo devolucion (frame):', errorMessage);
      }
    );

    console.log('📷 Escáner QR iniciado correctamente');
  } catch (err) {
    console.error('No se pudo iniciar escaneo devolución:', err);
    mostrarMensajeKiosco('No se pudo activar la cámara para escanear QR', 'danger');
    window._qrDevolucionActivo = false;
    try { await detenerEscaneoQRDevolucionSegura(); } catch(e){}
  }
}



function ExitoDevolucionQR(qrCodeMessage) {
  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarMensajeKiosco('⚠️ Usuario no identificado', 'danger');
    return;
  }

  validarDevolucionQR(qrCodeMessage, idUsuario)
    .then(res => {
      if (res.success && res.coincide) {
        devolverRecurso(res.id_detalle);
      } else {
        mostrarMensajeKiosco(res.message || '❌ QR no válido para devolución', 'warning');
      }
    })
    .catch(err => {
      console.error('Error validando QR:', err);
      mostrarMensajeKiosco('❌ Error al validar QR', 'danger');
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

    if (texto === 'confirmar' || texto === 'confirmar devolución') {
      const btn = document.getElementById('btnConfirmarDevolucion');
      if (btn && !btn.disabled) {
        btn.click();
        recog.stop();
      }
    } else if (texto === 'volver') {
      volverARecursosAsignadosDesdeDevolucionQR();
      recog.stop();
    } else if (texto === 'cerrar') {
      const btn = document.getElementById('btnCerrarErrorQR');
      if (btn) {
        btn.click();
        recog.stop();
      }
    }
  };

  recog.onerror = function (e) {
    console.warn('Reconocimiento devolución QR falló', e);
  };

  try {
    recog.start();
    console.log('🎤 Reconocimiento voz activo en paso 9');
  } catch (e) {
    console.warn('No se pudo iniciar reconocimiento QR', e);
  }

  window._recogQRDevolucion = recog;
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

  if (!qrContainer) {
    console.error('No se encontró el contenedor de escaneo QR')
  getRenderer('mostrarMensajeKiosco')('No se encontró el contenedor de escaneo QR', 'danger');
    return;
  }

  if (isScanning) return; // ya está activo

  qrContainer.innerHTML = '';
  if (btnEscanear) btnEscanear.classList.add('d-none');
  if (btnCancelar) btnCancelar.classList.remove('d-none');
  if (textoCamara) textoCamara.classList.remove('d-none');

  scanner = new Html5Qrcode("qr-reader");
  isScanning = true;

  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 400, height: 400 } },
    qrCodeMessage => {
      console.log('QR detectado:', qrCodeMessage);
      limpiarQRregistroRecursos();
      registrarPorQRregistroRecursos(qrCodeMessage);
    },
    errorMessage => {
      console.warn('Error de escaneo:', errorMessage);
    }
  ).catch(err => {
    console.error('Error al iniciar escaneo:', err);
  getRenderer('mostrarMensajeKiosco')('No se pudo activar la cámara para escanear QR', 'danger');
    limpiarQRregistroRecursos();
  });
}

function cancelarEscaneoQRregistroRecursos() {
  limpiarQRregistroRecursos();
}

function registrarPorQRregistroRecursos(codigoQR) {
  const id_usuario = window.localStorage.getItem('id_usuario');
  if (!id_usuario) {
  getRenderer('mostrarMensajeKiosco')('⚠️ No hay trabajador identificado', 'danger');
    return Promise.resolve({ success: false, reason: 'no_usuario' });
  }

  const meta = (typeof document !== 'undefined') && document.querySelector('meta[name="csrf-token"]');
  const csrf = meta && meta.content ? meta.content : null;
  const headers = { 'Content-Type': 'application/json' };
  if (csrf) headers['X-CSRF-TOKEN'] = csrf;

  return fetch(`/terminal/registrar-por-qr`, {
    method: 'POST',
    headers,
    body: JSON.stringify({ codigo_qr: codigoQR, id_usuario })
  })
  .then(res => {
    if (!res || (typeof res.ok === 'boolean' && !res.ok)) {
      throw new Error(res ? `HTTP ${res.status}` : 'network error');
    }
    return res.json();
  })
  .then(data => {
    if (data && data.success) {
      const mensaje = `✅ Recurso registrado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`;
  if (typeof window.mostrarMensajeKiosco === 'function') getRenderer('mostrarMensajeKiosco')(mensaje, 'success');
      if (typeof window.nextStep === 'function') window.nextStep(2);
    } else {
      if (data && data.message === 'QR no encontrado') {
  getRenderer('mostrarMensajeKiosco')('❌ QR no encontrado en el sistema', 'danger');
      } else if (data && data.message === 'Este recurso ya está asignado') {
  getRenderer('mostrarMensajeKiosco')(`⚠️ Este recurso ya está asignado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`, 'warning');
      } else {
  getRenderer('mostrarMensajeKiosco')((data && data.message) || 'Error al registrar recurso por QR', 'danger');
      }
    }
    return data;
  })
  .catch(err => {
    window.mostrarMensajeKiosco('Error de red al registrar recurso por QR', 'danger');
    console.log('❌ Error de red al registrar recurso por QR', err);
    return { success: false, error: err };
  });
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

function limpiarQRregistroRecursos() {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (scanner && isScanning) {
    scanner.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      if (btnCancelar) btnCancelar.classList.add('d-none');
      if (btnEscanear) btnEscanear.classList.remove('d-none');
      if (textoCamara) textoCamara.classList.add('d-none');
      isScanning = false;
    });
  } else {
    qrContainer.innerHTML = '';
    if (btnCancelar) btnCancelar.classList.add('d-none');
    if (btnEscanear) btnEscanear.classList.remove('d-none');
    if (textoCamara) textoCamara.classList.add('d-none');
    isScanning = false;
  }
}

// === Paso 1: Escaneo QR para login o inicio de sesión === 
function activarEscaneoQRLogin() {
  const qrContainer = document.getElementById('qr-login-reader');
  const wrapper = document.getElementById('qr-login-container');

  if (!qrContainer || !wrapper || isScanning) {
    console.error('❌ activarEscaneoQRLogin: contenedor o wrapper no disponible, o escaneo ya activo');
    return;
  }

  wrapper.style.display = 'block';
  qrContainer.innerHTML = '';
  scanner = new Html5Qrcode("qr-login-reader");
  isScanning = true;

  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 250, height: 250 } },
    qrCodeMessage => {
      console.log('QR de login detectado:', qrCodeMessage);

      // 👉 detenemos el escaneo para liberar la cámara
      detenerEscaneoQRLogin();

      // 👉 llamamos al método corregido que envía { codigo_qr: ... }
      identificarPorQRLogin(qrCodeMessage);
    },
    errorMessage => {
      console.warn('Error escaneo login:', errorMessage);
    }
  ).catch(err => {
    console.error('No se pudo iniciar escaneo login:', err);
    window.mostrarMensajeKiosco('No se pudo activar la cámara para escanear QR', 'danger');
    detenerEscaneoQRLogin();
  });
}

function detenerEscaneoQRLogin() {
  const qrContainer = document.getElementById('qr-login-reader');
  const wrapper = document.getElementById('qr-login-container');

  if (scanner && isScanning) {
    scanner.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      wrapper.style.display = 'none';
      console.log('📴 detenerEscaneoQRLogin: escaneo login detenido y UI oculta');
      isScanning = false;
    });
  } else {
    qrContainer.innerHTML = '';
    wrapper.style.display = 'none';
    isScanning = false;
  }
}

function identificarPorQRLogin(codigoQR) {
  const meta = document.querySelector('meta[name="csrf-token"]');
  const csrf = meta && meta.content ? meta.content : null;
  const headers = { 'Content-Type': 'application/json' };
  if (csrf) headers['X-CSRF-TOKEN'] = csrf;
  fetch('/terminal/identificar', {
    method: 'POST',
    headers,
    body: JSON.stringify({ codigo_qr: codigoQR })
  })
  .then(res => res.json())
  .then(data => {
    console.log('Respuesta login QR:', data);

    if (data.success) {
      // Usuario válido (rol trabajador + estado Alta)
      localStorage.setItem('id_usuario', data.usuario.id);
      window.nextStep(2);
      document.getElementById('saludo-trabajador').textContent = `Hola ${data.usuario.name}`;
    } else {
      // Mensajes diferenciados según backend
      if (data.message === 'Usuario no encontrado') {
        window.mostrarMensajeKiosco('❌ Usuario no encontrado en el sistema', 'danger');
      console.log('❌ Usuario no encontrado en el sistema');
      } else if (data.message === 'Este usuario no tiene permisos para usar el kiosco') {
        window.mostrarMensajeKiosco('⚠️ Este usuario no tiene permisos para usar el kiosco', 'warning');
      console.log('⚠️ Este usuario no tiene permisos para usar el kiosco');
      } else if (data.message === 'El usuario no está en estado Alta y no puede usar el kiosco') {
        window.mostrarMensajeKiosco('⛔ El usuario no está en estado Alta y no puede usar el kiosco', 'danger');
      console.log('⛔ El usuario no está en estado Alta y no puede usar el kiosco');
      } else {
        window.mostrarMensajeKiosco(data.message || 'Error al identificar por QR', 'danger');
      console.log('Error al identificar por QR');
      }
    }
  })
  .catch(err => {
    console.error('Error en fetch login QR:', err);
    window.mostrarMensajeKiosco('Error de red al identificar por QR', 'danger');
  });
}

// Función para botón Volver en step3
function volverAInicio() {
  // Limpiamos la sesión del trabajador
  localStorage.removeItem('id_usuario');
  console.log('volverAInicio: sesión limpiada');

  // Volvemos al paso 1
  window.nextStep(1);
  // Opcional: limpiar el campo clave por si quedó algo escrito
  const claveInput = document.getElementById('clave');
  if (claveInput) claveInput.value = '';
  if (claveInput) claveInput.focus();


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
  getRenderer('mostrarMensajeKiosco')('No se pudieron cargar las subcategorías', 'danger');
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

  const porPagina = 5;
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
  getRenderer('mostrarMensajeKiosco')('No se pudieron cargar los recursos', 'danger');
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

  const porPagina = 5;
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
  getRenderer('mostrarMensajeKiosco')('No se pudieron cargar las series', 'danger');
      console.log('❌ No se pudieron cargar las series', e);
    }
  };

  xhr.onerror = function () {
  getRenderer('mostrarMensajeKiosco')('❌ Error de red al cargar las series', 'danger');
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

  const porPagina = 5;
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
    modalEl._recogIntentosFallidos = 0;

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
    if (typeof mostrarMensaje === 'function') mostrarMensaje('Solicitud cancelada.', 'info');
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
          mostrarMensaje('No se reconoció el comando. Decí “aceptar” o “cancelar”.', 'info');
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
  const id_usuario = window.localStorage.getItem('id_usuario');
  
   // validaciones inline
  if (!serieId) {
  mostrarMensajeKiosco && getRenderer('mostrarMensajeKiosco')('Serie inválida', 'warning');
    return { success: false, reason: 'invalid_series' };
  }
  
  if (!id_usuario) {
  if (typeof window.mostrarMensajeKiosco === 'function') getRenderer('mostrarMensajeKiosco')('⚠️ No hay trabajador identificado', 'danger');
    return { success: false, reason: 'no_usuario' };
  }

  try {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = meta && meta.content ? meta.content : null;
    const headers = { 'Content-Type': 'application/json' };
    if (csrf) headers['X-CSRF-TOKEN'] = csrf;

    const res = await fetch(`/terminal/prestamos/${id_usuario}`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ series: [serieId] })
    });

    if (!res || (typeof res.ok === 'boolean' && !res.ok)) {
      const statusText = res && res.status ? `HTTP ${res.status}` : 'network error';
      if (typeof window.mostrarMensajeKiosco === 'function') 
        {
          getRenderer('mostrarMensajeKiosco')('Error de red al registrar recurso', 'danger');
          console.log('❌ Error de red al registrar recurso');
        }
      return { success: false, reason: 'http_error', status: res && res.status, statusText };
    }

    const data = await res.json();

    if (data && data.success) {
      if (typeof window.mostrarMensajeKiosco === 'function') 
        {
          mostrarModalResultadoRegistro('Recurso asignado correctamente', boton);


          //mostrarModalKiosco('✅ Recurso asignado correctamente', 'success');

          console.log('✅ Recurso asignado correctamente');
        }

      // ✅ Actualizar botón si se pasó como referencia
      if (boton && boton instanceof HTMLElement) {
        boton.innerHTML = `<span class="flex-grow-1 text-start">✅ Recurso asignado</span>`;
        boton.disabled = true;
        boton.classList.remove('btn-outline-success');
        boton.classList.add('btn-success');
      }

      return { success: true, data };
    } else {
  if (typeof window.mostrarMensajeKiosco === 'function') getRenderer('mostrarMensajeKiosco')((data && data.message) || 'Error al registrar recurso', 'danger');
      return { success: false, reason: 'backend_error', data };
    }
  } catch (err) {
    if (typeof window.mostrarMensajeKiosco === 'function') 
      {
  getRenderer('mostrarMensajeKiosco')('Error de red al registrar recurso', 'danger');
        console.log('❌ Error de red al registrar recurso');
      }
    return { success: false, reason: 'exception', error: err && (err.message || String(err)) };
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
          getRenderer('mostrarMensajeKiosco')('Debés identificarte antes de abrir el Menú principal', 'warning');
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
        claveInput.focus();
        getRenderer('mostrarMensajeKiosco')('🧹 clave borrada', 'info');
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
        btnMenu.style.opacity = '0.5';
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
        btnCerrar.style.opacity = '0.5';
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
        setTimeout(() => {
          try {
            const btnMenu = document.getElementById('boton-flotante-menu-principal');
            const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
            const activo = typeof n === 'number' ? 'step' + n : (document.querySelector('.step.active')?.id || getStepActivo());
            const enStep1 = (activo === 'step1' || activo === '1');

            if (btnMenu) {
              if (enStep1) {
                btnMenu.disabled = true;
                btnMenu.setAttribute('aria-disabled', 'true');
                btnMenu.style.pointerEvents = 'none';
                btnMenu.style.opacity = '0.5';
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
                btnCerrar.style.opacity = '0.5';
              } else {
                btnCerrar.disabled = false;
                btnCerrar.removeAttribute('aria-disabled');
                btnCerrar.style.pointerEvents = 'auto';
                btnCerrar.style.opacity = '1';
              }
            }
          } catch (e) { console.warn('Reaplicacion estado botones falló', e); }
        }, 40);
      };
      window._nextStepWrappedForMenuProtection = true;
    }
  } catch (e) {
    console.warn('No se pudo wrappear nextStep para protección adicional', e);
  }
});

function mostrarModalResultadoRegistro(mensaje, botonSerie = null, tipo = 'success') {
  const modalEl = document.getElementById('modalResultadoRegistro');
  const body = document.getElementById('modalResultadoRegistroBody');
  const btnAceptar = document.getElementById('btnAceptarResultadoRegistro');
  const btnCerrar = document.getElementById('btnCerrarResultadoRegistro');

  if (!modalEl || !body || !btnAceptar || !btnCerrar) return;

  // 🧠 Extraer texto de serie desde el botón
  let serieTexto = '';
  if (botonSerie && botonSerie instanceof HTMLElement) {
    const el = botonSerie.querySelector('.flex-grow-1');
    serieTexto = el ? el.textContent.trim() : botonSerie.textContent.trim();
  }

  // 🖤 Mensaje en negro con serie incluida
  const textoFinal = serieTexto ? `${mensaje}: ${serieTexto}` : mensaje;
  body.textContent = textoFinal;
  body.className = 'text-dark';

  // 🛑 Detener todos los recogedores
  detenerTodosLosRecogedoresLocales();
  if (window.recognitionGlobal) {
    try {
      window.recognitionGlobal.abort?.();
      window.recognitionGlobal.stop?.();
    } catch (e) {}
    window.recognitionGlobal = null;
    window.recognitionRunning = false;
    window.recognitionGlobalPaused = true;
  }

  // Marcar el modal como esperando comando de voz
  modalEl.classList.add('esperando-aceptar');

  const cerrar = () => {
    try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
    modalEl.classList.remove('esperando-aceptar');
    window.recognitionGlobalPaused = false;
    safeStartRecognitionGlobal();
  };

  btnAceptar.onclick = cerrar;
  btnCerrar.onclick = cerrar;
  document.querySelectorAll('.btn-cerrar-modal').forEach(btn => {
    btn.onclick = cerrar;
  });

  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}


function BorrarClave() {
  const claveInput = document.getElementById('clave');
  if (claveInput) {
    claveInput.value = '';
    claveInput.focus();
    getRenderer('mostrarMensajeKiosco')('🧹 clave borrada', 'info');
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
    accion: () => {
      console.log('📦 opción seleccionada: herramienta en mano');
      setModoEscaneo('manual');
    },
    clase: "btn-outline-dark",
    icono: "/images/trabajadorHerramienta.svg"
  },
  {
  id: 2,
  texto: "Quiero solicitar una herramienta",
  accion: () => {
    const id_usuario = window.localStorage.getItem('id_usuario');
    if (!id_usuario) {
      console.warn('⚠️ cargarMenuPrincipal: no hay id_usuario para solicitar herramienta');
      window.mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
      return;
    }

    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = meta && meta.content ? meta.content : null;
    const headers = { 'Content-Type': 'application/json' };
    if (csrf) headers['X-CSRF-TOKEN'] = csrf;
    fetch('/terminal/solicitar', {
      method: 'POST',
      headers,
      body: JSON.stringify({ id_usuario })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        console.warn('❌ No se puede solicitar herramientas:', data.message);
        window.mostrarMensajeKiosco(data.message || 'No se puede solicitar herramientas', 'warning');
        return;
      }

      console.log('🛠️ opción seleccionada: solicitar herramienta');
      step5ReturnTarget = 2;
      window.nextStep(5);
    })
    .catch(() => {
      console.error('❌ Error de red al validar EPP');
      window.mostrarMensajeKiosco('Error de red al validar EPP', 'danger');
    });
  },
  clase: "btn-outline-dark",
  icono: "/images/herramienta2.svg"
},
  // dentro de opciones en cargarMenuPrincipal, reemplazar accion de la opción 3 por:
 {
  id: 3,
  texto: "Ver recursos asignados",
  accion: () => {
    console.log('📋 opción seleccionada: ver recursos asignados');
    window.cargarRecursos().then(() => {
      abrirStepRecursos();
    });
  },
  clase: "btn-outline-dark",
  icono: "/images/list.svg"
}



];


  console.log('📋 cargarMenuPrincipal: opciones generadas', opciones);

  opciones.forEach(op => {
  const btn = document.createElement('button');

  if (op.clase.includes('simple')) {
    // Botón limpio sin badge ni layout flex
    btn.className = `btn btn-primary btn-lg mt-3`;
    btn.textContent = op.texto;
  } else {
    // Botones con badge y layout horizontal
    btn.className = `btn ${op.clase} btn-lg d-flex align-items-center justify-content-start m-2 w-100`;
    btn.innerHTML = `
  <span class="badge-opcion">Opción ${op.id}</span>
  <span class="ms-2 flex-grow-1 text-start d-flex align-items-center gap-2">
    ${op.icono ? `<img src="${op.icono}" alt="Icono" class="icono-opcion">` : ''}
    ${op.texto}
  </span>
`;
  }

  btn.onclick = op.accion;
  contenedor.appendChild(btn);
});

}


// 👇 nuevo: función para botón Volver en step5
function volverDesdeStep5() {
  window.nextStep(step5ReturnTarget);
}

// step 10 - recursos asignados
function abrirStepRecursos(forceRestart = false) {
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
    <button class="btn btn-primary me-2 d-flex align-items-center gap-2" id="tab-epp-step" type="button" aria-selected="true">
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
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead><tr><th>Subcategoría / Recurso</th><th>Serie</th><th>Fecha de préstamo</th><th>Fecha de devolución</th><th>Devolver</th></tr></thead>
              <tbody id="tablaEPP-step"></tbody>
            </table>
          </div>
          <div id="paginadorEPP-step" class="d-flex flex-wrap justify-content-center mt-3"></div>
        </div>

        <div id="panel-herramientas-step" class="tab-pane">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead><tr><th>Subcategoría / Recurso</th><th>Serie</th><th>Fecha de préstamo</th><th>Fecha de devolución</th><th>Devolver</th></tr></thead>
              <tbody id="tablaHerramientas-step"></tbody>
            </table>
          </div>
          <div id="paginadorHerramientas-step" class="d-flex flex-wrap justify-content-center mt-3"></div>
        </div>
      </div>

      <div class="text-center mt-3">
      <button id="btnVolverStepRecursos" class="btn btn-primary texto-volver d-flex align-items-center gap-2">
        <img src="/images/volver.svg" alt="Volver" class="icono-opcion">
        <span>Volver</span>
      </button>
    </div>`;
    document.querySelector('.container-kiosk')?.appendChild(stepEl);
  }

  if (stepEl._opening && !forceRestart) return;
  stepEl._opening = true;

  recognitionGlobalPaused = true;
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('abrirStepRecursos: safeStopRecognitionGlobal failed', e); }

  try { nextStep(10); } catch (e) { console.warn('abrirStepRecursos: nextStep(10) falló', e); }

  try {
    const tabEPP = document.getElementById('tab-epp-step');
    const tabHerr = document.getElementById('tab-herramientas-step');
    const panelEPP = document.getElementById('panel-epp-step');
    const panelHerr = document.getElementById('panel-herramientas-step');

    if (tabEPP) { tabEPP.classList.add('active'); tabEPP.setAttribute('aria-selected','true'); }
    if (tabHerr) { tabHerr.classList.remove('active'); tabHerr.setAttribute('aria-selected','false'); }
    if (panelEPP) { panelEPP.classList.add('show','active'); }
    if (panelHerr) { panelHerr.classList.remove('show','active'); }

    if (window.recursosEPP) renderTablaRecursosStep('tablaEPP-step', window.recursosEPP, window.paginaEPPActual || 1, 'paginadorEPP-step');
    else document.getElementById('tablaEPP-step').innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;

    if (window.recursosHerramientas) renderTablaRecursosStep('tablaHerramientas-step', window.recursosHerramientas, window.paginaHerramientasActual || 1, 'paginadorHerramientas-step');
  } catch (e) { console.warn('abrirStepRecursos: preparar UI falló', e); }

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
        document.getElementById('panel-epp-step')?.classList.add('show','active');
        document.getElementById('panel-herramientas-step')?.classList.remove('show','active');
        tabEPPBtn.classList.add('active'); tabEPPBtn.setAttribute('aria-selected','true');
        tabHerrBtn.classList.remove('active'); tabHerrBtn.setAttribute('aria-selected','false');
        safeStartRecognitionGlobal();
      });
      tabEPPBtn._connected = true;
    }
    if (tabHerrBtn && !tabHerrBtn._connected) {
      tabHerrBtn.addEventListener('click', () => {
        document.getElementById('panel-herramientas-step')?.classList.add('show','active');
        document.getElementById('panel-epp-step')?.classList.remove('show','active');
        tabHerrBtn.classList.add('active'); tabHerrBtn.setAttribute('aria-selected','true');
        tabEPPBtn.classList.remove('active'); tabEPPBtn.setAttribute('aria-selected','false');
        safeStartRecognitionGlobal();
      });
      tabHerrBtn._connected = true;
    }
  } catch (e) { console.warn('abrirStepRecursos: conectar listeners falló', e); }

  try {
    iniciarReconocimientoLocalStep10();
  } catch (e) {
    console.warn('abrirStepRecursos: iniciarReconocimientoLocalStep10 falló', e);
  }

  stepEl._opening = false;
}

function renderTablaRecursosStep(tablaId, recursos = [], pagina = 1, paginadorId) {
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('renderTablaRecursosStep: safeStop failed', e); }

  const tabla = document.getElementById(tablaId);
  const paginador = document.getElementById(paginadorId);
  if (!tabla || !paginador) {
    try {
      if (getStepActivo() !== 'step10') {
        setTimeout(() => safeStartRecognitionGlobal(), 80);
      }
    } catch (e) {}
    return;
  }

  const porPagina = 5;
  const totalPaginas = Math.max(1, Math.ceil((recursos || []).length / porPagina));
  const inicio = (pagina - 1) * porPagina;
  const visibles = (recursos || []).slice(inicio, inicio + porPagina);

  tabla.innerHTML = '';
  if (visibles.length === 0) {
    tabla.innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
    paginador.innerHTML = '';
    try {
      if (getStepActivo() !== 'step10') {
        setTimeout(() => safeStartRecognitionGlobal(), 80);
      }
    } catch (e) {}
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
    b.textContent = `Pagina ${i}`;
    b.onclick = () => {
      try { safeStopRecognitionGlobal(); } catch (e) {}
      setTimeout(() => renderTablaRecursosStep(tablaId, recursos, i, paginadorId), 60);
    };
    paginador.appendChild(b);
  }

  if (tablaId === 'tablaEPP-step') window.paginaEPPActual = pagina;
  if (tablaId === 'tablaHerramientas-step') window.paginaHerramientasActual = pagina;

  try {
    if (getStepActivo() !== 'step10') {
      setTimeout(() => safeStartRecognitionGlobal(), 80);
    }
  } catch (e) {}
}

function confirmarDevolucionPorVozStep10(index) {
  console.log(`🎤 confirmarDevolucionPorVozStep10: opción ${index}`);

  const botonesEPP = document.querySelectorAll('#tablaEPP-step button');
  const botonesHerr = document.querySelectorAll('#tablaHerramientas-step button');

  const eppActivo = document.getElementById('tab-epp-step')?.classList.contains('active');
  const herrActivo = document.getElementById('tab-herramientas-step')?.classList.contains('active');

  let btn = null;
  if (eppActivo) btn = document.querySelector(`#tablaEPP-step button[data-opcion-index="${index}"]`) || Array.from(botonesEPP)[index-1];
  else if (herrActivo) btn = document.querySelector(`#tablaHerramientas-step button[data-opcion-index="${index}"]`) || Array.from(botonesHerr)[index-1];
  else btn = document.querySelector(`#tablaEPP-step button[data-opcion-index="${index}"]`) || document.querySelector(`#tablaHerramientas-step button[data-opcion-index="${index}"]`);

  if (!btn) {
    getRenderer('mostrarMensajeKiosco')(`No se encontró la opción ${index}. Verificá que esté visible.`, 'warning');
    return;
  }

  const detalleId = btn.dataset.detalleId;
  const serie = btn.dataset.serie || '';
  console.log(`➡️ confirmarDevolucionPorVozStep10: detalleId=${detalleId}, serie=${serie}`);

  window._modalConfirmedByVoice = true;
  try { safeStopRecognitionGlobal(); } catch (e) {}
  mostrarStepDevolucionQR(serie, detalleId);
}

function handleStep10Pagina(numero) {
  if (!Number.isFinite(numero) || numero < 1) {
    getRenderer('mostrarMensajeKiosco')('Número de página no reconocido', 'warning');
    return;
  }
  const eppActivo = document.getElementById('tab-epp-step')?.classList.contains('active');
  const herrActivo = document.getElementById('tab-herramientas-step')?.classList.contains('active');

  if (eppActivo) {
    const total = Math.max(1, Math.ceil((window.recursosEPP?.length || 0) / 5));
    if (numero > total) { getRenderer('mostrarMensajeKiosco')('Número de página inválido', 'warning'); return; }
    renderTablaRecursosStep('tablaEPP-step', window.recursosEPP || [], numero, 'paginadorEPP-step');
    return;
  }

  if (herrActivo) {
    const total = Math.max(1, Math.ceil((window.recursosHerramientas?.length || 0) / 5));
    if (numero > total) { getRenderer('mostrarMensajeKiosco')('Número de página inválido', 'warning'); return; }
    renderTablaRecursosStep('tablaHerramientas-step', window.recursosHerramientas || [], numero, 'paginadorHerramientas-step');
    return;
  }
  
  console.log(`📄 handleStep10Pagina: solicitando página ${numero}`);
  getRenderer('mostrarMensajeKiosco')('No se detectó el tab activo', 'warning');
}




// 🔧 Normalizar texto (quita acentos)
function normalizarTexto(str) {
  console.log('🔤 normalizarTexto: texto original →', str);
  
  return str
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
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

    if (mostrarMensajesMicrofono)
      window.mostrarMensajeKiosco('🎤 Micrófono activo: podés dar comandos por voz', 'info');
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

  if (recognitionGlobalPaused) {
    console.log("⏸️ recognitionGlobal.onend: pausado, no se reinicia");
    return;
  }

  if (getStepActivo() === 'step10') {
  console.log('⛔️ recognitionGlobal.onend: no se reinicia en step10');
  return;
}


  safeStartRecognitionGlobal();
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
  if (claveInput) claveInput.focus();
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
              <h5 class="modal-title" id="modalCerrarSesionLabel">Confirmación de cierre de sesion</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalCerrarSesionBody">
              ¿Desea cerrar sesion?
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

  try { window.nextStep && window.nextStep(1); } catch (e) { console.warn('⚠️ ejecutarCerrarSesion: nextStep(1) falló', e); }

  // reintentar levantar reconocimiento después de un pequeño delay (opcional)
  try {
    recognitionGlobalPaused = false;
    setTimeout(() => {
      safeStartRecognitionGlobal();
      console.log('🎤 recognitionGlobal: intento de reinicio tras logout');
    }, 120);
  } catch (e) {
    console.warn('⚠️ ejecutarCerrarSesion: safeStartRecognitionGlobal falló', e);
  }
}

// --- Mostrar modal y conectar botones (idempotente) ---
function mostrarModalCerrarSesion() {
  const modalEl = asegurarModalCerrarSesion();
  if (!modalEl) return;

  if (modalEl._opening) return;
  modalEl._opening = true;

  recognitionGlobalPaused = true;
  try { safeStopRecognitionGlobal(); } catch (e) { console.warn('⚠️ mostrarModalCerrarSesion: safeStop falló', e); }

  const aceptarBtn = modalEl.querySelector('#btnAceptarCerrarSesion');
  const cancelarBtn = modalEl.querySelector('#btnCancelarCerrarSesion');

  function onAceptar() {
    try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
    modalEl._opening = false;
    ejecutarCerrarSesion();
    setTimeout(() => {
      recognitionGlobalPaused = false;
      const step = getStepActivo();
      if (!esStepConReconocimientoLocal(step)) {
        safeStartRecognitionGlobal();
      }
      console.log('🎤 recognitionGlobal: reiniciado tras aceptar cierre de sesión');
    }, 120);
  }

  function onCancelar() {
    reactivarReconocimientoActual();

    try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch (e) {}
    modalEl._opening = false;
    recognitionGlobalPaused = false;
    const step = getStepActivo();
    if (!esStepConReconocimientoLocal(step)) {
      safeStartRecognitionGlobal();
    }
    console.log('🎤 recognitionGlobal: reiniciado tras cancelar cierre de sesión');
  }

  try { aceptarBtn && aceptarBtn.removeEventListener('click', onAceptar); } catch (e) {}
  try { cancelarBtn && cancelarBtn.removeEventListener('click', onCancelar); } catch (e) {}
  if (aceptarBtn) aceptarBtn.addEventListener('click', onAceptar);
  if (cancelarBtn) cancelarBtn.addEventListener('click', onCancelar);

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

  // 🛑 Detenemos todos los recogedores locales antes de iniciar el del modal
  detenerTodosLosRecogedoresLocales(); // ← ESTE ES EL PUNTO CLAVE QUE TE MENCIONÉ

  // 🎤 Reconocimiento de voz local dentro del modal
  try {
    if ('webkitSpeechRecognition' in window) {
      detenerTodosLosRecogedoresLocales(); // ← Esto garantiza exclusividad

      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = true;
      recog.interimResults = false;

      modalEl._actionTaken = false;

      recog.onresult = function (event) {
        const textoRec = (event.results?.[0]?.[0]?.transcript || '').toLowerCase().trim();
        const limpio = normalizarTexto(textoRec);
        console.log('🎤 Texto reconocido (modal cerrar sesión):', limpio);

        if (modalEl._actionTaken || !modalEl.classList.contains('show')) return;

        if (limpio === 'aceptar') {
          modalEl._actionTaken = true;
          console.log('🟢 cerrar sesión: voz reconocida como aceptar');
          setTimeout(() => {
            try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch(e){}
            ejecutarCerrarSesion();
          }, 50);
          return;
        }

        if (limpio === 'cancelar') {
          modalEl._actionTaken = true;
          console.log('🔴 cerrar sesión: voz reconocida como cancelar');
          setTimeout(() => {
            try { bootstrap.Modal.getInstance(modalEl)?.hide(); } catch(e){}
            recognitionGlobalPaused = false;
            const step = getStepActivo();
            if (!esStepConReconocimientoLocal(step)) {
              safeStartRecognitionGlobal();
            }
          }, 50);
          return;
        }

        console.log('⚠️ cerrar sesión: comando no reconocido → ignorado:', limpio);
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
      };

      recog.onerror = function (e) {
        console.warn('Reconocimiento modal cerrar sesión falló', e);
        if (e.error === 'aborted') {
          console.log('⛔️ recog modal abortado, no se reinicia');
          return;
        }
        try {
          recog.stop();
          setTimeout(() => {
            recog.start();
            console.log('🔁 recog modal reiniciado tras error');
          }, 300);
        } catch (err) {
          console.warn('⚠️ recog modal reinicio falló:', err);
        }
      };


      modalEl._recogInstance = recog;
      window._recogCerrarSesion = recog; // ← Guardamos como si fuera un step exclusivo
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
        recog.stop();
      }
    } catch (e) {
      console.warn('No se pudo limpiar recog modal cerrar sesión', e);
    }
    modalEl._recogInstance = null;
    modalEl._actionTaken = false;
    window._recogCerrarSesion = null; // ← Limpiamos como cualquier recogedor de step

    recognitionGlobalPaused = false;
    const step = getStepActivo();
    if (!esStepConReconocimientoLocal(step)) {
      try {
        safeStartRecognitionGlobal();
        console.log('🎤 recognitionGlobal: reiniciado tras cerrar modal de sesión');
      } catch (e) {
        console.warn('⚠️ No se pudo reiniciar reconocimiento tras cerrar modal de sesión', e);
      }
    }
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

  // boton Cerrar Sesion
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
    btnCerrar.style.pointerEvents = 'auto';
    btnCerrar.textContent = 'Cerrar sesión';
    btnCerrar.setAttribute('aria-label', 'Cerrar sesión');
    wrapper.appendChild(btnCerrar);
  }

  // boton Menu Principal
  let btnMenu = document.getElementById('boton-flotante-menu-principal');
  if (!btnMenu) {
    btnMenu = document.createElement('button');
    btnMenu.id = 'boton-flotante-menu-principal';
    btnMenu.type = 'button';
    btnMenu.title = 'Menu principal';
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
    btnMenu.style.pointerEvents = 'auto';
    btnMenu.textContent = 'Menu principal';
    btnMenu.setAttribute('aria-label', 'Menu principal');
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
      console.log('📋 Menu principal: botón pulsado');
      try { safeStopRecognitionGlobal(); } catch (e) { console.warn('⚠️ Menu principal: safeStop falló', e); }
      try {
        window.nextStep && window.nextStep(2);
        try { cargarMenuPrincipal && cargarMenuPrincipal(); } catch (e) {}
        console.log('➡️ Navegando a step2 (¿Qué querés hacer?)');
      } catch (e) { console.warn('⚠️ Menu principal: nextStep(2) falló', e); }
      try { setTimeout(() => { safeStartRecognitionGlobal(); console.log('🎤 recognitionGlobal: intento reinicio tras ir a menu principal'); }, 120); } catch(e){}
    });
    btnMenu._listenerAttached = true;
  }

  return { btnCerrar, btnMenu };
}

// --- Control de visibilidad: ocultar en step1 ---
function actualizarVisibilidadBotonesPorStep(stepId) {
  const btnCerrar = document.getElementById('boton-flotante-cerrar-sesion');
  const btnMenu = document.getElementById('boton-flotante-menu-principal');
  if (!btnCerrar || !btnMenu) return;
  const ocultar = (stepId === 'step1' || stepId === 1);
  btnCerrar.style.display = ocultar ? 'none' : 'inline-block';
  btnMenu.style.display = ocultar ? 'none' : 'inline-block';
  console.log(ocultar ? '👀 Botones ocultos (step1)' : '👀 Botones visibles (no-step1)');
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


//globales
function pausarReconocimientoGlobal() {
  recognitionGlobalPaused = true;
  safeStopRecognitionGlobal();
  console.log('🛑 Reconocimiento global pausado');
}

function reactivarReconocimientoGlobal() {
  recognitionGlobalPaused = false;
  safeStartRecognitionGlobal();
  console.log('🎤 Reconocimiento global reactivado');
}



//===============================
// STEPS 
// LOCALES
//===============================
function iniciarReconocimientoLocalStep2() {

  window.iniciarReconocimientoLocalStep2 = iniciarReconocimientoLocalStep2;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
 const lastIndex = event.results.length - 1;
  const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
  const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
  console.log('🎤 [step2] Reconocido (interim):', limpio);

  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;

    if (matchOpcion(limpio, 1, "herramienta en mano")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Herramienta en mano', 'success');
      setModoEscaneo('manual');
    } else if (matchOpcion(limpio, 2, "solicitar herramienta")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
      step5ReturnTarget = 2;
      nextStep(5);
    } else if (matchOpcion(limpio, 3, "ver recursos", "recursos asignados")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
      cargarRecursos().then(() => abrirStepRecursos());
    } else {
      mostrarMensajeKiosco('⚠️ Comando no reconocido en menú principal', 'info');
    }
  };

  recog.onerror = function (e) {
    console.warn('[step2] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
    // Reiniciar si el step sigue activo
    if (getStepActivo() === 'step2') {
      try { recog.start(); } catch (e) { console.warn('[step2] No se pudo reiniciar recog:', e); }
    }
  };

  recog.start();
  window._recogStep2 = recog;
}

function iniciarReconocimientoLocalStep3() {

  window.iniciarReconocimientoLocalStep3 = iniciarReconocimientoLocalStep3;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step3] Reconocido (interim):', limpio);
  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;
   
    if (matchOpcion(limpio, 1, "qr", "escanear")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Escanear QR', 'success');
      activarEscaneoQRregistroRecursos();
    } else if (limpio.includes("cancelar")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Cancelar escaneo', 'success');
      cancelarEscaneoQRregistroRecursos();
    } else if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar manualmente', 'success');
      step5ReturnTarget = 3;
      detenerEscaneoQRregistroRecursos(5);
    } else if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
      mostrarMensajeKiosco('🎤 Comando reconocido: Volver al menú principal', 'success');
      detenerEscaneoQRregistroRecursos(2);
    } else {
      //mostrarMensajeKiosco('⚠️ Comando no reconocido en escaneo QR', 'info');
    }
  };

  recog.onerror = function (e) {
    console.warn('[step3] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
    if (getStepActivo() === 'step3') {
      try { recog.start(); } catch (e) { console.warn('[step3] No se pudo reiniciar recog:', e); }
    }
  };

  recog.start();
  window._recogStep3 = recog;
}

function iniciarReconocimientoLocalStep5() {

  window.iniciarReconocimientoLocalStep5 = iniciarReconocimientoLocalStep5;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step5] Reconocido (interim):', limpio);
  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;
   
    if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "opcion volver")) {
      mostrarMensajeKiosco(step5ReturnTarget === 3 ? '🎤 Volver a "herramienta en mano"' : '🎤 Volver al menú principal', 'success');
      nextStep(step5ReturnTarget);
      return;
    }

    const botonesCat = document.querySelectorAll('#categoria-buttons button');
    for (let i = 0; i < botonesCat.length; i++) {
      const btn = botonesCat[i];
      if (matchOpcion(limpio, i + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
        return;
      }
    }

    mostrarMensajeKiosco('⚠️ Comando no reconocido en selección de categoría', 'info');
  };

  recog.onerror = function (e) {
    console.warn('[step5] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
    if (getStepActivo() === 'step5') {
      try { recog.start(); } catch (e) { console.warn('[step5] No se pudo reiniciar recog:', e); }
    }
  };

  recog.start();
  window._recogStep5 = recog;
}

function iniciarReconocimientoLocalStep6() {

  window.iniciarReconocimientoLocalStep6 = iniciarReconocimientoLocalStep6;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step6] Reconocido (interim):', limpio);
  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;
   
    // Volver
    if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "opcion volver")) {
      mostrarMensajeKiosco('🎤 Volver a categorías', 'success');
      nextStep(5);
      return;
    }

    // Paginación
    const matchPagina = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
    if (matchPagina && Array.isArray(window.subcategoriasActuales)) {
      const token = matchPagina[1];
      const numero = numeroDesdeToken(token);
      const total = Math.max(1, Math.ceil(window.subcategoriasActuales.length / 5));
      if (!isNaN(numero) && numero >= 1 && numero <= total) {
        renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
        return;
      }
      mostrarMensajeKiosco('Número de página inválido', 'warning');
      return;
    }

    // Botones
    const botonesSub = document.querySelectorAll('#subcategoria-buttons button');
    for (let i = 0; i < botonesSub.length; i++) {
      const btn = botonesSub[i];
      if (matchOpcion(limpio, i + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
        return;
      }
    }

    mostrarMensajeKiosco('⚠️ Comando no reconocido en subcategorías', 'info');
  };

  recog.onerror = function (e) {
    console.warn('[step6] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
    if (getStepActivo() === 'step6') {
      try { recog.start(); } catch (e) { console.warn('[step6] No se pudo reiniciar recog:', e); }
    }
  };

  recog.start();
  window._recogStep6 = recog;
}

function iniciarReconocimientoLocalStep7() {

  window.iniciarReconocimientoLocalStep7 = iniciarReconocimientoLocalStep7;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step7] Reconocido (interim):', limpio);
  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;
   
    // Volver
    if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
      mostrarMensajeKiosco('🎤 Volver a subcategorías', 'success');
      nextStep(6);
      return;
    }

    // Paginación
    const matchPagina = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
    if (matchPagina && Array.isArray(window.recursosActuales)) {
      const token = matchPagina[1];
      const numero = numeroDesdeToken(token);
      const total = Math.max(1, Math.ceil(window.recursosActuales.length / 5));
      if (!isNaN(numero) && numero >= 1 && numero <= total) {
        renderRecursosPaginados(window.recursosActuales, numero);
        return;
      }
      mostrarMensajeKiosco('Número de página inválido', 'warning');
      return;
    }

    // Botones
    const botonesRec = document.querySelectorAll('#recurso-buttons button');
    botonesRec.forEach((btn, index) => {
      if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
      }
    });
  };

  recog.onerror = function (e) {
    console.warn('[step7] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
    if (getStepActivo() === 'step7') {
      try { recog.start(); } catch (e) { console.warn('[step7] No se pudo reiniciar recog:', e); }
    }
  };

  recog.start();
  window._recogStep7 = recog;
}

function iniciarReconocimientoLocalStep9() {

  window.iniciarReconocimientoLocalStep9 = iniciarReconocimientoLocalStep9;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function (event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step9] Reconocido (interim):', limpio);
  
    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;
   
    if (/\b(confirmar|confirm)\b/.test(limpio)) {
      const btn = document.getElementById('btnConfirmarDevolucion');
      if (btn && !btn.disabled) {
        try { btn.click(); } catch (e) { confirmarDevolucionQRActual(); }
      } else {
        if (!window.modalKioscoActivo) {
          mostrarMensajeKiosco('Aún no se detectó un QR válido para confirmar', 'warning');
        } else {
          console.log('⏸️ Modal ya activo, no se vuelve a mostrar');
        }
      }
      return;
    }


    if (esComandoVolver(limpio) || /\b(volver|regresar|atrás)\b/.test(limpio)) {
      mostrarMensajeKiosco('🎤 Volver a recursos asignados', 'success');
      volverARecursosAsignadosDesdeDevolucionQR();
      return;
    }

    mostrarMensajeKiosco('⚠️ Comando no reconocido. Decí "confirmar" o "volver".', 'info');
  };

  recog.onerror = function (e) {
    console.warn('[step9] Error en reconocimiento local:', e);
  };

  recog.onend = function () {
  if (getStepActivo() === 'step9' && !window._recogStep9PausadoPorModal) {
    try { recog.start(); } catch (e) { console.warn('[step9] No se pudo reiniciar recog:', e); }
  } else {
    console.log('[step9] onend ignorado por pausa modal');
  }
};


  recog.start();
  window._recogStep9 = recog;
}

function pausarReconocimientoLocalStep9PorModal() {
  window._recogStep9PausadoPorModal = true;
  if (window._recogStep9) {
    try {
      window._recogStep9.onresult = null;
      window._recogStep9.onerror = null;
      window._recogStep9.onend = null;
      window._recogStep9.stop?.();
      window._recogStep9 = null;
      console.log('🛑 Reconocimiento local step9 pausado por modal');
    } catch (e) {
      console.warn('⚠️ No se pudo pausar recog step9', e);
    }
  }
}

function reactivarReconocimientoLocalStep9SiAplica() {
  window._recogStep9PausadoPorModal = false;
  if (getStepActivo() === 'step9') {
    iniciarReconocimientoLocalStep9();
    console.log('🎤 Reconocimiento local step9 reactivado tras cierre de modal');
  }
}

function iniciarReconocimientoLocalStep10() {

  if (window._recogStep10 && typeof window._recogStep10.stop === 'function') {
  try { window._recogStep10.stop(); } catch (e) {}
  window._recogStep10 = null;
}

  window.iniciarReconocimientoLocalStep10 = iniciarReconocimientoLocalStep10;

  if (!('webkitSpeechRecognition' in window)) return;

  const recog = new webkitSpeechRecognition();
  recog.lang = 'es-ES';
  recog.continuous = true;
  recog.interimResults = true;

  recog.onresult = function(event) {
    const lastIndex = event.results.length - 1;
    const texto = (event.results[lastIndex][0]?.transcript || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log('🎤 [step10] Reconocido (interim):', limpio);

    // comandos globales de menu principal y cerrar sesion
    if (procesarComandosGlobalesDesdeLocal(limpio)) return;


    if (esComandoVolver(limpio) || /\b(volver)\b/.test(limpio)) {
      recognitionGlobalPaused = false;
      safeStartRecognitionGlobal();
      nextStep(2);
      getRenderer('mostrarMensajeKiosco')('Volviendo al menú principal', 'info');
      return;
    }

    const tabCambio = matchTabCambio(limpio);
    if (tabCambio === 'epp') {
      document.getElementById('tab-epp-step')?.click();
      getRenderer('mostrarMensajeKiosco')('✅ Mostrando EPP', 'success');
      return;
    }
    if (tabCambio === 'herramientas') {
      document.getElementById('tab-herramientas-step')?.click();
      getRenderer('mostrarMensajeKiosco')('✅ Mostrando Herramientas', 'success');
      return;
    }

    const match = limpio.match(/opcion\s*(\d{1,2})/i);
    if (match) {
      const index = parseInt(match[1], 10);
      if (!isNaN(index)) {
        confirmarDevolucionPorVozStep10(index);
      } else {
        getRenderer('mostrarMensajeKiosco')('Opción no reconocida', 'warning');
      }
      return;
    }

    const mp = limpio.match(/^pagina\s*(\d{1,2})$/i);
    if (mp) {
      const numero = parseInt(mp[1], 10);
      if (!isNaN(numero)) {
        handleStep10Pagina(numero);
      } else {
        getRenderer('mostrarMensajeKiosco')('Número de página no reconocido', 'warning');
      }
      return;
    }

    console.log('⚠️ [step10] Comando no reconocido:', limpio);
  };

recog.onerror = function(e) {
  console.warn('[step10] Error en reconocimiento local:', e);
  if (e.error === 'aborted') {
    console.log('⏸️ Abortado, pero se reinicia recogedor local step10');
  }

  if (getStepActivo() !== 'step10') return;

  try {
    recog.stop();
  } catch (err) {}

  setTimeout(() => {
    if (!window._recogStep10) {
      console.log('🔁 Reiniciando recogedor local step10 tras error');
      iniciarReconocimientoLocalStep10();
    } else {
      console.log('⏸️ recogedor step10 ya activo, no se reinicia');
    }
  }, 300);
};



  try {
    safeStopRecognitionGlobal();
    recog.start();
    console.log('🎤 reconocimiento local (step10) iniciado');
  } catch (e) {
    console.warn('step10 recog.start falló', e);
  }

  window._recogStep10 = recog;
}

function reiniciarReconocimientoLocalDelStepActual() {
  const step = getStepActivo();
  const fnName = `iniciarReconocimientoLocal${step.charAt(4).toUpperCase()}${step.slice(5)}`;
  const fn = window[fnName];
  if (typeof fn === 'function') {
    try {
      fn();
      console.log(`🎤 recogedor local reiniciado en ${step}`);
    } catch (e) {
      console.warn(`⚠️ Error al reiniciar recogedor local en ${step}`, e);
    }
  } else {
    console.warn(`⚠️ No se encontró función para reiniciar recogedor en ${step}`);
  }
}


function procesarComandosGlobalesDesdeLocal(limpio) {
  if (!limpio || typeof limpio !== 'string') return false;

  const texto = normalizarTexto(limpio).trim();
  const step = getStepActivo();
  const modalCerrar = document.getElementById('modalCerrarSesion');

  // ✅ Solo si el modal está visible, procesamos “aceptar” y “cancelar”
  if (modalCerrar?.classList.contains('show')) {
    if (texto === 'aceptar') {
      console.log('🟢 comando global: aceptar modal cerrar sesión');
      try { bootstrap.Modal.getInstance(modalCerrar)?.hide(); } catch (e) {}
      ejecutarCerrarSesion();
      return true;
    }

    if (texto === 'cancelar') {
      console.log('🔴 comando global: cancelar modal cerrar sesión');
      try { bootstrap.Modal.getInstance(modalCerrar)?.hide(); } catch (e) {}
      recognitionGlobalPaused = false;

      if (esStepConReconocimientoLocal(step)) {
        // ✅ Reiniciar recogedor local del step actual
        try {
          const fnName = `iniciarReconocimientoLocal${step.charAt(4).toUpperCase()}${step.slice(5)}`;
          const fn = window[fnName];
          if (typeof fn === 'function') {
            fn();
            console.log(`🎤 recogedor local reiniciado tras cancelar en ${step}`);
          } else {
            console.warn(`⚠️ No se encontró función para reiniciar recogedor en ${step}`);
          }
        } catch (e) {
          console.warn(`⚠️ Error al reiniciar recogedor local en ${step}`, e);
        }
      } else {
        safeStartRecognitionGlobal();
      }

      return true;
    }
  }

  // 🔒 Bloquear "cerrar sesión" en step1
  if (step !== 'step1' && /\b(cerrar sesión|cerrar sesion)\b/.test(texto)) {
    mostrarModalCerrarSesion();
    return true;
  }

  // 🔒 Bloquear "menu principal" en step1 y step2
  if (step !== 'step1' && step !== 'step2' && /\b(menu principal)\b/.test(texto)) {
    recognitionGlobalPaused = false;
    safeStartRecognitionGlobal();
    nextStep(2);
    getRenderer('mostrarMensajeKiosco')('Volviendo al menú principal', 'info');
    return true;
  }

  return false;
}



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

function detenerTodosLosRecogedoresLocales() {
  const recogKeys = [
    '_recogStep2',
    '_recogStep3',
    '_recogStep5',
    '_recogStep6',
    '_recogStep7',
    '_recogStep9',
    '_recogStep10',
    '_recogCerrarSesion'

  ];
  recogKeys.forEach(key => {
    try {
      if (window[key]) {
        window[key].stop();
        console.log(`🛑 ${key} detenido`);
      }
    } catch (e) {
      console.warn(`⚠️ No se pudo detener ${key}`, e);
    }
  });
}

function esStepConReconocimientoLocal(step) {
  return ['step2','step3','step5','step6','step7','step9','step10'].includes(step);
}



// Export CommonJS para tests
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { parsearClavePorVoz };
}


function procesarComandoVoz(rawTexto) {
  try {
    if (!rawTexto && typeof rawTexto !== 'string') return;
    const texto = String(rawTexto || '').toLowerCase().trim();
    const limpio = normalizarTexto(texto).replace(/\b(\w+)\s+\1\b/g, '$1');
    console.log("👉 Reconocido (raw):", rawTexto, "| normalizado:", limpio, "| Step activo:", getStepActivo());

    // Si el kiosco está mostrando un modal kiosco forzado, priorizamos su cierre por voz
    if (window.modalKioscoActivo) {
      if (matchCerrar(limpio) || limpio.includes('entendido') || limpio.includes('cerrar mensaje')) {
        console.log('🎤 Cierre por voz del modal kiosco:', limpio);
        cerrarModalKiosco();
      } else {
        console.log('🚫 Comando bloqueado por modal kiosco activo:', limpio);
      }
      return;
    }

    
    // Si hay modal de error QR visible priorizamos su cierre
    const modalErrorQR = document.getElementById('modalErrorQR');
    const modalErrorVisible = modalErrorQR && modalErrorQR.classList.contains('show');
    if (modalErrorVisible) {
      if (/\b(cerrar|cerrar error|cerrar modal|cerrar qr)\b/.test(limpio)) {
        console.log('🎤 Comando de voz: cerrar modal error QR');
        cerrarModalErrorQR();
      } else {
        console.log('⚠️ Modal de error QR abierto, comando ignorado:', limpio);
      }
      return;
    }

    const step = getStepActivo();

const modalRegistro = document.getElementById('modalResultadoRegistro');
const modalRegistroVisible = modalRegistro?.classList.contains('show');
const esperandoAceptar = modalRegistro?.classList.contains('esperando-aceptar');

if (modalRegistroVisible && esperandoAceptar) {
  if (limpio.includes('aceptar')) {
    console.log('🎤 Comando de voz: aceptar modal de registro');
    document.getElementById('btnAceptarResultadoRegistro')?.click();
    return;
  } else {
    console.log('⚠️ Modal de registro abierto, comando ignorado:', limpio);
    return;
  }
}


    // boton permanente de cerrar sesión
    if (step !== 'step1') {
        if (/\b(cerrar sesión|cerrar sesion)\b/.test(limpio)) {
          console.log('🔐 Comando de voz detectado: cerrar sesión');
          mostrarModalCerrarSesion(); // tu función actual para abrir el modal
          return;
        }
    }

   /*  // boton permanente de menu principal
    if (step !== 'step1' && step !== 'step2') {
        if (/\b(menu principal)\b/.test(limpio)) {
        recognitionGlobalPaused = false;
        safeStartRecognitionGlobal();
        nextStep(2);
        getRenderer('mostrarMensajeKiosco')('Volviendo al menú principal', 'info');
        return;
      }
    }
*/
    // Si estamos en step10 (pantalla de recursos asignados) manejamos comandos allí
   /* if (step === 'step10') {

      if (esComandoVolver(limpio) || /\b(volver)\b/.test(limpio)) {
      recognitionGlobalPaused = false;
      safeStartRecognitionGlobal();
      nextStep(2);
      getRenderer('mostrarMensajeKiosco')('Volviendo al menú principal', 'info');
      return;
    }


      // cambio de tab por voz
      const tabPorStep = matchTabCambio(limpio);
      if (tabPorStep === 'epp') {
        document.getElementById('tab-epp-step')?.click();
        getRenderer('mostrarMensajeKiosco')('✅ Mostrando EPP', 'success');
        return;
      }
      if (tabPorStep === 'herramientas') {
        document.getElementById('tab-herramientas-step')?.click();
        getRenderer('mostrarMensajeKiosco')('✅ Mostrando Herramientas', 'success');
        return;
      }

      // Devolución por voz: "opcion N"
      const mOp = limpio.match(/opcion\s*(\d{1,2})/i);
      if (mOp) {
        const index = parseInt(mOp[1], 10);
        if (!isNaN(index)) {
          confirmarDevolucionPorVozStep10(index);
        } else {
          getRenderer('mostrarMensajeKiosco')('Opción no reconocida', 'warning');
        }
        return;
      }

      // Paginación "pagina N"
      const mp = limpio.match(/^pagina\s*(\d{1,2})$/i);
      if (mp) {
        const numero = parseInt(mp[1], 10);
        if (!isNaN(numero)) handleStep10Pagina(numero);
        else getRenderer('mostrarMensajeKiosco')('Número de página no reconocido', 'warning');
        return;
      }

      console.log('⚠️ step10: comando no reconocido', limpio);
      return;
    }*/

    // Comandos globales cuando no estamos bloqueados por modales ni step10
    if (recognitionGlobalPaused) {
      console.log('⚠️ Reconocimiento global pausado, ignorando comando:', limpio);
      return;
    }

    

    // === Step1: Login ===
    if (step === 'step1') {

      if (/\b(iniciar sesión con qr|iniciar sesion con qr|qr)\b/.test(limpio)) {
    console.log('🎤 Comando de voz: iniciar sesión con QR');
    activarEscaneoQRLogin(); // ejecuta la función del botón
    return;
  }

  // 🧠 Intento de ingreso por voz usando frase activadora
  const clave = parsearClavePorVoz(rawTexto);
  if (clave) {
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = clave;
      claveInput.focus();
      getRenderer('mostrarMensajeKiosco')(`🎤 Clave reconocida: ${clave}`, 'success');
      // Opcional: avanzar automáticamente
      // nextStep();
    }
    return;
  }

  // 🧹 Comando para borrar el campo clave
  if (/\b(borrar|borrar clave|borrar todo)\b/.test(limpio)) {
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = '';
      claveInput.focus();
      getRenderer('mostrarMensajeKiosco')('🧹 clave borrada por voz', 'info');
    }
    return;
  }

  // ▶️ Comando para continuar login manualmente
  if (/\b(continuar)\b/.test(limpio)) {
    console.log('🎤 Comando de voz: Continuar login');
    identificarTrabajador(); // tu función actual para validar y avanzar
    return;
  }

  // 🧩 Fallback: si se dictó solo números sin frase activadora
  if (!/^[a-zA-Z]/.test(limpio) && /^\d/.test(limpio)) {
    // Si querés permitir ingreso de clave por bloques sin activadora
    const claveInput = document.getElementById('clave');
    if (claveInput) {
      claveInput.value = limpio.replace(/\s+/g, '');
      claveInput.focus();
      getRenderer('mostrarMensajeKiosco')('🎤 clave dictado por voz', 'info');
    }
    return;
  }
}


/*  // === Step2: Menú principal y navegación ===
    if (step === 'step2') {
      // normalizar repeticiones
      const textoSimple = limpio.replace(/\b(\w+)\s+\1\b/g, '$1');

      // Si modalRecursos estuviera abierto (en el viejo enfoque) no lo procesamos aquí,
      // pero ahora preferimos abrir step10 desde menú con la opción correspondiente.
      if (matchOpcion(textoSimple, 1, "herramienta en mano")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Herramienta en mano', 'success');
        setModoEscaneo('manual');
        return;
      }

      if (matchOpcion(textoSimple, 2, "solicitar herramienta", "quiero solicitar", "pedir herramienta")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
        step5ReturnTarget = 2;
        window.nextStep(5);
        return;
      }

      if (matchOpcion(textoSimple, 3, "ver recursos", "recursos asignados", "mostrar recursos")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
        window.cargarRecursos().then(() => abrirStepRecursos());
        return;
      }

      if (matchOpcion(textoSimple, 4, "volver", "inicio", "regresar", "atrás", "cerrar")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver al inicio', 'success');
        volverAInicio();
        return;
      }

      // paginación por tab si corresponde (comandos "pagina EPP 2", etc.)
      const matchPaginaEPP = textoSimple.match(/^pagina\s*epp\s*(\d{1,2})$/i);
      const matchPaginaHerr = textoSimple.match(/^pagina\s*herramientas\s*(\d{1,2})$/i);
      if (matchPaginaEPP) {
        const numero = parseInt(matchPaginaEPP[1], 10);
        const total = Math.ceil((window.recursosEPP?.length || 0) / 5);
        if (numero >= 1 && numero <= total) renderTablaRecursos('tablaEPP', window.recursosEPP, numero, 'paginadorEPP');
        else window.mostrarMensajeKiosco('Número de página inválido para EPP', 'warning');
        return;
      }
      if (matchPaginaHerr) {
        const numero = parseInt(matchPaginaHerr[1], 10);
        const total = Math.ceil((window.recursosHerramientas?.length || 0) / 5);
        if (numero >= 1 && numero <= total) renderTablaRecursos('tablaHerramientas', window.recursosHerramientas, numero, 'paginadorHerramientas');
        else window.mostrarMensajeKiosco('Número de página inválido para herramientas', 'warning');
        return;
      }

      console.log("⚠️ Step2: No se reconoció comando válido");
      return;
    }

    // === Step3: Escaneo QR ===
    if (step === 'step3') {
      if (matchOpcion(limpio, 1, "qr", "escanear")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Escanear QR', 'success');
        activarEscaneoQRregistroRecursos();
        return;
      }
      if (limpio.includes("cancelar")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Cancelar escaneo', 'success');
        cancelarEscaneoQRregistroRecursos();
        return;
      }
      if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar manualmente', 'success');
        step5ReturnTarget = 3;
        detenerEscaneoQRregistroRecursos(5);
        return;
      }
      if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver al menú principal', 'success');
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
        window.mostrarMensajeKiosco(step5ReturnTarget === 3 ? '🎤 Comando reconocido: Volver a "Tengo la herramienta en mano"' : '🎤 Comando reconocido: Volver al menú principal', 'success');
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
          const totalPaginas = Math.max(1, Math.ceil(window.subcategoriasActuales.length / 5));
          if (numero > totalPaginas) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
          renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
          return;
        }
      }
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "opcion volver")) { window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a categorías', 'success'); window.nextStep(5); return; }
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
          const totalPaginas = Math.max(1, Math.ceil(window.recursosActuales.length / 5));
          if (numero > totalPaginas) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
          renderRecursosPaginados(window.recursosActuales, numero);
          return;
        }
      }
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) { window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a subcategorías', 'success'); window.nextStep(6); return; }
      const botonesRec = document.querySelectorAll('#recurso-buttons button');
      botonesRec.forEach((btn, index) => { try { if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) { btn.click(); } } catch (e) { console.warn('Error al procesar botón recurso', e); } });
      console.log("⚠️ Step7: Procesada entrada (si hubo coincidencias)");
      return;
    }
*/

    if (step === 'step8') {
      const matchPaginaSer = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
      if (matchPaginaSer && Array.isArray(window.seriesActuales)) {
        const token = matchPaginaSer[1];
        const numero = numeroDesdeToken(token);
        if (!isNaN(numero) && numero >= 1) {
          const totalPaginas = Math.max(1, Math.ceil(window.seriesActuales.length / 5));
          if (numero > totalPaginas) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
          renderSeriesPaginadas(window.seriesActuales, numero);
          return;
        }
      }
      if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) { window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a recursos', 'success'); window.nextStep(7); return; }
      const botonesSeries = document.querySelectorAll('#serie-buttons button');
      botonesSeries.forEach((btn, index) => { try { if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) { btn.click(); } } catch (e) { console.warn('Error al procesar botón serie', e); } });
      console.log("⚠️ Step8: Procesada entrada (si hubo coincidencias)");
      return;
    }

    // === Step9: Devolución por QR ===
   /* if (step === 'step9') {
      if (/\b(confirmar|confirm|aceptar|accept)\b/.test(limpio)) {
        const btn = document.getElementById('btnConfirmarDevolucion');
        if (btn && !btn.disabled) { try { btn.click(); } catch(e) { confirmarDevolucionQRActual(); } return; }
        getRenderer('mostrarMensajeKiosco')('Aún no se detectó un QR válido para confirmar', 'warning');
        return;
      }
      if (esComandoVolver(limpio) || /\b(volver)\b/.test(limpio)) { volverARecursosAsignadosDesdeDevolucionQR(); return; }
      console.warn('⚠️ step9: comando no reconocido en devoluciones:', limpio);
      getRenderer('mostrarMensajeKiosco')('No se reconoció el comando. Decí "confirmar" o "volver".', 'info');
      return;
    }*/

    // === Paginación y navegación globales (fallback) ===
    const matchPaginaAny = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
    if (matchPaginaAny) {
      const token = matchPaginaAny[1];
      const numero = numeroDesdeToken(token);
      if (isNaN(numero) || numero < 1) { window.mostrarMensajeKiosco('Número de página no reconocido', 'warning'); return; }

     /* if (step === 'step6' && Array.isArray(window.subcategoriasActuales)) {
        const total = Math.max(1, Math.ceil(window.subcategoriasActuales.length / 5));
        if (numero > total) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
        renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
        return;
      }
      if (step === 'step7' && Array.isArray(window.recursosActuales)) {
        const total = Math.max(1, Math.ceil(window.recursosActuales.length / 5));
        if (numero > total) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
        renderRecursosPaginados(window.recursosActuales, numero);
        return;
      }*/
      if (step === 'step8' && Array.isArray(window.seriesActuales)) {
        const total = Math.max(1, Math.ceil(window.seriesActuales.length / 5));
        if (numero > total) { window.mostrarMensajeKiosco('Número de página inválido', 'warning'); return; }
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
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Cerrar recursos asignados', 'success');
        return;
      }
    }

    console.log("⚠️ procesarComandoVoz: comando no reconocido en ningún step");
  } catch (err) {
    console.warn('procesarComandoVoz: excepción', err);
  }
}

/*
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    parsearclavePorBloques
  });
}*/
