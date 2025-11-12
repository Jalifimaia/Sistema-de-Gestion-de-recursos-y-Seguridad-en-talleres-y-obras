// tokenUtils.test.js
const {
  getHeadersSeguros,
  refrescarTokenCSRF,
  verificarSesionActiva,
  manejarErrorFetch
} = require('./tokenUtils');

beforeEach(() => {
  document.body.innerHTML = `<meta name="csrf-token" content="test-token">`;

  global.localStorage = {
    getItem: jest.fn(),
    setItem: jest.fn()
  };

  global.mostrarModalKioscoSinVoz = jest.fn();
  global.fetch = jest.fn();
});

afterEach(() => {
  jest.clearAllMocks();
});


describe('getHeadersSeguros', () => {
  test('devuelve headers con token CSRF si está presente', () => {
    const headers = getHeadersSeguros();
    expect(headers['Content-Type']).toBe('application/json');
    expect(headers['X-CSRF-TOKEN']).toBe('test-token');
  });

  test('devuelve headers sin X-CSRF-TOKEN si no hay meta', () => {
    document.querySelector('meta[name="csrf-token"]').remove();
    const headers = getHeadersSeguros();
    expect(headers['Content-Type']).toBe('application/json');
    expect(headers['X-CSRF-TOKEN']).toBeUndefined();
  });
});

describe('refrescarTokenCSRF', () => {
  test('actualiza el meta tag con nuevo token', async () => {
    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({ token: 'nuevo-token' })
    });

    const token = await refrescarTokenCSRF();
    expect(token).toBe('nuevo-token');
    expect(document.querySelector('meta[name="csrf-token"]').content).toBe('nuevo-token');
  });

  test('lanza error si no hay token en respuesta', async () => {
    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({})
    });

    await expect(refrescarTokenCSRF()).rejects.toThrow('No se pudo actualizar el token CSRF');
  });
});

describe('verificarSesionActiva', () => {
  test('devuelve false si no hay id_usuario', async () => {
    localStorage.getItem.mockReturnValue(null);
    const result = await verificarSesionActiva();
    expect(result).toBe(false);
    expect(mostrarModalKioscoSinVoz).toHaveBeenCalledWith(expect.stringContaining('No hay trabajador'), 'danger');
  });

  test('intenta refrescar token si no hay CSRF', async () => {
    localStorage.getItem.mockReturnValue('123');
    document.querySelector('meta[name="csrf-token"]').remove();

    fetch.mockResolvedValueOnce({
      json: () => Promise.resolve({ token: 'refrescado' })
    });

    const result = await verificarSesionActiva();
    expect(result).toBe(true);
    expect(document.querySelector('meta[name="csrf-token"]').content).toBe('refrescado');
  });

  test('devuelve false si refresco falla', async () => {
    localStorage.getItem.mockReturnValue('123');
    document.querySelector('meta[name="csrf-token"]').remove();

    fetch.mockRejectedValueOnce(new Error('fallo'));
    const result = await verificarSesionActiva();
    expect(result).toBe(false);
    expect(mostrarModalKioscoSinVoz).toHaveBeenCalledWith(expect.stringContaining('No se pudo recuperar'), 'danger');
  });
});

describe('manejarErrorFetch', () => {
  beforeEach(() => {
    jest.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    console.error.mockRestore();
  });

  test('muestra mensaje para error 419', () => {
    const err = new Error('HTTP 419');
    const result = manejarErrorFetch(err, 'Test');
    expect(result.success).toBe(false);
    expect(mostrarModalKioscoSinVoz).toHaveBeenCalledWith(expect.stringContaining('expirada'), 'danger');
  });

  test('muestra mensaje para error 500', () => {
    const err = new Error('HTTP 500');
    const result = manejarErrorFetch(err, 'Test');
    expect(mostrarModalKioscoSinVoz).toHaveBeenCalledWith(expect.stringContaining('interno'), 'danger');
  });

  test('muestra mensaje genérico si no coincide', () => {
    const err = new Error('otra cosa');
    const result = manejarErrorFetch(err, 'Test');
    expect(mostrarModalKioscoSinVoz).toHaveBeenCalledWith(expect.stringContaining('Test'), 'danger');
  });
});
