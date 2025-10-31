document.addEventListener('DOMContentLoaded', function () {
  const today = new Date();
  const dia = String(today.getDate()).padStart(2, '0');
  const mes = String(today.getMonth() + 1).padStart(2, '0'); // +1 porque los meses van de 0 a 11
  const año = today.getFullYear();
  const fechaFormateada = `${dia}/${mes}/${año}`;

  const fechaSpan = document.getElementById('today');
  if (fechaSpan) {
    fechaSpan.textContent = fechaFormateada;
  }
});
