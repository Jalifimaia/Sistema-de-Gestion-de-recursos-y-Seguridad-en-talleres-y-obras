let scanner;
let isScanning = false; // 👈 flag de estado


function mostrarMensajeKiosco(texto, tipo = 'warning') {
  const mensaje = document.getElementById('mensaje-kiosco');
  mensaje.className = `alert alert-${tipo} text-center`;
  mensaje.textContent = texto;
  mensaje.classList.remove('d-none');

  setTimeout(() => {
    mensaje.classList.add('d-none');
  }, 4000);
}

function nextStep(n) {
  if (n !== 3) detenerEscaneoQR(); // 👈 apaga escáner si salís del step3
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');

  if (n === 5) cargarCategorias();
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
        localStorage.setItem('id_usuario', res.usuario.id);
        nextStep(2);
        document.getElementById('saludo-trabajador').textContent = `Hola ${res.usuario.name}`;

      } else {
        mostrarMensajeKiosco(res.message, 'danger');
      }
    } catch (e) {
      // .error(xhr.responseText);
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

      categorias.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-dark btn-lg m-2';
        btn.textContent = cat.nombre_categoria;
        btn.dataset.categoriaId = cat.id; // 👈 clave para delegación si querés
        // ✅ Enganche directo
        btn.onclick = () => seleccionarCategoria(cat.id);
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

  xhr.onerror = function () {
    mostrarMensajeKiosco('Error de red al cargar subcategorías', 'danger');
  };

  xhr.onload = function () {
    try {
      const subcategorias = JSON.parse(xhr.responseText);

      const contenedor = document.getElementById('subcategoria-buttons');
      if (!contenedor) {
        mostrarMensajeKiosco('No se encontró el contenedor de subcategorías', 'danger');
        return;
      }
      contenedor.innerHTML = '';

      const disponibles = subcategorias.filter(s => s.disponibles > 0);
      const noDisponibles = subcategorias.filter(s => s.disponibles === 0);

      nextStep(6);

      if (disponibles.length === 0 && noDisponibles.length === 0) {
        contenedor.innerHTML = `
          <p class="text-warning">⚠️ No hay subcategorías registradas en esta categoría.</p>
          <button class="btn btn-outline-secondary mt-3" onclick="nextStep(5)">Volver a categorías</button>
        `;
        return;
      }

      if (disponibles.length > 0) {
        contenedor.innerHTML += `<h5 class="mt-2">🟢 Subcategorías con recursos disponibles:</h5>`;
        const grupo = document.createElement('div');
        grupo.className = 'd-flex flex-wrap gap-2';

        disponibles.forEach(s => {
          const btn = document.createElement('button');
          btn.className = 'btn btn-outline-dark btn-lg';
          btn.textContent = `${s.nombre} (${s.disponibles})`;
          btn.dataset.subcategoriaId = s.id; // 👈 clave para delegación
          grupo.appendChild(btn);
        });

        contenedor.appendChild(grupo);
      }

      if (noDisponibles.length > 0) {
        contenedor.innerHTML += `<h5 class="mt-4">🚫 No disponibles:</h5>`;
        const lista = document.createElement('ul');
        lista.className = 'text-muted';

        noDisponibles.forEach(s => {
          const item = document.createElement('li');
          item.textContent = s.nombre;
          lista.appendChild(item);
        });

        contenedor.appendChild(lista);
      }

    } catch (e) {
      console.error('Error parseando subcategorías:', e, xhr.responseText);
      mostrarMensajeKiosco('No se pudieron cargar las subcategorías', 'danger');
    }
  };

  xhr.send();
}


