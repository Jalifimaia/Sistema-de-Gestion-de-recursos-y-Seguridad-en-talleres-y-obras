require('../terminal');

describe('Escáner QR de login', () => {
  beforeEach(() => {
    document.body.innerHTML = `<div id="qr-login-reader"></div>`;
    global.scannerLogin = null;
    global.isScanningLogin = false;
  });

  test('inicia escáner si contenedor está disponible', () => {
    global.Html5Qrcode = jest.fn().mockImplementation(() => ({
      start: jest.fn().mockResolvedValue(),
    }));

    window.activarEscaneoQRLogin();
    expect(Html5Qrcode).toHaveBeenCalledWith('qr-login-reader');
  });

  test('no inicia si ya está escaneando', () => {
    global.Html5Qrcode = jest.fn();
    global.isScanningLogin = true;

    window.activarEscaneoQRLogin();
    expect(Html5Qrcode).not.toHaveBeenCalled();
  });

test('detiene y limpia escáner correctamente', async () => {
  document.body.innerHTML = `<div id="qr-login-reader"></div>`;
  global.scannerLogin = {
    stop: jest.fn().mockResolvedValue()
  };
  global.isScanningLogin = true;

  await window.detenerEscaneoQRLogin();

  expect(global.scannerLogin.stop).toHaveBeenCalled();
  expect(global.isScanningLogin).toBe(false);
});


});
