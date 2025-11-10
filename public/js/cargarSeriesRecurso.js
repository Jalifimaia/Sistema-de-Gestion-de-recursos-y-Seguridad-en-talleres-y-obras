window.addEventListener('load', function () {
  const modalTitle = document.getElementById('modalSeriesLabel');
  const tablaBody = document.getElementById('tablaSeriesBody');
  const buscadorSerie = document.getElementById('buscadorSerie');
  const filtroEstado = document.getElementById('filtroEstado');

  document.querySelectorAll('.btn-ver-series').forEach(btn => {
    btn.addEventListener('click', function () {
      const nombre = this.dataset.nombre;
      const rawSeries = this.dataset.series;
      const decodedSeries = rawSeries.replace(/&quot;/g, '"');
      let series = [];

      try {
        series = JSON.parse(decodedSeries);
      } catch (error) {
        console.error('Error al parsear series:', error);
        tablaBody.innerHTML = '<tr><td colspan="3" class="text-danger text-center">Error al cargar las series</td></tr>';
        return;
      }

      modalTitle.textContent = `Series del recurso: ${nombre}`;
      tablaBody.innerHTML = '';
      buscadorSerie.value = '';
      filtroEstado.value = 'todos';

      if (!series.length) {
        tablaBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay series registradas</td></tr>';
        return;
      }

      series.forEach(serie => {
        const nroSerie = serie.nro_serie || (serie.codigo?.codigo_base ?? 'SIN-CODIGO') + '-' + String(serie.correlativo ?? '00').padStart(2, '0');
        const estado = serie.estado?.nombre_estado || serie.nombre_estado || 'Sin estado';
        const color = serie.color?.nombre_color || 'Sin color';

        const fila = document.createElement('tr');
        fila.innerHTML = `<td>${nroSerie}</td><td>${estado}</td><td>${color}</td>`;
        fila.dataset.serie = nroSerie.toLowerCase();
        fila.dataset.estado = estado.toLowerCase();
        tablaBody.appendChild(fila);
      });

      aplicarFiltrosModal();
    });
  });

  buscadorSerie?.addEventListener('input', aplicarFiltrosModal);
  filtroEstado?.addEventListener('change', aplicarFiltrosModal);

  function aplicarFiltrosModal() {
    const texto = buscadorSerie.value.toLowerCase();
    const estadoSeleccionado = filtroEstado.value.toLowerCase();
    const filas = Array.from(tablaBody.querySelectorAll('tr'));

    const visibles = filas.filter(fila => {
      const serie = fila.dataset.serie || '';
      const estado = fila.dataset.estado || '';
      const coincideSerie = serie.includes(texto);
      const coincideEstado = (estadoSeleccionado === 'todos') || (estado === estadoSeleccionado);
      return coincideSerie && coincideEstado;
    });

    filas.forEach(fila => fila.style.display = 'none');
    paginarTablaSeries(visibles, 7);
  }

  function paginarTablaSeries(filas, porPagina = 7) {
    if (!filas.length) return;

    const paginacion = document.getElementById('paginacionSeries');
    const info = document.getElementById('infoPaginacionSeries');
    paginacion.innerHTML = '';
    info.textContent = '';

    const total = filas.length;
    const paginas = Math.ceil(total / porPagina);
    let actual = 1;

    function mostrarPagina(n) {
      actual = n;
      const inicio = (n - 1) * porPagina;
      const fin = inicio + porPagina;

      const visibles = filas.slice(inicio, fin);
      filas.forEach(fila => fila.style.display = 'none');
      visibles.forEach(fila => fila.style.display = '');

      // Reaplicar colores alternados
      visibles.forEach((fila, index) => {
        fila.classList.remove('table-row-par', 'table-row-impar');
        fila.classList.add(index % 2 === 0 ? 'table-row-par' : 'table-row-impar');
      });

      // Actualizar botón activo
      const botones = paginacion.querySelectorAll('.page-item');
      botones.forEach(btn => btn.classList.remove('active'));
      if (botones[n]) botones[n].classList.add('active');

      info.textContent = `Mostrando ${Math.min(fin, total)} de ${total} series`;
    }

    // Flecha izquierda
    const prevLi = document.createElement('li');
    prevLi.className = 'page-item';
    const prevA = document.createElement('a');
    prevA.className = 'page-link';
    prevA.href = '#';
    prevA.innerHTML = '&laquo;';
    prevA.addEventListener('click', e => {
      e.preventDefault();
      if (actual > 1) mostrarPagina(actual - 1);
    });
    prevLi.appendChild(prevA);
    paginacion.appendChild(prevLi);

    // Botones de página
    for (let i = 1; i <= paginas; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === 1 ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = i;
      a.addEventListener('click', e => {
        e.preventDefault();
        mostrarPagina(i);
      });
      li.appendChild(a);
      paginacion.appendChild(li);
    }

    // Flecha derecha
    const nextLi = document.createElement('li');
    nextLi.className = 'page-item';
    const nextA = document.createElement('a');
    nextA.className = 'page-link';
    nextA.href = '#';
    nextA.innerHTML = '&raquo;';
    nextA.addEventListener('click', e => {
      e.preventDefault();
      if (actual < paginas) mostrarPagina(actual + 1);
    });
    nextLi.appendChild(nextA);
    paginacion.appendChild(nextLi);

    mostrarPagina(1);
  }
});
