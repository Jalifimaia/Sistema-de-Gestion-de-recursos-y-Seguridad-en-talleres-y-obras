document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('recursoForm');
  const mensaje = document.getElementById('mensaje');
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');
  const nuevaSubInput = document.getElementById('nuevaSubcategoria');
  const agregarBtn = document.getElementById('agregarSubcategoria');
  const descripcion = document.getElementById('descripcion');

  // 🔹 Cargar subcategorías dinámicamente al cambiar categoría
  categoriaSelect.addEventListener('change', function () {
    const categoriaId = this.value;
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
        subcategoriaSelect.innerHTML = '<option>Error al cargar</option>';
        subcategoriaSelect.disabled = true;
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
      mensaje.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    });
  });
  form.addEventListener('submit', function (e) {
  e.preventDefault();

  // 🔹 Limpiar errores previos
  form.querySelectorAll('.text-danger.small.mt-1').forEach(el => el.remove());

  let firstInvalid = null;

  // 🔹 Validar campos requeridos
  const requiredFields = form.querySelectorAll('[required]');
  requiredFields.forEach(field => {
    const container = field.closest('.mb-3') || field.parentElement;
    const errorId = 'error-' + field.id;

    if (!field.value.trim()) {
      const error = document.createElement('div');
      error.className = 'text-danger small mt-1';
      error.id = errorId;
      error.textContent = 'Este campo es obligatorio.';
      container.appendChild(error);
      if (!firstInvalid) firstInvalid = field;
    }
  });

  // 🔹 Validar descripción: máximo 4 palabras
  const palabras = descripcion.value.trim().split(/\s+/);
  if (palabras.length > 4) {
    const container = descripcion.closest('.mb-3');
    const error = document.createElement('div');
    error.className = 'text-danger small mt-1';
    error.id = 'error-descripcion';
    error.textContent = '⚠️ Te pasaste de las 4 palabras.';
    container.appendChild(error);
    if (!firstInvalid) firstInvalid = descripcion;
  }

      if (firstInvalid) {
  firstInvalid.focus();

  // Recolectar mensajes de error actuales
  const errors = Array.from(form.querySelectorAll('.text-danger.small.mt-1'))
                      .map(el => el.textContent);

  // Mostrar modal solo si el error corresponde a campos obligatorios vacíos
  if (errors.includes('Este campo es obligatorio.') &&
      (firstInvalid.id === 'nombre' ||
       firstInvalid.id === 'categoria' ||
       firstInvalid.id === 'id_subcategoria' ||
       firstInvalid.id === 'costo_unitario')) {
    const modalErrorEl = document.getElementById('modalErrorCampos');
    if (modalErrorEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      new bootstrap.Modal(modalErrorEl).show();
    }
  }

  return; // No enviar si hay errores
}



  const payload = {
    id_subcategoria: subcategoriaSelect.value,
    nombre: document.getElementById('nombre').value,
    descripcion: descripcion.value,
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

      let firstBackendError = null;

      Object.entries(data.errors).forEach(([field, messages]) => {
        const input = document.getElementById(field);
        if (!input) return;

        const container = input.closest('.mb-3') || input.parentElement;
        const error = document.createElement('div');
        error.className = 'text-danger small mt-1';
        error.textContent = messages[0];
        container.appendChild(error);

        if (!firstBackendError) firstBackendError = input;
      });

      if (firstBackendError) firstBackendError.focus();

      throw new Error('Hay errores en el formulario.');
    } else {
      const text = await res.text();
      throw new Error(`Respuesta inesperada del servidor. Código ${res.status}`);
    }
  })
  .then(data => {
  form.reset();
  subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
  subcategoriaSelect.disabled = true;

  // 🔹 Mostrar el modal de éxito
  const modalEl = document.getElementById('modalRecursoCreado');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    new bootstrap.Modal(modalEl).show();
  }
})

  .catch(error => {
    mensaje.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
  });
});

form.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();

    // Simular envío del formulario para que se dispare el único submit que tenemos
    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
  }
});

const input = document.getElementById('costo_unitario');

  input.addEventListener('input', () => {
    // quitamos todo lo que no sea dígito
    let value = input.value.replace(/\D/g, '');
    if (value) {
      // formateamos con separador de miles
      input.value = new Intl.NumberFormat('es-AR').format(value);
    }
  });

  // al enviar el form, limpiamos los puntos para que el backend reciba el número real
  input.form.addEventListener('submit', () => {
    input.value = input.value.replace(/\./g, '');
  });
  
  console.log('✅ recurso.js cargado');
});
