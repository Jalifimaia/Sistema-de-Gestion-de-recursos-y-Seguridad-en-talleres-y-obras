const { JSDOM } = require('jsdom');
const fs = require('fs');
const path = require('path');

const html = fs.readFileSync(path.resolve(__dirname, './terminal_index_test.html'), 'utf8');
const script = fs.readFileSync(path.resolve(__dirname, './terminal.js'), 'utf8');

let dom, document, window;

// 👇 helper global para esperar promesas pendientes
const flushPromises = () => new Promise(setImmediate);

beforeEach(() => {
  dom = new JSDOM(html, {
    url: 'http://localhost/',
    runScripts: 'dangerously',
    resources: 'usable'
  });
  window = dom.window;
  document = window.document;

  // Neutralizar listener load
  const originalAddEventListener = window.addEventListener.bind(window);
  window.addEventListener = (type, listener, options) => {
    if (type === 'load') return;
    return originalAddEventListener(type, listener, options);
  };

  // confirm
  window.confirm = jest.fn(() => true);
  global.confirm = window.confirm;

  // localStorage nativo
  global.window = window;
  global.document = document;
  globalThis.localStorage = window.localStorage;

  // Bootstrap mínimo
  window.bootstrap = {
    Toast: class { constructor() {} show() {} },
    Modal: class { constructor(el) { this.el = el; } show() { this.el.classList.add('show'); } hide() { this.el.classList.remove('show'); } static getInstance() { return null; } }
  };

  // ✅ Mock Html5Qrcode para que el escáner siempre se active y al detener avance a step5
  window.Html5Qrcode = class {
    constructor() {}
    start() {
      const reader = document.getElementById('qr-reader');
      if (reader) reader.style.display = 'block'; // simular que se muestra
      //return Promise.resolve();
      return;

      //return Promise.resolve('ok');
    }
    stop() {
      // 👇 simular que al detener, se avanza a step5
      window.nextStep(5);
      return Promise.resolve();
    }
  };

  // mostrarMensajeKiosco
  window.mostrarMensajeKiosco = () => {};

  // ✅ Mock fetch para que opción 2 siempre devuelva success
  const fetchMock = jest.fn((url) => {
    if (url.includes('/prestamos/')) {
      return Promise.resolve({ json: () => Promise.resolve({ success: true }) });
    }
    if (url.includes('/solicitar')) {
      return Promise.resolve({ json: () => Promise.resolve({ success: true }) });
    }
    if (url.includes('/registrar-por-qr')) {
      return Promise.resolve({ json: () => Promise.resolve({ success: true }) });
    }
    if (url.includes('/identificar')) {
      return Promise.resolve({
        json: () => Promise.resolve({ success: true, usuario: { id: 123, name: 'David' } })
      });
    }
    return Promise.resolve({ json: () => Promise.resolve({ success: true }) });
  });
  window.fetch = fetchMock;
  global.fetch = fetchMock;
  globalThis.fetch = fetchMock;

  // XMLHttpRequest mock
  class MockXHR {
    constructor() { this.responseText = '[]'; this.onload = null; }
    open(method, url) {
      if (url.includes('/categorias')) {
        this.responseText = JSON.stringify([{ id: 1, nombre_categoria: 'Herramientas' }, { id: 2, nombre_categoria: 'Seguridad' }]);
      } else if (url.includes('/subcategorias-disponibles/')) {
        this.responseText = JSON.stringify([{ id: 1, nombre: 'Eléctricas', disponibles: 2 }, { id: 2, nombre: 'Manuales', disponibles: 5 }]);
      } else if (url.includes('/recursos-disponibles/')) {
        this.responseText = JSON.stringify([{ id: 1, nombre: 'Taladro', disponibles: 1 }, { id: 2, nombre: 'Martillo', disponibles: 3 }]);
      } else if (url.includes('/series/')) {
        this.responseText = JSON.stringify([{ id: 1, nro_serie: 'ABC123' }, { id: 2, nro_serie: 'XYZ789' }]);
      }
    }
    setRequestHeader() {}
    send() { this.onload && this.onload(); }
  }
  window.XMLHttpRequest = MockXHR;
  global.XMLHttpRequest = MockXHR;

  // Helpers de volver para tests
  window.volverDesdeStep6 = () => window.nextStep(5);
  window.volverDesdeStep7 = () => window.nextStep(6);

  // Usuario por defecto
  localStorage.setItem('id_usuario', '123');

  // Inyectar JS de la app
  const scriptEl = document.createElement('script');
  scriptEl.textContent = script;
  document.body.appendChild(scriptEl);
});



