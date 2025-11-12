// public/js/inventario-actions.js
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('modalConfirmBajaRecurso');
  const modalText = document.getElementById('modalConfirmBajaText');
  const modalConfirmBtn = document.getElementById('modalBajaConfirm');
  const modalCancelBtn = document.getElementById('modalBajaCancel');
  const modalInstance = (modalEl && typeof bootstrap?.Modal === 'function') ? new bootstrap.Modal(modalEl) : null;
  let currentForm = null;
  let currentRow = null;

  // Attach click to the new eliminar buttons
  document.querySelectorAll('.btn-dar-baja').forEach(btn => {
  btn.addEventListener('click', function () {
    const form = this.closest('.dar-baja-form');
    const nombre = form?.dataset.nombre || 'Recurso';
    const modalEl = document.getElementById('modalConfirmBajaRecurso');
    const modalText = document.getElementById('modalConfirmBajaText');
    const modalConfirmBtn = document.getElementById('modalBajaConfirm');

    if (modalText) modalText.textContent = `¿Seguro que querés marcar como baja el recurso "${nombre}"?`;

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    modalConfirmBtn.onclick = async () => {
      const action = form.getAttribute('action');
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      modalConfirmBtn.disabled = true;

      try {
        const res = await fetch(action, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        });

        if (res.ok) {
          const row = form.closest('tr');
          const accionesCell = row.querySelector('.acciones-cell');
          if (accionesCell) {
            accionesCell.innerHTML = '<span class="badge bg-secondary fw-semibold">Dado de baja</span>';
          }
          row.classList.add('table-row-baja');
        } else {
          const data = await res.json();
          alert(data?.error || 'No se pudo marcar como baja');
        }
      } catch (err) {
        console.error(err);
        alert('Error al marcar como baja');
      } finally {
        modal.hide();
        modalConfirmBtn.disabled = false;
      }
    };
  });
});


  // Confirm action: do fetch to backend DELETE route (form action)
  async function doMarkAsBaja() {
    if (!currentForm || !currentRow) return;
    const action = currentForm.getAttribute('action');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    // Visual feedback: disable confirm
    if (modalConfirmBtn) modalConfirmBtn.disabled = true;

    try {
      // Use fetch and send DELETE (form already includes method spoofing but via fetch send actual DELETE)
      const res = await fetch(action, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ manual: true })
      });

      // If server responds with redirect or html, treat status 200-299 as success
      if (res.ok) {
        // Update UI: replace acciones cell content with "Dado de baja" badge and disable row actions
        const accionesCell = currentRow.querySelector('.acciones-cell');
        if (accionesCell) {
          accionesCell.innerHTML = '<span class="badge bg-secondary fw-semibold">Dado de baja</span>';
        }
        // Optionally mark row visually
        currentRow.classList.add('table-row-baja');
      } else {
        // Try to extract JSON error message
        let msg = 'No se pudo marcar como baja';
        try {
          const data = await res.json();
          if (data?.message) msg = data.message;
          if (data?.error) msg = data.error;
        } catch (err) { /* ignore parsing */ }
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
