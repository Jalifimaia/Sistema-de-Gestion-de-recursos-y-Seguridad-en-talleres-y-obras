let contador = 1;
const categoriaSelect = document.getElementById('categoria');
const subcategoriaSelect = document.getElementById('subcategoria');
const recursoSelect = document.getElementById('recurso');
const serieSelect = document.getElementById('serie');
const contenedorSeries = document.getElementById('contenedorSeries');
const agregarBtn = document.getElementById('agregar');

// 🔹 Cargar subcategorías al cambiar categoría
categoriaSelect.addEventListener('change', () => {
  const id = categoriaSelect.value;
  if (!id) return;

  fetch(`/api/prestamo/subcategorias/${id}`)
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

// 🔹 Cargar recursos al cambiar subcategoría
subcategoriaSelect.addEventListener('change', () => {
  const id = subcategoriaSelect.value;
  if (!id) return;

  fetch(`/api/prestamo/recursos/${id}`)
    .then(res => res.json())
    .then(data => {
      recursoSelect.innerHTML = '<option selected disabled>Seleccione un recurso</option>';
      data.forEach(r => {
        recursoSelect.innerHTML += `<option value="${r.id}">${r.nombre}</option>`;
      });

      serieSelect.innerHTML = '<option selected disabled>Seleccione una serie</option>';
    });
});

// 🔹 Cargar series al cambiar recurso
recursoSelect.addEventListener('change', () => {
  const id = recursoSelect.value;
  if (!id) return;

  fetch(`/api/prestamo/series/${id}`)
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

// 🔹 Agregar recurso dinámico
agregarBtn.addEventListener('click', () => {
  const recursoText = recursoSelect.options[recursoSelect.selectedIndex]?.text;
  const serieText = serieSelect.options[serieSelect.selectedIndex]?.text;
  const serieId = serieSelect.value;

  if (!serieId || serieSelect.selectedIndex === 0) return;

  if (document.querySelector(`input[value="${serieId}"]`)) {
    alert('Esta serie ya fue agregada.');
    return;
  }

  const tarjeta = document.createElement('div');
  tarjeta.className = 'col-md-4';
  tarjeta.innerHTML = `
    <div class="card border-success shadow-sm">
      <div class="card-body p-2">
        <h6 class="card-title mb-1">${recursoText}</h6>
        <p class="card-text text-muted mb-2">Serie: ${serieText}</p>
        <input type="hidden" name="series[]" value="${serieId}">
        <button type="button" class="btn btn-sm btn-outline-danger eliminar w-100 mt-2">Quitar</button>
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

// 🔹 Dar de baja recurso
document.querySelectorAll('.dar-baja').forEach(btn => {
  btn.addEventListener('click', function () {
    const detalleId = this.dataset.id;

    if (!confirm('¿Estás seguro de que querés dar de baja este recurso?')) return;

    fetch(`/prestamos/detalle/${detalleId}/baja`, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    .then(res => {
      if (!res.ok) throw new Error('Error al dar de baja');
      return res.json().catch(() => ({}));
    })
    .then(() => {
      console.log(`Recurso ${detalleId} dado de baja`);
      location.reload();
    })
    .catch(err => {
      console.error(err);
      alert('No se pudo dar de baja el recurso.');
    });
  });
});
