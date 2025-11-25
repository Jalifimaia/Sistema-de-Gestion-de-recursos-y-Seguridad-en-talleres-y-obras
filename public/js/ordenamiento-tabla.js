// Función reutilizable para agregar ordenamiento a cualquier tabla
function inicializarOrdenamiento(tabla) {
  if (!tabla) return;
  
  const tbody = tabla.querySelector('tbody');
  const cabeceras = tabla.querySelectorAll('thead th');
  
  if (!tbody || !cabeceras.length) return;
  
  let ordenActual = {
    columna: null,
    ascendente: true
  };

  // Agregar iconos y eventos a las cabeceras ordenables
  cabeceras.forEach((th, index) => {
    // Excluir la última columna si es "Acciones"
    const textoTh = th.textContent.trim().toLowerCase();
    if (textoTh === 'acciones') return;
    
    // Excluir columnas marcadas con data-no-sort
    if (th.hasAttribute('data-no-sort')) return;
    
    th.style.cursor = 'pointer';
    th.style.userSelect = 'none';
    
    // Solo agregar icono si no existe ya
    if (!th.querySelector('.bi-arrow-down-up')) {
      th.innerHTML += ' <i class="bi bi-arrow-down-up ms-1 text-muted"></i>';
    }
    
    th.addEventListener('click', () => ordenarTabla(index, th));
  });

  function ordenarTabla(columnaIndex, cabecera) {
    const filas = Array.from(tbody.querySelectorAll('tr'));
    
    // Determinar si es la misma columna o una nueva
    const mismaColumna = ordenActual.columna === columnaIndex;
    const ascendente = mismaColumna ? !ordenActual.ascendente : true;
    
    // Actualizar iconos
    actualizarIconos(cabecera, ascendente);
    
    // Ordenar filas
    filas.sort((a, b) => {
      let valorA = obtenerValorCelda(a, columnaIndex);
      let valorB = obtenerValorCelda(b, columnaIndex);
      
      // Detectar si es un valor numérico (con o sin formato de moneda)
      const esNumericoA = /^[\d$.,\s]+$/.test(valorA.trim());
      const esNumericoB = /^[\d$.,\s]+$/.test(valorB.trim());
      
      if (esNumericoA && esNumericoB) {
        valorA = parseFloat(valorA.replace(/[$.]/g, '').replace(',', '.')) || 0;
        valorB = parseFloat(valorB.replace(/[$.]/g, '').replace(',', '.')) || 0;
      }
      
      // Detectar si es una fecha (formato dd/mm/yyyy o dd/mm/yyyy hh:mm)
      const fechaRegex = /^\d{2}\/\d{2}\/\d{4}/;
      if (fechaRegex.test(valorA) && fechaRegex.test(valorB)) {
        valorA = convertirFechaATimestamp(valorA);
        valorB = convertirFechaATimestamp(valorB);
      }
      
      // Comparación
      if (valorA < valorB) return ascendente ? -1 : 1;
      if (valorA > valorB) return ascendente ? 1 : -1;
      return 0;
    });
    
    // Reinsertar filas ordenadas
    filas.forEach(fila => tbody.appendChild(fila));
    
    // Actualizar estado
    ordenActual = { columna: columnaIndex, ascendente };
  }

  function obtenerValorCelda(fila, columnaIndex) {
    const celda = fila.children[columnaIndex];
    if (!celda) return '';
    
    // Si tiene un SVG (checklist con iconos), ordenar por presencia
    const img = celda.querySelector('img');
    if (img) {
      const src = img.getAttribute('src') || '';
      // checkCheck.svg = 1 (sí), crossCross.svg = 0 (no)
      if (src.includes('checkCheck')) return '1';
      if (src.includes('crossCross')) return '0';
    }
    
    // Si tiene un badge o span interno, obtener su texto
    const badge = celda.querySelector('.badge, span');
    if (badge) return badge.textContent.trim().toLowerCase();
    
    return celda.textContent.trim().toLowerCase();
  }

  function convertirFechaATimestamp(fechaStr) {
    // Formato: dd/mm/yyyy o dd/mm/yyyy hh:mm
    const partes = fechaStr.split(' ');
    const fecha = partes[0].split('/');
    const hora = partes[1] ? partes[1].split(':') : ['0', '0'];
    
    // Crear fecha: año, mes-1, día, hora, minuto
    return new Date(
      parseInt(fecha[2]), 
      parseInt(fecha[1]) - 1, 
      parseInt(fecha[0]),
      parseInt(hora[0] || 0),
      parseInt(hora[1] || 0)
    ).getTime();
  }

  function actualizarIconos(cabeceraActiva, ascendente) {
    // Resetear todos los iconos
    cabeceras.forEach(th => {
      const icono = th.querySelector('i');
      if (icono) {
        icono.className = 'bi bi-arrow-down-up ms-1 text-muted';
      }
    });
    
    // Actualizar icono de la cabecera activa
    const icono = cabeceraActiva.querySelector('i');
    if (icono) {
      icono.className = ascendente 
        ? 'bi bi-arrow-up ms-1 text-primary' 
        : 'bi bi-arrow-down ms-1 text-primary';
    }
  }
}

// Inicializar todas las tablas al cargar el DOM
document.addEventListener('DOMContentLoaded', function () {
  // Buscar todas las tablas con clase .table-naranja
  const tablas = document.querySelectorAll('table.table-naranja');
  tablas.forEach(tabla => inicializarOrdenamiento(tabla));
});

// Exportar función para uso en modales o contenido dinámico
window.inicializarOrdenamiento = inicializarOrdenamiento;