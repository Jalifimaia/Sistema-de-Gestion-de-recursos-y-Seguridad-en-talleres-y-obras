// public/js/cargarSeriesRecurso.js
window.addEventListener('load', function () {
  const modalEl = document.getElementById('modalSeries'); // contenedor del modal
  const modalTitle = document.getElementById('modalSeriesLabel');
  const tablaBody = document.getElementById('tablaSeriesBody');
  const buscadorSerie = document.getElementById('buscadorSerie');
  const filtroEstado = document.getElementById('filtroEstado');
  const paginacionSeries = document.getElementById('paginacionSeries');
  const infoPaginacionSeries = document.getElementById('infoPaginacionSeries');

  // defensiva: si faltan elementos críticos, no rompemos la página
  if (!tablaBody) return console.warn('tablaSeriesBody no encontrada en DOM — no se cargan series en modal.');

  // Helper para obtener instancia bootstrap sin fallar
  function getModalInstance(el) {
    if (!el) return null;
    // Bootstrap 5: getOrCreateInstance, pero usamos fallback para compatibilidad
    if (typeof bootstrap?.Modal?.getOrCreateInstance === 'function') {
      return bootstrap.Modal.getOrCreateInstance(el);
    }
    try {
      return new bootstrap.Modal(el);
    } catch (e) {
      console.warn('No se pudo crear instancia de bootstrap.Modal', e);
      return null;
    }
  }

  document.querySelectorAll('.btn-ver-series').forEach(btn => {
    btn.addEventListener('click', function () {
      const nombre = this.dataset.nombre || '';
      const rawSeries = this.dataset.series || '[]';
      let series = [];

      try {
        // dataset ya debería contener JSON válido; algunos escapes vienen como &quot;
        const decoded = rawSeries.replace(/&quot;/g, '"');
        series = JSON.parse(decoded);
      } catch (err) {
        console.error('Error parseando series JSON', err);
        tablaBody.innerHTML = '<tr><td colspan="4" class="text-danger text-center">Error al cargar las series</td></tr>';
        return;
      }

      if (modalTitle) modalTitle.textContent = `Series del recurso: ${nombre}`;
      tablaBody.innerHTML = '';
      if (buscadorSerie) buscadorSerie.value = '';
      if (filtroEstado) filtroEstado.value = 'todos';

      if (!series.length) {
        tablaBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay series registradas</td></tr>';
        // limpiar paginación
        if (paginacionSeries) paginacionSeries.innerHTML = '';
        if (infoPaginacionSeries) infoPaginacionSeries.textContent = '';
        return;
      }

      // Construir filas de manera segura
      series.forEach(serie => {
        const nroSerie = serie.nro_serie || ((serie.codigo && serie.codigo.codigo_base) ? (serie.codigo.codigo_base + '-' + String(serie.correlativo ?? 0).padStart(2,'0')) : 'SIN-CODIGO');
        const estado = serie.estado?.nombre_estado || serie.nombre_estado || 'Sin estado';
        const color = serie.color?.nombre_color || 'Sin color';
        const serieId = serie.id ?? '';

        const fila = document.createElement('tr');
        fila.dataset.serie = String(nroSerie).toLowerCase();
        fila.dataset.estado = String(estado).toLowerCase();

        // Serie
        const tdSerie = document.createElement('td');
        tdSerie.textContent = nroSerie;

        // Estado -> badge
        const tdEstado = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = 'badge px-2 py-1 border rounded small fw-semibold';
        badge.textContent = estado;
        switch ((estado || '').toLowerCase()) {
          case 'disponible':
            badge.classList.add('bg-success');
            break;
          case 'prestado':
            badge.classList.add('bg-warning', 'text-dark');
            break;
          case 'en reparación':
          case 'en reparacion':
          case 'dañado':
          case 'danado':
            badge.classList.add('bg-danger');
            break;
          case 'devuelto':
            badge.classList.add('bg-info', 'text-dark');
            break;
          case 'baja':
            badge.classList.add('bg-secondary');
            break;
          default:
            badge.classList.add('bg-secondary');
        }
        tdEstado.appendChild(badge);

        // Color
        const tdColor = document.createElement('td');
        tdColor.textContent = color;

        // Acciones (Eliminar serie -> marcar baja)
        const tdAcciones = document.createElement('td');
        tdAcciones.className = 'text-nowrap';

        const contAcc = document.createElement('div');
        contAcc.className = 'd-flex align-items-center gap-2 flex-wrap';

        // Botón editar-serie (placeholder)
        const btnEditarSerie = document.createElement('button');
        btnEditarSerie.type = 'button';
        btnEditarSerie.className = 'btn btn-sm btn-editar btn-accion-compact';
        btnEditarSerie.title = 'Editar';
        btnEditarSerie.innerHTML = '<i class="bi bi-pencil"></i><span class="ms-1">Editar</span>';
        btnEditarSerie.addEventListener('click', () => {
          // redirigir a edit si existe ruta, ejemplo:
          if (serieId) window.location.href = `/serie_recurso/${serieId}/edit`;
        });
        contAcc.appendChild(btnEditarSerie);

        // Botón ver QR
        const btnQr = document.createElement('a');
        btnQr.href = `/series/${serieId}/qr`; // ruta showQr
        btnQr.target = '_blank';
        btnQr.className = 'btn btn-sm btn-success btn-accion';
        btnQr.title = 'Ver QR';
        btnQr.innerHTML = '<i class="bi bi-qr-code"></i><span class="ms-1">QR</span>';
        contAcc.appendChild(btnQr);
              
        // Botón descargar QR en PDF (usar la ruta que sí funciona)
        const btnQrPdf = document.createElement('a');
        btnQrPdf.href = `/series-qr/${serieId}/pdf`;
        btnQrPdf.target = '_blank';
        btnQrPdf.className = 'btn btn-sm btn-warning btn-accion';
        btnQrPdf.title = 'Descargar QR en PDF';
        btnQrPdf.innerHTML = '<i class="bi bi-file-earmark-pdf"></i><span class="ms-1">PDF</span>';
        contAcc.appendChild(btnQrPdf);

        // Botón eliminar-serie (marcar baja)
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.className = 'btn btn-sm btn-eliminar-serie btn-danger btn-accion';
        btnEliminar.title = 'Eliminar (marcar baja)'; 
        btnEliminar.dataset.id = serieId;
        btnEliminar.dataset.nro = nroSerie;
        btnEliminar.innerHTML = '<i class="bi bi-trash"></i><span class="ms-1">Eliminar</span>';

        btnEliminar.addEventListener('click', function () {
          const nro = this.dataset.nro || serieId || 'sin nro';
          const modalConfirm = document.getElementById('modalConfirmDelete');
          const modalText = document.getElementById('modalConfirmDeleteText');
          const modalConfirmBtn = document.getElementById('modalConfirmDeleteBtn');

          if (modalConfirm && modalText && modalConfirmBtn) {
            modalText.textContent = `¿Seguro que querés marcar como baja la serie "${nro}"?`;
            const bs = getModalInstance(modalConfirm);
            // handler único por click (evitar duplicates)
            const handle = () => {
              modalConfirmBtn.disabled = true;
              // Hacer fetch a endpoint PATCH /serie_recurso/{id}/baja
              const id = serieId;
              const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              if (!id) {
                bs?.hide();
                modalConfirmBtn.disabled = false;
                modalConfirmBtn.removeEventListener('click', handle);
                return;
              }
              fetch(`/serie_recurso/${id}/baja`, {
                method: 'PATCH',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': token,
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: 'baja' })
              }).then(res => {
                if (res.ok) {
                  // marcar visualmente
                  fila.classList.add('table-row-baja');
                  contAcc.querySelectorAll('button, a').forEach(el => {
                    el.disabled = true;
                    el.classList.remove('btn-accion');
                    el.classList.add('btn-outline-secondary');
                  });
                  badge.className = 'badge px-2 py-1 border rounded small fw-semibold bg-secondary';
                  badge.textContent = 'Baja';
                } else {
                  console.error('Error en PATCH baja serie', res.status);
                }
              }).catch(err => console.error(err))
                .finally(() => {
                  bs?.hide();
                  modalConfirmBtn.disabled = false;
                  modalConfirmBtn.removeEventListener('click', handle);
                });
            };
            modalConfirmBtn.addEventListener('click', handle);
            bs?.show();
          } else {
            // fallback confirm
            if (!confirm(`¿Seguro que querés marcar como baja la serie "${nro}"?`)) return;
            fila.classList.add('table-row-baja');
            contAcc.querySelectorAll('button, a').forEach(el => {
              el.disabled = true;
              el.classList.remove('btn-accion');
              el.classList.add('btn-outline-secondary');
            });
            badge.className = 'badge px-2 py-1 border rounded small fw-semibold bg-secondary';
            badge.textContent = 'Baja';
          }
        });
        contAcc.appendChild(btnEliminar);

        tdAcciones.appendChild(contAcc);

        fila.appendChild(tdSerie);
        fila.appendChild(tdEstado);
        fila.appendChild(tdColor);
        fila.appendChild(tdAcciones);
        tablaBody.appendChild(fila);
      });

      // aplicar filtros y paginación
      aplicarFiltrosModal();
    });
  });

  // filtros y paginación (defensiva)
  function aplicarFiltrosModal() {
    const texto = (buscadorSerie?.value || '').toLowerCase();
    const estadoSeleccionado = (filtroEstado?.value || 'todos').toLowerCase();
    const filas = Array.from(tablaBody.querySelectorAll('tr'));
    const visibles = filas.filter(fila => {
      const serie = fila.dataset.serie || '';
      const estado = fila.dataset.estado || '';
      return serie.includes(texto) && (estadoSeleccionado === 'todos' || estado === estadoSeleccionado);
    });
    filas.forEach(f => f.style.display = 'none');
    paginarTablaSeries(visibles, 7);
  }

  function paginarTablaSeries(filas, porPagina = 7) {
    const pag = paginacionSeries;
    const info = infoPaginacionSeries;
    if (!pag || !info) {
      filas.forEach(f => f.style.display = '');
      return;
    }

    pag.innerHTML = '';
    info.textContent = '';

    const total = filas.length;
    if (total === 0) {
      tablaBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay series que coincidan</td></tr>';
      return;
    }

    const paginas = Math.ceil(total / porPagina);
    let actual = 1;

    function mostrarPagina(n) {
      actual = n;
      const inicio = (n - 1) * porPagina;
      const fin = inicio + porPagina;
      const visibles = filas.slice(inicio, fin);
      filas.forEach(f => f.style.display = 'none');
      visibles.forEach(f => f.style.display = '');
      visibles.forEach((f, idx) => {
        f.classList.remove('table-row-par', 'table-row-impar');
        f.classList.add(idx % 2 === 0 ? 'table-row-par' : 'table-row-impar');
      });

      // activar botón por texto (seguro si hay prev/next)
      const botones = Array.from(pag.querySelectorAll('.page-item'));
      botones.forEach(b => b.classList.remove('active'));
      botones.forEach(b => {
        const link = b.querySelector('a');
        if (link && link.textContent.trim() === String(n)) b.classList.add('active');
      });

      info.textContent = `Mostrando ${Math.min(fin, total)} de ${total} series`;
    }

    // prev
    const prev = document.createElement('li');
    prev.className = 'page-item';
    const aPrev = document.createElement('a');
    aPrev.className = 'page-link';
    aPrev.href = '#';
    aPrev.innerHTML = '&laquo;';
    aPrev.addEventListener('click', e => { e.preventDefault(); if (actual > 1) mostrarPagina(actual - 1); });
    prev.appendChild(aPrev);
    pag.appendChild(prev);

    // pages
    for (let i = 1; i <= paginas; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === 1 ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = i;
      a.addEventListener('click', e => { e.preventDefault(); mostrarPagina(i); });
      li.appendChild(a);
      pag.appendChild(li);
    }

    // next
    const next = document.createElement('li');
    next.className = 'page-item';
    const aNext = document.createElement('a');
    aNext.className = 'page-link';
    aNext.href = '#';
    aNext.innerHTML = '&raquo;';
    aNext.addEventListener('click', e => { e.preventDefault(); if (actual < paginas) mostrarPagina(actual + 1); });
    next.appendChild(aNext);
    pag.appendChild(next);

    mostrarPagina(1);
  }

  // listeners filtros modal
  if (buscadorSerie) buscadorSerie.addEventListener('input', aplicarFiltrosModal);
  if (filtroEstado) filtroEstado.addEventListener('change', aplicarFiltrosModal);
});
