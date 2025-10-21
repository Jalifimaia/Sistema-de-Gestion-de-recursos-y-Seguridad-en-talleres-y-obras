let scanner;
let isScanning = false; // 👈 flag de estado


function mostrarMensajeKiosco(texto, tipo = 'info') {
  const container = document.getElementById('toastContainer');

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

  // Agregar al contenedor
  container.appendChild(toastEl);

  // Inicializar y mostrar
  const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
  toast.show();

  // Eliminar del DOM cuando se oculta
  toastEl.addEventListener('hidden.bs.toast', () => {
    toastEl.remove();
  });
}




function nextStep(n) {
  // 🔒 Cerrar modal de recursos si está abierto
  const modalEl = document.getElementById('modalRecursos');
  if (modalEl) {
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
      modalInstance.hide();
    }
  }

  // 🔒 Detener escaneo QR si no estamos en step3
  if (n !== 3) detenerEscaneoQR();

  // 🔄 Cambiar step activo
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');

  // ⚡ Acciones específicas por step
  if (n === 2) cargarMenuPrincipal();
  if (n === 5) cargarCategorias();

  // 🎤 El micrófono global sigue activo en todo momento
}






function identificarTrabajador() {
  const dni = document.getElementById('dni').value;
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '/terminal/identificar', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

  xhr.onload = function () {
    try {
      const res = JSON.parse(xhr.responseText);
      if (res.success) {
        // Usuario válido (rol trabajador + estado Alta)
        localStorage.setItem('id_usuario', res.usuario.id);
        nextStep(2);
        document.getElementById('saludo-trabajador').textContent = `Hola ${res.usuario.name}`;
      } else {
        // Mensajes diferenciados según backend
        if (res.message === 'Usuario no encontrado') {
          mostrarMensajeKiosco('❌ Usuario no encontrado en el sistema', 'danger');
        } else if (res.message === 'Este usuario no tiene permisos para usar el kiosco') {
          mostrarMensajeKiosco('⚠️ Este usuario no tiene permisos para usar el kiosco', 'warning');
        } else if (res.message === 'El usuario no está en estado Alta y no puede usar el kiosco') {
          mostrarMensajeKiosco('⛔ El usuario no está en estado Alta y no puede usar el kiosco', 'danger');
        } else {
          mostrarMensajeKiosco(res.message || 'Error al identificar al trabajador', 'danger');
        }
      }
    } catch (e) {
      console.error('Error parseando respuesta identificarTrabajador:', e, xhr.responseText);
      mostrarMensajeKiosco('Error al identificar al trabajador', 'danger');
    }
  };

  xhr.send('dni=' + encodeURIComponent(dni));
}



function simularEscaneo() {
  //alert("Simulación de escaneo QR");
  nextStep(5);
}

function cargarCategorias() {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', '/terminal/categorias', true);

  xhr.onload = function () {
    try {
      const categorias = JSON.parse(xhr.responseText);
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
    }
  };

  xhr.send();
}

function cargarRecursos() {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!id_usuario) return;

  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/recursos-asignados/${id_usuario}`, true);

  xhr.onload = function () {
    try {
      const recursos = JSON.parse(xhr.responseText);
      const tabla = document.getElementById('tablaRecursos');
      tabla.innerHTML = '';

      if (recursos.length === 0) {
        tabla.innerHTML = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
        return;
      }

      recursos.forEach(r => {
        tabla.innerHTML += `<tr>
          <td>${r.categoria}</td>
          <td>${r.subcategoria} / ${r.recurso}</td>
          <td>${r.serie}</td>
          <td> ${r.fecha_prestamo || '-'}</td>
          <td> ${r.fecha_devolucion || '-'}</td>
          <td>
            <button class="btn btn-sm btn-outline-danger" onclick="devolverRecurso(${r.detalle_id})">
              Devolver
            </button>
          </td>
        </tr>`;
      });

    } catch (e) {
      mostrarMensajeKiosco('Error al cargar recursos asignados', 'danger');
    }
  };

  xhr.send();
}


function mostrarRecursosAsignados(recursos) {
  const contenedor = document.getElementById('contenedorRecursos');
  contenedor.innerHTML = '';

  recursos.forEach(r => {
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';

    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title mb-1">${r.recurso}</h5>
        <p class="card-text mb-1">Serie: <strong>${r.serie}</strong></p>
        <p class="card-text mb-1">Categoría: ${r.categoria}</p>
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
  if (!confirm('¿Confirmás que querés devolver este recurso?')) return;

  fetch(`/terminal/devolver/${detalleId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      mostrarMensajeKiosco('✅ Recurso devuelto correctamente', 'success');
      cargarRecursos(); // refresca la tabla de recursos asignados
    } else {
      mostrarMensajeKiosco(data.message || 'Error al devolver recurso', 'danger');
    }
  })
  .catch(() => {
    mostrarMensajeKiosco('Error de red al devolver recurso', 'danger');
  });
}


