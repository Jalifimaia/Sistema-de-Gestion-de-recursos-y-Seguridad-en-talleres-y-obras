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


let recognitionGlobalWasRunning = false;

function safeStopRecognitionGlobal() {
  try {
    if (recognitionGlobal && recognitionRunning) {
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
    } catch (err) {
      // Ignorar error si el estado ya está started o si es InvalidStateError
      const isAlreadyStarted = err && (err.name === 'InvalidStateError' || /recognition has already started/i.test(err.message || ''));
      if (isAlreadyStarted) {
        console.log('safeStartRecognitionGlobal: start ignorado, reconocimiento ya iniciado');
        recognitionRunning = true;
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




let scanner;
let isScanning = false; // 👈 flag de estado

// helper simple para detectar "volver" en variantes
function esComandoVolver(limpio) {
  if (!limpio) return false;
  const s = normalizarTexto(String(limpio)).trim();

  // coincidencias exactas o palabra dentro de frase (más tolerante)
  if (/^(volver)$/.test(s)) return true;
  if (/\b(volver)\b/.test(s)) return true;

  // tolerancia a prefijos/partículas comunes: "a volver", "en volver", "ir a volver", "voy a volver"
  if (/(?:\b(?:a|en|ir a|voy a|por favor)\b).*?\b(volver)\b/.test(s)) return true;

  // catch common ASR partials like "volver a", "volver por", "vuelve" etc
  //if (/\b(volver)\b/.test(s)) return true;

  return false;
}



function mostrarMensajeKiosco(texto, tipo = 'info') {
  // Asegurar contenedor, si no existe lo creamos (tests o entornos headless)
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    document.body.appendChild(container);
  }

  // Crear elemento toast
  const toastEl = document.createElement('div');
  toastEl.className = 'toast align-items-center border-0 mb-2';
  toastEl.setAttribute('role', 'alert');

  // Colores según tipo
  if (tipo === 'success') {
    toastEl.classList.add('text-bg-success');
  } else if (tipo === 'danger') {
    toastEl.classList.add('text-bg-danger');
  } else if (tipo === 'warning') {
    toastEl.classList.add('text-bg-warning', 'text-dark');
  } else {
    toastEl.classList.add('text-bg-info');
  }

  // Contenido del toast
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${texto}</div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  // Agregar al contenedor y mostrar
  container.appendChild(toastEl);
  try {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  } catch (e) {
    // En entornos de test sin bootstrap, simplemente mantener el elemento y removerlo luego
    setTimeout(() => { if (toastEl && toastEl.remove) toastEl.remove(); }, 4000);
  }
}


function nextStep(n) {
  // Cerrar modal de recursos si está abierto (con guardas)
  const modalEl = document.getElementById('modalRecursos');
  if (modalEl) {
    const modalInstance = (bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function')
      ? bootstrap.Modal.getInstance(modalEl)
      : null;
    if (modalInstance && typeof modalInstance.hide === 'function') {
      modalInstance.hide();
    }
  }

  // Detener escaneo QR si no estamos en step3
  if (n !== 3) {
    try { detenerEscaneoQR(); } catch (e) { /* no bloquear flujo por errores en stop */ }
  }

  // Ocultar todos los steps
  document.querySelectorAll('.step').forEach(s => {
    s.classList.remove('active');
    s.classList.add('d-none');
  });

  // Activar el step deseado
  const stepEl = document.getElementById('step' + n);
  if (stepEl && stepEl.classList) {
    stepEl.classList.remove('d-none');
    stepEl.classList.add('active');
  } else {
    console.warn('nextStep: step element not found:', 'step' + n);
  }

  // Acciones específicas por step
  if (n === 2) cargarMenuPrincipal();
  if (n === 5) window.cargarCategorias();
}


function identificarTrabajador() {
  const dni = document.getElementById('dni').value;
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
          document.getElementById('saludo-trabajador').textContent = `Hola ${res.usuario.name}`;
        } else {
      getRenderer('mostrarMensajeKiosco')(res.message || 'Error al identificar al trabajador', 'danger');
        }
        resolve(res);
      } catch (e) {
      getRenderer('mostrarMensajeKiosco')('Error al identificar al trabajador', 'danger');
        resolve({ success: false, error: e });
      }
    };

    xhr.send('dni=' + encodeURIComponent(dni));
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

      categorias.forEach((cat, index) => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-dark btn-lg d-flex justify-content-between align-items-center m-2';
        btn.dataset.categoriaId = cat.id;
        btn.onclick = () => seleccionarCategoria(cat.id);

        btn.innerHTML = `
          <span class="badge-opcion">Opción ${index + 1}</span>
          <span class="flex-grow-1 text-start">${cat.nombre_categoria}</span>
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
    btn.className = 'btn btn-outline-danger btn-lg d-flex justify-content-between align-items-center mt-2';
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
  const tabla = document.getElementById(tablaId);
  const paginador = document.getElementById(paginadorId);
  if (!tabla || !paginador) return;

  const porPagina = 5;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  tabla.innerHTML = '';

  if (visibles.length === 0) {
    tabla.innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
    paginador.innerHTML = '';
    return;
  }

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');

    btn.dataset.recurso = r.recurso || '';
    btn.dataset.serie = r.serie || '';

    btn.className = 'btn btn-sm btn-outline-danger';
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

  paginador.innerHTML = '';
  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement('button');
    btn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    btn.textContent = i;
    btn.onclick = () => getRenderer('renderTablaRecursos')(tablaId, recursos, i, paginadorId);
    paginador.appendChild(btn);
  }

  if (tablaId === 'tablaEPP') {
    window.paginaEPPActual = pagina;
  }
  if (tablaId === 'tablaHerramientas') {
    window.paginaHerramientasActual = pagina;
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
  const eppActivo = document.getElementById('tab-epp')?.getAttribute('aria-selected') === 'true';
  const herrActivo = document.getElementById('tab-herramientas')?.getAttribute('aria-selected') === 'true';
  console.log('🔍 Tabs activo -> EPP:', eppActivo, 'Herr:', herrActivo);

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
    getRenderer('mostrarMensajeKiosco')(`No se encontró opción ${index}`, 'warning');
    return;
  }

  const detalleId = btn.dataset.detalleId;
  const serie = btn.dataset.serie || ''; // <-- corregido: obtener serie del botón
  console.log(`➡️ confirmarDevolucionPorVoz: botón encontrado, detalleId=${detalleId}, serie=${serie}`);

  // Abrir modal de confirmación (marcamos que la apertura vino por voz)
  window._modalConfirmedByVoice = true;
  safeStopRecognitionGlobal(); // pausamos global antes de abrir modal de confirmación
  console.log('🛑 confirmarDevolucionPorVoz: recognition global pausado, mostrando modal confirmación');

  // Mostrar el paso de devolución: pasamos la serie desde el botón
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



// === Módulo: Devolución por QR ===

let serieEsperada = '';
let detalleIdActual = null;

function mostrarStepDevolucionQR(serie, detalleId) {

  safeStopRecognitionGlobal(); // 🔧 esto es clave

  serieEsperada = serie;
  detalleIdActual = detalleId;
  window.modoActual = 'devolucion';

  document.getElementById('serieEsperadaQR').textContent = serie;
  document.getElementById('qrFeedback').textContent = '';
  document.getElementById('btnConfirmarDevolucion').disabled = true;

  nextStep(9); // activa el step visualmente

  // Espera doble: render + layout
  requestAnimationFrame(() => {
    setTimeout(() => {
      const qrContainer = document.getElementById('qr-reader-devolucion');
      const bounds = qrContainer?.getBoundingClientRect();

      if (!qrContainer || bounds.width < 100 || bounds.height < 100) {
        console.warn('❌ Contenedor QR no tiene dimensiones válidas');
        mostrarMensajeKiosco('No se pudo activar la cámara. Intente nuevamente.', 'danger');
        return;
      }

      activarEscaneoQRDevolucion(); // ya implementado, escanea y llama a registrarPorQR()
    }, 250);
  });


  activarReconocimientoConfirmacionQR();

}

function validarQRDevolucion(qrCode, idUsuario) {
  return fetch('/terminal/validar-qr-devolucion', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ codigo_qr: qrCode, id_usuario: idUsuario })
  })
  .then(res => {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  });
}






function confirmarDevolucionActual() {
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
      mostrarMensajeKiosco('✅ Recurso devuelto correctamente.', 'success');
      nextStep(2); // volver al menú principal o recursos asignados
    } else {
      mostrarMensajeKiosco(data.message || '❌ Error al devolver recurso.', 'danger');
    }
  })
  .catch(err => {
    mostrarMensajeKiosco('❌ Error de red al devolver recurso.', 'danger');
    console.error(err);
  });
}



function volverARecursosAsignados() {
  detenerEscaneoQR();
  nextStep(2); // o el paso donde están los recursos asignados

  if (window._recogQRDevolucion) {
  try { window._recogQRDevolucion.stop(); } catch(e){}
  window._recogQRDevolucion = null;
}

}

// Bind del botón de confirmación
document.getElementById('btnConfirmarDevolucion').addEventListener('click', confirmarDevolucionActual);



function activarEscaneoQRDevolucion() {
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

  try {
    const html5QrCode = new Html5Qrcode(contenedorId);
    html5QrCode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      (decodedText) => {
        validarQRDevolucion(decodedText, idUsuario)
          .then(res => {
            html5QrCode.stop().catch(e => console.warn('Error al detener escáner', e));

            if (res.success && res.coincide) {
              detalleIdActual = res.id_detalle;
              document.getElementById('btnConfirmarDevolucion').disabled = false;
              document.getElementById('qrFeedback').textContent = '';
              mostrarMensajeKiosco('✅ QR válido, listo para confirmar devolución', 'success');
            } else {
              document.getElementById('qrFeedback').textContent = res.message || 'QR no válido';
              mostrarMensajeKiosco(res.message || '❌ QR no válido para devolución', 'warning');
            }
          })
          .catch(err => {
            html5QrCode.stop().catch(e => console.warn('Error al detener escáner', e));
            console.error('Error validando QR:', err);
            mostrarMensajeKiosco('❌ Error al validar QR', 'danger');
          });
      },
      (errorMessage) => {
        console.log("Error de escaneo (devolución):", errorMessage);
      }
    );
  } catch (e) {
    console.error('Error al iniciar escaneo QR de devolución:', e);
    mostrarMensajeKiosco('No se pudo activar la cámara.', 'danger');
  }
}


function onScanSuccess(qrCodeMessage) {
  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarMensajeKiosco('⚠️ Usuario no identificado', 'danger');
    return;
  }

  validarQRDevolucion(qrCodeMessage, idUsuario)
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

function activarReconocimientoConfirmacionQR() {
  if (!('webkitSpeechRecognition' in window)) return;

  safeStopRecognitionGlobal(); // 🔧 detener el global antes de iniciar el local

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
      volverARecursosAsignados();
      recog.stop();
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
  const contenedor = document.getElementById('subcategoria-buttons');
  const paginador = document.getElementById('paginadorSubcategorias');
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

  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = i;
  pagBtn.onclick = () => getRenderer('renderSubcategoriasPaginadas')(subcategorias, i);
    paginador.appendChild(pagBtn);
  }

  window.paginaSubcategoriasActual = pagina;
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
  const contenedor = document.getElementById('recurso-buttons');
  const paginador = document.getElementById('paginadorRecursos');
  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = 5;
  const totalPaginas = Math.ceil(recursos.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = recursos.slice(inicio, inicio + porPagina);

  visibles.forEach((r, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-success btn-lg d-flex justify-content-between align-items-center m-2';
    btn.dataset.recursoId = r.id;

    btn.innerHTML = `
      <span class="badge-opcion">Opción ${index + 1}</span>
      <span class="flex-grow-1 text-start">${r.nombre}</span>
      <span class="badge-disponibles">${r.disponibles} disponibles</span>
    `;
    contenedor.appendChild(btn);
  });

  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = i;
  pagBtn.onclick = () => getRenderer('renderRecursosPaginados')(recursos, i);
    paginador.appendChild(pagBtn);
  }

  window.paginaRecursosActual = pagina;
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
  const contenedor = document.getElementById('serie-buttons');
  const paginador = document.getElementById('paginadorSeries');
  contenedor.innerHTML = '';
  paginador.innerHTML = '';

  const porPagina = 5;
  const totalPaginas = Math.ceil(series.length / porPagina);
  const inicio = (pagina - 1) * porPagina;
  const visibles = series.slice(inicio, inicio + porPagina);

  visibles.forEach((s, index) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-success btn-lg d-flex justify-content-between align-items-center m-2';
    btn.dataset.serieId = s.id;

    const textoSerie = s.nro_serie || s.codigo || `Serie ${s.id}`;
    btn.innerHTML = `
      <span class="badge-opcion">Opción ${index + 1}</span>
      <span class="flex-grow-1 text-start">${textoSerie}</span>
    `;

    contenedor.appendChild(btn);
  });

  for (let i = 1; i <= totalPaginas; i++) {
    const pagBtn = document.createElement('button');
    pagBtn.className = `btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline-secondary'} m-1`;
    pagBtn.textContent = i;
  pagBtn.onclick = () => getRenderer('renderSeriesPaginadas')(series, i);
    paginador.appendChild(pagBtn);
  }

  window.paginaSeriesActual = pagina;
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
      if (existing && typeof existing.stop === 'function') existing.stop();
    } catch (e) {}
    modalEl._recogInstance = null;
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

  try { if (aceptarBtn) { aceptarBtn.removeEventListener('click', onAceptar); aceptarBtn.addEventListener('click', onAceptar); } } catch (e) {}
  try { if (cancelarBtn) { cancelarBtn.removeEventListener('click', onCancelar); cancelarBtn.addEventListener('click', onCancelar); } } catch (e) {}

  try {
    recognitionGlobalPaused = true;
    if (recognitionGlobal && typeof recognitionGlobal.abort === 'function') {
      recognitionGlobal.abort();
      console.log('🛑 Recognition global abortado y marcado como pausado');
    }
  } catch (e) { console.warn('⚠️ No se pudo abortar recognitionGlobal:', e); }

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

        if (modalActionTaken) return;

        if (texto.includes('aceptar')) {
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
    window.botonSerieSeleccionada = null;
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
          getRenderer('mostrarMensajeKiosco')('✅ Recurso asignado correctamente', 'success');
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
  const idUsuario = localStorage.getItem('id_usuario');
  if (!idUsuario) {
    mostrarMensajeKiosco('⚠️ Usuario no identificado', 'danger');
    return;
  }

  // Inicializar escáner QR
  const qrScanner = new Html5Qrcode("qr-reader-devolucion");
  qrScanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    onScanSuccess
  );
});



