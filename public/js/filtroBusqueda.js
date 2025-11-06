document.addEventListener('DOMContentLoaded', function () {
  const filtroSelect = document.getElementById('filtroInventario');
  const buscador = document.getElementById('buscador');

  const normalizar = str => str
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // elimina tildes
    .replace(/\s+/g, ' ')
    .trim();

  function filtrarFilas() {
    const filtro = filtroSelect ? filtroSelect.value : 'todos';
    const textoBusqueda = buscador ? normalizar(buscador.value) : '';
    const filas = document.querySelectorAll('table tbody tr');

    filas.forEach(fila => {
      const categoria = normalizar(fila.querySelector('td:nth-child(4)')?.textContent || '');
      const nombre = normalizar(fila.querySelector('td:nth-child(1)')?.textContent || '');
      const estados = Array.from(fila.querySelectorAll('select option'))
        .map(opt => opt.getAttribute('data-estado')?.toLowerCase())
        .filter(Boolean);

      let mostrar = true;

      // --- Filtro por tipo ---
      if (filtro === 'herramienta' && !categoria.includes('herramienta')) mostrar = false;
      else if (filtro === 'epp' && !categoria.includes('epp')) mostrar = false;
      else if (filtro === 'reparacion' && !estados.includes('en reparación')) mostrar = false;
      else if (filtro === 'baja') {
        // mostrar solo si todas las series están dadas de baja
        const todasBaja = estados.length > 0 && estados.every(e => e === 'baja');
        if (!todasBaja) mostrar = false;
      }
      else if (filtro === 'devueltos' && !estados.includes('devuelto')) mostrar = false;
      else if (filtro === 'sin-series' && fila.querySelector('select option')) mostrar = false;

      // --- Filtro por texto (nombre) ---
      if (textoBusqueda && !nombre.includes(textoBusqueda)) mostrar = false;

      fila.style.display = mostrar ? '' : 'none';
    });
    // Rehabilitar botones de acción aunque la fila esté filtrada
    document.querySelectorAll('a.btn, button').forEach(btn => {
      btn.style.pointerEvents = 'auto';
      btn.style.position = 'relative';
      btn.style.zIndex = '10';
    });

  }

  // --- Eventos de filtrado ---
  if (filtroSelect) filtroSelect.addEventListener('change', filtrarFilas);
  if (buscador) buscador.addEventListener('input', filtrarFilas);

  // --- Mostrar estado dinámico ---
  window.mostrarEstado = function (select) {
    const selectedOption = select.options[select.selectedIndex];
    const estado = selectedOption.getAttribute('data-estado');
    const talle = selectedOption.getAttribute('data-talle');
    const fila = select.closest('tr');
    const badge = fila.querySelector('.estado-vencimiento');

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

  // --- Inicializar los estados ---
  document.querySelectorAll('select[data-id]').forEach(select => {
    const firstValid = Array.from(select.options).find(opt => opt.value && opt.getAttribute('data-estado'));
    if (firstValid) {
      select.value = firstValid.value;
      mostrarEstado(select);
    }
  });
});
