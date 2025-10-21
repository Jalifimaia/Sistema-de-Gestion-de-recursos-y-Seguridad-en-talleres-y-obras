document.addEventListener('DOMContentLoaded', function () {
  const filtroSelect = document.getElementById('filtroInventario');
  if (filtroSelect) {
    filtroSelect.addEventListener('change', function () {
      const filtro = this.value;
      const filas = document.querySelectorAll('table tbody tr');

      filas.forEach(fila => {
        const categoria = fila.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();
        const estadoSerie = fila.querySelector('.estado-vencimiento')?.textContent?.toLowerCase() || '';

        let mostrar = true;

        if (filtro === 'herramienta' && !categoria.includes('herramienta')) {
          mostrar = false;
        } else if (filtro === 'epp' && !categoria.includes('epp')) {
          mostrar = false;
        } else if (filtro === 'reparacion' && !estadoSerie.includes('reparación')) {
          mostrar = false;
        }

        fila.style.display = mostrar ? '' : 'none';
      });
    });
  }

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

  const buscador = document.getElementById('buscador');
  if (buscador) {
    buscador.addEventListener('input', function () {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('table tbody tr');

      filas.forEach(fila => {
        const nombre = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const serieSelect = fila.querySelector('select');
        const series = Array.from(serieSelect?.options || []).map(opt => opt.textContent.toLowerCase());

        const coincideNombre = nombre.includes(filtro);
        const coincideSerie = series.some(serie => serie.includes(filtro));

        fila.style.display = (coincideNombre || coincideSerie) ? '' : 'none';
      });
    });
  }
});
