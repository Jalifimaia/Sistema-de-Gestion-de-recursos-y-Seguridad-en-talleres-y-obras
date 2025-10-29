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

    let tipoTalleGlobal = null;

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

        // Tipo de talle inconsistente
        if (requiereTalle) {
            if (index === 0) {
                tipoTalleGlobal = tipoTalle;
            } else if (tipoTalle && tipoTalleGlobal && tipoTalle !== tipoTalleGlobal) {
                hayTipoTalleInconsistente = true;
                error = true;
            }
        }

        fila.classList.toggle('table-danger', error);
    });

    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = hayDuplicados || hayCantidadCero || hayCamposFaltantes || hayTipoTalleInconsistente;

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

    const selectColor = colores.map(c => `<option value="${c.nombre}">${c.nombre}</option>`).join('');

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

    $('.color-select').select2({ tags: true, width: '100%' });
    generarPreviewCodigoPorFila();
    validarDuplicados(false);
}

document.addEventListener('input', () => {
    validacionActiva = true;
    generarPreviewCodigoPorFila();
    validarDuplicados(false);
});

document.addEventListener('DOMContentLoaded', () => {
    agregarFila();

    const form = document.getElementById('formSeries');
    const input = document.getElementById('combinaciones');

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
});
