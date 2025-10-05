document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('recursoForm');
    const mensaje = document.getElementById('mensaje');
    const categoriaSelect = document.getElementById('categoria');
    const subcategoriaSelect = document.getElementById('subcategoria');

    // 🔹 Cargar subcategorías dinámicamente
    categoriaSelect.addEventListener('change', function () {
        const categoriaId = this.value;
        subcategoriaSelect.innerHTML = '<option>Cargando...</option>';
        subcategoriaSelect.disabled = true;

        if (!categoriaId) {
            subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
            return;
        }

        fetch(`/api/subcategorias/${categoriaId}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Seleccione una subcategoría</option>';
                data.forEach(sub => {
                    options += `<option value="${sub.id}">${sub.nombre}</option>`;
                });
                subcategoriaSelect.innerHTML = options;
                subcategoriaSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error al cargar subcategorías:', error);
                subcategoriaSelect.innerHTML = '<option>Error al cargar</option>';
            });
    });

    // 🔹 Envío del formulario
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const payload = {
            id_subcategoria: subcategoriaSelect.value,
            nombre: document.getElementById('nombre').value,
            serie: document.getElementById('serie').value,
            descripcion: document.getElementById('descripcion').value,
            costo_unitario: document.getElementById('costo_unitario').value,
            id_estado: document.getElementById('estado').value,
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
        .then(res => {
            const contentType = res.headers.get('content-type');
            if (res.ok && contentType && contentType.includes('application/json')) {
                return res.json();
            } else {
                return res.text().then(text => {
                    throw new Error(`Error ${res.status}: ${text}`);
                });
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
            mensaje.innerHTML = `<div class="alert alert-danger">No se pudo guardar el recurso.</div>`;
        });
    });

    // 🔹 Agregar nueva subcategoría
    document.getElementById('agregarSubcategoria').addEventListener('click', function () {
        const nombre = document.getElementById('nuevaSubcategoria').value.trim();
        const categoriaId = categoriaSelect.value;

        if (!nombre || !categoriaId) {
            mensaje.innerHTML = `<div class="alert alert-warning">Escribí un nombre y seleccioná una categoría.</div>`;
            return;
        }

        fetch('/api/subcategorias', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ nombre, categoria_id: categoriaId }),
        })
        .then(res => res.json())
        .then(data => {
            const option = document.createElement('option');
            option.value = data.id;
            option.textContent = data.nombre;
            subcategoriaSelect.appendChild(option);
            subcategoriaSelect.value = data.id;
            subcategoriaSelect.disabled = false;
            document.getElementById('nuevaSubcategoria').value = '';
            mensaje.innerHTML = `<div class="alert alert-success">Subcategoría agregada.</div>`;
        })
        .catch(error => {
            console.error('Error al agregar subcategoría:', error);
            mensaje.innerHTML = `<div class="alert alert-danger">No se pudo agregar la subcategoría.</div>`;
        });
    });
});
