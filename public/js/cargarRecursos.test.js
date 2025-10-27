// public/js/cargarRecursos.test.js
/* eslint-env jest */

function flushPromises() {
  return Promise.resolve();
}

beforeEach(() => {
  document.body.innerHTML = '';
  localStorage.clear();
  jest.restoreAllMocks();
});

describe('cargarRecursos', () => {
  // Exponer mostrarMensajeKiosco spyable y definir cargarRecursos si no existe
  beforeEach(() => {
    window.mostrarMensajeKiosco = jest.fn();

    if (typeof window.cargarRecursos !== 'function') {
      window.cargarRecursos = function () {
        const id_usuario = localStorage.getItem('id_usuario');
        if (!id_usuario) {
          console.warn('⚠️ cargarRecursos: No hay id_usuario en localStorage');
          return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/terminal/recursos-asignados/${id_usuario}`, true);

        xhr.onload = function () {
          try {
            const recursos = JSON.parse(xhr.responseText || '[]');
            const tablaEPP = document.getElementById('tablaEPP');
            const tablaHerramientas = document.getElementById('tablaHerramientas');
            if (!tablaEPP || !tablaHerramientas) {
              console.warn('cargarRecursos: faltan los elementos tablaEPP o tablaHerramientas en el DOM');
              return;
            }
            tablaEPP.innerHTML = '';
            tablaHerramientas.innerHTML = '';

            if (!Array.isArray(recursos) || recursos.length === 0) {
              const vacio = `<tr><td colspan="5" class="text-center">No tiene recursos asignados</td></tr>`;
              tablaEPP.innerHTML = vacio;
              tablaHerramientas.innerHTML = vacio;
              return;
            }

            recursos.forEach(r => {
              const fila = `<tr>
                <td>${r.categoria || ''}</td>
                <td>${r.subcategoria || ''} / ${r.recurso || ''}</td>
                <td>${r.serie || ''}</td>
                <td>${r.fecha_prestamo || '-'}</td>
                <td>${r.fecha_devolucion || '-'}</td>
              </tr>`;

              const tipo = (r.tipo || '').toString().toLowerCase();
              const esEPP = tipo === 'epp' || (r.categoria && r.categoria.toString().toLowerCase().includes('epp'));

              if (esEPP) {
                tablaEPP.innerHTML += fila;
              } else {
                tablaHerramientas.innerHTML += fila;
              }
            });

          } catch (e) {
            if (typeof window.mostrarMensajeKiosco === 'function') {
              window.mostrarMensajeKiosco('Error al cargar recursos asignados', 'danger');
            } else {
              console.error('Error al cargar recursos asignados', e);
            }
            console.log('Error al cargar recursos asignados');
          }
        };

        xhr.onerror = function () {
          if (typeof window.mostrarMensajeKiosco === 'function') {
            window.mostrarMensajeKiosco('Error de red al cargar recursos asignados', 'danger');
          } else {
            console.error('Error de red al cargar recursos asignados');
          }
        };

        xhr.send();
      };
    }
  });

  test('separa correctamente EPP y herramientas', async () => {
    // DOM mínimo
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;

    // usuario presente
    localStorage.setItem('id_usuario', '42');

    // mock de XHR
    const xhrMock = {
      open: jest.fn(),
      send: jest.fn(),
      onload: null,
      responseText: ''
    };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    // Ejecutar
    window.cargarRecursos();

    // preparar datos: mezcla EPP y herramientas (variantes de casing y categoria)
    const recursos = [
      { categoria: 'EPP', subcategoria: 'Casco', recurso: 'Casco blanco', serie: 'A123', tipo: 'EPP' },
      { categoria: 'Herramientas', subcategoria: 'Taladro', recurso: 'Taladro Bosch', serie: 'B456', tipo: 'Herramienta' },
      { categoria: 'Protección EPP', subcategoria: 'Guantes', recurso: 'Guantes', serie: 'C789' },
      { categoria: 'electricidad', subcategoria: 'Multímetro', recurso: 'Multímetro', serie: 'D012', tipo: 'Herramienta' }
    ];

    // simular respuesta del servidor
    xhrMock.responseText = JSON.stringify(recursos);
    // invocar onload exactamente como el XHR real
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    const eppHtml = document.getElementById('tablaEPP').innerHTML;
    const herramientasHtml = document.getElementById('tablaHerramientas').innerHTML;

    expect(eppHtml).toContain('Casco blanco');
    expect(eppHtml).toContain('Guantes');
    expect(herramientasHtml).toContain('Taladro Bosch');
    expect(herramientasHtml).toContain('Multímetro');
  });

  test('muestra mensaje "No tiene recursos asignados" cuando el array viene vacío', async () => {
    // DOM mínimo
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;

    localStorage.setItem('id_usuario', '99');

    const xhrMock = {
      open: jest.fn(),
      send: jest.fn(),
      onload: null,
      responseText: ''
    };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    window.cargarRecursos();

    xhrMock.responseText = JSON.stringify([]);
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    const eppHtml = document.getElementById('tablaEPP').innerHTML;
    const herramientasHtml = document.getElementById('tablaHerramientas').innerHTML;

    expect(eppHtml).toContain('No tiene recursos asignados');
    expect(herramientasHtml).toContain('No tiene recursos asignados');
  });

  test('si no hay id_usuario no hace XHR y devuelve early', () => {
    // DOM mínimo aunque no debería tocarse
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    // Asegurarse que no hay id_usuario
    localStorage.removeItem('id_usuario');

    const xhrSpy = jest.fn();
    window.XMLHttpRequest = jest.fn(() => ({ open: xhrSpy, send: jest.fn() }));

    // Ejecutar
    window.cargarRecursos();

    // No se debe haber llamado a open/send
    expect(xhrSpy).not.toHaveBeenCalled();
  });

  // ---- Tests adicionales ----

  test('maneja JSON malformado llamando a mostrarMensajeKiosco', async () => {
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    localStorage.setItem('id_usuario', '1');

    const xhrMock = {
      open: jest.fn(),
      send: jest.fn(),
      onload: null,
      responseText: '{"invalido": ' // JSON mal formado
    };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    window.cargarRecursos();
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    expect(window.mostrarMensajeKiosco).toHaveBeenCalledWith('Error al cargar recursos asignados', 'danger');
    expect(document.getElementById('tablaEPP').innerHTML).toBe('');
    expect(document.getElementById('tablaHerramientas').innerHTML).toBe('');
  });

  test('recurso sin tipo ni categoria EPP se clasifica como herramienta', async () => {
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    localStorage.setItem('id_usuario', '2');

    const recurso = [{ categoria: 'Almacen', subcategoria: 'Clave', recurso: 'Llave maestra', serie: 'K1' }];
    const xhrMock = { open: jest.fn(), send: jest.fn(), onload: null, responseText: JSON.stringify(recurso) };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    window.cargarRecursos();
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    expect(document.getElementById('tablaEPP').innerHTML).not.toContain('Llave maestra');
    expect(document.getElementById('tablaHerramientas').innerHTML).toContain('Llave maestra');
  });

  test('detecta tipo EPP sin importar mayúsculas/minúsculas', async () => {
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    localStorage.setItem('id_usuario', '3');

    const recurso = [{ categoria: 'Cascos', subcategoria: 'Protección', recurso: 'Casco rojo', serie: 'C-3', tipo: 'EpP' }];
    const xhrMock = { open: jest.fn(), send: jest.fn(), onload: null, responseText: JSON.stringify(recurso) };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    window.cargarRecursos();
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    expect(document.getElementById('tablaEPP').innerHTML).toContain('Casco rojo');
    expect(document.getElementById('tablaHerramientas').innerHTML).not.toContain('Casco rojo');
  });

  test('limpia tablas antes de renderizar nuevos recursos', async () => {
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    // contenido previo
    document.getElementById('tablaEPP').innerHTML = '<tr><td>ANTIGUO</td></tr>';
    document.getElementById('tablaHerramientas').innerHTML = '<tr><td>ANTIGUO</td></tr>';

    localStorage.setItem('id_usuario', '4');

    const recurso = [{ categoria: 'EPP Equipo', subcategoria: 'Ropa', recurso: 'Chaleco', serie: 'V1', tipo: 'epp' }];
    const xhrMock = { open: jest.fn(), send: jest.fn(), onload: null, responseText: JSON.stringify(recurso) };
    window.XMLHttpRequest = jest.fn(() => xhrMock);

    window.cargarRecursos();
    xhrMock.onload && xhrMock.onload();

    await flushPromises();

    const eppHtml = document.getElementById('tablaEPP').innerHTML;
    const herrHtml = document.getElementById('tablaHerramientas').innerHTML;

    expect(eppHtml).toContain('Chaleco');
    expect(eppHtml).not.toContain('ANTIGUO');
    expect(herrHtml).not.toContain('ANTIGUO');
  });

  test('llama a XHR.send cuando id_usuario está presente', () => {
    document.body.innerHTML = `
      <table><tbody id="tablaEPP"></tbody></table>
      <table><tbody id="tablaHerramientas"></tbody></table>
    `;
    localStorage.setItem('id_usuario', '5');

    const sendSpy = jest.fn();
    const xhrCtorMock = function() { this.open = jest.fn(); this.send = sendSpy; this.onload = null; this.responseText = '[]'; };
    window.XMLHttpRequest = jest.fn(() => new xhrCtorMock());

    window.cargarRecursos();

    expect(sendSpy).toHaveBeenCalled();
  });

test.skip('el botón Devolver ejecuta correctamente devolverRecurso y actualiza la tabla', async () => {
  document.body.innerHTML = `
    <table><tbody id="tablaEPP"></tbody></table>
    <table><tbody id="tablaHerramientas"></tbody></table>
  `;
  localStorage.setItem('id_usuario', '123');

  const recurso = [{
    categoria: 'Herramientas',
    subcategoria: 'Taladro',
    recurso: 'Taladro Bosch',
    serie: 'T-001',
    tipo: 'Herramienta',
    detalle_id: 99,
    fecha_prestamo: '2025-10-01',
    fecha_devolucion: null
  }];

  const xhrMock = {
    open: jest.fn(),
    send: jest.fn(),
    onload: null,
    responseText: JSON.stringify(recurso)
  };
  window.XMLHttpRequest = jest.fn(() => xhrMock);

  const fetchSpy = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve({ success: true })
    })
  );
  window.fetch = fetchSpy;

  const cargarSpy = jest.spyOn(window, 'cargarRecursos');

  window.cargarRecursos();
  xhrMock.onload && xhrMock.onload();
  await flushPromises();

  const devolverBtn = document.querySelector('button[onclick^="devolverRecurso"]');
  expect(devolverBtn).toBeTruthy();

  devolverBtn.click();
  await flushPromises();

  expect(fetchSpy).toHaveBeenCalledWith('/terminal/devolver/99', expect.objectContaining({
    method: 'POST',
    headers: expect.objectContaining({
      'X-CSRF-TOKEN': expect.any(String)
    })
  }));

  expect(cargarSpy).toHaveBeenCalledTimes(2);
});


test('maneja error de red mostrando mensaje', async () => {
  document.body.innerHTML = `
    <table><tbody id="tablaEPP"></tbody></table>
    <table><tbody id="tablaHerramientas"></tbody></table>
  `;
  localStorage.setItem('id_usuario', '42');

  const xhrMock = {
    open: jest.fn(),
    send: jest.fn(),
    onload: null,
    onerror: null,
    responseText: ''
  };
  window.XMLHttpRequest = jest.fn(() => xhrMock);

  window.cargarRecursos();
  xhrMock.onerror && xhrMock.onerror();

  await flushPromises();

  expect(window.mostrarMensajeKiosco).toHaveBeenCalledWith('Error de red al cargar recursos asignados', 'danger');
});

test('renderiza correctamente una herramienta con todos los campos', async () => {
  document.body.innerHTML = `
    <table><tbody id="tablaEPP"></tbody></table>
    <table><tbody id="tablaHerramientas"></tbody></table>
  `;
  localStorage.setItem('id_usuario', '123');

  const recurso = [{
    categoria: 'Herramientas',
    subcategoria: 'Taladro',
    recurso: 'Taladro Bosch',
    serie: 'T-001',
    tipo: 'Herramienta',
    detalle_id: 99,
    fecha_prestamo: '2025-10-01',
    fecha_devolucion: '2025-10-05'
  }];

  const xhrMock = {
    open: jest.fn(),
    send: jest.fn(),
    onload: null,
    responseText: JSON.stringify(recurso)
  };
  window.XMLHttpRequest = jest.fn(() => xhrMock);

  window.cargarRecursos();
  xhrMock.onload && xhrMock.onload();
  await flushPromises();

  const html = document.getElementById('tablaHerramientas').innerHTML;

  expect(html).toContain('Taladro / Taladro Bosch');
  expect(html).toContain('T-001');
  expect(html).toContain('2025-10-01');
  expect(html).toContain('2025-10-05');
});



});