/*
// Listener global para debug de clicks
document.addEventListener('click', (e) => {
   .log('[DOC CLICK]', e.target, e);
}, { capture: true });
*/

const recursosTabs = document.getElementById('recursosTabs');
if (recursosTabs) {
  recursosTabs.addEventListener('shown.bs.tab', function (event) {
    const tabId = event.target.id;
    if (tabId === 'tab-epp') {
  getRenderer('renderTablaRecursos')('tablaEPP', window.recursosEPP || [], window.paginaEPPActual || 1, 'paginadorEPP');
    } else if (tabId === 'tab-herramientas') {
  getRenderer('renderTablaRecursos')('tablaHerramientas', window.recursosHerramientas || [], window.paginaHerramientasActual || 1, 'paginadorHerramientas');
    }
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





function activarEscaneoQR() {
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
      cleanupScanUI();
      registrarPorQR(qrCodeMessage);
    },
    errorMessage => {
      console.warn('Error de escaneo:', errorMessage);
    }
  ).catch(err => {
    console.error('Error al iniciar escaneo:', err);
  getRenderer('mostrarMensajeKiosco')('No se pudo activar la cámara para escanear QR', 'danger');
    cleanupScanUI();
  });
}


function cancelarEscaneoQR() {
  cleanupScanUI();
}


function registrarPorQR(codigoQR) {
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




function detenerEscaneoQR(next = null) {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (scanner && isScanning) {
    console.log('📴 detenerEscaneoQR: deteniendo escaneo activo');
    scanner.stop().catch(() => {}).then(() => {
      qrContainer.innerHTML = '';
      if (btnCancelar) btnCancelar.classList.add('d-none');
      if (btnEscanear) btnEscanear.classList.remove('d-none');
      if (textoCamara) textoCamara.classList.add('d-none');
      isScanning = false;
      if (next) window.nextStep(next); // 👈 avanzar al paso cuando termina
      console.log('➡️ detenerEscaneoQR: avanzando a step', next);
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


function cleanupScanUI() {
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
      identificarPorQR(qrCodeMessage);
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

function identificarPorQR(codigoQR) {
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


function volverAInicio() {
  // Limpiamos la sesión del trabajador
  localStorage.removeItem('id_usuario');
  console.log('🔙 volverAInicio: sesión limpiada');

  // Volvemos al paso 1
  window.nextStep(1);

  // Opcional: limpiar el campo DNI por si quedó algo escrito
  const dniInput = document.getElementById('dni');
  if (dniInput) dniInput.value = '';
}

// 👇 nuevo: target de retorno para step5
let step5ReturnTarget = 2; // default: menú principal

function setModoEscaneo(modo) {
  const titulo = document.getElementById('titulo-step3');
  if (modo === 'manual') {
    console.log('🔄 setModoEscaneo: modo manual activado');
    titulo.textContent = '📦 Tengo la herramienta en mano';
    detenerEscaneoQR();
    // 👇 si luego vamos a solicitar manualmente (step5), el volver debe regresar acá (step3)
    step5ReturnTarget = 3;
  } else {
    console.log('🔄 setModoEscaneo: modo escaneo QR activado');
    titulo.textContent = '📷 Escanear Recurso';
    activarEscaneoQR();
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
      texto: "📦 Tengo la herramienta en mano",
      accion: () => {
        console.log('📦 opción seleccionada: herramienta en mano');
        setModoEscaneo('manual');
      },
      clase: "btn-outline-success"
    },
    {
      id: 2,
      texto: "🛠️ Quiero solicitar una herramienta",
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
      clase: "btn-outline-primary"
    },
    {
      id: 3,
      texto: "📋 Ver recursos asignados",
      accion: () => {
        console.log('📋 opción seleccionada: ver recursos asignados');
        window.cargarRecursos().then(() => {
          abrirModalRecursos();
        });

      },
      clase: "btn-info"
    },
    {
      id: 4,
      texto: "🔙 Volver",
      accion: () => {
        console.log('🔙 opción seleccionada: volver al inicio');
        volverAInicio();
      },
      clase: "btn-secondary"
    }
  ];

  console.log('📋 cargarMenuPrincipal: opciones generadas', opciones);

  opciones.forEach(op => {
    const btn = document.createElement('button');
    btn.className = `btn ${op.clase} btn-lg d-flex align-items-center justify-content-start m-2 w-100`;
    btn.onclick = op.accion;

    btn.innerHTML = `
      <span class="badge-opcion">Opción ${op.id}</span>
      <span class="ms-2 flex-grow-1 text-start">${op.texto}</span>
    `;

    contenedor.appendChild(btn);
  });
}


// 👇 nuevo: función para botón Volver en step5
function volverDesdeStep5() {
  window.nextStep(step5ReturnTarget);
}


function abrirModalRecursos() {
  const modalEl = document.getElementById('modalRecursos');
  if (!modalEl) return;
  if (!(window.bootstrap && bootstrap.Modal)) {
    console.warn('abrirModalRecursos: bootstrap.Modal no disponible');
    return;
  }

  // Evitar reentradas durante la apertura
  if (modalEl._opening) {
    console.log('abrirModalRecursos: ya en proceso de apertura, ignorando llamada');
    return;
  }
  modalEl._opening = true;

  // Pausar el reconocimiento global de forma segura antes de mostrar el modal
  recognitionGlobalPaused = true;
  try {
    safeStopRecognitionGlobal();
  } catch (e) {
    console.warn('abrirModalRecursos: error al pausar reconocimiento global', e);
  }
  console.log('🛑 Reconocimiento global pausado antes de abrir modal');

  // Obtener o crear instancia y mostrar modal
  const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modalInstance.show();

  // Registrar shown.bs.modal para acciones cuando el modal ya está visible
  modalEl.addEventListener('shown.bs.modal', () => {
    modalEl._opening = false;
    console.log('✅ Modal de recursos completamente visible (shown.bs.modal)');

    // Permitir procesamiento de comandos por voz en el modal: levantamos la pausa
    recognitionGlobalPaused = false;

    // Intentar arrancar el recognition global de forma segura para que procesarComandoVoz
    // reciba comandos mientras el modal está visible
    try {
      safeStartRecognitionGlobal();
      console.log('🎤 safeStartRecognitionGlobal llamado desde shown.bs.modal (modal recursos)');
    } catch (e) {
      console.warn('abrirModalRecursos: no se pudo iniciar recognitionGlobal en shown.bs.modal', e);
    }
  }, { once: true });

  // hidden.bs.modal: limpieza y reactivación segura del reconocimiento global
  modalEl.addEventListener('hidden.bs.modal', function onHiddenRecursos() {
    modalEl.removeEventListener('hidden.bs.modal', onHiddenRecursos);

    // limpieza mínima por seguridad
    try {
      const recog = modalEl._recogInstance;
      if (recog) {
        try { recog.onresult = null; } catch(e){}
        try { recog.onerror = null; } catch(e){}
        try { recog.stop(); } catch(e){}
      }
    } catch (e) {}
    modalEl._recogInstance = null;

    modalEl._opening = false;
    recognitionGlobalPaused = false;

    try {
      // Intentar arrancar siempre de forma segura; safeStart gestiona recreación y errores
      safeStartRecognitionGlobal();
      console.log('🎤 safeStartRecognitionGlobal llamado tras cerrar modal recursos');
    } catch (e) {
      console.warn('abrirModalRecursos hidden: safeStartRecognitionGlobal falló (ignored)', e);
    }

    recognitionGlobalWasRunning = false;
  }, { once: true });

  // Forzar activar tab EPP visualmente como comportamiento por defecto
  const tabBtn = document.getElementById('tab-epp');
  if (tabBtn && window.bootstrap && bootstrap.Tab) {
    try {
      new bootstrap.Tab(tabBtn).show();
    } catch (e) {
      console.warn('abrirModalRecursos: error al activar tab-epp', e);
    }
  }

  // Actualizar estado visual de tabs/panels (guardas por si no existen)
  const panelEPP = document.getElementById('panel-epp');
  const panelHerr = document.getElementById('panel-herramientas');
  const tabEPP = document.getElementById('tab-epp');
  const tabHerr = document.getElementById('tab-herramientas');

  if (tabEPP && tabEPP.classList) {
    tabEPP.classList.add('active');
    tabEPP.setAttribute('aria-selected', 'true');
  }
  if (tabHerr && tabHerr.classList) {
    tabHerr.classList.remove('active');
    tabHerr.setAttribute('aria-selected', 'false');
  }
  if (panelEPP && panelEPP.classList) panelEPP.classList.add('show', 'active');
  if (panelHerr && panelHerr.classList) panelHerr.classList.remove('show', 'active');

  // Renderizar tabla EPP si existen recursos y el elemento de tabla está presente
  if (window.recursosEPP && document.getElementById('tablaEPP')) {
    try {
      renderTablaRecursos('tablaEPP', window.recursosEPP || [], window.paginaEPPActual || 1, 'paginadorEPP');
      console.log('abrirModalRecursos: renderTablaRecursos tablaEPP ejecutado');
    } catch (e) {
      console.warn('abrirModalRecursos: error al renderizar tablaEPP', e);
    }
  } else {
    const tabla = document.getElementById('tablaEPP');
    const paginador = document.getElementById('paginadorEPP');
    if (tabla) tabla.innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
    if (paginador) paginador.innerHTML = '';
  }
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
  const numerosPalabra = MAPA_NUMEROS; // usa el mapa global
  const palabra = numerosPalabra[numero];
  // logs opcionales para debug (puedes desactivar en producción)
  console.log('🎯 matchOpcion: evaluando coincidencia para opción', numero);

  // coincidencias explícitas
  if (limpio.includes(`opcion ${numero}`) || limpio.includes(`opción ${numero}`)) return numero;
  if (palabra && (limpio.includes(`opcion ${palabra}`) || limpio.includes(`opción ${palabra}`))) return numero;
  // si el usuario habla solo el número ("tres" o "3")
  if (limpio === `${numero}` || limpio === palabra) return numero;

  // palabras clave (ej: "herramienta en mano")
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



function procesarComandoVoz(limpio) {
  const step = getStepActivo();

  // 🧰 Cierre por voz del modal de recursos asignados
const modalRecursos = document.getElementById('modalRecursos');
const modalVisible = modalRecursos && modalRecursos.classList.contains('show');

if (modalVisible && /\b(cerrar|cerrar recursos asignados)\b/.test(limpio)) {
  console.log('🎤 Comando de voz: cerrar modal recursos asignados');
  const modalInstance = bootstrap.Modal.getInstance(modalRecursos);
  if (modalInstance) {
    modalInstance.hide();
    getRenderer('mostrarMensajeKiosco')('Modal cerrado por voz', 'info');
  }
  return;
}

  console.log("👉 Texto reconocido (normalizado):", limpio, " | Step activo:", step);

  if (recognitionGlobalPaused) {
    console.log('⚠️ Reconocimiento global pausado, ignorando comando:', limpio);
    return;
  }

  // === Step2: Menú principal y modal recursos ===
  if (step === 'step2') {
    limpio = limpio.replace(/\b(\w+)\s+\1\b/g, '$1');

    const modalEl = document.getElementById('modalRecursos');
    const modalAbierto = modalEl && modalEl.classList.contains('show');

    // ✅ Comando de devolución por voz dentro del modal
    if (modalAbierto) {
      const matchOpcionNum = limpio.match(/^(devolver\s*)?opcion\s*(\d{1,2})$/i);
      if (matchOpcionNum) {
        const index = parseInt(matchOpcionNum[2], 10);
        console.log(`🎤 Comando de devolución por voz detectado: opción ${index}`);
        confirmarDevolucionPorVoz(index);
        return;
      }
    }

    // ✅ Comandos de cambio de tab por voz (funcionan dentro y fuera del modal)
    if (limpio === 'ver epp') {
      const tabBtn = document.getElementById('tab-epp');
      if (tabBtn) new bootstrap.Tab(tabBtn).show();
      return;
    }

    if (limpio === 'ver herramientas') {
      const tabBtn = document.getElementById('tab-herramientas');
      if (tabBtn) new bootstrap.Tab(tabBtn).show();
      return;
    }

    // ✅ Comandos del menú principal (solo si el modal NO está abierto)
    if (!modalAbierto) {
      if (matchOpcion(limpio, 1, "herramienta en mano")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Herramienta en mano', 'success');
        setModoEscaneo('manual');
        return;
      }

      if (matchOpcion(limpio, 2, "solicitar herramienta", "quiero solicitar", "pedir herramienta")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
        step5ReturnTarget = 2;
        window.nextStep(5);
        return;
      }

      if (matchOpcion(limpio, 3, "ver recursos", "recursos asignados", "mostrar recursos")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
        window.cargarRecursos().then(() => abrirModalRecursos());
        return;
      }

      if (matchOpcion(limpio, 4, "volver", "inicio", "regresar", "atrás", "cerrar")) {
        window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver al inicio', 'success');
        volverAInicio();
        return;
      }
    }

    // ✅ Paginación específica por tab
    const matchPaginaEPP = limpio.match(/^pagina\s*epp\s*(\d{1,2})$/i);
    const matchPaginaHerr = limpio.match(/^pagina\s*herramientas\s*(\d{1,2})$/i);

    if (matchPaginaEPP) {
      const numero = parseInt(matchPaginaEPP[1], 10);
      const total = Math.ceil((window.recursosEPP?.length || 0) / 5);
      if (numero >= 1 && numero <= total) {
        renderTablaRecursos('tablaEPP', window.recursosEPP, numero, 'paginadorEPP');
      } else {
        window.mostrarMensajeKiosco('Número de página inválido para EPP', 'warning');
      }
      return;
    }

    if (matchPaginaHerr) {
      const numero = parseInt(matchPaginaHerr[1], 10);
      const total = Math.ceil((window.recursosHerramientas?.length || 0) / 5);
      if (numero >= 1 && numero <= total) {
        renderTablaRecursos('tablaHerramientas', window.recursosHerramientas, numero, 'paginadorHerramientas');
      } else {
        window.mostrarMensajeKiosco('Número de página inválido para herramientas', 'warning');
      }
      return;
    }

    // ✅ Paginación genérica según tab activo
    const matchPaginaGen = limpio.match(/^pagina\s*(\d{1,2})$/i);
    if (matchPaginaGen) {
      const numero = parseInt(matchPaginaGen[1], 10);
      const eppActivo = document.getElementById('tab-epp')?.getAttribute('aria-selected') === 'true';
      const herrActivo = document.getElementById('tab-herramientas')?.getAttribute('aria-selected') === 'true';

      if (eppActivo) {
        const total = Math.ceil((window.recursosEPP?.length || 0) / 5);
        if (numero >= 1 && numero <= total) {
          renderTablaRecursos('tablaEPP', window.recursosEPP, numero, 'paginadorEPP');
        } else {
          window.mostrarMensajeKiosco('Número de página inválido para EPP', 'warning');
        }
        return;
      }

      if (herrActivo) {
        const total = Math.ceil((window.recursosHerramientas?.length || 0) / 5);
        if (numero >= 1 && numero <= total) {
          renderTablaRecursos('tablaHerramientas', window.recursosHerramientas, numero, 'paginadorHerramientas');
        } else {
          window.mostrarMensajeKiosco('Número de página inválido para herramientas', 'warning');
        }
        return;
      }

      window.mostrarMensajeKiosco('No se detectó el tab activo', 'warning');
      return;
    }

    console.log("⚠️ Step2: No se reconoció comando válido");
    return;
  }


  // === Step3: Escaneo QR ===
  else if (step === 'step3') {
    if (matchOpcion(limpio, 1, "qr", "escanear")) {
      window.mostrarMensajeKiosco('🎤 Comando reconocido: Escanear QR', 'success');
      console.log('🎤 Comando reconocido: Escanear QR');
      activarEscaneoQR();
      return;
    }

    if (limpio.includes("cancelar")) {
      window.mostrarMensajeKiosco('🎤 Comando reconocido: Cancelar escaneo', 'success');
      console.log('🎤 Comando reconocido: Cancelar escaneo');
      cancelarEscaneoQR();
      return;
    }

    if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
      window.mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar manualmente', 'success');
      console.log('🎤 Comando reconocido: Solicitar manualmente');
      step5ReturnTarget = 3;
      detenerEscaneoQR(5);
      return;
    }

    if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
      window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver al menú principal', 'success');
      console.log('🎤 Comando reconocido: Volver al menú principal');
      detenerEscaneoQR(2);
      return;
    }

    console.log("⚠️ Step3: No se reconoció ningún comando válido");
    return;
  }

  // === Step5: Categorías ===
  else if (step === 'step5') {
    if (matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
      window.mostrarMensajeKiosco(
        step5ReturnTarget === 3
          ? '🎤 Comando reconocido: Volver a "Tengo la herramienta en mano"'
          : '🎤 Comando reconocido: Volver al menú principal',
        'success'
      );
      window.nextStep(step5ReturnTarget);
      return;
    }

    const botonesCat = document.querySelectorAll('#categoria-buttons button');
    botonesCat.forEach((btn, index) => {
      if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
      }
    });

    console.log("⚠️ Step5: Procesada entrada (si hubo coincidencias)");
    return;
  }

  // === Step6: Subcategorías ===
else if (step === 'step6') {
  // --- Primer chequeo: paginación por voz en subcategorías ---
  const matchPaginaSub = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
  if (matchPaginaSub && Array.isArray(window.subcategoriasActuales)) {
    const token = matchPaginaSub[1];
    const numero = numeroDesdeToken(token);
    console.log('🔍 paginación step6 token:', token, '->', numero);
    if (!isNaN(numero) && numero >= 1) {
      const totalPaginas = Math.max(1, Math.ceil(window.subcategoriasActuales.length / 5));
      if (numero > totalPaginas) {
        window.mostrarMensajeKiosco('Número de página inválido', 'warning');
        console.log('⚠ Número de página inválido para subcategorías', numero, '>', totalPaginas);
        return;
      }
      renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
      return;
    }
  }

  // --- Interceptar "volver" antes de analizar botones por texto ---
  if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a categorías', 'success');
    console.log('🎤 Comando reconocido: Volver a categorías');
    window.nextStep(5);
    return;
  }

  // --- luego el bucle de botones (selección por opción o por texto) ---
  const botonesSub = document.querySelectorAll('#subcategoria-buttons button');
  botonesSub.forEach((btn, index) => {
    try {
      if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
      }
    } catch (e) {
      console.warn('Error al procesar botón subcategoría', e);
    }
  });

  console.log("⚠️ Step6: Procesada entrada (si hubo coincidencias)");
  return;
}

// === Step7: Recursos ===
else if (step === 'step7') {
  // --- Primer chequeo: paginación por voz en recursos ---
  const matchPaginaRec = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
  if (matchPaginaRec && Array.isArray(window.recursosActuales)) {
    const token = matchPaginaRec[1];
    const numero = numeroDesdeToken(token);
    console.log('🔍 paginación step7 token:', token, '->', numero);
    if (!isNaN(numero) && numero >= 1) {
      const totalPaginas = Math.max(1, Math.ceil(window.recursosActuales.length / 5));
      if (numero > totalPaginas) {
        window.mostrarMensajeKiosco('Número de página inválido', 'warning');
        console.log('⚠ Número de página inválido para recursos', numero, '>', totalPaginas);
        return;
      }
      renderRecursosPaginados(window.recursosActuales, numero);
      return;
    }
  }

  // --- Interceptar "volver" antes de analizar botones por texto ---
  if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a subcategorías', 'success');
    console.log('🎤 Comando reconocido: Volver a subcategorías');
    window.nextStep(6);
    return;
  }

  // --- luego el bucle de botones (selección por opción o por texto) ---
  const botonesRec = document.querySelectorAll('#recurso-buttons button');
  botonesRec.forEach((btn, index) => {
    try {
      if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
      }
    } catch (e) {
      console.warn('Error al procesar botón recurso', e);
    }
  });

  console.log("⚠️ Step7: Procesada entrada (si hubo coincidencias)");
  return;
}

// === Step8: Series ===
else if (step === 'step8') {
  // --- Primer chequeo: paginación por voz en series ---
  const matchPaginaSer = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
  if (matchPaginaSer && Array.isArray(window.seriesActuales)) {
    const token = matchPaginaSer[1];
    const numero = numeroDesdeToken(token);
    console.log('🔍 paginación step8 token:', token, '->', numero);
    if (!isNaN(numero) && numero >= 1) {
      const totalPaginas = Math.max(1, Math.ceil(window.seriesActuales.length / 5));
      if (numero > totalPaginas) {
        window.mostrarMensajeKiosco('Número de página inválido', 'warning');
        console.log('⚠ Número de página inválido para series', numero, '>', totalPaginas);
        return;
      }
      renderSeriesPaginadas(window.seriesActuales, numero);
      return;
    }
  }

  // --- Interceptar "volver" antes de analizar botones por texto ---
  if (esComandoVolver(limpio) || matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    window.mostrarMensajeKiosco('🎤 Comando reconocido: Volver a recursos', 'success');
    console.log('🎤 Comando reconocido: Volver a recursos');
    window.nextStep(7);
    return;
  }

  // --- luego el bucle de botones (selección por opción o por texto) ---
  const botonesSeries = document.querySelectorAll('#serie-buttons button');
  botonesSeries.forEach((btn, index) => {
    try {
      if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
        btn.click();
      }
    } catch (e) {
      console.warn('Error al procesar botón serie', e);
    }
  });

  console.log("⚠️ Step8: Procesada entrada (si hubo coincidencias)");
  return;
}

// Manejo explícito para step9 (Devolución por QR)
if (step === 'step9') {
  // Aceptar muchas variantes: "confirmar", "confirmar devolución", "aceptar", "confirm"
  if (/\b(confirmar|confirm|aceptar|acept)\b/.test(limpio)) {
    const btn = document.getElementById('btnConfirmarDevolucion');
    if (btn && !btn.disabled) {
      console.log('🎤 step9: comando confirmar detectado -> click confirmar');
      try { btn.click(); } catch(e) { confirmarDevolucionActual(); }
      return;
    } else {
      console.log('⚠️ step9: comando confirmar, pero botón deshabilitado');
      getRenderer('mostrarMensajeKiosco')('Aún no se detectó un QR válido para confirmar', 'warning');
      return;
    }
  }

  // Volver: usar tu helper de tolerancia
  if (esComandoVolver(limpio) || /\b(cancelar|salir|volver)\b/.test(limpio)) {
    console.log('🎤 step9: comando volver detectado -> volverARecursosAsignados');
    volverARecursosAsignados();
    return;
  }

  // si no coincidió en step9, devolvemos control para logs o fallback
  console.warn('⚠️ step9: comando no reconocido en devoluciones:', limpio);
  getRenderer('mostrarMensajeKiosco')('No se reconoció el comando. Decí "confirmar" o "volver".', 'info');
  return;
}


  /*

const mapaNumeros = {
  uno: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5,
  seis: 6, siete: 7, ocho: 8, nueve: 9, diez: 10,
  once: 11, doce: 12, trece: 13, catorce: 14, quince: 15,
  dieciseis: 16, diecisiete: 17, dieciocho: 18, diecinueve: 19, veinte: 20
};*/


  // === Paginación y navegación globales por si modal está abierto ===
  // Soporta "pagina 3", "pagina tres", "pagina veinte", etc.
  const matchPaginaAny = limpio.match(/^pagina\s*(\d{1,2}|[a-záéíóúñ]+)$/i);
  if (matchPaginaAny) {
  // token puede ser "3" o "tres"
  let token = matchPaginaAny[1];
  // convierte token a número soportando dígitos y palabras
  let numero = numeroDesdeToken(token); // usa helper declarado arriba

  console.log('🔍 matchPaginaAny token:', token, '-> numero:', numero, 'step:', step);

  if (isNaN(numero) || numero < 1) {
    window.mostrarMensajeKiosco('Número de página no reconocido', 'warning');
    return;
  }

  // Subcategorias (step6)
  if (step === 'step6' && Array.isArray(window.subcategoriasActuales)) {
    const totalPaginas = Math.max(1, Math.ceil(window.subcategoriasActuales.length / 5));
    if (numero > totalPaginas) {
      window.mostrarMensajeKiosco('Número de página inválido', 'warning');
      console.log('⚠ Número de página inválido para subcategorías', numero, '>', totalPaginas);
      return;
    }
    renderSubcategoriasPaginadas(window.subcategoriasActuales, numero);
    return;
  }

  // Recursos (step7)
  if (step === 'step7' && Array.isArray(window.recursosActuales)) {
    const totalPaginas = Math.max(1, Math.ceil(window.recursosActuales.length / 5));
    if (numero > totalPaginas) {
      window.mostrarMensajeKiosco('Número de página inválido', 'warning');
      console.log('⚠ Número de página inválido para recursos', numero, '>', totalPaginas);
      return;
    }
    renderRecursosPaginados(window.recursosActuales, numero);
    return;
  }

  // Series (step8)
  if (step === 'step8' && Array.isArray(window.seriesActuales)) {
    const totalPaginas = Math.max(1, Math.ceil(window.seriesActuales.length / 5));
    if (numero > totalPaginas) {
      window.mostrarMensajeKiosco('Número de página inválido', 'warning');
      console.log('⚠ Número de página inválido para series', numero, '>', totalPaginas);
      return;
    }
    renderSeriesPaginadas(window.seriesActuales, numero);
    return;
  }

  // Si no estamos en esos steps, no hacemos nada aquí
  console.log('⚠️ matchPaginaAny: comando página detectado pero no aplicable en step', step);
  return;
}


  // === Comando global: cerrar modal de recursos ===
  const modalEl = document.getElementById('modalRecursos');
  if (modalEl && modalEl.classList.contains('show')) {
    if (matchOpcion(limpio, 0, "volver", "cerrar", "cerrar recursos")) {
      console.log("✅ Comando global: Cerrar modal de recursos asignados");
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modalInstance.hide();
      window.mostrarMensajeKiosco('🎤 Comando reconocido: Cerrar recursos asignados', 'success');
      return;
    }
  }

  // Si llegamos aquí, no hubo comando reconocido
  console.log("⚠️ procesarComandoVoz: comando no reconocido en ningún step");
}


