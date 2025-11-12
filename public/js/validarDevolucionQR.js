const fetch = require('node-fetch');


function validarDevolucionQR(qrCode, idUsuario) {
  const serieEsperada = document.getElementById('serieEsperadaQR').textContent.trim();

  return fetch('/terminal/validar-qr-devolucion', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      codigo_qr: qrCode,
      id_usuario: idUsuario,
      serie_esperada: serieEsperada
    })
  })
  .then(async res => {
    const data = await res.json();

    if (!res.ok) {
      // No lanzar excepción: devolver respuesta con success false
      return {
        success: false,
        message: data.message || `Error HTTP ${res.status}`
      };
    }

    return data;
  })
  .catch(err => {
    console.error('Error de red en fetch:', err);
    return {
      success: false,
      message: 'Error de red al validar el QR'
    };
  });
}


if (typeof module !== 'undefined' && module.exports) {
  module.exports = Object.assign(module.exports || {}, {
    validarDevolucionQR
  });
}
