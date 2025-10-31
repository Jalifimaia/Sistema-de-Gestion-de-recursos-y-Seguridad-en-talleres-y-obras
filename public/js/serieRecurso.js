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
const requiereTalle = window.requiereTalle || false; // 👈 viene del Blade

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

// 👉 Genera preview de códigos, reiniciando por color + año + lote
function generarPreviewCodigoPorFila() {
    const version = document.getElementById('version')?.value || '';
    const lote = document.getElementById('lote')?.value || '';
    const anio = document.getElementById('anio')?.value || '';
    const anio2d = anio ? anio.toString().slice(-2) : '';
    const inicialesNombre = iniciales(nombreRecurso);
    const inicialesDesc = iniciales(descripcionRecurso);
    const loteNum = lote.toString().padStart(2, '0');

    // Contadores separados por color
    const contadoresPorColor = {};

    document.querySelectorAll('#combinacionesBody tr').forEach((fila) => {
        const color = fila.querySelector('.color-select')?.value || '';

        if (!contadoresPorColor[color]) {
            contadoresPorColor[color] = 1;
        }

        const correlativo = contadoresPorColor[color].toString().padStart(2, '0');

        // ❌ Ya no usamos inicialesColor
        const codigo = `${inicialesNombre}-V${version}-${inicialesDesc}-${anio2d}-${loteNum}-${correlativo}`;

        const campoCodigo = fila.querySelector('.codigo-preview');
        if (campoCodigo) campoCodigo.value = codigo;

        contadoresPorColor[color]++;
    });
}


function validarDuplicados(mostrarAlertas = false) {
    if (!validacionActiva && !mostrarAlertas) return;

    const combinaciones = new Set();
    let hayDuplicados = false;
    let hayCantidadCero = false;
    let hayCamposFaltantes = false;
    let hayTipoTalleInconsistente = false;
    let hayTipoTalleIncorrecto = false;

    let tipoTalleGlobal = null;

    // Detectar subcategoría del recurso
    const recursoSubcategoria = (document.getElementById('subcategoriaNombre')?.textContent || '').toLowerCase();
    const requiereTalle = ['chaleco', 'botas'].includes(recursoSubcategoria);
    const tipoEsperado = recursoSubcategoria === 'chaleco' ? 'Ropa' :
                         recursoSubcategoria === 'botas' ? 'Calzado' : null;

    document.querySelectorAll('#combinacionesBody tr').forEach((fila, index) => {
        const talle = requiereTalle ? (fila.querySelector('.talle-select')?.value || '') : '';
        const tipoTalle = requiereTalle ? (fila.querySelector('.tipo-talle')?.value || '') : '';
        const color = fila.querySelector('.color-select')?.value || '';
        const cantidad = parseInt(fila.querySelector('.cantidad-input')?.value || '0');
        const clave = `${talle}-${color}`.toLowerCase();

        let error = false;

        // Duplicados
        if (combinaciones.has(clave)) {
            hayDuplicados = true;
            error = true;
        } else {
            combinaciones.add(clave);
        }

        // Cantidad inválida
        if (cantidad <= 0) {
            hayCantidadCero = true;
            error = true;
        }

        // Campos faltantes
        if (!color || (requiereTalle && (!talle || !tipoTalle))) {
            hayCamposFaltantes = true;
            error = true;
        }

        // Tipo de talle inconsistente entre filas
        if (requiereTalle) {
            if (index === 0) {
                tipoTalleGlobal = tipoTalle;
            } else if (tipoTalle && tipoTalleGlobal && tipoTalle !== tipoTalleGlobal) {
                hayTipoTalleInconsistente = true;
                error = true;
            }
        }

        // Tipo de talle incorrecto según subcategoría (excepto "Otro")
        if (requiereTalle && tipoEsperado && tipoTalle &&
            !['otro', tipoEsperado.toLowerCase()].includes(tipoTalle.toLowerCase())) {
            hayTipoTalleIncorrecto = true;
            error = true;
        }

        fila.classList.toggle('table-danger', error);
    });

    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = hayDuplicados || hayCantidadCero || hayCamposFaltantes || hayTipoTalleInconsistente || hayTipoTalleIncorrecto;

    if (mostrarAlertas) {
        if (hayDuplicados) {
            alert('⚠️ Hay combinaciones repetidas. Revisá las filas en rojo.');
        }
        if (hayCantidadCero) {
            alert('⚠️ Hay cantidades en cero. Todas deben tener al menos 1 unidad.');
        }
        if (hayCamposFaltantes) {
            alert('⚠️ Faltan campos obligatorios (color, talle o tipo). Completá todas las combinaciones.');
        }
        if (hayTipoTalleInconsistente) {
            alert('⚠️ Todas las combinaciones deben usar el mismo tipo de talle.');
        }
        if (hayTipoTalleIncorrecto) {
            alert(`⚠️ El tipo de talle debe ser "${tipoEsperado}" u "Otro" para el recurso seleccionado.`);
        }
    }
}


