function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  };
  return String(text).replace(/[&<>"']/g, m => map[m]);
}

const colores = window.colores || [];
const nombreRecurso = window.nombreRecurso || '';
const descripcionRecurso = window.descripcionRecurso || '';
const requiereTalle = window.requiereTalle || false;
const tallesPorTipo = window.tallesPorTipo || {};

let validacionActiva = false;

function iniciales(texto) {
  return texto
    .trim()
    .split(/\s+/)
    .map(p => p[0])
    .join('')
    .toUpperCase();
}

// 👉 Genera preview de códigos
function generarPreviewCodigoPorFila() {
  const version = document.getElementById('version')?.value || '';
  const lote = document.getElementById('lote')?.value || '';
  const anio = document.getElementById('anio')?.value || '';
  const anio2d = anio ? anio.toString().slice(-2) : '';
  const inicialesNombre = iniciales(nombreRecurso);
  const inicialesDesc = iniciales(descripcionRecurso);
  const loteNum = lote.toString().padStart(2, '0');

  const contadoresPorColor = {};

  document.querySelectorAll('#combinacionesBody tr').forEach((fila) => {
    const color = fila.querySelector('.color-select')?.value || '';

    if (!contadoresPorColor[color]) {
      contadoresPorColor[color] = 1;
    }

    const correlativo = contadoresPorColor[color].toString().padStart(2, '0');
    const codigo = `${inicialesNombre}-V${version}-${inicialesDesc}-${anio2d}-${loteNum}-${correlativo}`;
    const campoCodigo = fila.querySelector('.codigo-preview');
    if (campoCodigo) campoCodigo.value = codigo;

    contadoresPorColor[color]++;
  });
}

// 👉 Validar combinaciones
function validarDuplicados(mostrarAlertas = false) {
  // Si la validación no está activa y no pedimos mostrar alertas, asumimos OK
  if (!validacionActiva && !mostrarAlertas) return true;

  const combinaciones = new Set();
  let hayDuplicados = false;
  let hayCantidadCero = false;
  let hayCamposFaltantes = false;
  let hayTipoTalleInconsistente = false;
  let hayTipoTalleIncorrecto = false;

  let tipoTalleGlobal = null;

  const recursoSubcategoria = (document.getElementById('subcategoriaNombre')?.textContent || '').toLowerCase();
  const requiereTalleLocal = ['chaleco', 'botas'].includes(recursoSubcategoria);
  const tipoEsperado = recursoSubcategoria === 'chaleco' ? 'Ropa' :
                       recursoSubcategoria === 'botas' ? 'Calzado' : null;

  document.querySelectorAll('#combinacionesBody tr').forEach((fila, index) => {
    const talle = requiereTalleLocal ? (fila.querySelector('.talle-select')?.value || '') : '';
    const tipoTalle = requiereTalleLocal ? (fila.querySelector('.tipo-talle')?.value || '') : '';
    const color = fila.querySelector('.color-select')?.value || '';
    const cantidad = parseInt(fila.querySelector('.cantidad-input')?.value || '0');
    const clave = `${talle}-${color}`.toLowerCase();

    let error = false;

    if (combinaciones.has(clave)) {
      hayDuplicados = true;
      error = true;
    } else {
      combinaciones.add(clave);
    }

    if (cantidad <= 0) {
      hayCantidadCero = true;
      error = true;
    }

    if (!color || (requiereTalleLocal && (!talle || !tipoTalle))) {
      hayCamposFaltantes = true;
      error = true;
    }

    if (requiereTalleLocal) {
      if (index === 0) {
        tipoTalleGlobal = tipoTalle;
      } else if (tipoTalle && tipoTalleGlobal && tipoTalle !== tipoTalleGlobal) {
        hayTipoTalleInconsistente = true;
        error = true;
      }
    }

    if (requiereTalleLocal && tipoEsperado && tipoTalle &&
      !['otro', tipoEsperado.toLowerCase()].includes(tipoTalle.toLowerCase())) {
      hayTipoTalleIncorrecto = true;
      error = true;
    }

    fila.classList.toggle('table-danger', error);
  });

  if (mostrarAlertas) {
    if (hayDuplicados) alert('⚠️ Hay combinaciones repetidas.'); //no sale el alert
    if (hayCantidadCero) alert('⚠️ Hay cantidades en cero.'); //no sale el alert
    if (hayCamposFaltantes) alert('⚠️ Faltan campos obligatorios.'); //no sale el alert
    if (hayTipoTalleInconsistente) alert('⚠️ Todas las filas deben usar el mismo tipo de talle.'); //no sale el alert

    if (hayTipoTalleIncorrecto) {
      const modalEl = document.getElementById('modalErrorTipoTalle');
      if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalEl).show();
      }
    }
  }

  const hayErrores = hayDuplicados || hayCantidadCero || hayCamposFaltantes || hayTipoTalleInconsistente || hayTipoTalleIncorrecto;
  return !hayErrores;
}


