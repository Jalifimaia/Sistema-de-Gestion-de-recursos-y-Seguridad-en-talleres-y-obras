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

function iniciales(texto) {
  return texto
    .trim()
    .split(/\s+/)
    .map(p => p[0])
    .join('')
    .toUpperCase();
}

// Genera preview de códigos
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

// Mostrar/ocultar mensaje de error de combinaciones
function mostrarErrorCombinaciones(mostrar, mensaje = '') {
  const errorDiv = document.getElementById('error-combinaciones');
  if (!errorDiv) return;
  
  if (mostrar && mensaje) {
    errorDiv.textContent = mensaje;
    errorDiv.classList.remove('d-none');
  } else {
    errorDiv.classList.add('d-none');
  }
}

// Helper: mostrar/ocultar error para un campo
function setFieldError(field, show) {
  const errorEl = document.getElementById('error-' + field.id);
  if (!errorEl) return;

  if (show) {
    errorEl.classList.remove('d-none');
    field.classList.add('is-invalid');
    field.setAttribute('aria-invalid', 'true');
  } else {
    errorEl.classList.add('d-none');
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
  }
}

// Validar combinaciones
function validarDuplicados(mostrarErrores = false) {
  console.log('▶ validarDuplicados() llamado. mostrarErrores:', mostrarErrores);

  const combinaciones = new Set();
  let hayDuplicados = false;
  let hayCantidadCero = false;
  let hayCamposFaltantes = false;
  let hayTipoTalleInconsistente = false;
  let hayTipoTalleIncorrecto = false;

  let tipoTalleGlobal = null;

  const requiereTalleLocal = Boolean(window.requiereTalle);
  const sub = (document.getElementById('subcategoriaNombre')?.textContent || '').toLowerCase();
  const tipoEsperado = sub === 'botas' ? 'Calzado' : (sub === 'chaleco' ? 'Ropa' : null);

  console.log('🔍 Estado de validación:', { requiereTalleLocal, tipoEsperado });

  document.querySelectorAll('#combinacionesBody tr').forEach((fila, index) => {
    const talle = requiereTalleLocal ? (fila.querySelector('.talle-select')?.value || '') : '';
    const tipoTalle = requiereTalleLocal ? (fila.querySelector('.tipo-talle')?.value || '') : '';
    const color = fila.querySelector('.color-select')?.value || '';
    const cantidad = parseInt(fila.querySelector('.cantidad-input')?.value || '0', 10);
    const clave = `${talle}-${color}`.toLowerCase();

    console.log(`Fila ${index}:`, { tipoTalle, talle, color, cantidad, clave });

    // Duplicados
    if (combinaciones.has(clave)) {
      hayDuplicados = true;
      console.log(`  ❗ duplicado en fila ${index} clave=${clave}`);
    } else {
      combinaciones.add(clave);
      console.log(`  ➕ agregado clave=${clave}`);
    }

    // Cantidad
    if (cantidad <= 0) {
      hayCantidadCero = true;
      console.log(`  ❗ cantidad <=0 en fila ${index}`);
    }

    // Faltantes
    if (!color || (requiereTalleLocal && (!talle || !tipoTalle))) {
      // nota: la variable requiereTalleLocal ya está definida; aquí se usa para el log
    }
    if (!color || (requiereTalleLocal && (!talle || !tipoTalle))) {
      hayCamposFaltantes = true;
      console.log(`  ❗ faltantes en fila ${index}`);
    }

    // Consistencia de tipo de talle
    if (requiereTalleLocal) {
      if (index === 0) {
        tipoTalleGlobal = tipoTalle;
      } else if (tipoTalle && tipoTalleGlobal && tipoTalle !== tipoTalleGlobal) {
        hayTipoTalleInconsistente = true;
        console.log(`  ❗ inconsistente tipoTalle fila ${index} global=${tipoTalleGlobal} actual=${tipoTalle}`);
      }
    }

    // Tipo incorrecto
    if (requiereTalleLocal && tipoEsperado && tipoTalle &&
      !['otro', tipoEsperado.toLowerCase()].includes(tipoTalle.toLowerCase())) {
      hayTipoTalleIncorrecto = true;
      console.log(`  ❗ tipoTalle incorrecto en fila ${index} esperado=${tipoEsperado}`);
    }

    if (mostrarErrores) {
      const completa = evaluarYResaltarFila(fila, requiereTalleLocal);
      console.log(`  🎨 mostrarErrores: fila ${index} completa=${completa}`);
    }
  });

  const hayErroresBasicos = hayDuplicados || hayCantidadCero || hayCamposFaltantes || hayTipoTalleInconsistente;
  console.log('Resumen errores:', {
    hayDuplicados, hayCantidadCero, hayCamposFaltantes, hayTipoTalleInconsistente, hayTipoTalleIncorrecto, hayErroresBasicos
  });

  if (mostrarErrores && hayErroresBasicos) {
    mostrarErrorCombinaciones(true, 'Corregí las combinaciones marcadas en rojo antes de continuar.');
  } else if (!hayErroresBasicos) {
    mostrarErrorCombinaciones(false);
  }

  if (mostrarErrores && hayTipoTalleIncorrecto) {
    const modalEl = document.getElementById('modalErrorTipoTalle');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      new bootstrap.Modal(modalEl).show();
    }
  }

  const hayErrores = hayErroresBasicos || hayTipoTalleIncorrecto;
  console.log('✅ validarDuplicados resultado:', { hayErrores, retorno: !hayErrores });
  return !hayErrores;
}

