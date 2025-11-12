global.Html5Qrcode = jest.fn().mockImplementation(() => ({
  start: jest.fn().mockResolvedValue(),
  stop: jest.fn().mockResolvedValue()
}));

global.nextStep = jest.fn();
global.safeStartRecognitionGlobal = jest.fn();
global.safeStopRecognitionGlobal = jest.fn();

beforeEach(() => {
  global.scannerLogin = { stop: jest.fn().mockResolvedValue() };
  global.scannerRegistro = { stop: jest.fn().mockResolvedValue() };
  global.scannerRegistroStep13 = { stop: jest.fn().mockResolvedValue() };

  global.isScanningLogin = true;
  global.isScanningRegistro = true;
  global.isScanningStep13 = true;
});