function seleccionarCategoria(categoriaId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/subcategorias-disponibles/${categoriaId}`, true);

  xhr.onload = function () {
    try {
      const subcategorias = JSON.parse(xhr.responseText);
      const contenedor = document.getElementById('subcategoria-buttons');
      contenedor.innerHTML = '';

      nextStep(6);

      subcategorias.forEach((s, index) => {
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
      const contenedor = document.getElementById('recurso-buttons');
      contenedor.innerHTML = '';

      nextStep(7);

      recursos.forEach((r, index) => {
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
    }
  };

  xhr.send();
}






function registrarSerie(serieId) {
  const id_usuario = localStorage.getItem('id_usuario');
  if (!id_usuario) {
    mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
    return;
  }

  if (!confirm('¿Confirmás que querés solicitar esta herramienta?')) return;

  fetch(`/terminal/prestamos/${id_usuario}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ series: [serieId] })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      mostrarMensajeKiosco('✅ Recurso asignado correctamente', 'success');
      nextStep(2);
    } else {
      mostrarMensajeKiosco(data.message || 'Error al registrar recurso', 'danger');
    }
  })
  .catch(() => {
    mostrarMensajeKiosco('Error de red al registrar recurso', 'danger');
  });
}




/*
// Listener global para debug de clicks
document.addEventListener('click', (e) => {
   .log('[DOC CLICK]', e.target, e);
}, { capture: true });
*/

// Delegación para subcategorías
document.getElementById('subcategoria-buttons').addEventListener('click', function (e) {
  const btn = e.target.closest('[data-subcategoria-id]');
  if (btn) {
    seleccionarSubcategoria(btn.dataset.subcategoriaId);
  }
});

// Delegación para recursos
document.getElementById('recurso-buttons').addEventListener('click', function (e) {
  const btn = e.target.closest('[data-recurso-id]');
  if (btn) {
    seleccionarRecurso(btn.dataset.recursoId);
  }
});

// Delegación para series
document.getElementById('serie-buttons').addEventListener('click', function (e) {
  const btn = e.target.closest('[data-serie-id]');
  if (btn) {
    registrarSerie(btn.dataset.serieId);
  }
});


function activarEscaneoQR() {
  const qrContainer = document.getElementById('qr-reader');
  const btnEscanear = document.getElementById('btn-escanear-qr');
  const btnCancelar = document.getElementById('btn-cancelar-qr');
  const textoCamara = document.getElementById('texto-camara-activa');

  if (!qrContainer) {
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
    return;
  }

  fetch(`/terminal/registrar-por-qr`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ codigo_qr: codigoQR, id_usuario })
  })
  .then(res => res.json())
  .then(data => {
    console.log('Respuesta registrarPorQR:', data);

    if (data.success) {
      // Caso éxito
      const mensaje = `✅ Recurso registrado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`;
      mostrarMensajeKiosco(mensaje, 'success');
      nextStep(2);
    } else {
      // Mensajes diferenciados según backend
      if (data.message === 'QR no encontrado') {
        mostrarMensajeKiosco('❌ QR no encontrado en el sistema', 'danger');
      } else if (data.message === 'Este recurso ya está asignado') {
        mostrarMensajeKiosco(`⚠️ Este recurso ya está asignado: ${data.recurso || ''} ${data.serie ? '- Serie: ' + data.serie : ''}`, 'warning');
      } else {
        mostrarMensajeKiosco(data.message || 'Error al registrar recurso por QR', 'danger');
      }
    }
  })
  .catch(err => {
    console.error('Error en registrarPorQR:', err);
    mostrarMensajeKiosco('Error de red al registrar recurso por QR', 'danger');
  });
}




