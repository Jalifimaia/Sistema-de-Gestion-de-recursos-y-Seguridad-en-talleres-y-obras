let contador = 1;
const categoriaSelect = document.getElementById('categoria');
const subcategoriaSelect = document.getElementById('subcategoria');
const recursoSelect = document.getElementById('recurso');
const serieSelect = document.getElementById('serie');
const tabla = document.querySelector('#tablaPrestamos tbody');
const agregarBtn = document.getElementById('agregar');

// Cargar subcategorías al cambiar categoría
categoriaSelect.addEventListener('change', () => {
  fetch(`/api/subcategorias/${categoriaSelect.value}`)
    .then(res => res.json())
    .then(data => {
      subcategoriaSelect.innerHTML = '<option selected disabled>Seleccione una subcategoría</option>';
      data.forEach(sub => {
        subcategoriaSelect.innerHTML += `<option value="${sub.id}">${sub.nombre}</option>`;
      });
      recursoSelect.innerHTML = '<option selected disabled>Seleccione un recurso</option>';
      serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
    });
});

// Cargar recursos al cambiar subcategoría
subcategoriaSelect.addEventListener('change', () => {
  fetch(`/api/recursos/${subcategoriaSelect.value}`)
    .then(res => res.json())
    .then(data => {
      recursoSelect.innerHTML = '<option selected disabled>Seleccione un recurso</option>';
      data.forEach(r => {
        recursoSelect.innerHTML += `<option value="${r.id}">${r.nombre}</option>`;
      });
      serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
    });
});

// Cargar series al cambiar recurso
recursoSelect.addEventListener('change', () => {
  fetch(`/api/series/${recursoSelect.value}`)
    .then(res => res.json())
    .then(data => {
      serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
      data.forEach(s => {
        serieSelect.innerHTML += `<option value="${s.id}">${s.nro_serie}</option>`;
      });

      // Ocultar series ya agregadas
      if (Array.isArray(window.seriesOcultas)) {
        window.seriesOcultas.forEach(id => {
          const option = serieSelect.querySelector(`option[value="${id}"]`);
          if (option) option.style.display = 'none';
        });
      }
    });
});

// Agregar a tabla
agregarBtn.addEventListener('click', () => {
  const recursoText = recursoSelect.options[recursoSelect.selectedIndex]?.text;
  const serieText = serieSelect.options[serieSelect.selectedIndex]?.text;
  const serieId = serieSelect.value;

  if (!serieId || serieSelect.selectedIndex === 0) return;

  const fila = document.createElement('tr');
  fila.innerHTML = `
    <td>${contador++}</td>
    <td>${recursoText}</td>
    <td>${serieText}</td>
    <td>
      <button type="button" class="btn btn-sm btn-danger eliminar">Eliminar</button>
      <input type="hidden" name="series[]" value="${serieId}">
    </td>
  `;
  tabla.appendChild(fila);

  const optionToHide = serieSelect.querySelector(`option[value="${serieId}"]`);
  if (optionToHide) optionToHide.style.display = 'none';

  window.seriesOcultas = window.seriesOcultas || [];
  window.seriesOcultas.push(serieId);

  fila.querySelector('.eliminar').addEventListener('click', () => {
    if (optionToHide) optionToHide.style.display = 'block';
    fila.remove();
    window.seriesOcultas = window.seriesOcultas.filter(id => id !== serieId);
  });

  serieSelect.selectedIndex = 0;
});

// Precargar tabla con series ya prestadas
window.addEventListener('load', () => {
  if (Array.isArray(window.detalles) && window.detalles.length > 0) {
    window.seriesOcultas = [];

    window.detalles.forEach((detalle) => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td>${contador++}</td>
        <td>${detalle.recurso_nombre}</td>
        <td>${detalle.serie_nro}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger eliminar">Eliminar</button>
          <input type="hidden" name="series[]" value="${detalle.serie_id}">
        </td>
      `;
      tabla.appendChild(fila);

      window.seriesOcultas.push(detalle.serie_id);

      fila.querySelector('.eliminar').addEventListener('click', () => {
        const option = serieSelect.querySelector(`option[value="${detalle.serie_id}"]`);
        if (option) option.style.display = 'block';
        fila.remove();
        window.seriesOcultas = window.seriesOcultas.filter(id => id !== detalle.serie_id);
      });
    });
  }
});
