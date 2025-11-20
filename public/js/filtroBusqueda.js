document.addEventListener('DOMContentLoaded', function () {
  try {
    const tabla = document.querySelector('table.table-naranja tbody');
    if (!tabla) return; // no hay tabla en esta vista, salimos limpio

    const filas = Array.from(tabla.querySelectorAll('tr'));
    const paginacion = document.getElementById('paginacion');
    const info = document.getElementById('infoPaginacion');
    const filtroSelect = document.getElementById('filtroInventario');
    const buscador = document.getElementById('buscador');

    const filasPorPagina = 10;
    let paginaActual = 1;

    function aplicarFiltrosYPaginar() {
      const filtro = filtroSelect?.value.toLowerCase() || 'todos';
      const texto = buscador?.value.toLowerCase() || '';

      const visibles = filas.filter(fila => {
        const nombre = fila.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const categoria = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const subcategoria = fila.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        const descripcion = fila.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || ''; // 🔑 nueva columna
        const categoriaCombinada = `${categoria} ${subcategoria}`;

        // Buscar en cualquiera de los campos
        const coincideTexto =
          nombre.includes(texto) ||
          categoria.includes(texto) ||
          subcategoria.includes(texto) ||
          descripcion.includes(texto);

        return (filtro === 'todos' || categoriaCombinada.includes(filtro)) && coincideTexto;
      });


      const totalPaginas = Math.ceil(visibles.length / filasPorPagina);
      paginaActual = Math.min(Math.max(1, paginaActual), totalPaginas || 1);

      // Ocultar todas y mostrar sólo las del page
      filas.forEach(fila => {
        fila.classList.add('hidden-row');
        fila.style.display = 'none';
        fila.style.backgroundColor = ''; // resetear color inline
      });

      visibles.forEach((fila, indexVisible) => {
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = paginaActual * filasPorPagina;
        if (indexVisible >= inicio && indexVisible < fin) {
          fila.classList.remove('hidden-row');
          fila.style.display = ''; // que vuelva a su estado por defecto (table-row)
          const rowIndexOnPage = indexVisible - inicio;
          fila.style.backgroundColor = (rowIndexOnPage % 2 === 0) ? '#ffffff' : '#ffeddf';
        }
      });

      if (info) {
        const desde = visibles.length ? Math.min((paginaActual - 1) * filasPorPagina + 1, visibles.length) : 0;
        const hasta = visibles.length ? Math.min(paginaActual * filasPorPagina, visibles.length) : 0;
        info.textContent = `Mostrando ${desde} a ${hasta} de ${visibles.length} elementos`;
      }

      renderizarBotones(totalPaginas);
    }

    function renderizarBotones(total) {
      if (!paginacion) return;
      paginacion.innerHTML = '';

      const crearItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.textContent = label;
        a.href = '#';
        a.addEventListener('click', e => {
          e.preventDefault();
          if (!disabled && paginaActual !== page) {
            paginaActual = Math.max(1, Math.min(page, total || 1));
            aplicarFiltrosYPaginar();
          }
        });
        li.appendChild(a);
        return li;
      };

      // Prev
      paginacion.appendChild(crearItem('«', paginaActual - 1, paginaActual === 1));

      for (let i = 1; i <= (total || 1); i++) {
        paginacion.appendChild(crearItem(i, i, false, i === paginaActual));
      }

      // Next
      paginacion.appendChild(crearItem('»', paginaActual + 1, paginaActual === total || total === 0));
    }

    // Eventos: re-evaluar y resetear página al cambiar filtro o búsqueda
    if (filtroSelect) {
      filtroSelect.addEventListener('change', () => {
        paginaActual = 1;
        aplicarFiltrosYPaginar();
      });
    }

    if (buscador) {
      buscador.addEventListener('input', () => {
        paginaActual = 1;
        aplicarFiltrosYPaginar();
      });
    }

    // Mostrar estado dinámico para selects de series (mantener compatibilidad si existen)
    window.mostrarEstado = function (select) {
      const selectedOption = select.options[select.selectedIndex];
      const estado = selectedOption?.getAttribute('data-estado');
      const talle = selectedOption?.getAttribute('data-talle');
      const fila = select.closest('tr');
      const badge = fila?.querySelector('.estado-vencimiento');

      if (!badge || !estado) {
        if (badge) badge.textContent = '';
        return;
      }

      badge.textContent = talle ? `${estado} (Talle ${talle})` : estado;
      badge.style.display = 'inline-block';
      badge.className = 'badge estado-vencimiento px-2 py-1 border rounded small fw-semibold';

      switch (estado.toLowerCase()) {
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