// Actualizar talles dinámicos
function actualizarTalle(selectTipo) {
  const tipo = selectTipo.value;
  const fila = selectTipo.closest('tr');
  const selectTalle = fila.querySelector('.talle-select');
  if (!selectTalle) return;
  selectTalle.innerHTML = tallesPorTipo[tipo]?.map(t => `<option value="${t}">${t}</option>`).join('') || '';
}

// Validar si todos los campos están completos
function validarCamposCompletos() {
  const form = document.getElementById('formSeries');
  const requiredFields = Array.from(form.querySelectorAll('[required]'));
  let primerCampoInvalido = null;
  let hayErrores = false;

  // Validar campos principales del formulario
  requiredFields.forEach(field => {
    const vacio = !String(field.value).trim();
    setFieldError(field, vacio);
    if (vacio && !primerCampoInvalido) primerCampoInvalido = field;
    if (vacio) hayErrores = true;
  });

  // Validar filas de combinaciones
  const filas = document.querySelectorAll('#combinacionesBody tr');
  let hayFilaIncompleta = false;

  filas.forEach(fila => {
    const tipoTalle = fila.querySelector('.tipo-talle');
    const talle = fila.querySelector('.talle-select');
    const color = fila.querySelector('.color-select');
    const cantidad = fila.querySelector('.cantidad-input');

    // Verificar según si requiere talle o no
    let filaCompleta = false;
    if (requiereTalle) {
      filaCompleta = tipoTalle?.value && talle?.value && color?.value && cantidad?.value && parseInt(cantidad.value) > 0;
    } else {
      filaCompleta = color?.value && cantidad?.value && parseInt(cantidad.value) > 0;
    }

    if (!filaCompleta) {
      hayFilaIncompleta = true;
    }
  });

  if (hayFilaIncompleta) {
    hayErrores = true;
    // Mostrar mensaje de error de combinaciones
    mostrarErrorCombinaciones(true, 'Corregí las combinaciones marcadas en rojo antes de continuar.');
  }

  return { valido: !hayErrores, primerCampoInvalido };
}

// Actualiza el estado de los botones de borrar
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

