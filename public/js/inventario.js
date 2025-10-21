function mostrarEstado(select) {
  const selected = select.options[select.selectedIndex];
  const estado = selected.getAttribute('data-estado');
  const talle = selected.getAttribute('data-talle');
  const recursoId = select.getAttribute('data-id');
  const serieId = selected.value;

  console.log("→ recursoId:", recursoId, "serieId:", serieId, "estado:", estado);

  const estadoDiv = document.getElementById('estado-' + recursoId);
  const qrBtnDiv = document.getElementById('qr-btn-' + recursoId);

  console.log("estadoDiv:", estadoDiv, "qrBtnDiv:", qrBtnDiv);

  // Mostrar estado
  if (estadoDiv) {
    if (estado) {
      estadoDiv.textContent = talle ? `${estado} (Talle ${talle})` : estado;
      estadoDiv.style.display = 'inline-block';
    } else {
      estadoDiv.style.display = 'none';
    }

    estadoDiv.className = 'px-2 py-1 border rounded small fw-semibold';

    switch (estado) {
      case 'Disponible':
        estadoDiv.classList.add('text-success');
        break;
      case 'Prestado':
        estadoDiv.classList.add('text-warning');
        break;
      case 'En reparación':
        estadoDiv.classList.add('text-danger');
        break;
      default:
        estadoDiv.classList.add('text-muted');
        break;
    }
  }

    // Mostrar botón QR dinámico
    if (qrBtnDiv) {
      if (serieId) {
        const link = qrBtnDiv.querySelector('a');
        link.href = `/series/${serieId}/qr`; // 👈 ruta individual
        qrBtnDiv.style.display = 'block';
      } else {
        qrBtnDiv.style.display = 'none';
      }
    }

}
