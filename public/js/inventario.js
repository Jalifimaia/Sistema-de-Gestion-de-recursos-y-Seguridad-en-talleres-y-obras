function mostrarEstado(select) {
  const selected = select.options[select.selectedIndex];
  const estado = selected.getAttribute('data-estado');
  const talle = selected.getAttribute('data-talle');
  const recursoId = select.getAttribute('data-id');
  const estadoDiv = document.getElementById('estado-' + recursoId);

  if (estadoDiv) {
    estadoDiv.textContent = talle ? `${estado} (Talle ${talle})` : estado;
    estadoDiv.style.display = 'inline-block';

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
}
