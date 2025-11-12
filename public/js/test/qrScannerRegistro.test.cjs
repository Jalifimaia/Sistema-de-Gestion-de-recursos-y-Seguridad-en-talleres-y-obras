require('../terminal');

describe('Escáner QR de registro (step3)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="qr-reader"></div>
      <button id="btn-escanear-qr"></button>
      <button id="btn-cancelar-qr"></button>
      <div id="texto-camara-activa"></div>
    `;
    global.scannerRegistro = null;
    global.isScanningRegistro = false;
  });

  test('inicia escáner si contenedor está disponible', () => {
    global.Html5Qrcode = jest.fn().mockImplementation(() => ({
      start: jest.fn().mockResolvedValue(),
    }));

    window.activarEscaneoQRregistroRecursos();
    expect(Html5Qrcode).toHaveBeenCalledWith('qr-reader');
  });

  test('no inicia si ya está escaneando', () => {
    global.Html5Qrcode = jest.fn();
    global.isScanningRegistro = true;

    window.activarEscaneoQRregistroRecursos();
    expect(Html5Qrcode).not.toHaveBeenCalled();
  });

  test('limpia correctamente el escáner', async () => {
    global.scannerRegistro = {
      stop: jest.fn().mockResolvedValue(),
    };
    global.isScanningRegistro = true;

    await window.limpiarQRregistroRecursos();
    expect(global.scannerRegistro.stop).toHaveBeenCalled();
    expect(global.isScanningRegistro).toBe(false);
  });
});
