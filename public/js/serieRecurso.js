document.addEventListener('DOMContentLoaded', function () {
  const campoTalle = document.getElementById('campoTalle');

  // Ocultar el campo si la categoría no es EPP
  if (!categoriaRecurso.includes('epp')) {
    campoTalle.style.display = 'none';
  }
});
