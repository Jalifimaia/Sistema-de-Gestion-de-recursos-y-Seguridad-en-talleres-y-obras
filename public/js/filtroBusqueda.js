document.addEventListener('DOMContentLoaded', function () {
  try {
    const tabla = document.querySelector('table.table-naranja');
    if (!tabla) return; // no hay tabla en esta vista, salimos limpio
    
    const tbody = tabla.querySelector('tbody');
    if (!tbody) return;

    const filas = Array.from(tbody.querySelectorAll('tr'));
    const paginacion = document.getElementById('paginacion');
    const info = document.getElementById('infoPaginacion');
    const filtroSelect = document.getElementById('filtroInventario');
    const buscador = document.getElementById('buscador');

    const filasPorPagina = 10;
    let paginaActual = 1;

    function aplicarFiltrosYPaginar() {
  const filtro = filtroSelect?.value.toLowerCase() || 'todos';
  const texto = buscador?.value.toLowerCase() || '';

  const filasFiltradas = filas.filter(fila => {
    const nombre = fila.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
    const categoria = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
    const subcategoria = fila.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
    const descripcion = fila.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || ''; 
    
    // 1. Filtrar por estado (filtro)
    const estadoFila = fila.dataset.estado?.toLowerCase() || 'disponible';
    const coincideFiltro = filtro === 'todos' || estadoFila === filtro;

    // 2. Filtrar por texto (buscador)
    const textoFila = `${nombre} ${categoria} ${subcategoria} ${descripcion}`;
    const coincideTexto = textoFila.includes(texto);

    return coincideFiltro && coincideTexto;
  });

  // 3. Aplicar paginación a las filas filtradas
  const total = filasFiltradas.length;
  const totalPaginas = Math.ceil(total / filasPorPagina);
  paginaActual = Math.max(1, Math.min(paginaActual, totalPaginas || 1));

  filas.forEach(f => f.style.display = 'none'); // Ocultar todas las filas
  
  const inicio = (paginaActual - 1) * filasPorPagina;
  const fin = Math.min(inicio + filasPorPagina, total);

  for (let i = inicio; i < fin; i++) {
      filasFiltradas[i].style.display = 'table-row'; // Mostrar solo las de la página actual
  }

  // 💡 SOLUCIÓN ZEBRA: Llamar aplicarZebra() después de cambiar las filas visibles
  if (tabla && tabla.aplicarZebra) {
      tabla.aplicarZebra();
  }
  
  // 4. Actualizar info y paginación (solo si existen los elementos)
  if (info) {
    info.textContent = `Mostrando ${total === 0 ? 0 : inicio + 1}-${fin} de ${total} recursos`;
  }

  if (paginacion) {
    paginacion.innerHTML = '';
    const crearItem = (label, page, disabled, isActive = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (isActive ? ' active' : '') + (disabled ? ' disabled' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.textContent = label;
        a.href = '#';
        a.addEventListener('click', e => {
          e.preventDefault();
          if (!disabled && paginaActual !== page) {
            paginaActual = Math.max(1, Math.min(page, totalPaginas || 1));
            aplicarFiltrosYPaginar();
          }
        });
        li.appendChild(a);
        return li;
    };

    // Prev
    paginacion.appendChild(crearItem('«', paginaActual - 1, paginaActual === 1));

    for (let i = 1; i <= totalPaginas; i++) {
      paginacion.appendChild(crearItem(i, i, false, i === paginaActual));
    }

    // Next
    paginacion.appendChild(crearItem('»', paginaActual + 1, paginaActual === totalPaginas || totalPaginas === 0));
  }
}

    
    // Event Listeners
    if (buscador) buscador.addEventListener('input', () => {
      paginaActual = 1;
      aplicarFiltrosYPaginar();
    });
    if (filtroSelect) filtroSelect.addEventListener('change', () => {
      paginaActual = 1;
      aplicarFiltrosYPaginar();
    });

    // Helper para badge de estado
    const mostrarEstado = (select) => {
      const estado = select.value.toLowerCase();
      const badge = document.getElementById(`estado-badge-${select.dataset.id}`);
      if (!badge) return;

      badge.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
      badge.className = 'badge px-2 py-1 rounded fw-semibold text-capitalize';

      switch (estado) {
        case 'disponible':
          badge.classList.add('bg-success');
          break;
        case 'prestado':
          badge.classList.add('bg-warning', 'text-dark');
          break;
        case 'en reparación':
        case 'dañado':
          badge.classList.add('bg-danger');
          break;
        default:
          badge.classList.add('bg-secondary');
          break;
      }
    };

    // Inicializar estados si existen selects
    document.querySelectorAll('select[data-id]').forEach(select => {
      const firstValid = Array.from(select.options).find(opt => opt.value && opt.getAttribute('data-estado'));
      if (firstValid) {
        select.value = firstValid.value;
        mostrarEstado(select);
      }
    });

    // Habilitar acciones visuales sobre botones por si hay overlays o estilos que los bloqueen
    function rehabilitarBotones() {
      document.querySelectorAll('a.btn, button').forEach(btn => {
        // solo aplicar si el estilo no fue intencionalmente marcado para ocultar
        if (!btn.classList.contains('no-pointer-reset')) {
          btn.style.pointerEvents = 'auto';
          btn.style.position = 'relative';
          btn.style.zIndex = '10';
        }
      });
    }
    rehabilitarBotones();

    // Iniciar
    aplicarFiltrosYPaginar();
  } catch (err) {
    // Logueamos el error sin romper el resto de los scripts de la página
    console.error('Error en filtroBusqueda.js inicializando:', err);
  }
});