// Agregar fila
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
      <select class="form-select color-select">
        <option value="" disabled selected>Seleccione o escriba</option>
        ${selectColor}
      </select>
    </td>
    <td><input type="number" class="form-control cantidad-input" min="1" value="1"></td>
    <td><input type="text" class="form-control codigo-preview" disabled></td>
    <td><button type="button" class="btn btn-sm btn-danger btn-delete" onclick="eliminarFila(this)">✕</button></td>
  `;

  row.innerHTML = cols;
  tbody.appendChild(row);

  try {
    if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
      $(row).find('select').select2({ tags: true, width: '100%' });

      $(row).find('select').on('select2:select select2:clear', function() {
        this.dispatchEvent(new Event('change', { bubbles: true }));
      });

      $(row).find('select.tipo-talle').on('select2:open', function() {
        setTimeout(function() {
          $('.select2-container--open .select2-search__field').hide();
        }, 0);
      });
    }
  } catch (e) {}

  generarPreviewCodigoPorFila();
  validarDuplicados(false);
  updateDeleteButtons();
};

// Eliminar fila
window.eliminarFila = function(btn) {
  btn.closest('tr').remove();
  generarPreviewCodigoPorFila();
  updateDeleteButtons();
};

// Validación de fecha
window.validarFechaAdquisicionInline = function() {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('error-fecha_adquisicion');
  if (!input || !errDiv) return true;

  errDiv.classList.add('d-none');
  input.classList.remove('is-invalid');

  const valor = input.value;
  if (!valor) return true;

  const today = new Date(); 
  today.setHours(0, 0, 0, 0);
  const fecha = new Date(valor); 
  fecha.setHours(0, 0, 0, 0);

  if (fecha > today) {
    const msg = 'La fecha de adquisición no puede ser posterior al día de hoy.';
    errDiv.textContent = msg;
    errDiv.classList.remove('d-none');
    input.classList.add('is-invalid');
    return false;
  }

  return true;
};

function showFechaServerError(msg) {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('error-fecha_adquisicion');
  if (!input || !errDiv) return;
  errDiv.textContent = msg || 'Fecha inválida.';
  errDiv.classList.remove('d-none');
  input.classList.add('is-invalid');
}

function clearFechaError() {
  const input = document.getElementById('fecha_adquisicion');
  const errDiv = document.getElementById('error-fecha_adquisicion');
  if (!input || !errDiv) return;
  errDiv.textContent = '';
  errDiv.classList.add('d-none');
  input.classList.remove('is-invalid');
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

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
  agregarFila();
  updateDeleteButtons();

  // Listeners para campos principales del formulario
  ['version', 'anio', 'lote', 'fecha_adquisicion', 'fecha_vencimiento'].forEach(id => {
    const campo = document.getElementById(id);
    if (campo) {
      campo.addEventListener('change', () => {
        generarPreviewCodigoPorFila();
        // Limpiar errores al cambiar
        if (campo.value) {
          setFieldError(campo, false);
        }
      });
      campo.addEventListener('input', () => {
        generarPreviewCodigoPorFila();
        // Limpiar errores al escribir
        if (campo.value) {
          setFieldError(campo, false);
        }
      });
    }
  });

  // Listener para fecha de adquisición
  const fechaInput = document.getElementById('fecha_adquisicion');
  if (fechaInput) {
    fechaInput.addEventListener('input', () => {
      clearFechaError();
    });
    fechaInput.addEventListener('change', () => {
      validarFechaAdquisicionInline();
    });
  }

  // Listener para la tabla de combinaciones
// --- BLOQUE DE LISTENERS Y COMPROBACIÓN EN TIEMPO REAL ---
const tbody = document.getElementById('combinacionesBody');
if (tbody) {
  function comprobarTablaYError() {
    const requiereTalleLocal = Boolean(window.requiereTalle);
    const filas = document.querySelectorAll('#combinacionesBody tr');
    let hayFilaIncompleta = false;

    filas.forEach((fila, idx) => {
      const completa = evaluarYResaltarFila(fila, requiereTalleLocal);
      if (!completa) {
        hayFilaIncompleta = true;
        console.log(`❗ fila ${idx} incompleta`);
      } else {
        console.log(`✅ fila ${idx} completa`);
      }
    });

    const tablaOk = validarDuplicados(false); // evalúa duplicados sin marcar rojo
    console.log('🔎 comprobarTablaYError:', { hayFilaIncompleta, tablaOk });

    // Ocultar mensaje si todas las filas están completas y no hay errores
    if (!hayFilaIncompleta && tablaOk) {
      mostrarErrorCombinaciones(false);
      console.log('👌 Todas las combinaciones completas y válidas, ocultando mensaje.');
    }
  }

  tbody.addEventListener('input', ev => {
    const t = ev.target;
    if (t.matches('.cantidad-input, .color-select, .talle-select, .tipo-talle')) {
      console.log('✏️ input en combinaciones:', t.className);
      generarPreviewCodigoPorFila();
      comprobarTablaYError();
    }
  });

  tbody.addEventListener('change', ev => {
    const t = ev.target;
    if (t.matches('.color-select, .talle-select, .tipo-talle')) {
      console.log('🔁 change en combinaciones:', t.className);
      generarPreviewCodigoPorFila();
      comprobarTablaYError();
    }
  });
}

  // Limitar lote a 5 dígitos
  const loteInput = document.getElementById('lote');
  if (loteInput) {
    loteInput.addEventListener('input', () => {
      if (loteInput.value.length > 5) {
        loteInput.value = loteInput.value.slice(0, 5);
      }
    });
  }

  // Manejo del envío del formulario
// Manejo del envío del formulario
// Manejo del envío del formulario
const form = document.getElementById('formSeries');
if (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    console.log('🚀 Submit iniciado');

    // 1) Fecha
    const fechaOk = validarFechaAdquisicionInline();
    console.log('⏱ validarFechaAdquisicionInline:', fechaOk);
    if (!fechaOk) {
      document.getElementById('fecha_adquisicion')?.focus();
      const modalErrorCampos = document.getElementById('modalErrorCampos');
      if (modalErrorCampos && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalErrorCampos).show();
      }
      console.log('⛔ Fin submit: fecha inválida');
      return;
    }

    // 2) Campos principales
    const validacion = validarCamposCompletos();
    console.log('📋 validarCamposCompletos:', validacion);
    const camposPrincipalesOk = validacion.valido;

    // 3) Resaltar columnas y calcular filas incompletas
    const requiereTalleLocal = Boolean(window.requiereTalle);
    const filas = document.querySelectorAll('#combinacionesBody tr');
    let hayFilaIncompleta = false;

    filas.forEach((fila, idx) => {
      const completa = evaluarYResaltarFila(fila, requiereTalleLocal);
      if (!completa) {
        hayFilaIncompleta = true;
        console.log(`❗ fila ${idx} incompleta`);
      } else {
        console.log(`✅ fila ${idx} completa`);
      }
    });

    // --- Decisión separada para campos principales y combinaciones ---
    console.log('🔔 Estado previo a decisión:', { camposPrincipalesOk, hayFilaIncompleta });

    // Si hay errores en los campos principales, mostramos solo esos errores y no el mensaje de combinaciones (salvo que también haya filas incompletas)
    if (!camposPrincipalesOk) {
      if (validacion.primerCampoInvalido) {
        validacion.primerCampoInvalido.focus();
      }

      if (!hayFilaIncompleta) {
        mostrarErrorCombinaciones(false);
      } else {
        mostrarErrorCombinaciones(true, 'Corregí las combinaciones marcadas en rojo antes de continuar.');
      }

      const modalErrorCampos = document.getElementById('modalErrorCampos');
      if (modalErrorCampos && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalErrorCampos).show();
      }

      console.log('⛔ Fin submit: faltantes en campos principales');
      return;
    }

    // Si llegamos acá, los campos principales están OK.
    // Si hay filas incompletas, mostramos el mensaje de combinaciones y retornamos.
    if (hayFilaIncompleta) {
      mostrarErrorCombinaciones(true, 'Corregí las combinaciones marcadas en rojo antes de continuar.');
      const modalErrorCampos = document.getElementById('modalErrorCampos');
      if (modalErrorCampos && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalErrorCampos).show();
      }
      console.log('⛔ Fin submit: hay filas incompletas en combinaciones');
      return;
    }

    // Si llegamos hasta aquí, campos principales OK y filas completas.
    mostrarErrorCombinaciones(false);
    console.log('👌 Todo completo en combinaciones y campos principales, seguimos.');

    // 4) Validar duplicados y reglas
    const ok = validarDuplicados(true);
    console.log('🔁 validarDuplicados(true) retorno:', ok);
    if (!ok) {
      const modalErrorCampos = document.getElementById('modalErrorCampos');
      if (modalErrorCampos && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalErrorCampos).show();
      }
      console.log('⛔ Fin submit: errores en duplicados/reglas');
      return;
    }

    // 5) Armar payload
    const combinaciones = [];
    filas.forEach((fila, idx) => {
      const tipoTalle = requiereTalleLocal ? (fila.querySelector('.tipo-talle')?.value || '') : null;
      const talle = requiereTalleLocal ? (fila.querySelector('.talle-select')?.value || '') : null;
      const colorId = fila.querySelector('.color-select')?.value || '';
      const cantidad = fila.querySelector('.cantidad-input')?.value || '';
      if (colorId && parseInt(cantidad, 10) > 0) {
        combinaciones.push({ tipo_talle: tipoTalle, talle, color_id: colorId, cantidad });
      }
      console.log(`📦 fila ${idx} para payload:`, { tipoTalle, talle, color_id: colorId, cantidad });
    });

    if (combinaciones.length === 0) {
      showFormError('No hay combinaciones válidas para guardar.');
      console.log('⛔ Fin submit: combinaciones vacías para payload');
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

    console.log('🚚 Enviando payload:', payload);

    clearFormError();
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
      console.log("📨 Respuesta del servidor:", { ok: res.ok, status: res.status, data });

      if (!res.ok) {
        if (data && data.errors && data.errors.fecha_adquisicion) {
          showFechaServerError(Array.isArray(data.errors.fecha_adquisicion) ? data.errors.fecha_adquisicion.join(' ') : String(data.errors.fecha_adquisicion));
          return;
        }
        const serverMsg = data && data.message ? String(data.message) : null;
        if (serverMsg && /fecha/i.test(serverMsg)) {
          showFechaServerError(serverMsg.includes('posterior') ? 'La fecha de adquisición no puede ser mayor a la fecha actual.' : serverMsg);
          return;
        }
        showFormError(serverMsg || 'Error al guardar las series. Revisá los campos marcados.');
        return;
      }

      clearFormError();
      clearFechaError();
      const modalEl = document.getElementById('modalSeriesAgregadas');
      if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(modalEl).show();
      }
      console.log('✅ Submit finalizado con éxito');
    })
    .catch(err => {
      console.error("❌ Error en la petición:", err);
      showFormError('Error de red o del servidor. Revisá la consola para más detalles.');
    });
  });
}

});

function evaluarYResaltarFila(fila, requiereTalleLocal) {
  const idx = Array.from(document.querySelectorAll('#combinacionesBody tr')).indexOf(fila);

  const tipoTalleEl = fila.querySelector('.tipo-talle');
  const talleEl = fila.querySelector('.talle-select');
  const colorEl = fila.querySelector('.color-select');
  const cantidadEl = fila.querySelector('.cantidad-input');

  const tipoTalleVal = tipoTalleEl?.value || '';
  const talleVal = talleEl?.value || '';
  const colorVal = colorEl?.value || '';
  const cantidadNum = parseInt(cantidadEl?.value || '0', 10);
  const cantidadOk = Number.isFinite(cantidadNum) && cantidadNum > 0;

  console.log(`🔎 evaluarYResaltarFila fila ${idx}:`, {
    requiereTalleLocal, tipoTalleVal, talleVal, colorVal, cantidadNum, cantidadOk
  });

  // Limpieza previa (solo columnas)
  fila.querySelectorAll('.td-invalid').forEach(td => td.classList.remove('td-invalid'));
  fila.querySelectorAll('.select2-invalid').forEach(s2 => s2.classList.remove('select2-invalid'));
  fila.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

  // Tipo de talle (si aplica)
  if (requiereTalleLocal && tipoTalleEl) {
    const td = tipoTalleEl.closest('td');
    const invalid = !tipoTalleVal;
    if (invalid) {
      tipoTalleEl.classList.add('is-invalid');
      td?.classList.add('td-invalid');
      td?.querySelector('.select2-selection')?.classList.add('select2-invalid');
    }
    console.log(`  ▶ tipoTalle invalido=${invalid}`);
  }

  // Talle (si aplica)
  if (requiereTalleLocal && talleEl) {
    const td = talleEl.closest('td');
    const invalid = !talleVal;
    if (invalid) {
      talleEl.classList.add('is-invalid');
      td?.classList.add('td-invalid');
      td?.querySelector('.select2-selection')?.classList.add('select2-invalid');
    }
    console.log(`  ▶ talle invalido=${invalid}`);
  }

  // Color (siempre)
  if (colorEl) {
    const td = colorEl.closest('td');
    const invalid = !colorVal;
    if (invalid) {
      colorEl.classList.add('is-invalid');
      td?.classList.add('td-invalid');
      td?.querySelector('.select2-selection')?.classList.add('select2-invalid');
    }
    console.log(`  ▶ color invalido=${invalid}`);
  }

  // Cantidad (siempre)
  if (cantidadEl) {
    const td = cantidadEl.closest('td');
    const invalid = !cantidadOk;
    if (invalid) {
      cantidadEl.classList.add('is-invalid');
      td?.classList.add('td-invalid');
    }
    console.log(`  ▶ cantidad invalido=${invalid}`);
  }

  const completa = requiereTalleLocal
    ? Boolean(tipoTalleVal && talleVal && colorVal && cantidadOk)
    : Boolean(colorVal && cantidadOk);

  console.log(`  ✅ fila ${idx} completa=${completa}`);
  return completa;
}


console.log('✅ crearSeries.js cargado');