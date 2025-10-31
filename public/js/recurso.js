document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('recursoForm');
  const mensaje = document.getElementById('mensaje');
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');
  const nuevaSubInput = document.getElementById('nuevaSubcategoria');
  const agregarBtn = document.getElementById('agregarSubcategoria');

  // 🔹 Cargar subcategorías dinámicamente al cambiar categoría
  categoriaSelect.addEventListener('change', function () {
    const categoriaId = this.value;
    console.log('Categoría seleccionada:', categoriaId);

    subcategoriaSelect.innerHTML = '<option>Cargando...</option>';
    subcategoriaSelect.disabled = true;

    if (!categoriaId) {
      subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
      return;
    }

    fetch(`/inventario/ajax/subcategorias/${encodeURIComponent(categoriaId)}`)
      .then(res => {
        if (!res.ok) throw new Error(`Error ${res.status}`);
        return res.json();
      })
      .then(data => {
        let options = '<option value="">Seleccione una subcategoría</option>';
        data.forEach(sub => {
          options += `<option value="${sub.id}">${sub.nombre}</option>`;
        });
        subcategoriaSelect.innerHTML = options;
        subcategoriaSelect.disabled = false;
      })
      .catch(error => {
        console.error('Error al cargar subcategorías:', error.message);
        subcategoriaSelect.innerHTML = '<option>Error al cargar</option>';
        subcategoriaSelect.disabled = true;
      });
  });

  // 🔹 Envío del formulario de recurso
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const payload = {
      id_subcategoria: subcategoriaSelect.value,
      nombre: document.getElementById('nombre').value,
      descripcion: document.getElementById('descripcion').value,
      costo_unitario: document.getElementById('costo_unitario').value,
    };

    fetch('/recursos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(payload),
    })
    .then(async res => {
      const contentType = res.headers.get('content-type');
      if (res.ok && contentType && contentType.includes('application/json')) {
        return res.json();
      } else if (res.status === 422) {
        const data = await res.json();
        const errores = Object.values(data.errors).flat().join('<br>');
        throw new Error(errores);
      } else {
        const text = await res.text();
        throw new Error(`Respuesta inesperada del servidor. Código ${res.status}`);
      }
    })
    .then(data => {
      mensaje.innerHTML = `<div class="alert alert-success">Recurso creado correctamente.</div>`;
      form.reset();
      subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
      subcategoriaSelect.disabled = true;
    })
    .catch(error => {
      console.error('Error al guardar el recurso:', error.message);
      mensaje.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    });
  });

  // 🔹 Agregar nueva subcategoría con validación contra duplicados
  agregarBtn.addEventListener('click', function () {
    const nombre = nuevaSubInput.value.trim();
    const categoriaId = categoriaSelect.value;

    if (!nombre || !categoriaId) {
      mensaje.innerHTML = `<div class="alert alert-warning">Escribí un nombre y seleccioná una categoría.</div>`;
      return;
    }

    // Validación: evitar duplicados en la categoría actual
    const nombreNormalizado = nombre.toLowerCase();
    const yaExiste = Array.from(subcategoriaSelect.options).some(opt =>
      opt.textContent.trim().toLowerCase() === nombreNormalizado
    );

    if (yaExiste) {
      mensaje.innerHTML = `<div class="alert alert-warning">⚠️ Esa subcategoría ya existe en esta categoría.</div>`;
      return;
    }

    fetch('/subcategorias', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ nombre, categoria_id: categoriaId }),
    })
    .then(async res => {
      const contentType = res.headers.get('content-type');
      if (res.ok && contentType && contentType.includes('application/json')) {
        return res.json();
      } else if (res.status === 409) {
        const data = await res.json();
        throw new Error(data.error || 'Ya existe una subcategoría con ese nombre.');
      } else if (res.status === 422) {
        const data = await res.json();
        const errores = Object.values(data.errors).flat().join('<br>');
        throw new Error(errores);
      } else {
        const text = await res.text();
        throw new Error(`Respuesta inesperada del servidor. Código ${res.status}`);
      }
    })
    .then(data => {
      categoriaSelect.dispatchEvent(new Event('change'));
      setTimeout(() => {
        subcategoriaSelect.value = data.id;
      }, 300);

      nuevaSubInput.value = '';
      mensaje.innerHTML = `<div class="alert alert-success">Subcategoría agregada.</div>`;
    })
    .catch(error => {
      console.error('Error al agregar subcategoría:', error.message);
      mensaje.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    });
  });

  // 🔹 Mostrar modal si existe
  const modalEl = document.getElementById('modalRecursoCreado');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    new bootstrap.Modal(modalEl).show();
  }

  console.log('✅ recurso.js cargado');
});
