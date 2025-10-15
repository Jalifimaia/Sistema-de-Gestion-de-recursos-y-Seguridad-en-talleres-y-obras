document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const fechaFormateada = today.toLocaleDateString('es-AR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
    const fechaSpan = document.getElementById('today');
    if (fechaSpan) {
      fechaSpan.textContent = fechaFormateada;
    }
  });