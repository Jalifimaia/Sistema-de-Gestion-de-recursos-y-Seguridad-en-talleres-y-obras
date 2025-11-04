document.addEventListener('DOMContentLoaded', function () {
  // DOM elements (con fallbacks mínimos)
  const trabajadorSelect = document.getElementById('id_trabajador') || document.getElementById('id_trabajador_select') || document.querySelector('select[name="id_trabajador"]');
  const trabajadorHidden = document.getElementById('id_trabajador_hidden') || document.querySelector('input[name="id_trabajador"]') || document.querySelector('input[name="id_trabajador_hidden"]');
  const cambiarTrabajadorBtn = document.getElementById('cambiarTrabajador');
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('subcategoria');
  const recursoSelect = document.getElementById('recurso');
  const serieSelect = document.getElementById('serie');
  const contenedorSeries = document.getElementById('contenedorSeries');
  const agregarBtn = document.getElementById('agregar');

  // Helpers
  function safeAddChange(el, fn) { if (el) el.addEventListener('change', fn); }
  function haySeriesAgregadas() { return contenedorSeries && contenedorSeries.querySelectorAll('input[name="series[]"]').length > 0; }
  function limpiarBackdropsYBody() { document.querySelectorAll('.modal-backdrop').forEach(b => b.remove()); document.body.classList.remove('modal-open'); }

  // Sincronizar hidden con select si existiera (y seguridad inicial)
  function syncTrabajadorHidden() {
    const val = trabajadorSelect ? trabajadorSelect.value || '' : (trabajadorHidden ? trabajadorHidden.value : '');
    if (trabajadorHidden) trabajadorHidden.value = val;
  }
  syncTrabajadorHidden();

  // Des/activar boton agregar según existencia trabajador
  function actualizarEstadoAgregar() {
    if (!agregarBtn) return;
    const tieneTrabajador = (trabajadorHidden && trabajadorHidden.value) || (trabajadorSelect && trabajadorSelect.value);
    agregarBtn.disabled = !tieneTrabajador;
  }
  actualizarEstadoAgregar();

  // Si ya hay series renderizadas, bloquear cambios de trabajador
  if (haySeriesAgregadas()) {
    if (trabajadorSelect) trabajadorSelect.disabled = true;
    if (cambiarTrabajadorBtn) cambiarTrabajadorBtn.style.display = 'inline-block';
    actualizarEstadoAgregar();
  }

  // AJAX: cargar subcategorías
  safeAddChange(categoriaSelect, () => {
    const id = categoriaSelect.value;
    if (!id) return;
    fetch(`/prestamo/subcategorias/${id}`)
      .then(res => { if (!res.ok) throw new Error('Respuesta no OK'); return res.json(); })
      .then(data => {
        if (!subcategoriaSelect) return;
        subcategoriaSelect.innerHTML = '<option selected disabled>Seleccione una subcategoría</option>';
        if (recursoSelect) recursoSelect.innerHTML = '<option selected disabled>Seleccione un recurso</option>';
        if (serieSelect) serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
        data.forEach(sub => {
          const nombre = sub.nombre || sub.nombre_subcategoria || 'Sin nombre';
          subcategoriaSelect.insertAdjacentHTML('beforeend', `<option value="${sub.id}">${nombre}</option>`);
        });
      })
      .catch(err => { console.error('Error al cargar subcategorías:', err); alert('No se pudieron cargar las subcategorías.'); });
  });

  // AJAX: cargar recursos
  safeAddChange(subcategoriaSelect, () => {
    const id = subcategoriaSelect.value;
    if (!id) return;
    fetch(`/prestamo/recursos/${id}`)
      .then(res => { if (!res.ok) throw new Error('Respuesta no OK'); return res.json(); })
      .then(data => {
        if (!recursoSelect) return;
        recursoSelect.innerHTML = '<option selected disabled>Seleccione un recurso</option>';
        if (serieSelect) serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
        data.forEach(r => {
          recursoSelect.insertAdjacentHTML('beforeend', `<option value="${r.id}">${r.nombre}</option>`);
        });
      })
      .catch(err => { console.error('Error al cargar recursos:', err); alert('No se pudieron cargar los recursos.'); });
  });

  // AJAX: cargar series
  safeAddChange(recursoSelect, () => {
    const id = recursoSelect.value;
    if (!id) return;
    fetch(`/prestamo/series/${id}`)
      .then(res => { if (!res.ok) throw new Error('Respuesta no OK'); return res.json(); })
      .then(data => {
        if (!serieSelect) return;
        serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
        data.forEach(s => {
          serieSelect.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.nro_serie}</option>`);
        });
        // ocultar las series ya usadas si vienen en window.seriesOcultas
        if (Array.isArray(window.seriesOcultas)) {
          window.seriesOcultas.forEach(idOculto => {
            const option = serieSelect.querySelector(`option[value="${idOculto}"]`);
            if (option) option.style.display = 'none';
          });
        }
      })
      .catch(err => { console.error('Error al cargar series:', err); alert('No se pudieron cargar las series.'); });
  });

  // Handler Agregar (mínimas validaciones)
  if (agregarBtn && contenedorSeries && serieSelect && recursoSelect) {
    agregarBtn.addEventListener('click', (e) => {
      e.preventDefault();

      // forzamos sync por si
      syncTrabajadorHidden();
      actualizarEstadoAgregar();

      const trabajadorId = (trabajadorHidden && trabajadorHidden.value) || (trabajadorSelect && trabajadorSelect.value) || null;
      if (!trabajadorId) {
        alert('Seleccioná primero un trabajador antes de agregar recursos.');
        return;
      }

      const serieId = serieSelect.value;
      const serieText = serieSelect.options[serieSelect.selectedIndex]?.text || '';
      const recursoText = recursoSelect.options[recursoSelect.selectedIndex]?.text || '';

      if (!serieId || serieSelect.selectedIndex === 0) {
        alert('Seleccioná una serie válida.');
        return;
      }

      // evitar duplicados (inputs hidden series[])
      if (contenedorSeries.querySelector(`input[name="series[]"][value="${serieId}"]`)) {
        alert('Esta serie ya fue agregada.');
        return;
      }

      // crear tarjeta DOM con hidden input (mismo name que espera el backend)
      const tarjeta = document.createElement('div');
      tarjeta.className = 'col-md-4';
      tarjeta.innerHTML = `
        <div class="card border-success shadow-sm">
          <div class="card-body p-2">
            <h6 class="card-title mb-1">${recursoText}</h6>
            <p class="card-text text-muted mb-2">Serie: <strong>${serieText}</strong></p>
            <input type="hidden" name="series[]" value="${serieId}">
            <button type="button" class="btn btn-sm btn-outline-danger eliminar w-100 mt-2">Quitar</button>
          </div>
        </div>
      `;
      contenedorSeries.appendChild(tarjeta);

      // ocultar option en el select de series para evitar re-uso
      const optionToHide = serieSelect.querySelector(`option[value="${serieId}"]`);
      if (optionToHide) optionToHide.style.display = 'none';

      // bloquear cambio de trabajador
      if (trabajadorSelect) trabajadorSelect.disabled = true;
      if (trabajadorHidden) trabajadorHidden.value = trabajadorSelect ? trabajadorSelect.value || trabajadorHidden.value : trabajadorHidden.value;
      if (cambiarTrabajadorBtn) cambiarTrabajadorBtn.style.display = 'inline-block';

      actualizarEstadoAgregar();

      // handler remover tarjeta
      tarjeta.querySelector('.eliminar').addEventListener('click', () => {
        if (optionToHide) optionToHide.style.display = 'block';
        tarjeta.remove();
        if (!haySeriesAgregadas()) {
          if (trabajadorSelect) trabajadorSelect.disabled = false;
          if (cambiarTrabajadorBtn) cambiarTrabajadorBtn.style.display = 'none';
          if (trabajadorHidden) trabajadorHidden.value = trabajadorSelect ? trabajadorSelect.value || '' : '';
        }
        actualizarEstadoAgregar();
      });

      // mostrar modal de confirmación opcional
      const modalEl = document.getElementById('modalRecursoAgregado');
      if (modalEl && typeof bootstrap !== 'undefined') {
        limpiarBackdropsYBody();
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        modalEl.addEventListener('hidden.bs.modal', () => { try { modal.dispose(); } catch(e){}; limpiarBackdropsYBody(); }, { once: true });
      }

      // reset serie select
      serieSelect.selectedIndex = 0;
    });
  }

  // Botón Cambiar trabajador (si existe)
  if (cambiarTrabajadorBtn) {
    cambiarTrabajadorBtn.addEventListener('click', () => {
      const confirmModalEl = document.getElementById('modalConfirmarCambioTrabajador');
      if (!confirmModalEl || typeof bootstrap === 'undefined') {
        if (!confirm('Cambiar trabajador eliminará los recursos agregados. Continuar?')) return;
        limpiarSeleccionTrabajador();
        return;
      }
      const modalBody = confirmModalEl.querySelector('.modal-body');
      if (modalBody) modalBody.textContent = 'Cambiar trabajador eliminará los recursos agregados. ¿Desea continuar?';
      limpiarBackdropsYBody();
      const confirmModal = new bootstrap.Modal(confirmModalEl);
      confirmModal.show();
      const btnConfirm = confirmModalEl.querySelector('[data-action="confirm"]');
      const btnCancel = confirmModalEl.querySelector('[data-action="cancel"]');
      function onConfirm() { limpiarSeleccionTrabajador(); try{ confirmModal.hide(); }catch(e){} }
      function onCancel() { try{ confirmModal.hide(); }catch(e){} }
      if (btnConfirm) btnConfirm.addEventListener('click', onConfirm, { once: true });
      if (btnCancel) btnCancel.addEventListener('click', onCancel, { once: true });
      confirmModalEl.addEventListener('hidden.bs.modal', () => { try{ confirmModal.dispose(); }catch(e){}; limpiarBackdropsYBody(); }, { once: true });
    });
  }

  function limpiarSeleccionTrabajador() {
    if (contenedorSeries) contenedorSeries.innerHTML = '';
    if (serieSelect) {
      Array.from(serieSelect.options).forEach(opt => opt.style.display = 'block');
      serieSelect.selectedIndex = 0;
    }
    if (trabajadorSelect) {
      trabajadorSelect.disabled = false;
      trabajadorSelect.selectedIndex = 0;
    }
    if (trabajadorHidden) trabajadorHidden.value = '';
    if (cambiarTrabajadorBtn) cambiarTrabajadorBtn.style.display = 'none';
    actualizarEstadoAgregar();
  }

  // Dar de baja recursos (botones preexistentes)
  document.querySelectorAll('.dar-baja').forEach(btn => {
    btn.addEventListener('click', function () {
      const detalleId = this.dataset.id;
      if (!detalleId) return;
      if (!confirm('¿Estás segura de que querés dar de baja este recurso?')) return;
      fetch(`/prestamos/detalle/${detalleId}/baja`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(res => { if (!res.ok) throw new Error('Error al dar de baja'); return res.json().catch(()=>({})); })
      .then(() => location.reload())
      .catch(err => { console.error(err); alert('No se pudo dar de baja el recurso.'); });
    });
  });

  // Mensajes suaves si faltan elementos
  if (!categoriaSelect) console.warn('No se encontró el select #categoria en el DOM.');
  if (!subcategoriaSelect) console.warn('No se encontró el select #subcategoria en el DOM.');
  if (!recursoSelect) console.warn('No se encontró el select #recurso en el DOM.');
  if (!serieSelect) console.warn('No se encontró el select #serie en el DOM.');
  if (!contenedorSeries) console.warn('No se encontró el contenedor #contenedorSeries en el DOM.');
  if (!agregarBtn) console.warn('No se encontró el botón #agregar en el DOM.');
});
