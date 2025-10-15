let contador = 1;
const categoriaSelect = document.getElementById('categoria');
const subcategoriaSelect = document.getElementById('subcategoria');
const recursoSelect = document.getElementById('recurso');
const serieSelect = document.getElementById('serie');
const contenedorSeries = document.getElementById('contenedorSeries');
const agregarBtn = document.getElementById('agregar');

// Cargar subcategorías al cambiar categoría
categoriaSelect.addEventListener('change', () => {
  const id = categoriaSelect.value;
  if (!id) return;

  fetch(`/subcategorias/${id}`)
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
  const id = subcategoriaSelect.value;
  if (!id) return;

  fetch(`/recursos/${id}`)
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
  const id = recursoSelect.value;
  if (!id) return;

  fetch(`/series/${id}`)
    .then(res => res.json())
    .then(data => {
      serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
      data.forEach(s => {
        serieSelect.innerHTML += `<option value="${s.id}">${s.nro_serie}</option>`;
      });

      if (Array.isArray(window.seriesOcultas)) {
        window.seriesOcultas.forEach(id => {
          const option = serieSelect.querySelector(`option[value="${id}"]`);
          if (option) option.style.display = 'none';
        });
      }
    });
});

// Agregar tarjeta de serie
agregarBtn.addEventListener('click', () => {
  const recursoText = recursoSelect.options[recursoSelect.selectedIndex]?.text;
  const serieText = serieSelect.options[serieSelect.selectedIndex]?.text;
  const serieId = serieSelect.value;

  if (!serieId || serieSelect.selectedIndex === 0) return;

  const tarjeta = document.createElement('div');
  tarjeta.className = 'col-md-4';
  tarjeta.innerHTML = `
    <div class="card border-success shadow-sm">
      <div class="card-body p-2">
        <h6 class="card-title mb-1">${recursoText}</h6>
        <p class="card-text text-muted mb-2">Serie: ${serieText}</p>
        <input type="hidden" name="series[]" value="${serieId}">
        <button type="button" class="btn btn-sm btn-outline-danger w-100 eliminar">Eliminar</button>
      </div>
    </div>
  `;
  contenedorSeries.appendChild(tarjeta);

  const optionToHide = serieSelect.querySelector(`option[value="${serieId}"]`);
  if (optionToHide) optionToHide.style.display = 'none';

  window.seriesOcultas = window.seriesOcultas || [];
  window.seriesOcultas.push(serieId);

  tarjeta.querySelector('.eliminar').addEventListener('click', () => {
    if (optionToHide) optionToHide.style.display = 'block';
    tarjeta.remove();
    window.seriesOcultas = window.seriesOcultas.filter(id => id !== serieId);
  });

  serieSelect.selectedIndex = 0;
});

// Precargar tarjetas en modo edición
window.addEventListener('load', () => {
  if (Array.isArray(window.detalles) && window.detalles.length > 0) {
    window.seriesOcultas = [];

    window.detalles.forEach((detalle) => {
      const tarjeta = document.createElement('div');
      tarjeta.className = 'col-md-4';
      tarjeta.innerHTML = `
        <div class="card border-success shadow-sm">
          <div class="card-body p-2">
            <h6 class="card-title mb-1">${detalle.recurso_nombre}</h6>
            <p class="card-text text-muted mb-2">Serie: ${detalle.serie_nro}</p>
            <input type="hidden" name="series[]" value="${detalle.serie_id}">
            <button type="button" class="btn btn-sm btn-outline-danger w-100 eliminar">Eliminar</button>
          </div>
        </div>
      `;
      contenedorSeries.appendChild(tarjeta);

      window.seriesOcultas.push(detalle.serie_id);

      tarjeta.querySelector('.eliminar').addEventListener('click', () => {
        const option = serieSelect.querySelector(`option[value="${detalle.serie_id}"]`);
        if (option) option.style.display = 'block';
        tarjeta.remove();
        window.seriesOcultas = window.seriesOcultas.filter(id => id !== detalle.serie_id);
      });
    });
  }
});
