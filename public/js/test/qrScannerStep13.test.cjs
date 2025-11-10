require('../terminal');

describe('Escáner QR de registro por QR (step13)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="qr-reader-step13"></div>
      <button id="btn-escanear-qr-step13"></button>
      <button id="btn-cancelar-qr-step13"></button>
      <div id="texto-camara-activa-step13"></div>
    `;
    global.scannerRegistroStep13 = null;
    global.isScanningStep13 = false;
  });

  test('inicia escáner si contenedor está disponible', () => {
    global.Html5Qrcode = jest.fn().mockImplementation(() => ({
      start: jest.fn().mockResolvedValue(),
    }));

    window.activarEscaneoQRregistroRecursosStep13();
    expect(Html5Qrcode).toHaveBeenCalledWith('qr-reader-step13');
  });

  test('no inicia si ya está escaneando', () => {
    global.Html5Qrcode = jest.fn();
    global.isScanningStep13 = true;

    window.activarEscaneoQRregistroRecursosStep13();
    expect(Html5Qrcode).not.toHaveBeenCalled();
  });

  test('limpia correctamente el escáner', async () => {
    global.scannerRegistroStep13 = {
      stop: jest.fn().mockResolvedValue(),
    };
    global.isScanningStep13 = true;

    await window.limpiarQRregistroRecursosStep13();
    expect(global.scannerRegistroStep13.stop).toHaveBeenCalled();
    expect(global.isScanningStep13).toBe(false);
  });
});