// 👉 Actualizar talles dinámicos
function actualizarTalle(selectTipo) {
  const tipo = selectTipo.value;
  const fila = selectTipo.closest('tr');
  const selectTalle = fila.querySelector('.talle-select');
  if (!selectTalle) return;
  selectTalle.innerHTML = tallesPorTipo[tipo]?.map(t => `<option value="${t}">${t}</option>`).join('') || '';
}

// 👉 Validar filas
function validarCombinaciones() {
  const filas = document.querySelectorAll('#combinacionesBody tr');
  let hayFilaValida = false;
  let hayFilaInvalida = false;

  filas.forEach(fila => {
    const color = fila.querySelector('.color-select');
    const cantidad = fila.querySelector('.cantidad-input');
    const tieneColor = color && color.value.trim() !== '';
    const tieneCantidad = cantidad && parseInt(cantidad.value) > 0;

    if (tieneColor && tieneCantidad) hayFilaValida = true;
    else hayFilaInvalida = true;
  });

  const btnGuardar = document.getElementById('btnGuardar');
  btnGuardar.disabled = !hayFilaValida || hayFilaInvalida;
}

// 👉 Actualiza el estado de los botones de borrar: si solo hay 1 fila, la primera queda deshabilitada; si hay >1, todas habilitadas
function updateDeleteButtons() {
  const filas = document.querySelectorAll('#combinacionesBody tr');
  if (filas.length === 0) return;
  if (filas.length === 1) {
    const btn = filas[0].querySelector('.btn-delete');
    if (btn) btn.disabled = true;
    return;
  }
  filas.forEach(fila => {
    const btn = fila.querySelector('.btn-delete');
    if (btn) btn.disabled = false;
  });
}



// 👉 Agregar fila
window.agregarFila = function () {
  const tbody = document.getElementById('combinacionesBody');
  const row = document.createElement('tr');

  const selectColor = colores.map(c =>
    `<option value="${escapeHtml(c.id)}">${escapeHtml(c.nombre)}</option>`).join('');

  let cols = '';

  if (requiereTalle) {
    let tipoOptions = Object.keys(tallesPorTipo)
      .map(tipo => `<option value="${tipo}">${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</option>`)
      .join('');

    cols += `
      <td>
        <select class="form-select tipo-talle" onchange="actualizarTalle(this)">
          <option value="" disabled selected>Tipo de talle</option>
          ${tipoOptions}
        </select>
      </td>
      <td>
        <select class="form-select talle-select">
          <option value="" disabled selected>Seleccione tipo primero</option>
        </select>
      </td>`;
  }

  cols += `
    <td>
      <select class="form-select color-select" onchange="generarPreviewCodigoPorFila()">
        <option value="" disabled selected>Seleccione o escriba</option>
        ${selectColor}
      </select>
    </td>
    <td><input type="number" class="form-control cantidad-input" min="0" value="0"></td>
    <td><input type="text" class="form-control codigo-preview" disabled></td>
    <td><button type="button" class="btn btn-sm btn-danger btn-delete" onclick="this.closest('tr').remove(); generarPreviewCodigoPorFila(); validarDuplicados(); updateDeleteButtons();">✕</button></td>
  `;

  row.innerHTML = cols;
  tbody.appendChild(row);

  try {
    if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
      $(row).find('select').select2({ tags: true, width: '100%' });
    }
  } catch (e) {}
  generarPreviewCodigoPorFila();
  validarDuplicados(false);
  validarCombinaciones();
  updateDeleteButtons();
};