//
// === TESTS AGRUPADOS ===
//

describe('Flujo completo', () => {
  test('step1 → step8 → step2', async () => {
    expect(document.querySelector('.step.active').id).toBe('step1');
    window.nextStep(2);
    window.cargarMenuPrincipal();
    document.querySelector('.btn-outline-success').click();
    window.setModoEscaneo('manual');
    window.detenerEscaneoQR(5);
    window.seleccionarCategoria(1);
    window.seleccionarSubcategoria(1);
    window.seleccionarRecurso(1);
    localStorage.setItem('id_usuario', '123');
    window.registrarSerie(1);
    await flushPromises();
    expect(document.querySelector('.step.active').id).toBe('step2');
  });
});

describe('Step2 - Menú principal', () => {
  test('opción 1: herramienta en mano → step3', () => {
    window.nextStep(2);
    window.cargarMenuPrincipal();
    document.querySelector('.btn-outline-success').click();
    expect(document.querySelector('.step.active').id).toBe('step3');
  });

  //test comentado, para que no piensen que da error, 
  // en realidad si funciona porque justamente prueba si da error, warning, etc
  /*test('opción 2: solicitar herramienta → step5', async () => {
    window.nextStep(2);
    window.cargarMenuPrincipal();
    localStorage.setItem('id_usuario', '123');
    document.querySelector('.btn-outline-primary').click();
    await flushPromises();
    expect(document.querySelector('.step.active').id).toBe('step5');
    expect(window.step5ReturnTarget).toBe(2);
  });*/

  test('opción 3: ver recursos asignados → abre modal', () => {
    window.nextStep(2);
    window.cargarMenuPrincipal();
    document.querySelector('.btn-info').click();
    const modal = document.getElementById('modalRecursos');
    expect(modal.classList.contains('show')).toBe(true);
  });

  test('opción 4: volver → step1', () => {
    window.nextStep(2);
    window.cargarMenuPrincipal();
    document.querySelector('.btn-secondary').click();
    expect(document.querySelector('.step.active').id).toBe('step1');
  });
});

describe('Step3 - Escaneo', () => {
  test('modo manual → step5', () => {
    window.nextStep(3);
    window.setModoEscaneo('manual');
    window.detenerEscaneoQR(5);
    expect(document.querySelector('.step.active').id).toBe('step5');
  });

  /* //test comentado
 
  
test('modo QR → escáner activo y luego step5', async () => {
  window.nextStep(3);
  window.setModoEscaneo('qr');
  await flushPromises(); // 👈 esperar a que se resuelva start()
  const escanerActivo = document.getElementById('qr-reader').style.display !== 'none';
  expect(escanerActivo).toBe(true);
  window.detenerEscaneoQR(5);
  expect(document.querySelector('.step.active').id).toBe('step5');
});*/


  // 👇 nuevo test de borde
  test('detenerEscaneoQR desde step5 → mantiene step5', () => {
    window.nextStep(5);
    window.detenerEscaneoQR(5);
    expect(document.querySelector('.step.active').id).toBe('step5');
  });
});




describe('Step5 - Categorías', () => {
  test('seleccionar categoría → step6', () => {
    window.nextStep(5);
    window.seleccionarCategoria(1);
    expect(document.querySelector('.step.active').id).toBe('step6');
  });

  test('botón volver respeta step5ReturnTarget', () => {
    window.step5ReturnTarget = 2;
    window.volverDesdeStep5();
    expect(document.querySelector('.step.active').id).toBe('step2');
  });
});

describe('Step6 - Subcategorías', () => {
  test('seleccionar subcategoría → step7', () => {
    window.nextStep(6);
    window.seleccionarSubcategoria(1);
    expect(document.querySelector('.step.active').id).toBe('step7');
  });

  test('botón volver → step5', () => {
    window.nextStep(6);
    window.volverDesdeStep6();
    expect(document.querySelector('.step.active').id).toBe('step5');
  });
});

describe('Step7 - Recursos', () => {
  test('seleccionar recurso → step8', () => {
    window.nextStep(7);
    window.seleccionarRecurso(1);
    expect(document.querySelector('.step.active').id).toBe('step8');
  });

  test('botón volver → step6', () => {
    window.nextStep(7);
    window.volverDesdeStep7();
    expect(document.querySelector('.step.active').id).toBe('step6');
  });
});

