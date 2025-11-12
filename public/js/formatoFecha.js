document.addEventListener('DOMContentLoaded', function () {
  const now = new Date();

  const fechaFormatter = new Intl.DateTimeFormat('es-AR', {
    timeZone: 'America/Argentina/Buenos_Aires',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });

  const horaFormatter = new Intl.DateTimeFormat('es-AR', {
    timeZone: 'America/Argentina/Buenos_Aires',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  });

  const fechaFormateada = fechaFormatter.format(now); // "dd/mm/aaaa"
  const horaFormateada = horaFormatter.format(now);   // "HH:MM:SS"

  const fechaSpan = document.getElementById('today');
  if (fechaSpan) {
    fechaSpan.textContent = `${fechaFormateada} ${horaFormateada}`;
  }
});