function seleccionarSubcategoria(subcategoriaId) {
  console.log('→ Entrando a seleccionarSubcategoria con ID:', subcategoriaId);

  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/recursos-disponibles/${subcategoriaId}`, true);

  xhr.onerror = function () {
    mostrarMensajeKiosco('Error de red al cargar recursos', 'danger');
  };

  xhr.onload = function () {
    try {
      const recursos = JSON.parse(xhr.responseText);

      const contenedor = document.getElementById('recurso-buttons');
      if (!contenedor) {
        mostrarMensajeKiosco('No se encontró el contenedor de recursos', 'danger');
        return;
      }
      contenedor.innerHTML = '';

      const disponibles = recursos.filter(r => r.disponibles > 0);
      const noDisponibles = recursos.filter(r => r.disponibles === 0);

      nextStep(7);

      if (disponibles.length === 0 && noDisponibles.length === 0) {
        contenedor.innerHTML = `
          <p class="text-warning">⚠️ No hay recursos registrados en esta subcategoría.</p>
          <button class="btn btn-outline-secondary mt-3" onclick="nextStep(6)">Volver a subcategorías</button>
        `;
        return;
      }

      if (disponibles.length > 0) {
        contenedor.innerHTML += `<h5 class="mt-2">🟢 Recursos disponibles:</h5>`;
        const grupo = document.createElement('div');
        grupo.className = 'd-flex flex-wrap gap-2';

        disponibles.forEach(r => {
          const btn = document.createElement('button');
          btn.className = 'btn btn-outline-success btn-lg';
          btn.textContent = `${r.nombre} (${r.disponibles})`;
          btn.dataset.recursoId = r.id; // 👈 clave para delegación
          grupo.appendChild(btn);
        });

        contenedor.appendChild(grupo);
      }

      if (noDisponibles.length > 0) {
        contenedor.innerHTML += `<h5 class="mt-4">🚫 No disponibles:</h5>`;
        const lista = document.createElement('ul');
        lista.className = 'text-muted';

        noDisponibles.forEach(r => {
          const item = document.createElement('li');
          item.textContent = r.nombre;
          lista.appendChild(item);
        });

        contenedor.appendChild(lista);
      }

    } catch (e) {
      console.error('❌ Error parseando recursos:', e, xhr.responseText);
      mostrarMensajeKiosco('No se pudieron cargar los recursos', 'danger');
    }
  };

  xhr.send();
}



function seleccionarRecurso(recursoId) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `/terminal/series/${recursoId}`, true);

  xhr.onerror = function () {
    mostrarMensajeKiosco('Error de red al cargar series', 'danger');
  };

  xhr.onload = function () {
    try {
      const series = JSON.parse(xhr.responseText);

      const contenedor = document.getElementById('serie-buttons');
      if (!contenedor) {
        mostrarMensajeKiosco('No se encontró el contenedor de series', 'danger');
        return;
      }
      contenedor.innerHTML = '';

      nextStep(8);

      if (series.length === 0) {
        contenedor.innerHTML = `
          <p class="text-danger">🚫 No hay series disponibles para el recurso seleccionado.</p>
          <button class="btn btn-outline-secondary mt-3" onclick="nextStep(7)">Volver a recursos</button>
        `;
        return;
      }

      const titulo = document.createElement('h5');
      titulo.className = 'mb-3';
      titulo.textContent = `🔢 Seleccioná la serie disponible (${series.length} encontradas)`;
      contenedor.appendChild(titulo);

      const grupo = document.createElement('div');
      grupo.className = 'd-flex flex-wrap gap-2';

      series.forEach(s => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-success btn-lg';
        btn.textContent = s.nro_serie || s.codigo || `Serie ${s.id}`;
        btn.dataset.serieId = s.id;

        // ✅ Enganche explícito del evento


        grupo.appendChild(btn);
      });

      contenedor.appendChild(grupo);

    } catch (e) {
      mostrarMensajeKiosco('No se pudieron cargar las series disponibles', 'danger');
    }
  };

  xhr.send();
}


function registrarSerie(serieId) {
  const dni = document.getElementById('dni').value;
  if (!dni) {
    mostrarMensajeKiosco('⚠️ No hay trabajador identificado', 'danger');
    return;
  }

  if (!confirm('¿Confirmás que querés solicitar esta herramienta?')) return;

  fetch(`/terminal/prestamos/${dni}`, {
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
  fetch(`/terminal/registrar-por-qr`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ codigo_qr: codigoQR, dni: document.getElementById('dni').value })
  })
  .then(res => res.json())
  .then(data => {
  if (data.success) {
    const mensaje = `✅ Recurso registrado: ${data.recurso} - Serie: ${data.serie}`;
    mostrarMensajeKiosco(mensaje, 'success');
    nextStep(2);
  } else {
    const mensaje = data.recurso && data.serie
      ? `⚠️ Ya asignado: ${data.recurso} - Serie: ${data.serie}`
      : (data.message || 'QR no válido');
    mostrarMensajeKiosco(mensaje, 'danger');
  }

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



function setModoEscaneo(modo) {
  const titulo = document.getElementById('titulo-step3');
  if (modo === 'manual') {
    titulo.textContent = '📦 Tengo la herramienta en mano';
    detenerEscaneoQR();
  } else {
    titulo.textContent = '📷 Escanear Recurso';
    activarEscaneoQR();
  }
  nextStep(3);
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
