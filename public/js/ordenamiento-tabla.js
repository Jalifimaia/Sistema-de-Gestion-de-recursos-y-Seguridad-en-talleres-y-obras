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

  // Aplicar zebra inicial (solo a filas visibles por defecto)
  aplicarZebra();

  cabeceras.forEach((th, index) => {
    const textoTh = th.textContent.trim().toLowerCase();
    if (textoTh === 'acciones') return;
    
    if (th.hasAttribute('data-no-sort')) return;
    
    th.style.cursor = 'pointer';
    th.style.userSelect = 'none';
    
    if (!th.querySelector('.bi-arrow-down-up')) {
      th.innerHTML += ' <i class="bi bi-arrow-down-up ms-1 text-muted"></i>';
    }
    
    th.addEventListener('click', () => ordenarTabla(index, th));
  });

  function ordenarTabla(columnaIndex, cabecera) {
    const filas = Array.from(tbody.querySelectorAll('tr'));
    
    const mismaColumna = ordenActual.columna === columnaIndex;
    const ascendente = mismaColumna ? !ordenActual.ascendente : true;
    
    // 1. Obtener el tipo de dato de la primera fila no oculta
    const primeraFilaVisible = filas.find(tr => tr.style.display !== 'none');
    let esNumerico = false;
    if (primeraFilaVisible) {
      const celda = primeraFilaVisible.cells[columnaIndex];
      if (celda) {
        // Simple check: si solo hay números y quizás un punto o coma.
        esNumerico = /^-?\d+(\.\d+)?(,\d+)?$/.test(celda.textContent.trim().replace(',', '.'));
      }
    }

    // 2. Ordenar las filas
    filas.sort((filaA, filaB) => {
      const celdaA = filaA.cells[columnaIndex].textContent.trim();
      const celdaB = filaB.cells[columnaIndex].textContent.trim();

      let valorA = celdaA;
      let valorB = celdaB;

      if (esNumerico) {
        valorA = parseFloat(celdaA.replace(',', '.') || 0);
        valorB = parseFloat(celdaB.replace(',', '.') || 0);
        // Manejar NaNs si la conversión falla
        if (isNaN(valorA)) valorA = ascendente ? Infinity : -Infinity;
        if (isNaN(valorB)) valorB = ascendente ? Infinity : -Infinity;
      } else {
        valorA = celdaA.toLowerCase();
        valorB = celdaB.toLowerCase();
      }
      
      let comparacion = 0;
      if (valorA > valorB) {
        comparacion = 1;
      } else if (valorA < valorB) {
        comparacion = -1;
      }

      return ascendente ? comparacion : -comparacion;
    });

    // 3. Reinsertar las filas ordenadas
    filas.forEach(fila => tbody.appendChild(fila));

    ordenActual = { columna: columnaIndex, ascendente: ascendente };
    actualizarIconos(cabecera, ascendente);
    
    // 💡 LLAMADA CRÍTICA: Volver a aplicar el zebra después del ordenamiento
    aplicarZebra();
  }

  function actualizarIconos(cabeceraActiva, ascendente) {
    cabeceras.forEach(th => {
      const icono = th.querySelector('i');
      if (icono) {
        icono.className = 'bi bi-arrow-down-up ms-1 text-muted';
      }
    });
    
    const icono = cabeceraActiva.querySelector('i');
    if (icono) {
      icono.className = ascendente 
        ? 'bi bi-arrow-up ms-1 text-primary' 
        : 'bi bi-arrow-down ms-1 text-primary';
    }
  }

  // --- FUNCIÓN MEJORADA QUE SOLO CUENTA FILAS VISIBLES ---
  function aplicarZebra() {
    const filas = Array.from(tbody.querySelectorAll('tr'));
    let contadorVisibles = 0; // Contador que sólo aumenta para filas visibles
    
    filas.forEach((tr) => {
      // 1. Limpieza total de estilos previos
      tr.style.removeProperty('background-color');
      tr.style.removeProperty('--bs-table-accent-bg');
      Array.from(tr.children).forEach(td => {
        td.style.removeProperty('background-color');
        td.style.removeProperty('box-shadow');
      });
      tr.classList.remove('even-row', 'odd-row');

      // 2. DETECTAR SI LA FILA ESTÁ VISIBLE (si no tiene 'display: none')
      const esVisible = tr.style.display !== 'none'; 

      if (esVisible) {
        contadorVisibles++; // Solo aumentamos si la fila se ve
        
        // Usamos el contadorVisibles para la lógica par/impar
        if (contadorVisibles % 2 === 0) {
          tr.classList.add('even-row');
        } else {
          tr.classList.add('odd-row');
        }
      }
    });
  }
  // --------------------------------------------------------

  // Exportar la función para que otros scripts (como los de paginación) puedan llamarla
  tabla.aplicarZebra = aplicarZebra;
}

document.addEventListener('DOMContentLoaded', function () {
  const tablas = document.querySelectorAll('table.table-naranja');
  tablas.forEach(tabla => {
    inicializarOrdenamiento(tabla);
  });
});