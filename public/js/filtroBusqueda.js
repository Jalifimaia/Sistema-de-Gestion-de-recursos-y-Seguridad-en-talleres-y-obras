document.addEventListener('DOMContentLoaded', function () {
  const filtroSelect = document.getElementById('filtroInventario');
  const buscador = document.getElementById('buscador');
  const filas = Array.from(document.querySelectorAll('table.table-naranja tbody tr'));

  function aplicarFiltros() {
    const filtro = filtroSelect?.value.toLowerCase() || 'todos';
    const texto = buscador?.value.toLowerCase() || '';

    const visibles = [];

    filas.forEach(fila => {
      const nombre = fila.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
      const categoria = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
      const coincideCategoria = (filtro === 'todos') || categoria.includes(filtro);
      const coincideNombre = nombre.includes(texto);
      const mostrar = coincideCategoria && coincideNombre;

      fila.classList.toggle('hidden-row', !mostrar);
      if (mostrar) visibles.push(fila);
    });

    // Reaplicar colores alternados solo a visibles
    visibles.forEach((fila, index) => {
      fila.style.backgroundColor = (index % 2 === 0) ? '#ffffff' : '#ffeddf';
    });
  }

  filtroSelect?.addEventListener('change', aplicarFiltros);
  buscador?.addEventListener('input', aplicarFiltros);
  aplicarFiltros(); // aplicar al cargar
});
