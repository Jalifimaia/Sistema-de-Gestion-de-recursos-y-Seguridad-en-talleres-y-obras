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
    if (hayDuplicados) alert('⚠️ Hay combinaciones repetidas.');
    if (hayCantidadCero) alert('⚠️ Hay cantidades en cero.');
    if (hayCamposFaltantes) alert('⚠️ Faltan campos obligatorios.');
    if (hayTipoTalleInconsistente) alert('⚠️ Todas las filas deben usar el mismo tipo de talle.');

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
    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); generarPreviewCodigoPorFila(); validarDuplicados()">✕</button></td>
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
};

// 👉 Inicialización
document.addEventListener('DOMContentLoaded', () => {
  agregarFila();

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

  const form = document.getElementById('formSeries');
  if (form) {
    form.addEventListener('submit', function (e) {
  e.preventDefault();

  // Si la validación falla (y ya mostramos modal/alerts), no enviamos la petición
  const ok = validarDuplicados(true);
  if (!ok) return;

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
    alert('⚠️ No hay combinaciones válidas para guardar.');
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
      console.error("⚠️ Error del servidor:", data);
      throw new Error(data.message || 'Error al guardar las series.');
    }

    const modalEl = document.getElementById('modalSeriesAgregadas');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      new bootstrap.Modal(modalEl).show();
    }
  })
  .catch(err => {
    console.error("❌ Error en la petición:", err);
    alert('❌ Error al guardar. Revisá la consola para más detalles.');
  });
});

  }
});

console.log('✅ serieRecurso.js cargado');
