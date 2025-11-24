document.addEventListener('DOMContentLoaded', function () {
  // Modales
  const modalConfirmEl = document.getElementById('modalConfirmBajaRecurso');
  const modalErrorEl = document.getElementById('modalErrorPrestamos');
  
  const modalConfirmInstance = (modalConfirmEl && typeof bootstrap?.Modal === 'function') ? new bootstrap.Modal(modalConfirmEl) : null;
  const modalErrorInstance = (modalErrorEl && typeof bootstrap?.Modal === 'function') ? new bootstrap.Modal(modalErrorEl) : null;
  
  const modalConfirmText = document.getElementById('modalConfirmBajaText');
  const modalConfirmBtn = document.getElementById('modalBajaConfirm');
  const modalCancelBtn = document.getElementById('modalBajaCancel');
  const modalErrorNombre = document.getElementById('modalErrorNombreRecurso');
  const modalErrorCategoriaSubcategoria = document.getElementById('modalErrorCategoriaSubcategoria');
  
  let currentForm = null;
  let currentRow = null;

  // Attach click to eliminar buttons
  document.querySelectorAll('.btn-marcar-baja').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      e.preventDefault(); // Prevenir cualquier acción por defecto
      
      currentForm = this.closest('.marcar-baja-form');
      currentRow = currentForm.closest('tr');
      const nombre = currentForm?.dataset.nombre || 'Recurso';
      
      // Obtener categoría y subcategoría de la fila
      const categoria = currentRow.querySelector('.recurso-categoria')?.textContent.trim() || '';
      const subcategoria = currentRow.querySelector('.recurso-subcategoria')?.textContent.trim() || '';
      
      const action = currentForm.getAttribute('action');
      
      // Extraer el ID del recurso de la URL: /recursos/{id}/baja
      const parts = action.split('/').filter(p => p); // Filtrar partes vacías
      const recursoId = parts[parts.length - 2]; // Penúltimo elemento
      
      console.log('Action URL:', action);
      console.log('Parts:', parts);
      console.log('Recurso ID:', recursoId);

      // Verificar si tiene préstamos activos antes de mostrar el modal
      try {
        const url = `/recursos/${recursoId}/verificar-prestamos`;
        console.log('Fetching:', url);
        
        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Data received:', data);

        if (data.tiene_prestamos) {
          // Mostrar modal de error
          console.log('Mostrando modal de error - tiene préstamos');
          if (modalErrorNombre) {
            modalErrorNombre.textContent = nombre;
          }
          if (modalErrorCategoriaSubcategoria) {
            modalErrorCategoriaSubcategoria.textContent = `${categoria} - ${subcategoria}`;
          }
          if (modalErrorInstance) modalErrorInstance.show();
          
          // Limpiar referencias
          currentForm = null;
          currentRow = null;
        } else {
          // Mostrar modal de confirmación
          console.log('Mostrando modal de confirmación - no tiene préstamos');
          if (modalConfirmText) {
            modalConfirmText.textContent = `¿Seguro que querés marcar como baja el recurso "${nombre}"?`;
          }
          if (modalConfirmInstance) modalConfirmInstance.show();
        }
      } catch (error) {
        console.error('Error al verificar préstamos:', error);
        // En caso de error, mostrar el modal de confirmación por defecto
        if (modalConfirmText) {
          modalConfirmText.textContent = `¿Seguro que querés marcar como baja el recurso "${nombre}"?`;
        }
        if (modalConfirmInstance) modalConfirmInstance.show();
      }
    });
  });

  // Confirm action: do fetch to backend DELETE route (form action)
  async function doMarkAsBaja() {
    if (!currentForm || !currentRow) return;
    const action = currentForm.getAttribute('action');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (modalConfirmBtn) modalConfirmBtn.disabled = true;

    try {
      const res = await fetch(action, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
      });

      if (res.ok) {
        const accionesCell = currentRow.querySelector('.acciones-cell');
        if (accionesCell) {
          accionesCell.innerHTML = '<span class="badge bg-secondary fw-semibold">Dado de baja</span>';
        }
        currentRow.classList.add('table-row-baja');
      } else {
        let msg = 'No se pudo marcar como baja';
        try {
          const data = await res.json();
          if (data?.message) msg = data.message;
          if (data?.error) msg = data.error;
        } catch (err) { /* ignore */ }
        alert(msg);
      }
    } catch (err) {
      console.error('Error en petición de baja:', err);
      alert('Error al marcar como baja. Revisá la consola.');
    } finally {
      if (modalConfirmInstance) modalConfirmInstance.hide();
      if (modalConfirmBtn) modalConfirmBtn.disabled = false;
      currentForm = null;
      currentRow = null;
    }
  }

  if (modalConfirmBtn) {
    modalConfirmBtn.addEventListener('click', function () {
      doMarkAsBaja();
    });
  }

  if (modalCancelBtn) {
    modalCancelBtn.addEventListener('click', function () {
      currentForm = null;
      currentRow = null;
    });
  }
});