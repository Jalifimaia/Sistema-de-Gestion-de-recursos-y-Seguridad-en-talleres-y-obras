// Mocks globales antes de cargar terminal.js
global.scannerRegistro = { stop: jest.fn().mockResolvedValue() };
global.isScanningRegistro = true;

global.scannerRegistroStep13 = { stop: jest.fn().mockResolvedValue() };
global.isScanningStep13 = true;

global.scannerLogin = { stop: jest.fn().mockResolvedValue() };
global.isScanningLogin = true;

global.Html5Qrcode = jest.fn().mockImplementation(() => ({
  start: jest.fn().mockResolvedValue(),
  stop: jest.fn().mockResolvedValue()
}));

global.nextStep = jest.fn();
global.safeStartRecognitionGlobal = jest.fn();
global.safeStopRecognitionGlobal = jest.fn();

// Cargar terminal.js después de definir los mocks
require('../terminal');

describe('Integración entre escáneres QR y navegación de steps', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="qr-reader"></div>
      <div id="qr-reader-step13"></div>
      <div id="qr-login-reader"></div>
      <button id="btn-escanear-qr"></button>
      <button id="btn-cancelar-qr"></button>
      <div id="texto-camara-activa"></div>
      <button id="btn-escanear-qr-step13"></button>
      <button id="btn-cancelar-qr-step13"></button>
      <div id="texto-camara-activa-step13"></div>
    `;
  });

  test('navegar de step3 a step13 detiene escáner de registro', async () => {
    await window.limpiarQRregistroRecursos();
    expect(global.scannerRegistro.stop).toHaveBeenCalled();
    expect(global.isScanningRegistro).toBe(false);

    window.activarEscaneoQRregistroRecursosStep13();
    expect(global.isScanningStep13).toBe(true);
  });

  test('cancelar escaneo en step13 limpia escáner', async () => {
    await window.limpiarQRregistroRecursosStep13();
    expect(global.scannerRegistroStep13.stop).toHaveBeenCalled();
    expect(global.isScanningStep13).toBe(false);
  });

  test('volver al login limpia escáner y reactiva reconocimiento', async () => {
    await window.detenerEscaneoQRLogin();
    expect(global.scannerLogin.stop).toHaveBeenCalled();
    expect(global.isScanningLogin).toBe(false);

    window.activarEscaneoQRLogin();
    expect(global.isScanningLogin).toBe(true);
  });

  test('activar escáner en step13 tras volver desde otro step', async () => {
    global.scannerRegistroStep13 = null;
    global.isScanningStep13 = false;

    window.activarEscaneoQRregistroRecursosStep13();
    expect(global.scannerRegistroStep13).not.toBeNull();
    expect(global.isScanningStep13).toBe(true);
  });
});
