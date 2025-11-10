document.addEventListener('DOMContentLoaded', function () {
  const filas = Array.from(document.querySelectorAll('table.table-naranja tbody tr'));
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
      return (filtro === 'todos' || categoria.includes(filtro)) && nombre.includes(texto);
    });

    const totalPaginas = Math.ceil(visibles.length / filasPorPagina);
    paginaActual = Math.min(paginaActual, totalPaginas || 1);

    filas.forEach(fila => fila.classList.add('hidden-row'));
    visibles.forEach((fila, index) => {
      const inicio = (paginaActual - 1) * filasPorPagina;
      const fin = paginaActual * filasPorPagina;
      if (index >= inicio && index < fin) {
        fila.classList.remove('hidden-row');
        fila.style.backgroundColor = (index % 2 === 0) ? '#ffffff' : '#ffeddf';
      }
    });

    info.textContent = `Mostrando ${Math.min((paginaActual - 1) * filasPorPagina + 1, visibles.length)} a ${Math.min(paginaActual * filasPorPagina, visibles.length)} de ${visibles.length} elementos`;
    renderizarBotones(totalPaginas);
  }

  function renderizarBotones(total) {
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
          paginaActual = page;
          aplicarFiltrosYPaginar();
        }
      });
      li.appendChild(a);
      return li;
    };

    paginacion.appendChild(crearItem('«', paginaActual - 1, paginaActual === 1));
    for (let i = 1; i <= total; i++) {
      paginacion.appendChild(crearItem(i, i, false, i === paginaActual));
    }
    paginacion.appendChild(crearItem('»', paginaActual + 1, paginaActual === total));
  }

  filtroSelect?.addEventListener('change', () => {
    paginaActual = 1;
    aplicarFiltrosYPaginar();
  });

  buscador?.addEventListener('input', () => {
    paginaActual = 1;
    aplicarFiltrosYPaginar();
  });

  aplicarFiltrosYPaginar();
  
});
