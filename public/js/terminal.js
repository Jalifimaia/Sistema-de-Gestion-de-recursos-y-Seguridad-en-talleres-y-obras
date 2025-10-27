let scanner;
let isScanning = false; // 👈 flag de estado


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

  // Cambiar step activo con guardas
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  const stepEl = document.getElementById('step' + n);
  if (stepEl && stepEl.classList) {
    stepEl.classList.add('active');
  } else {
    console.warn('nextStep: step element not found:', 'step' + n);
  }

  // Acciones específicas por step
  if (n === 2) cargarMenuPrincipal();
  if (n === 5) cargarCategorias();
}


function identificarTrabajador() {
  const dni = document.getElementById('dni').value;
  return new Promise((resolve) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/terminal/identificar', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

    xhr.onload = function () {
      try {
        const res = JSON.parse(xhr.responseText);
        if (res.success) {
          localStorage.setItem('id_usuario', res.usuario.id);
          nextStep(2);
          document.getElementById('saludo-trabajador').textContent = `Hola ${res.usuario.name}`;
        } else {
          mostrarMensajeKiosco(res.message || 'Error al identificar al trabajador', 'danger');
        }
        resolve(res);
      } catch (e) {
        mostrarMensajeKiosco('Error al identificar al trabajador', 'danger');
        resolve({ success: false, error: e });
      }
    };

    xhr.send('dni=' + encodeURIComponent(dni));
  });
}


function simularEscaneo() {
  //alert("Simulación de escaneo QR");
  console.log('🧪 simularEscaneo: simulación activada, avanzando a step5');
  nextStep(5);
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
      mostrarMensajeKiosco('No se pudieron cargar las categorías', 'danger');
      console.log('No se pudieron cargar las categorías');
    }
  };

  xhr.send();
}

function cargarRecursos() {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!id_usuario) {
    console.warn('⚠️ cargarRecursos: No hay id_usuario en localStorage');
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/recursos-asignados/${id_usuario}`, true);

  xhr.onload = function () {
    try {
      const recursos = JSON.parse(xhr.responseText);
      const tablaEPP = document.getElementById('tablaEPP');
      const tablaHerramientas = document.getElementById('tablaHerramientas');
      tablaEPP.innerHTML = '';
      tablaHerramientas.innerHTML = '';

      if (recursos.length === 0) {
        const vacio = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
        tablaEPP.innerHTML = vacio;
        tablaHerramientas.innerHTML = vacio;
        return;
      }

      recursos.forEach(r => {
        const fila = `<tr>
  <td>${r.subcategoria} / ${r.recurso}</td>
  <td>${r.serie}</td>
  <td>${r.fecha_prestamo || '-'}</td>
  <td>${r.fecha_devolucion || '-'}</td>
  <td>
    ${`
      <button class="btn btn-sm btn-outline-danger" onclick="devolverRecurso(${r.detalle_id})">
        Devolver
      </button>`}
  </td>
</tr>`;


        const tipo = r.tipo?.toLowerCase();
        const esEPP = tipo === 'epp' || (r.categoria && r.categoria.toLowerCase().includes('epp'));

        if (esEPP) {
          tablaEPP.innerHTML += fila;
        } else {
          tablaHerramientas.innerHTML += fila;
        }
      });

    } catch (e) {
      mostrarMensajeKiosco('Error al cargar recursos asignados', 'danger');
      console.log('Error al cargar recursos asignados');
    }
  };

  xhr.send();
}




function mostrarRecursosAsignados(recursos) {
  const contenedor = document.getElementById('contenedorRecursos');
  contenedor.innerHTML = '';

  recursos.forEach(r => {
    console.log('📋 mostrarRecursosAsignados: recursos recibidos', recursos);
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';

    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title mb-1">${r.recurso}</h5>
        <p class="card-text mb-1">Serie: <strong>${r.serie}</strong></p>
        <p class="card-text mb-1">Subcategoría: ${r.subcategoria}</p>
        <p class="card-text mb-1">📅 Prestado: ${r.fecha_prestamo}</p>
        <p class="card-text mb-1">📅 Devolución: ${r.fecha_devolucion}</p>
        <button class="btn btn-outline-danger btn-sm mt-2" onclick="devolverRecurso(${r.detalle_id})">
          Devolver recurso
        </button>
      </div>
    `;

    contenedor.appendChild(card);
  });
}