/*
// Exponer API pública para entorno de tests y JSDOM
if (typeof window !== 'undefined') {

  // Funciones principales (si existen en este scope se asignan; si no, se colocan stubs seguros)
  window.identificarTrabajador = typeof identificarTrabajador === 'function' ? identificarTrabajador : (async () => ({ success: false }));
  window.registrarSerie = typeof registrarSerie === 'function' ? registrarSerie : (async () => ({ success: false }));
  window.registrarPorQR = typeof registrarPorQR === 'function' ? registrarPorQR : (async () => ({ success: false }));
  window.mostrarRecursosAsignados = typeof mostrarRecursosAsignados === 'function' ? mostrarRecursosAsignados : (() => {});
  window.getStepActivo = typeof getStepActivo === 'function' ? getStepActivo : (() => {
    const s = document.querySelector('.step.active'); return s ? s.id : null;
  });
  window.nextStep = typeof nextStep === 'function' ? nextStep : ((n) => {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const el = document.getElementById('step' + n); if (el) el.classList.add('active');
  });

  // Navegación / selección
  window.seleccionarCategoria = typeof seleccionarCategoria === 'function' ? seleccionarCategoria : (() => {});
  window.seleccionarSubcategoria = typeof seleccionarSubcategoria === 'function' ? seleccionarSubcategoria : (() => {});
  window.seleccionarRecurso = typeof seleccionarRecurso === 'function' ? seleccionarRecurso : (() => {});
  window.confirmarSerieModal = typeof confirmarSerieModal === 'function' ? confirmarSerieModal : ((...a) => { if (confirm) confirm('Confirm?'); });

  // Recursos, tablas y tablas auxiliares
  window.devolverRecurso = typeof devolverRecurso === 'function' ? devolverRecurso : (async () => ({ success: false }));
  window.renderTablaRecursos = typeof renderTablaRecursos === 'function' ? renderTablaRecursos : (() => {});
  window.renderPaginacionRecursos = typeof renderPaginacionRecursos === 'function' ? renderPaginacionRecursos : (() => {});
  window.renderRecursosPaginados = typeof renderRecursosPaginados === 'function' ? renderRecursosPaginados : (() => {});
  window.renderSubcategoriasPaginadas = typeof renderSubcategoriasPaginadas === 'function' ? renderSubcategoriasPaginadas : (() => {});
  window.renderSeriesPaginadas = typeof renderSeriesPaginadas === 'function' ? renderSeriesPaginadas : (() => {});
  window.renderSubcategoriasPaginadas = typeof renderSubcategoriasPaginadas === 'function' ? renderSubcategoriasPaginadas : (() => {});
  window.actualizarRecursos = typeof actualizarRecursos === 'function' ? actualizarRecursos : ((list) => {
    const cont = document.getElementById('recursos'); if (cont) { cont.innerHTML = list.map(r => `<div>${r.nombre}</div>`).join(''); }
  });

  // Escaneo / QR
  window.activarEscaneoQR = typeof activarEscaneoQR === 'function' ? activarEscaneoQR : (async () => {});
  window.cancelarEscaneoQR = typeof cancelarEscaneoQR === 'function' ? cancelarEscaneoQR : (() => {});
  window.detenerEscaneoQR = typeof detenerEscaneoQR === 'function' ? detenerEscaneoQR : ((next=null) => { if (next) (window.nextStep||(()=>{}))(next); });
  window.cleanupScanUI = typeof cleanupScanUI === 'function' ? cleanupScanUI : (() => {});
  window.activarEscaneoQRLogin = typeof activarEscaneoQRLogin === 'function' ? activarEscaneoQRLogin : (async () => {});
  window.detenerEscaneoQRLogin = typeof detenerEscaneoQRLogin === 'function' ? detenerEscaneoQRLogin : (async () => {});

  // Login por QR
  window.identificarPorQR = typeof identificarPorQR === 'function' ? identificarPorQR : (async () => ({ success: false }));

  // Menú / carga
  window.cargarCategorias = typeof cargarCategorias === 'function' ? cargarCategorias : (async () => []);
  window.cargarRecursos = typeof cargarRecursos === 'function' ? cargarRecursos : (async () => {});
  window.cargarMenuPrincipal = typeof cargarMenuPrincipal === 'function' ? cargarMenuPrincipal : (() => {});

  // Reconocimiento de voz
  window.iniciarReconocimientoGlobal = typeof iniciarReconocimientoGlobal === 'function' ? iniciarReconocimientoGlobal : (() => {});
  window.iniciarReconocimientoVoz = typeof iniciarReconocimientoVoz === 'function' ? iniciarReconocimientoVoz : (() => {});
  window.recognitionGlobal = typeof recognitionGlobal !== 'undefined' ? recognitionGlobal : null;

  // Matchers y utilitarios
  window.normalizarTexto = typeof normalizarTexto === 'function' ? normalizarTexto : ((s='') => (''+s).toLowerCase().trim().normalize('NFD').replace(/[\u0300-\u036f]/g, ''));
  window.matchOpcion = typeof matchOpcion === 'function' ? matchOpcion : ((limpio, n) => (''+limpio).includes(`opcion ${n}`));
  window.matchTextoBoton = typeof matchTextoBoton === 'function' ? matchTextoBoton : ((limpio, btn) => (btn && (''+btn.textContent).toLowerCase().includes((''+limpio).toLowerCase())));
  window.matchRecurso = typeof matchRecurso === 'function' ? matchRecurso : ((f,r) => (''+f).toLowerCase().includes((''+r).toLowerCase()));
  window.matchAccion = typeof matchAccion === 'function' ? matchAccion : ((s='') => /solicit|pedir|quiero/.test(s));
  window.matchVolver = typeof matchVolver === 'function' ? matchVolver : ((s='') => /volver|inicio|regresar|atrás|cerrar/.test(s));
  window.matchCerrar = typeof matchCerrar === 'function' ? matchCerrar : ((s='') => /cerrar|salir/.test(s));
  window.matchConfirmar = typeof matchConfirmar === 'function' ? matchConfirmar : ((s='') => /confirmar|si|ok/.test(s));

  // Validaciones
  window.validarUsuario = typeof validarUsuario === 'function' ? validarUsuario : ((u) => !!u && u.rol === 'Trabajador' && u.estado === 'Alta');
  window.validarEstado = typeof validarEstado === 'function' ? validarEstado : ((e) => e !== 'Baja');
  window.validarRol = typeof validarRol === 'function' ? validarRol : ((r) => r === 'Trabajador');
  window.validarQR = typeof validarQR === 'function' ? validarQR : ((q) => !!q && String(q).trim().length > 0);
  window.validarSerie = typeof validarSerie === 'function' ? validarSerie : ((n) => Number.isInteger(Number(n)) && Number(n) > 0);
  window.validarRecurso = typeof validarRecurso === 'function' ? validarRecurso : ((r) => r != null);
  window.validarCategoria = typeof validarCategoria === 'function' ? validarCategoria : ((id) => Number(id) > 0);
  window.validarSubcategoria = typeof validarSubcategoria === 'function' ? validarSubcategoria : ((id) => Number(id) > 0);
  window.validarPasoActual = typeof validarPasoActual === 'function' ? validarPasoActual : ((step) => (window.getStepActivo && window.getStepActivo()) === step);
  window.validarSesion = typeof validarSesion === 'function' ? validarSesion : (() => !!window.localStorage.getItem('id_usuario'));

  // UI helpers mínimos
  window.mostrarMensajeKiosco = window.mostrarMensajeKiosco || ((msg,tipo) => { const t = document.createElement('div'); t.className='toast'; t.textContent = msg; (document.getElementById('toastContainer')||document.body).appendChild(t); return t; });
  window.mostrarModal = window.mostrarModal || ((id,contenido) => { const m = document.getElementById(id); if (m) { m.innerHTML = contenido; m.classList.add('show'); } });
  window.cerrarModal = window.cerrarModal || ((id) => { const m = document.getElementById(id); if (m) m.classList.remove('show'); });

  // Helpers de UI/estado para tests
  window.actualizarPaso = window.actualizarPaso || ((stepId) => {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const el = document.getElementById(stepId); if (el) el.classList.add('active');
  });
  window.actualizarUI = window.actualizarUI || ((id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; });
  window.actualizarBotones = window.actualizarBotones || ((n) => {
    document.querySelectorAll('button[data-step]').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`button[data-step="${n}"]`); if (btn) btn.classList.add('active');
  });

  // Fallbacks para volverDesdeStepX si no fueron definidas
  window.volverDesdeStep5 = window.volverDesdeStep5 || (() => { if (window.nextStep) window.nextStep(window.step5ReturnTarget || 2); });
  window.volverDesdeStep6 = window.volverDesdeStep6 || (() => { if (window.nextStep) window.nextStep(5); });
  window.volverDesdeStep7 = window.volverDesdeStep7 || (() => { if (window.nextStep) window.nextStep(6); });

  // No sobrescribir mocks que tests hayan colocado antes: sólo definir si no existe
  // (ej: mostrarMensajeKiosco ya puede ser mockeada por beforeEach)
  if (!window.mostrarMensajeKiosco) {
    window.mostrarMensajeKiosco = (texto, tipo='info') => window.mostrarMensajeKiosco ? window.mostrarMensajeKiosco(texto, tipo) : null;
  }
}


  module.exports = Object.assign(module.exports || {}, {
    confirmarSerieModal: typeof confirmarSerieModal !== 'undefined' ? confirmarSerieModal : null,
    seleccionarRecurso: typeof seleccionarRecurso !== 'undefined' ? seleccionarRecurso : null,
    registrarSerie: typeof registrarSerie !== 'undefined' ? registrarSerie : null,
    registrarPorQR: typeof registrarPorQR !== 'undefined' ? registrarPorQR : null,
    identificarTrabajador: typeof identificarTrabajador !== 'undefined' ? identificarTrabajador : null,
    getStepActivo: typeof getStepActivo !== 'undefined' ? getStepActivo : null,
    nextStep: typeof nextStep !== 'undefined' ? nextStep : null,
    mostrarMensajeKiosco: typeof mostrarMensajeKiosco !== 'undefined' ? mostrarMensajeKiosco : null,
    cargarRecursos: typeof cargarRecursos !== 'undefined' ? cargarRecursos : null,
    cargarCategorias: typeof cargarCategorias !== 'undefined' ? cargarCategorias : null,
    cargarMenuPrincipal: typeof cargarMenuPrincipal !== 'undefined' ? cargarMenuPrincipal : null,
    activarEscaneoQR: typeof activarEscaneoQR !== 'undefined' ? activarEscaneoQR : null,
    activarEscaneoQRLogin: typeof activarEscaneoQRLogin !== 'undefined' ? activarEscaneoQRLogin : null,
    detenerEscaneoQR: typeof detenerEscaneoQR !== 'undefined' ? detenerEscaneoQR : null,
    identificarPorQR: typeof identificarPorQR !== 'undefined' ? identificarPorQR : null,
    recognitionGlobal: typeof recognitionGlobal !== 'undefined' ? recognitionGlobal : null,
    mostrarRecursosAsignados,
    devolverRecurso

  });
  
// ----------------- Exports para Jest / CommonJS -----------------
// Reemplazar el bloque anterior de module.exports por este bloque completo.
// Solo se define module.exports si el entorno lo soporta (Jest / Node).
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    // Core flow
    procesarComandoVoz: typeof procesarComandoVoz !== 'undefined' ? procesarComandoVoz : null,
    getStepActivo: typeof getStepActivo !== 'undefined' ? getStepActivo : null,
    nextStep: typeof nextStep !== 'undefined' ? nextStep : null,

    // Helpers de texto / números
    normalizarTexto: typeof normalizarTexto !== 'undefined' ? normalizarTexto : (s='') => (''+s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim(),
    MAPA_NUMEROS: typeof MAPA_NUMEROS !== 'undefined' ? MAPA_NUMEROS : (typeof window !== 'undefined' ? window.MAPA_NUMEROS : undefined),
    numeroDesdeToken: typeof numeroDesdeToken !== 'undefined' ? numeroDesdeToken : (t => NaN),

    // Matchers y comandos
    matchOpcion: typeof matchOpcion !== 'undefined' ? matchOpcion : null,
    matchTextoBoton: typeof matchTextoBoton !== 'undefined' ? matchTextoBoton : null,
    esComandoVolver: typeof esComandoVolver !== 'undefined' ? esComandoVolver : null,
    matchVolver: typeof matchVolver !== 'undefined' ? matchVolver : null,

    // Renderers (que los tests suelen espiar)
    renderSubcategoriasPaginadas: typeof renderSubcategoriasPaginadas !== 'undefined' ? renderSubcategoriasPaginadas : null,
    renderRecursosPaginados: typeof renderRecursosPaginados !== 'undefined' ? renderRecursosPaginados : null,
    renderSeriesPaginadas: typeof renderSeriesPaginadas !== 'undefined' ? renderSeriesPaginadas : null,
    renderTablaRecursos: typeof renderTablaRecursos !== 'undefined' ? renderTablaRecursos : null,

    // Funciones de carga / acciones de red (stubs si no existen)
    cargarCategorias: typeof cargarCategorias !== 'undefined' ? cargarCategorias : null,
    cargarRecursos: typeof cargarRecursos !== 'undefined' ? cargarRecursos : null,
    seleccionarCategoria: typeof seleccionarCategoria !== 'undefined' ? seleccionarCategoria : null,
    seleccionarSubcategoria: typeof seleccionarSubcategoria !== 'undefined' ? seleccionarSubcategoria : null,
    seleccionarRecurso: typeof seleccionarRecurso !== 'undefined' ? seleccionarRecurso : null,

    // Modal / confirmación / registrar
    confirmarSerieModal: typeof confirmarSerieModal !== 'undefined' ? confirmarSerieModal : null,
    registrarSerie: typeof registrarSerie !== 'undefined' ? registrarSerie : null,
    registrarPorQR: typeof registrarPorQR !== 'undefined' ? registrarPorQR : null,
    identificarTrabajador: typeof identificarTrabajador !== 'undefined' ? identificarTrabajador : null,
    identificarPorQR: typeof identificarPorQR !== 'undefined' ? identificarPorQR : null,

    // Utilidades UI / testing
    mostrarMensajeKiosco: typeof mostrarMensajeKiosco !== 'undefined' ? mostrarMensajeKiosco : null,
    mostrarRecursosAsignados: typeof mostrarRecursosAsignados !== 'undefined' ? mostrarRecursosAsignados : null,
    devolverRecurso: typeof devolverRecurso !== 'undefined' ? devolverRecurso : null,

    // Speech control (exponer estado si existe)
    recognitionGlobal: typeof recognitionGlobal !== 'undefined' ? recognitionGlobal : null,
    recognitionGlobalPaused: typeof recognitionGlobalPaused !== 'undefined' ? recognitionGlobalPaused : null
  });
}
*/
