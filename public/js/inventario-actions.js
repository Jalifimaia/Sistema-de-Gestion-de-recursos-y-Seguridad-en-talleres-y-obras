document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('modalConfirmBajaRecurso');
  const modalText = document.getElementById('modalConfirmBajaText');
  const modalConfirmBtn = document.getElementById('modalBajaConfirm');
  const modalCancelBtn = document.getElementById('modalBajaCancel');
  const modalInstance = (modalEl && typeof bootstrap?.Modal === 'function') ? new bootstrap.Modal(modalEl) : null;
  let currentForm = null;
  let currentRow = null;

  // Attach click to eliminar buttons
  document.querySelectorAll('.btn-marcar-baja').forEach(btn => {
    btn.addEventListener('click', function () {
      currentForm = this.closest('.marcar-baja-form');
      currentRow = currentForm.closest('tr');
      const nombre = currentForm?.dataset.nombre || 'Recurso';

      if (modalText) {
        modalText.textContent = `¿Seguro que querés marcar como baja el recurso "${nombre}"?`;
      }

      if (modalInstance) modalInstance.show();
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
        method: 'DELETE', // 🔑 ahora coincide con la ruta y el form
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
      if (modalInstance) modalInstance.hide();
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