function devolverRecurso(detalleId) {
  if (!confirm('¿Confirmás que querés devolver este recurso?')) return Promise.resolve({ success: false, reason: 'cancelled' });

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
      cargarRecursos();
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


function seleccionarCategoria(categoriaId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/subcategorias-disponibles/${categoriaId}`, true);

  xhr.onload = function () {
    try {
      const subcategorias = JSON.parse(xhr.responseText);
      console.log('📁 seleccionarCategoria: subcategorías recibidas', subcategorias);
      const contenedor = document.getElementById('subcategoria-buttons');
      contenedor.innerHTML = '';

      nextStep(6);

      // 👇 solo renderizar las que tengan disponibles > 0
      subcategorias
        .filter(s => s.disponibles > 0)
        .forEach((s, index) => {
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
    } catch (e) {
      mostrarMensajeKiosco('No se pudieron cargar las subcategorías', 'danger');
      console.log('No se pudieron cargar las subcategorías');
    }
  };

  xhr.send();
}


function seleccionarSubcategoria(subcategoriaId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/recursos-disponibles/${subcategoriaId}`, true);

  xhr.onload = function () {
    try {
      const recursos = JSON.parse(xhr.responseText);
      console.log('📦 seleccionarSubcategoria: recursos recibidos', recursos);
      const contenedor = document.getElementById('recurso-buttons');
      contenedor.innerHTML = '';

      nextStep(7);

      // 👇 solo renderizar los que tengan disponibles > 0
      recursos
        .filter(r => r.disponibles > 0)
        .forEach((r, index) => {
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
    } catch (e) {
      mostrarMensajeKiosco('No se pudieron cargar los recursos', 'danger');
      console.log('No se pudieron cargar los recursos');
    }
  };

  xhr.send();
}


function seleccionarRecurso(recursoId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/series/${recursoId}`, true);

  xhr.onload = function () {
    try {
      const series = JSON.parse(xhr.responseText);
      console.log('🔢 seleccionarRecurso: series recibidas', series);
      const contenedor = document.getElementById('serie-buttons');
      contenedor.innerHTML = '';

      nextStep(8);

      series.forEach((s, index) => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-success btn-lg d-flex justify-content-between align-items-center m-2';
        btn.dataset.serieId = s.id;

        btn.innerHTML = `
          <span class="badge-opcion">Opción ${index + 1}</span>
          <span class="flex-grow-1 text-start">${s.nro_serie || s.codigo || `Serie ${s.id}`}</span>
        `;
        contenedor.appendChild(btn);
      });
    } catch (e) {
      mostrarMensajeKiosco('No se pudieron cargar las series', 'danger');
      console.log('No se pudieron cargar las series');
    }
  };

  xhr.send();
}


async function registrarSerie(serieId) {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!id_usuario) {
    if (typeof window.mostrarMensajeKiosco === 'function') window.mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
    return { success: false, reason: 'no_usuario' };
  }

  if (!confirm('¿Confirmás que querés solicitar esta herramienta?')) {
    return { success: false, reason: 'cancelled' };
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
      if (typeof window.mostrarMensajeKiosco === 'function') window.mostrarMensajeKiosco('Error de red al registrar recurso', 'danger');
      return { success: false, reason: 'http_error', status: res && res.status, statusText };
    }

    const data = await res.json();

    if (data && data.success) {
      if (typeof window.mostrarMensajeKiosco === 'function') window.mostrarMensajeKiosco('✅ Recurso asignado correctamente', 'success');
      if (typeof window.nextStep === 'function') window.nextStep(2);
      return { success: true, data };
    } else {
      if (typeof window.mostrarMensajeKiosco === 'function') window.mostrarMensajeKiosco((data && data.message) || 'Error al registrar recurso', 'danger');
      return { success: false, reason: 'backend_error', data };
    }
  } catch (err) {
    if (typeof window.mostrarMensajeKiosco === 'function') window.mostrarMensajeKiosco('Error de red al registrar recurso', 'danger');
    return { success: false, reason: 'exception', error: err && (err.message || String(err)) };
  }
}



/*
// Listener global para debug de clicks
document.addEventListener('click', (e) => {
   .log('[DOC CLICK]', e.target, e);
}, { capture: true });
*/

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

// Delegación para series
const _serieButtons = document.getElementById('serie-buttons');
if (_serieButtons) {
  _serieButtons.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-serie-id]');
    if (btn) registrarSerie(btn.dataset.serieId);
  });
}



function activarEscaneoQR() {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (!qrContainer) {
    console.error('No se encontró el contenedor de escaneo QR')
    mostrarMensajeKiosco('No se encontró el contenedor de escaneo QR', 'danger');
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
    mostrarMensajeKiosco('No se pudo activar la cámara para escanear QR', 'danger');
    cleanupScanUI();
  });
}


function cancelarEscaneoQR() {
  cleanupScanUI();
}


function registrarPorQR(codigoQR) {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!id_usuario) {
    mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
    return Promise.resolve({ success: false, reason: 'no_usuario' });
  }

  return fetch(`/terminal/registrar-por-qr`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ codigo_qr: codigoQR, id_usuario })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const mensaje = `✅ Recurso registrado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`;
      mostrarMensajeKiosco(mensaje, 'success');
      nextStep(2);
    } else {
      if (data.message === 'QR no encontrado') {
        mostrarMensajeKiosco('❌ QR no encontrado en el sistema', 'danger');
      } else if (data.message === 'Este recurso ya está asignado') {
        mostrarMensajeKiosco(`⚠️ Este recurso ya está asignado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`, 'warning');
      } else {
        mostrarMensajeKiosco(data.message || 'Error al registrar recurso por QR', 'danger');
      }
    }
    return data;
  })
  .catch(err => {
    mostrarMensajeKiosco('Error de red al registrar recurso por QR', 'danger');
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
      if (next) nextStep(next); // 👈 avanzar al paso cuando termina
      console.log('➡️ detenerEscaneoQR: avanzando a step', next);
    });
  } else {
    qrContainer.innerHTML = '';
    if (btnCancelar) btnCancelar.classList.add('d-none');
    if (btnEscanear) btnEscanear.classList.remove('d-none');
    if (textoCamara) textoCamara.classList.add('d-none');
    isScanning = false;
    if (next) nextStep(next);
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
    mostrarMensajeKiosco('No se pudo activar la cámara para escanear QR', 'danger');
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
  fetch('/terminal/identificar', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ codigo_qr: codigoQR })
  })
  .then(res => res.json())
  .then(data => {
    console.log('Respuesta login QR:', data);

    if (data.success) {
      // Usuario válido (rol trabajador + estado Alta)
      localStorage.setItem('id_usuario', data.usuario.id);
      nextStep(2);
      document.getElementById('saludo-trabajador').textContent = `Hola ${data.usuario.name}`;
    } else {
      // Mensajes diferenciados según backend
      if (data.message === 'Usuario no encontrado') {
        mostrarMensajeKiosco('❌ Usuario no encontrado en el sistema', 'danger');
      console.log('❌ Usuario no encontrado en el sistema');
      } else if (data.message === 'Este usuario no tiene permisos para usar el kiosco') {
        mostrarMensajeKiosco('⚠️ Este usuario no tiene permisos para usar el kiosco', 'warning');
      console.log('⚠️ Este usuario no tiene permisos para usar el kiosco');
      } else if (data.message === 'El usuario no está en estado Alta y no puede usar el kiosco') {
        mostrarMensajeKiosco('⛔ El usuario no está en estado Alta y no puede usar el kiosco', 'danger');
      console.log('⛔ El usuario no está en estado Alta y no puede usar el kiosco');
      } else {
        mostrarMensajeKiosco(data.message || 'Error al identificar por QR', 'danger');
      console.log('Error al identificar por QR');
      }
    }
  })
  .catch(err => {
    console.error('Error en fetch login QR:', err);
    mostrarMensajeKiosco('Error de red al identificar por QR', 'danger');
  });
}


function volverAInicio() {
  // Limpiamos la sesión del trabajador
  localStorage.removeItem('id_usuario');
  console.log('🔙 volverAInicio: sesión limpiada');

  // Volvemos al paso 1
  nextStep(1);

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
  nextStep(3);
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
        const id_usuario = localStorage.getItem('id_usuario');
        if (!id_usuario) {
          console.warn('⚠️ cargarMenuPrincipal: no hay id_usuario para solicitar herramienta');
          mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
          return;
        }

        fetch('/terminal/solicitar', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ id_usuario })
        })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            console.warn('❌ No se puede solicitar herramientas:', data.message);
            mostrarMensajeKiosco(data.message || 'No se puede solicitar herramientas', 'warning');
            return;
          }

          console.log('🛠️ opción seleccionada: solicitar herramienta');
          step5ReturnTarget = 2;
          nextStep(5);
        })
        .catch(() => {
          console.error('❌ Error de red al validar EPP');
          mostrarMensajeKiosco('Error de red al validar EPP', 'danger');
        });
      },
      clase: "btn-outline-primary"
    },
    {
      id: 3,
      texto: "📋 Ver recursos asignados",
      accion: () => {
        console.log('📋 opción seleccionada: ver recursos asignados');
        cargarRecursos();
        abrirModalRecursos(); // ✅ dispara log de apertura y cierre
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
  nextStep(step5ReturnTarget);
}


function abrirModalRecursos() {
  const modalEl = document.getElementById('modalRecursos');
  if (!modalEl) return;
  const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  
  console.log('📖 abrirModalRecursos: modal abierto');
  modalInstance.show();

  modalEl.addEventListener('hidden.bs.modal', () => {
  console.log('📖 cerrarModalRecursos: modal cerrado por interacción del usuario');
});

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

function iniciarReconocimientoGlobal() {
  if (!('webkitSpeechRecognition' in window)) {
    console.warn('⚠️ Tu navegador no soporta reconocimiento de voz');
    mostrarMensajeKiosco('⚠️ Tu navegador no soporta reconocimiento de voz', 'warning');
    return;
  }

  recognitionGlobal = new webkitSpeechRecognition();
  recognitionGlobal.lang = 'es-ES';
  recognitionGlobal.continuous = true;   // 👈 siempre escuchando
  recognitionGlobal.interimResults = false;

  recognitionGlobal.onstart = () => {
    recognitionRunning = true;
    console.log("🎤 Micrófono global activo");
    mostrarMensajeKiosco('🎤 Micrófono activo: podés dar comandos por voz', 'info');
  };

  recognitionGlobal.onerror = (event) => {
    if (event.error === "aborted") {
      console.log("ℹ️ Reconocimiento abortado intencionalmente");
      return; // ignoramos este caso
    }
    console.warn('Error en reconocimiento de voz:', event.error);
  };

  recognitionGlobal.onresult = (event) => {
    const texto = event.results[event.results.length - 1][0].transcript.toLowerCase().trim();
    const limpio = normalizarTexto(texto);
    console.log("👉 Reconocido:", limpio, "| Step activo:", getStepActivo());
    procesarComandoVoz(limpio);
  };

  recognitionGlobal.onend = () => {
    recognitionRunning = false;
    // 👇 Si se corta por cualquier motivo, lo reiniciamos
    if (!recognitionRunning) 
      {
        console.log("ℹ️ Reconocimiento reiniciado");
        recognitionGlobal.start();
      }
  };

  recognitionGlobal.start();
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
    mostrarMensajeKiosco('⚠️ Tu navegador no soporta reconocimiento de voz', 'warning');
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

function matchOpcion(limpio, numero, ...palabrasClave) {
  const numerosPalabra = {
    1:"uno", 2:"dos", 3:"tres", 4:"cuatro", 5:"cinco",
    6:"seis", 7:"siete", 8:"ocho", 9:"nueve", 10:"diez",
    11:"once", 12:"doce", 13:"trece", 14:"catorce", 15:"quince",
    16:"dieciseis", 17:"diecisiete", 18:"dieciocho", 19:"diecinueve", 20:"veinte",
    21:"veintiuno", 22:"veintidos", 23:"veintitres", 24:"veinticuatro", 25:"veinticinco",
    26:"veintiseis", 27:"veintisiete", 28:"veintiocho", 29:"veintinueve", 30:"treinta"
    // Podés seguir hasta 50 o más si querés
  };

  const palabra = numerosPalabra[numero];

  console.log('🎯 matchOpcion: evaluando coincidencia para opción', numero);

  return (
    limpio.includes(`opcion ${numero}`) ||
    limpio.includes(`opción ${numero}`) ||
    (palabra && (
      limpio.includes(`opcion ${palabra}`) ||
      limpio.includes(`opción ${palabra}`)
    )) ||
    limpio === `${numero}` ||
    limpio === palabra ||
    palabrasClave.some(p => limpio.includes(p))
  );
}

function matchTextoBoton(limpio, btn) {
  const texto = normalizarTexto(btn.textContent)
    .replace(/opcion\s*\d+/i, '')
    .replace(/[\s-]/g, '')
    .trim();
  const comando = limpio.replace(/[\s-]/g, '');

  console.log('🎯 matchTextoBoton: comparando comando vs botón', comando, texto);
  return texto.includes(comando) || comando.includes(texto);
}


function procesarComandoVoz(limpio) {
  const step = getStepActivo();
  console.log("👉 Texto reconocido (normalizado):", limpio, " | Step activo:", step);

// === Step1: Identificación ===
if (step === 'step1') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 0, "qr", "iniciar sesion con qr", "iniciar con qr", "sesion qr", "login qr")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Iniciar sesión con QR', 'success');
      console.log('🎤 Comando reconocido: Iniciar sesión con QR');
    activarEscaneoQRLogin();
    comandoEjecutado = true;
    return;
  }

  if (limpio.includes("continuar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Continuar con DNI', 'success');
      console.log('🎤 Comando reconocido: Continuar con DNI');
    identificarTrabajador();
    comandoEjecutado = true;
    return;
  }

  const numeros = limpio.replace(/\D/g, "");
  if (numeros.length > 6) {
    document.getElementById('dni').value = numeros;
    mostrarMensajeKiosco(`🎤 DNI detectado: ${numeros}`, 'success');
    console.log(`🎤 DNI detectado: ${numeros}`);
    comandoEjecutado = true;
    return;
  }

  if (!comandoEjecutado) {
    console.log("⚠️ Step1: No se reconoció comando válido");
  }
}


// === Step2: Menú principal ===
else if (step === 'step2') {
  let comandoEjecutado = false;

  // 🔄 Limpieza de repeticiones (ej: "tres tres" → "tres")
  limpio = limpio.replace(/\b(\w+)\s+\1\b/g, '$1');

  if (matchOpcion(limpio, 1, "herramienta en mano")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Herramienta en mano', 'success');
      console.log('🎤 Comando reconocido: Herramienta en mano');
    setModoEscaneo('manual');
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 2, "solicitar herramienta", "quiero solicitar", "pedir herramienta")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
      console.log('🎤 Comando reconocido: Solicitar herramienta');
    step5ReturnTarget = 2;
    nextStep(5);
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 3, "ver recursos", "recursos asignados", "mostrar recursos")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
      console.log('🎤 Comando reconocido: Ver recursos asignados');
    cargarRecursos();
    const modalEl = document.getElementById('modalRecursos');
    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInstance.show();
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 4, "volver", "inicio", "regresar", "atrás", "cerrar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver al inicio', 'success');
      console.log('🎤 Comando reconocido: Volver al inicio');
    volverAInicio();
    comandoEjecutado = true;
    return;
  }

  if (!comandoEjecutado) {
    console.log("⚠️ Step2: No se reconoció comando válido");
  }
}


// === Step3: Escaneo QR ===
else if (step === 'step3') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 1, "qr", "escanear")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Escanear QR', 'success');
      console.log('🎤 Comando reconocido: Escanear QR');
    activarEscaneoQR();
    comandoEjecutado = true;
    return;
  }

  if (limpio.includes("cancelar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Cancelar escaneo', 'success');
      console.log('🎤 Comando reconocido: Cancelar escaneo');
    cancelarEscaneoQR();
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar manualmente', 'success');
      console.log('🎤 Comando reconocido: Solicitar manualmente');
    step5ReturnTarget = 3;
    detenerEscaneoQR(5);
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver al menú principal', 'success');
      console.log('🎤 Comando reconocido: Volver al menú principal');
    detenerEscaneoQR(2);
    comandoEjecutado = true;
    return;
  }

  if (!comandoEjecutado) {
    console.log("⚠️ Step3: No se reconoció ningún comando válido");
  }
}


// === Step5: Categorías ===
else if (step === 'step5') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco(
      step5ReturnTarget === 3
        ? '🎤 Comando reconocido: Volver a "Tengo la herramienta en mano"'
        : '🎤 Comando reconocido: Volver al menú principal',
      'success'
    );
    nextStep(step5ReturnTarget);
    comandoEjecutado = true;
    return;
  }

  const botones = document.querySelectorAll('#categoria-buttons button');
  botones.forEach((btn, index) => {
    if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
      btn.click();
      comandoEjecutado = true;
    }
  });

  if (!comandoEjecutado) {
    console.log("⚠️ Step5: No se reconoció ninguna categoría");
  }
}

// === Step6: Subcategorías ===
else if (step === 'step6') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver a categorías', 'success');
      console.log('🎤 Comando reconocido: Volver a categorías');
    nextStep(5);
    comandoEjecutado = true;
    return;
  }

  const botones = document.querySelectorAll('#subcategoria-buttons button');
  botones.forEach((btn, index) => {
    if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
      btn.click();
      comandoEjecutado = true;
    }
  });

  if (!comandoEjecutado) {
    console.log("⚠️ Step6: No se reconoció ninguna subcategoría");
  }
}

// === Step7: Recursos ===
else if (step === 'step7') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver a subcategorías', 'success');
      console.log('🎤 Comando reconocido: Volver a subcategorías');
    nextStep(6);
    comandoEjecutado = true;
    return;
  }

  const botones = document.querySelectorAll('#recurso-buttons button');
  botones.forEach((btn, index) => {
    if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
      btn.click();
      comandoEjecutado = true;
    }
  });

  if (!comandoEjecutado) {
    console.log("⚠️ Step7: No se reconoció ningún recurso");
  }
}

// === Step8: Series ===
else if (step === 'step8') {
  let comandoEjecutado = false;

  if (matchOpcion(limpio, 0, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver a recursos', 'success');
      console.log('🎤 Comando reconocido: Volver a recursos');
    nextStep(7);
    comandoEjecutado = true;
    return;
  }

  const botones = document.querySelectorAll('#serie-buttons button');
  botones.forEach((btn, index) => {
    if (matchOpcion(limpio, index + 1) || matchTextoBoton(limpio, btn)) {
      btn.click();
      comandoEjecutado = true;
    }
  });

  if (!comandoEjecutado) {
    console.log("⚠️ Step8: No se reconoció ninguna serie");
  }
}


  // === Comando global: cerrar modal de recursos ===
  const modalEl = document.getElementById('modalRecursos');
  if (modalEl && modalEl.classList.contains('show')) {
    if (matchOpcion(limpio, 0, "volver", "cerrar", "cerrar recursos")) {
      console.log("✅ Comando global: Cerrar modal de recursos asignados");
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modalInstance.hide();
      mostrarMensajeKiosco('🎤 Comando reconocido: Cerrar recursos asignados', 'success');
      console.log('🎤 Comando reconocido: Cerrar recursos asignados');
      return;
    }
  }
}

// Exponer API pública para entorno de tests y JSDOM
if (typeof window !== 'undefined') {
  window.registrarSerie = window.registrarSerie || registrarSerie;
  window.registrarPorQR = window.registrarPorQR || registrarPorQR;
  window.identificarTrabajador = window.identificarTrabajador || identificarTrabajador;
  window.getStepActivo = window.getStepActivo || getStepActivo;
  window.nextStep = window.nextStep || nextStep;
  window.mostrarMensajeKiosco = window.mostrarMensajeKiosco || mostrarMensajeKiosco;
}