// 👉 Inicialización
document.addEventListener('DOMContentLoaded', () => {
  agregarFila();
  updateDeleteButtons();

  const tbody = document.getElementById('combinacionesBody');
  if (tbody) {
    tbody.addEventListener('input', ev => {
      const t = ev.target;
      if (t.matches('.cantidad-input, .color-select, .talle-select, .tipo-talle')) {
        validacionActiva = true;
        generarPreviewCodigoPorFila();
        validarDuplicados(false);
        validarCombinaciones();
      }
    });

    tbody.addEventListener('change', ev => {
      const t = ev.target;
      if (t.matches('.color-select, .talle-select, .tipo-talle')) {
        validacionActiva = true;
        generarPreviewCodigoPorFila();
        validarDuplicados(false);
        validarCombinaciones();
      }
    });
  }

  // valida y muestra el error debajo del input usando invalid-feedback (Bootstrap)
window.validarFechaAdquisicionInline = function() {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('fecha_adquisicion_error');
  if (!input || !errDiv) return true;

  // limpiar estado previo
  errDiv.textContent = '';
  errDiv.style.display = 'none';
  input.classList.remove('is-invalid');

  const valor = input.value;
  if (!valor) return true;

  const today = new Date(); today.setHours(0,0,0,0);
  const fecha = new Date(valor); fecha.setHours(0,0,0,0);

  if (fecha > today) {
    const msg = 'La fecha de adquisición no puede ser posterior al día de hoy.';
    errDiv.textContent = msg;
    errDiv.style.display = 'block';
    input.classList.add('is-invalid');
    return false;
  }

  return true;
};

// mostrar error de servidor para la fecha (usa el mismo div)
function showFechaServerError(msg) {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('fecha_adquisicion_error');
  if (!input || !errDiv) return;
  errDiv.textContent = msg || 'Fecha inválida.';
  errDiv.style.display = 'block';
  input.classList.add('is-invalid');
}

// limpiar mensaje de fecha
function clearFechaError() {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('fecha_adquisicion_error');
  if (!input || !errDiv) return;
  errDiv.textContent = '';
  errDiv.style.display = 'none';
  input.classList.remove('is-invalid');
}

// listeners: limpiar y revalidar al cambiar
const fechaInput = document.getElementById('fecha_adquisicion');
if (fechaInput) {
  fechaInput.addEventListener('input', () => clearFechaError());
  fechaInput.addEventListener('change', () => validarFechaAdquisicionInline());
}

function clearFormError() {
  const prev = document.getElementById('form_series_error');
  if (prev) prev.remove();
}
function showFormError(msg) {
  clearFormError();
  const formEl = document.getElementById('formSeries');
  if (!formEl) return;
  const div = document.createElement('div');
  div.id = 'form_series_error';
  div.className = 'alert alert-danger mt-2';
  div.textContent = msg || 'Ocurrió un error al guardar.';
  formEl.prepend(div);
}
function showFechaServerError(msg) {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('fecha_adquisicion_error');
  if (!input || !errDiv) return;
  errDiv.textContent = msg || 'Fecha inválida.';
  errDiv.style.display = 'block';
  input.classList.add('is-invalid');
}
function clearFechaError() {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('fecha_adquisicion_error');
  if (!input || !errDiv) return;
  errDiv.textContent = '';
  errDiv.style.display = 'none';
  input.classList.remove('is-invalid');
}


  const form = document.getElementById('formSeries');

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // validar fecha de adquisición antes de continuar
      if (!window.validarFechaAdquisicionInline()) {
        document.getElementById('fecha_adquisicion')?.focus();
        return;
      }

      // validar duplicados sin mostrar alertos; si falla mostramos mensaje inline y abortamos
      const ok = validarDuplicados(false);
      if (!ok) {
        showFormError('Corrigí las combinaciones marcadas en rojo antes de continuar.');
        return;
      }

      const filas = document.querySelectorAll('#combinacionesBody tr');
      const combinaciones = [];

      filas.forEach(fila => {
        const tipoTalle = requiereTalle ? (fila.querySelector('.tipo-talle')?.value || '') : null;
        const talle = requiereTalle ? (fila.querySelector('.talle-select')?.value || '') : null;
        const color = fila.querySelector('.color-select')?.value || '';
        const cantidad = fila.querySelector('.cantidad-input')?.value || '';

        if (color && cantidad > 0) {
          combinaciones.push({ tipo_talle: tipoTalle, talle, color_nombre: color, cantidad });
        }
      });

      if (combinaciones.length === 0) {
        showFormError('⚠️ No hay combinaciones válidas para guardar.');
        return;
      }


      const payload = {
        id_recurso: document.querySelector('[name="id_recurso"]').value,
        combinaciones: JSON.stringify(combinaciones),
        version: document.getElementById('version').value,
        anio: document.getElementById('anio').value,
        lote: document.getElementById('lote').value,
        fecha_adquisicion: document.getElementById('fecha_adquisicion').value,
        fecha_vencimiento: document.getElementById('fecha_vencimiento').value,
        id_estado: document.querySelector('[name="id_estado"]').value,
      };

      clearFormError(); // limpia errores previos
      fetch(form.action, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify(payload),
      })
      .then(async res => {
        const data = await res.json();
        console.log("📦 Respuesta del servidor:", data);

        if (!res.ok) {
          // 422 o cualquier error: priorizamos mensajes de validación del backend
          // si backend devuelve estructura errors.fecha_adquisicion o message con 'fecha', lo mostramos debajo del input
          if (data && data.errors && data.errors.fecha_adquisicion) {
            showFechaServerError(Array.isArray(data.errors.fecha_adquisicion) ? data.errors.fecha_adquisicion.join(' ') : String(data.errors.fecha_adquisicion));
            return;
          }
          const serverMsg = data && data.message ? String(data.message) : null;
          if (serverMsg && /fecha/i.test(serverMsg)) {
            showFechaServerError(serverMsg.includes('posterior') ? 'La fecha de adquisición no puede ser mayor a la fecha actual.' : serverMsg);
            return;
          }

          // otros errores de validación del backend o estado 422
          showFormError(serverMsg || 'Error al guardar las series. Revisá los campos marcados.');
          return;
        }

        // éxito: limpiamos errores y mostramos modal
        clearFormError();
        clearFechaError();
        const modalEl = document.getElementById('modalSeriesAgregadas');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
          new bootstrap.Modal(modalEl).show();
        }
      })
      .catch(err => {
        console.error("❌ Error en la petición:", err);
        // mostrar mensaje genérico inline (sin alert)
        showFormError('Error de red o del servidor. Revisá la consola para más detalles.');
      });
    });
  }
});


console.log('✅ serieRecurso.js cargado');