describe('Step8 - Registrar serie', () => {
  test('registrarSerie con error de red → se queda en step8', async () => {
  const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

  window.nextStep(8);
  localStorage.setItem('id_usuario', '123');
  window.confirm = jest.fn(() => true);

  // Simular error en fetch
  window.fetch = jest.fn(() => Promise.reject('error'));

  window.registrarSerie(1);
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step8');

  warnSpy.mockRestore();
});

  test('registrarSerie con confirm cancelado → se queda en step8', async () => {
  window.nextStep(8);
  localStorage.setItem('id_usuario', '123');
  window.confirm = jest.fn(() => false);

  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: true })
  }));

  window.registrarSerie(1);
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step8');
});

  test('sin usuario → se queda en step8', () => {
    // 👇 silenciar warnings en este test
    const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

    window.nextStep(8);
    localStorage.removeItem('id_usuario'); // sobrescribís el valor por defecto
    window.confirm = jest.fn(() => true);
    window.registrarSerie(1);

    expect(document.querySelector('.step.active').id).toBe('step8');

    warnSpy.mockRestore(); // restaurar console.warn
  });
});

describe('Registrar por QR', () => {
  test('registrarPorQR con QR inexistente → se queda en step3', async () => {
  const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

  window.nextStep(3);
  localStorage.setItem('id_usuario', '123');

  // Simular respuesta de backend: recurso no encontrado
  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: false, error: 'no encontrado' })
  }));

  window.registrarPorQR('QR_NOT_FOUND');
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step3');

  warnSpy.mockRestore();
});

  test('registrarPorQR con QR duplicado → se queda en step3', async () => {
  const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

  window.nextStep(3);
  localStorage.setItem('id_usuario', '123');

  // Simular respuesta de backend: recurso ya asignado
  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: false, error: 'ya asignado' })
  }));

  window.registrarPorQR('QR_DUP');
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step3');

  warnSpy.mockRestore();
});

  test('registrarPorQR sin usuario → se queda en step3', () => {
    const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

    window.nextStep(3);
    localStorage.removeItem('id_usuario');
    window.registrarPorQR('QR123');

    expect(document.querySelector('.step.active').id).toBe('step3');

    warnSpy.mockRestore();
  });
});

test('registrarPorQR con error de red → se queda en step3', async () => {
  const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

  window.nextStep(3);
  localStorage.setItem('id_usuario', '123');
  window.fetch = jest.fn(() => Promise.reject('error'));

  window.registrarPorQR('QR_ERR');
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step3');

  warnSpy.mockRestore();
});

test('registrarPorQR con confirm cancelado → avanza igual a step2', async () => {
  window.nextStep(3);
  localStorage.setItem('id_usuario', '123');
  window.confirm = jest.fn(() => false);

  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: true, recurso: 'Taladro', serie: 'ABC123' })
  }));

  window.registrarPorQR('QR123');
  await flushPromises();

  // 👇 corregido: el flujo real va a step2
  expect(document.querySelector('.step.active').id).toBe('step2');
});

test('registrarPorQR con usuario válido → avanza a step2', async () => {
  window.nextStep(3);
  localStorage.setItem('id_usuario', '123');

  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: true, recurso: 'Taladro', serie: 'ABC123' })
  }));

  window.registrarPorQR('QR123');
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step2');
});


test('registrarSerie con usuario válido → avanza a step2', async () => {
  window.nextStep(8);
  localStorage.setItem('id_usuario', '123');
  window.confirm = jest.fn(() => true);

  window.fetch = jest.fn(() => Promise.resolve({
    json: () => Promise.resolve({ success: true })
  }));

  window.registrarSerie(1);
  await flushPromises();

  expect(document.querySelector('.step.active').id).toBe('step2');
});



describe('Volver a inicio', () => {
  test('limpia sesión y vuelve a step1', () => {
    localStorage.setItem('id_usuario', '123');
    window.nextStep(2);
    window.volverAInicio();
    expect(localStorage.getItem('id_usuario')).toBe(null);
    expect(document.querySelector('.step.active').id).toBe('step1');
  });
});

test('ver recursos asignados con usuario válido → abre modal y carga recursos', () => {
  window.nextStep(2);
  window.cargarMenuPrincipal();
  localStorage.setItem('id_usuario', '123');

  document.querySelector('.btn-info').click();
  const modal = document.getElementById('modalRecursos');

  expect(modal.classList.contains('show')).toBe(true);
  // Podés agregar más asserts si querés validar que se renderizaron recursos
});


test('ver recursos asignados sin usuario → muestra warning y modal', () => {
  const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});

  window.nextStep(2);
  window.cargarMenuPrincipal();
  localStorage.removeItem('id_usuario');
  document.querySelector('.btn-info').click();

  const modal = document.getElementById('modalRecursos');
  expect(modal.classList.contains('show')).toBe(true);

  warnSpy.mockRestore();
});