function detenerEscaneoQR(next = null) {
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
      if (next) nextStep(next); // 👈 avanzar al paso cuando termina
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

  if (!qrContainer || !wrapper || isScanning) return;

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
      } else if (data.message === 'Este usuario no tiene permisos para usar el kiosco') {
        mostrarMensajeKiosco('⚠️ Este usuario no tiene permisos para usar el kiosco', 'warning');
      } else if (data.message === 'El usuario no está en estado Alta y no puede usar el kiosco') {
        mostrarMensajeKiosco('⛔ El usuario no está en estado Alta y no puede usar el kiosco', 'danger');
      } else {
        mostrarMensajeKiosco(data.message || 'Error al identificar por QR', 'danger');
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
    titulo.textContent = '📦 Tengo la herramienta en mano';
    detenerEscaneoQR();
    // 👇 si luego vamos a solicitar manualmente (step5), el volver debe regresar acá (step3)
    step5ReturnTarget = 3;
  } else {
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
    { id: 1, texto: "📦 Tengo la herramienta en mano", accion: () => setModoEscaneo('manual'), clase: "btn-outline-success" },
    { id: 2, texto: "🛠️ Quiero solicitar una herramienta", accion: () => {
    const id_usuario = localStorage.getItem('id_usuario');
    if (!id_usuario) {
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
        mostrarMensajeKiosco(data.message || 'No se puede solicitar herramientas', 'warning');
        return;
      }

      // ✅ Si pasa la validación, continuar
      step5ReturnTarget = 2;
      nextStep(5);
    })
    .catch(() => {
      mostrarMensajeKiosco('Error de red al validar EPP', 'danger');
    });
  }, clase: "btn-outline-primary" },
    { id: 3, texto: "📋 Ver recursos asignados", accion: () => {
        cargarRecursos();
        const modalEl = document.getElementById('modalRecursos');
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
      }, clase: "btn-info" },
    { id: 4, texto: "🔙 Volver", accion: () => volverAInicio(), clase: "btn-secondary" }
  ];

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
  modalInstance.show();
}


// 🔧 Normalizar texto (quita acentos)
function normalizarTexto(str) {
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
    if (!recognitionRunning) recognitionGlobal.start();
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
    activarEscaneoQRLogin();
    comandoEjecutado = true;
    return;
  }

  if (limpio.includes("continuar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Continuar con DNI', 'success');
    identificarTrabajador();
    comandoEjecutado = true;
    return;
  }

  const numeros = limpio.replace(/\D/g, "");
  if (numeros.length > 6) {
    document.getElementById('dni').value = numeros;
    mostrarMensajeKiosco(`🎤 DNI detectado: ${numeros}`, 'success');
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
    setModoEscaneo('manual');
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 2, "solicitar herramienta", "quiero solicitar", "pedir herramienta")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar herramienta', 'success');
    step5ReturnTarget = 2;
    nextStep(5);
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 3, "ver recursos", "recursos asignados", "mostrar recursos")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Ver recursos asignados', 'success');
    cargarRecursos();
    const modalEl = document.getElementById('modalRecursos');
    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInstance.show();
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 4, "volver", "inicio", "regresar", "atrás", "cerrar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver al inicio', 'success');
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
    activarEscaneoQR();
    comandoEjecutado = true;
    return;
  }

  if (limpio.includes("cancelar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Cancelar escaneo', 'success');
    cancelarEscaneoQR();
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 2, "manual", "solicitar manualmente")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Solicitar manualmente', 'success');
    step5ReturnTarget = 3;
    detenerEscaneoQR(5);
    comandoEjecutado = true;
    return;
  }

  if (matchOpcion(limpio, 3, "volver", "atrás", "regresar")) {
    mostrarMensajeKiosco('🎤 Comando reconocido: Volver al menú principal', 'success');
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
      return;
    }
  }
}
