document.addEventListener('DOMContentLoaded', function () {
  const filtroSelect = document.getElementById('filtroInventario');
  if (!filtroSelect) return;

  filtroSelect.addEventListener('change', function () {
    const filtro = this.value;
    const filas = document.querySelectorAll('table tbody tr');

    filas.forEach(fila => {
      const categoria = fila.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
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


  window.mostrarEstado = function (select) {
    const selectedOption = select.options[select.selectedIndex];
    const estado = select.value;
    const fechaVencimiento = selectedOption.getAttribute('data-fecha-vencimiento');
    const badge = select.parentElement.querySelector('.estado-vencimiento');

    badge.className = 'badge estado-vencimiento'; // Reset classes

    if (!estado || !fechaVencimiento) {
      badge.textContent = '';
      return;
    }

    const hoy = new Date();
    const fechaV = new Date(fechaVencimiento);
    const diffTime = fechaV - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const fechaSolo = fechaV.toISOString().slice(0, 10);

    let texto = '';
    if (estado === 'Vencido' || diffDays < 0) {
      texto = `Vencido - ${fechaSolo}`;
      badge.classList.add('bg-danger');
    } else if (estado === 'Por vencer' || diffDays <= 7) {
      texto = `Por vencer - ${fechaSolo}`;
      badge.classList.add('bg-warning', 'text-dark');
    } else if (estado === 'Vigente') {
      texto = `Vigente - ${fechaSolo}`;
      badge.classList.add('bg-success');
    }
    badge.textContent = texto;
  };


   const buscador = document.getElementById('buscador');
  if (!buscador) return;

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
});
