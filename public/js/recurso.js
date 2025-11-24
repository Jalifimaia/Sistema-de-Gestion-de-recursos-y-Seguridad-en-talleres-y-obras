document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('recursoForm');
  const mensaje = document.getElementById('mensaje');
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');
  const descripcion = document.getElementById('descripcion');
  const contador = document.getElementById('contadorPalabras');
  const costoInput = document.getElementById('costo_unitario');

  // 🔹 Función para contar palabras
  function contarPalabras(texto) {
    const palabras = texto.trim().split(/\s+/).filter(p => p.length > 0);
    return palabras.length;
  }

  // 🔹 Actualizar contador de palabras
  function actualizarContador() {
    const cantidad = descripcion.value.trim() === '' ? 0 : contarPalabras(descripcion.value);
    contador.textContent = `${cantidad}/4 palabras`;
    contador.classList.toggle('text-danger', cantidad > 4);
    contador.classList.toggle('text-muted', cantidad <= 4);
  }

  // 🔹 Limitar a 4 palabras en descripción
  descripcion.addEventListener('input', function() {
    const texto = this.value;
    const palabras = texto.trim().split(/\s+/).filter(p => p.length > 0);
    
    if (palabras.length > 4) {
      this.value = palabras.slice(0, 4).join(' ');
    }
    
    actualizarContador();
    limpiarErrorCampo(this);
  });

  // Bloquear espacio si ya hay 4 palabras completas
  descripcion.addEventListener('keydown', function(e) {
    if (e.key === ' ') {
      const palabras = this.value.trim().split(/\s+/).filter(p => p.length > 0);
      if (palabras.length >= 4) {
        e.preventDefault();
      }
    }
  });

  actualizarContador();

  // 🔹 Costo unitario: solo números
  costoInput.addEventListener('input', () => {
    let value = costoInput.value.replace(/\D/g, '');
    if (value) {
      costoInput.value = new Intl.NumberFormat('es-AR').format(value);
    } else {
      costoInput.value = '';
    }
    limpiarErrorCampo(costoInput);
  });

  // 🔹 Cargar subcategorías dinámicamente al cambiar categoría
  categoriaSelect.addEventListener('change', function () {
    const categoriaId = this.value;
    subcategoriaSelect.innerHTML = '<option>Cargando...</option>';
    subcategoriaSelect.disabled = true;
    limpiarErrorCampo(this);

    if (!categoriaId) {
      subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
      subcategoriaSelect.disabled = true;
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

  // 🔹 Función para limpiar error de un campo
  function limpiarErrorCampo(campo) {
    const container = campo.closest('.mb-3') || campo.closest('.col-md-6') || campo.closest('.col-12') || campo.parentElement;
    const errorExistente = container.querySelector('.text-danger.small.mt-1');
    if (errorExistente) {
      errorExistente.remove();
    }
  }

  // 🔹 Limpiar errores al interactuar
  [document.getElementById('nombre')].forEach(el => {
    if (el) {
      el.addEventListener('input', function() {
        limpiarErrorCampo(this);
      });
    }
  });

  subcategoriaSelect.addEventListener('change', function() {
    limpiarErrorCampo(this);
  });

  // 🔹 Validación del formulario
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Limpiar errores previos
    form.querySelectorAll('.text-danger.small.mt-1').forEach(el => el.remove());

    let firstInvalid = null;
    let hayErrores = false;

    // Validar campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
      const container = field.closest('.mb-3') || field.closest('.col-md-6') || field.closest('.col-12') || field.parentElement;

      if (!field.value.trim()) {
        const error = document.createElement('div');
        error.className = 'text-danger small mt-1  no-asterisk';
        error.textContent = 'Este campo es obligatorio.';
        container.appendChild(error);
        if (!firstInvalid) firstInvalid = field;
        hayErrores = true;
      }
    });

    // Validar descripción: máximo 4 palabras
    const palabras = contarPalabras(descripcion.value);
    if (descripcion.value.trim() && palabras > 4) {
      const container = descripcion.closest('.col-12') || descripcion.parentElement;
      const error = document.createElement('div');
      error.className = 'text-danger small mt-1';
      error.textContent = '⚠️ La descripción debe tener máximo 4 palabras.';
      container.appendChild(error);
      if (!firstInvalid) firstInvalid = descripcion;
      hayErrores = true;
    }

    if (hayErrores) {
      firstInvalid.focus();
      const modalErrorEl = document.getElementById('modalErrorCampos');
      if (modalErrorEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalErrorEl).show();
      }
      return;
    }

    // Preparar payload
    const payload = {
      id_subcategoria: subcategoriaSelect.value,
      nombre: document.getElementById('nombre').value,
      descripcion: descripcion.value,
      costo_unitario: costoInput.value.replace(/\./g, ''),
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

          const container = input.closest('.mb-3') || input.closest('.col-md-6') || input.closest('.col-12') || input.parentElement;
          const error = document.createElement('div');
          error.className = 'text-danger small mt-1';
          error.textContent = messages[0];
          container.appendChild(error);

          if (!firstBackendError) firstBackendError = input;
        });

        if (firstBackendError) firstBackendError.focus();

        throw new Error('Hay errores en el formulario.');
      } else {
        throw new Error(`Respuesta inesperada del servidor. Código ${res.status}`);
      }
    })
    .then(data => {
      form.reset();
      subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
      subcategoriaSelect.disabled = true;
      actualizarContador();

      const modalEl = document.getElementById('modalRecursoCreado');
      if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
      }
    })
    .catch(error => {
      if (mensaje) {
        mensaje.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
      }
    });
  });

  // 🔹 Prevenir envío con Enter
  form.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }
  });

  console.log('✅ recurso.js cargado');
});