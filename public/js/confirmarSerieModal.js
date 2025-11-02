function confirmarSerieModal(serieId, serieTexto = '', options = {}) {
  const registrar = options.registrarSerie || window.registrarSerie;
  const mostrarMensaje = options.mostrarMensajeKiosco || window.mostrarMensajeKiosco;

  const body = document.getElementById('modalConfirmarSerieBody');
  if (body) body.textContent = `¿Confirmás que querés solicitar el recurso "${serieTexto}"?`;

  const modalEl = document.getElementById('modalConfirmarSerie');
  if (!modalEl) {
    if (confirm(`¿Confirmás que querés solicitar el recurso "${serieTexto}"?`)) {
      if (typeof registrar === 'function') registrar(serieId);
    }
    return;
  }

  const modal = new bootstrap.Modal(modalEl);
  const aceptarBtn = document.getElementById('btnAceptarSerie');
  const cancelarBtn = document.getElementById('btnCancelarSerie');

  function cleanup() {
    try {
      const existing = modalEl._recogInstance;
      if (existing && typeof existing.stop === 'function') existing.stop();
    } catch (e) {}
    modalEl._recogInstance = null;
  }

  function onAceptar() {
    modal.hide();
    cleanup();
    if (typeof registrar === 'function') registrar(serieId);
  }

  function onCancelar() {
    modal.hide();
    cleanup();
    if (typeof mostrarMensaje === 'function') mostrarMensaje('Solicitud cancelada.', 'info');
  }

  // Evitar duplicados y enlazar handlers al evento click de los botones
  try { if (aceptarBtn) { aceptarBtn.removeEventListener('click', onAceptar); aceptarBtn.addEventListener('click', onAceptar); } } catch (e) {}
  try { if (cancelarBtn) { cancelarBtn.removeEventListener('click', onCancelar); cancelarBtn.addEventListener('click', onCancelar); } } catch (e) {}

  // Pausar recognition global antes de abortarlo para evitar reinicios automáticos
  try {
    recognitionGlobalPaused = true;
    if (recognitionGlobal && typeof recognitionGlobal.abort === 'function') {
      recognitionGlobal.abort();
      console.log('🛑 Recognition global abortado y marcado como pausado');
    }
  } catch (e) { console.warn('⚠️ No se pudo abortar recognitionGlobal:', e); }

  // Iniciar reconocimiento local y, en lugar de llamar directamente a las funciones,
  // invocar el click de los botones para que los spies en tests sean ejecutados
  try {
    if ('webkitSpeechRecognition' in window) {
      const recog = new webkitSpeechRecognition();
      recog.lang = 'es-ES';
      recog.continuous = true;
      recog.interimResults = false;

      recog.onresult = function (event) {
        const texto = (event.results && event.results[0] && event.results[0][0] && event.results[0][0].transcript)
          ? event.results[0][0].transcript.toLowerCase().trim()
          : '';
        console.log('🎤 Texto reconocido (modal):', texto);

        if (texto.includes('aceptar')) {
          // disparar click para respetar handlers y spies
          try { aceptarBtn?.click(); } catch (e) { onAceptar(); }
          try { recog.stop(); } catch (e) {}
        } else if (texto.includes('cancelar')) {
          try { cancelarBtn?.click(); } catch (e) { onCancelar(); }
          try { recog.stop(); } catch (e) {}
        }
      };

      recog.onerror = function (e) {
        if (e && e.error === 'aborted') {
          console.log('ℹ️ Reconocimiento modal abortado (intencional/conflicto)');
          return;
        }
        console.warn('Reconocimiento de voz modal falló', e);
      };

      modalEl._recogInstance = recog;
      try { recog.start(); } catch (e) { console.warn('No se pudo iniciar reconocimiento del modal:', e); }
    }
  } catch (e) {
    console.warn('No se pudo crear reconocimiento del modal', e);
  }

  const onHidden = () => {
    modalEl.removeEventListener('hidden.bs.modal', onHidden);
    cleanup();
    recognitionGlobalPaused = false;
    try {
      if (recognitionGlobal && typeof recognitionGlobal.start === 'function') {
        console.log('🎤 Reiniciando recognitionGlobal después de modal');
        recognitionGlobal.start();
      }
    } catch (e) { console.warn('No se pudo reiniciar recognitionGlobal:', e); }
  };
  modalEl.addEventListener('hidden.bs.modal', onHidden);

  modal.show();
}


module.exports = { confirmarSerieModal };