function actualizarTalle(selectTipo) {
    const tipo = selectTipo.value;
    const fila = selectTipo.closest('tr');
    const selectTalle = fila.querySelector('.talle-select');

    if (!selectTalle) return;

    selectTalle.innerHTML = tallesPorTipo[tipo]?.map(t => `<option value="${t}">${t}</option>`).join('') || '';
}

function agregarFila() {
    const tbody = document.getElementById('combinacionesBody');
    const row = document.createElement('tr');

    // Construir opciones de color usando id como value y nombre como texto
        const selectColor = colores.map(c => {
        const val = c.id;
        const txt = c.nombre;
        return `<option value="${escapeHtml(val)}">${escapeHtml(txt)}</option>`;
        }).join('');


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
        </td>
    `;
}


    cols += `
        <td>
            <select class="form-select color-select" onchange="generarPreviewCodigoPorFila()">
                <option value="" disabled selected>Seleccione o escriba</option>
                ${selectColor}
            </select>
        </td>
        <td>
            <input type="number" class="form-control cantidad-input" min="0" value="0">
        </td>
        <td>
            <input type="text" class="form-control codigo-preview" disabled>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); generarPreviewCodigoPorFila(); validarDuplicados()">✕</button>
        </td>
    `;

    row.innerHTML = cols;
    tbody.appendChild(row);

    // Inicializar select2 solo en los selects de la fila nueva (si está disponible)
        try {
  if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
    // Inicializar todos los selects con select2
    $(row).find('select').select2({ tags: true, width: '100%' });

    // Interceptar creación de nuevos colores
    $(row).find('.color-select').select2({
      tags: true,
      width: '100%',
      createTag: function (params) {
        return {
          id: params.term,
          text: params.term,
          newOption: true
        };
      },
      insertTag: function (data, tag) {
        data.push(tag);
      }
    }).on('select2:select', function (e) {
  const data = e.params.data;
  if (data.newOption) {
    fetch('/colores/crear', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ nombre: data.id })
    })
    .then(res => res.json())
    .then(nuevo => {
      if (nuevo.id) {
        const select = e.target;

        // Eliminar el option temporal (ej. value="Rosa")
        const oldOption = select.querySelector(`option[value="${data.id}"]`);
        if (oldOption) oldOption.remove();

        // Crear un nuevo option con ID real y texto correcto
        const newOption = new Option(nuevo.nombre, nuevo.id, true, true);
        select.add(newOption);

        // Refrescar Select2 con el nuevo valor
        $(select).val(nuevo.id).trigger('change.select2');
      }
    });
  }
});

  }
} catch (e) {
  // noop
}


    generarPreviewCodigoPorFila();
    validarDuplicados(false);
}


document.addEventListener('DOMContentLoaded', () => {
    // Inicializa una fila si hace falta
    agregarFila();

    // Delegación de eventos sobre el tbody para inputs dinámicos
    const tbody = document.getElementById('combinacionesBody');
    if (tbody) {
      // input cubre typing en cantidad y selects que disparan input (si no, change también está abajo)
      tbody.addEventListener('input', (ev) => {
        const t = ev.target;
        if (t.matches('.cantidad-input') || t.matches('.color-select') || t.matches('.talle-select') || t.matches('.tipo-talle')) {
          validacionActiva = true;
          generarPreviewCodigoPorFila();
          validarDuplicados(false);
        }
      });

      // change captura select2 y cambios formales en selects
      tbody.addEventListener('change', (ev) => {
        const t = ev.target;
        if (t.matches('.color-select') || t.matches('.talle-select') || t.matches('.tipo-talle')) {
          validacionActiva = true;
          generarPreviewCodigoPorFila();
          validarDuplicados(false);
        }
      });
    }

    // Maneja submit (tu lógica original)
    const form = document.getElementById('formSeries');
    const input = document.getElementById('combinaciones');

    if (form) {
      form.addEventListener('submit', function (e) {
        const filas = document.querySelectorAll('#combinacionesBody tr');
        const combinaciones = [];

        filas.forEach(fila => {
          const tipoTalle = requiereTalle ? (fila.querySelector('.tipo-talle')?.value || '') : null;
          const talle = requiereTalle ? (fila.querySelector('.talle-select')?.value || '') : null;
          const color = fila.querySelector('.color-select')?.value || '';
          const cantidad = fila.querySelector('.cantidad-input')?.value || '';

          if (color && cantidad > 0) {
            combinaciones.push({
              tipo_talle: tipoTalle,
              talle: talle,
              color_nombre: color,
              cantidad: cantidad
            });
          }
        });

        if (combinaciones.length === 0) {
          e.preventDefault();
          alert('⚠️ No hay combinaciones válidas para guardar.');
          return;
        }

        input.value = JSON.stringify(combinaciones);
      });
    }
});
