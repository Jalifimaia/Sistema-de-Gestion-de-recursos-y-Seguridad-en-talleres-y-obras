function getHeadersSeguros() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  const csrf = meta?.content;
  const headers = { 'Content-Type': 'application/json' };
  if (csrf) headers['X-CSRF-TOKEN'] = csrf;
  return headers;
}

function refrescarTokenCSRF() {
  return fetch('/csrf-token')
    .then(res => res.json())
    .then(data => {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta && data.token) {
        meta.setAttribute('content', data.token);
        return data.token;
      }
      throw new Error('No se pudo actualizar el token CSRF');
    });
}

async function verificarSesionActiva() {
  const id_usuario = localStorage.getItem('id_usuario');
  let csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  if (!id_usuario) {
    mostrarModalKioscoSinVoz('⚠️ No hay trabajador identificado', 'danger');
    return false;
  }

  if (!csrf) {
    try {
      csrf = await refrescarTokenCSRF();
    } catch (e) {
      mostrarModalKioscoSinVoz('⚠️ No se pudo recuperar el token CSRF. Refrescar la página.', 'danger');
      return false;
    }
  }

  return true;
}

function manejarErrorFetch(err, contexto = 'Error de red') {
  const mensaje = typeof err === 'string' ? err :
    err?.message?.includes('419') ? '⚠️ Sesión expirada. Refrescar la página.' :
    err?.message?.includes('500') ? '⛔ Error interno del servidor.' :
    `${contexto}. Verificá conexión o sesión.`;

  console.error(`❌ ${contexto}:`, err);
  mostrarModalKioscoSinVoz(mensaje, 'danger');
  return { success: false, error: err };
}

module.exports = {
  getHeadersSeguros,
  refrescarTokenCSRF,
  verificarSesionActiva,
  manejarErrorFetch
};
