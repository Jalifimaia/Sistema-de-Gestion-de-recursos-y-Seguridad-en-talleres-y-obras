jest.mock('./validarDevolucionQR', () => ({
  validarDevolucionQR: jest.fn()
}));

const { validarDevolucionQR } = require('./validarDevolucionQR');

describe('validarDevolucionQR', () => {
  const mockIdUsuario = 123;
  const serieEsperada = 'P-V3-AYEEC-10-01-001';

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('debe aceptar QR válido y coincidente', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: true,
      coincide: true,
      id_detalle: 456
    });

    const res = await validarDevolucionQR('HX-038', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(true);
    expect(res.coincide).toBe(true);
    expect(res.id_detalle).toBe(456);
  });

  it('debe rechazar QR válido pero no asignado', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: true,
      coincide: false,
      message: 'El recurso ya fue devuelto o no está asignado a este usuario'
    });

    const res = await validarDevolucionQR('OTRO-QR', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(true);
    expect(res.coincide).toBe(false);
    expect(res.message).toMatch(/no está asignado/i);
  });

  it('debe rechazar QR inexistente', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: false,
      message: 'QR no corresponde a ninguna serie registrada'
    });

    const res = await validarDevolucionQR('QR-INVALIDO-XYZ', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(false);
    expect(res.message).toMatch(/no corresponde/i);
  });

  it('debe rechazar QR con préstamo no activo', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: true,
      coincide: false,
      message: 'El préstamo no está activo'
    });

    const res = await validarDevolucionQR('HX-038', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(true);
    expect(res.coincide).toBe(false);
    expect(res.message).toMatch(/no está activo/i);
  });

  it('debe rechazar QR ya devuelto', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: true,
      coincide: false,
      message: 'El recurso ya fue devuelto'
    });

    const res = await validarDevolucionQR('HX-038', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(true);
    expect(res.coincide).toBe(false);
    expect(res.message).toMatch(/ya fue devuelto/i);
  });

  it('debe manejar error de red', async () => {
    validarDevolucionQR.mockRejectedValue(new Error('Network error'));

    await expect(validarDevolucionQR('HX-038', mockIdUsuario, serieEsperada)).rejects.toThrow('Network error');
  });

  it('debe rechazar QR asignado a otro usuario', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: true,
      coincide: false,
      message: 'Este recurso está asignado a otro usuario'
    });

    const res = await validarDevolucionQR('HX-038', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(true);
    expect(res.coincide).toBe(false);
    expect(res.message).toMatch(/otro usuario/i);
  });

  it('debe rechazar QR que no coincide con la serie esperada', async () => {
    validarDevolucionQR.mockResolvedValue({
      success: false,
      message: 'El QR escaneado no coincide con el recurso que se está devolviendo'
    });

    const res = await validarDevolucionQR('QR-DE-OTRO-RECURSO', mockIdUsuario, serieEsperada);
    expect(res.success).toBe(false);
    expect(res.message).toMatch(/no coincide/i);
  });
});
