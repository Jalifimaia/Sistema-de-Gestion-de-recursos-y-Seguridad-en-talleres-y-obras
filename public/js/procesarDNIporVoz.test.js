const { parsearDNIPorBloques } = require('./parsearDNI');

describe('parsearDNIPorBloques', () => {
  test('veinte millones quinientos ochenta y tres mil trescientos veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil trescientos veintiuno')).toBe('20583321');
  });

  test('20 millones 583 321 → 20583321', () => {
    expect(parsearDNIPorBloques('20 millones 583 321')).toBe('20583321');
  });

  test('2 0 5 8 3 3 2 1 → 20583321', () => {
    expect(parsearDNIPorBloques('2 0 5 8 3 3 2 1')).toBe('20583321');
  });

  test('mi dni es veinte millones quinientos ochenta y tres mil trescientos veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('mi dni es veinte millones quinientos ochenta y tres mil trescientos veintiuno')).toBe('20583321');
  });

  test('20, 583.321 → 20583321', () => {
    expect(parsearDNIPorBloques('20, 583.321')).toBe('20583321');
  });

  test('veinte millones quinientos ochenta y tres mil trescientos veinte y uno → 20583321', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil trescientos veinte y uno')).toBe('20583321');
  });

  test('veinte millones quinientos ochenta y tres mil tres veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil tres veintiuno')).toBe('20583321');
  });

  test('mi número es veinte millones quinientos ochenta y tres mil trescientos veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('mi número es veinte millones quinientos ochenta y tres mil trescientos veintiuno')).toBe('20583321');
  });

  test('dni veinte millones quinientos ochenta y tres mil trescientos veintiuno por favor → 20583321', () => {
    expect(parsearDNIPorBloques('dni veinte millones quinientos ochenta y tres mil trescientos veintiuno por favor')).toBe('20583321');
  });

  test('20 millones quinientos ochenta y tres mil 321 → 20583321', () => {
    expect(parsearDNIPorBloques('20 millones quinientos ochenta y tres mil 321')).toBe('20583321');
  });

  test('20 millones 583 mil trescientos veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('20 millones 583 mil trescientos veintiuno')).toBe('20583321');
  });

  test('cincuenta millones doscientos treinta mil cuatrocientos uno → 50230401', () => {
    expect(parsearDNIPorBloques('cincuenta millones doscientos treinta mil cuatrocientos uno')).toBe('50230401');
  });

  test('mil millones → 1000000000', () => {
    expect(parsearDNIPorBloques('mil millones')).toBe('1000000000');
  });

  test('resultado tiene longitud válida de DNI', () => {
    const resultado = parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil trescientos veintiuno');
    expect(resultado.length).toBeGreaterThanOrEqual(7);
    expect(resultado.length).toBeLessThanOrEqual(9);
  });

  /* Nuevos tests añadidos */

  test('entrada con guiones y y compuesta → 12345678', () => {
    expect(parsearDNIPorBloques('doce millones trescientos cuarenta y cinco mil seiscientos setenta y ocho')).toBe('12345678');
  });

  test('números mezclados palabras y dígitos → 30500123', () => {
    expect(parsearDNIPorBloques('30 millones 500 mil 123')).toBe('30500123');
  });

  test('formas con veintiuno separado → 11234567', () => {
    expect(parsearDNIPorBloques('once millones doscientos treinta y cuatro mil quinientos sesenta y siete')).toBe('11234567');
  });

  test('entrada con palabras sueltas fuera de orden no numérica → empty', () => {
    expect(parsearDNIPorBloques('hola esto no es un dni')).toBe('');
  });

  test('cadenas vacías o null → empty', () => {
    expect(parsearDNIPorBloques('')).toBe('');
    expect(parsearDNIPorBloques(null)).toBe('');
    expect(parsearDNIPorBloques(undefined)).toBe('');
  });

  test('mezcla ambigua tres veintiuno → 20583321', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil tres veintiuno')).toBe('20583321');
  });

  test('espacios múltiples y comas → 20583321', () => {
    expect(parsearDNIPorBloques('  veinte   millones,   quinientos   ochenta y   tres   mil  321  ')).toBe('20583321');
  });

});


// tests adicionales para parsearDNIPorBloques
describe('parsearDNIPorBloques - tests adicionales', () => {

  test('ambigua: "tres veintiuno" dentro de miles → 20583321 (variante)', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil tres veintiuno')).toBe('20583321');
  });

  test('ambigua: "tres veintiuno" sin contexto → 321', () => {
    expect(parsearDNIPorBloques('tres veintiuno')).toBe('321');
  });

  test('omisión de "cientos": "tres veintiuno" interpretado como 321 en bloque mayor', () => {
    expect(parsearDNIPorBloques('un millón tres veintiuno')).toBe('1000321');
  });

  test('acento y variaciones ortográficas → 20583321', () => {
    expect(parsearDNIPorBloques('Veinté millones quinientos ochenta y tres mil trescientos veintidós')).not.toBe(''); // asegurar normalización
  });

  test('ceros a la izquierda en dígitos → 00123456 => 123456', () => {
    expect(parsearDNIPorBloques('0 0 1 2 3 4 5 6')).toBe('00123456');
  });

  test('mezcla de guiones y barras → 20583321', () => {
    expect(parsearDNIPorBloques('20-millones 583/321')).toBe('20583321');
  });

  test('texto con palabras irrelevantes intercaladas → 20583321', () => {
    expect(parsearDNIPorBloques('hola mi dni es veinte millones y 583 mil, gracias 321')).toBe('20583321');
  });

  test('solo palabras no numéricas devuelve vacío', () => {
    expect(parsearDNIPorBloques('esto no tiene numeros')).toBe('');
  });

  test('número grande con espacios extra → 1000000000', () => {
    expect(parsearDNIPorBloques('  mil    millones  ')).toBe('1000000000');
  });

  test('veintidos con tilde y sin tilde → 22000000', () => {
    expect(parsearDNIPorBloques('veintidós millones')).toBe('22000000');
    expect(parsearDNIPorBloques('veintidos millones')).toBe('22000000');
  });

});

// tests adicionales para parsearDNIPorBloques (casos reportados fallando)
describe('parsearDNIPorBloques - casos difíciles reportados', () => {

  test('tres veintiuno solo → 321', () => {
    expect(parsearDNIPorBloques('tres veintiuno')).toBe('321');
  });

  test('tres veintiuno dentro de miles → 20583321', () => {
    expect(parsearDNIPorBloques('veinte millones quinientos ochenta y tres mil tres veintiuno')).toBe('20583321');
  });

  test('omisión de "cientos" en bloque mayor: un millón tres veintiuno → 1000321', () => {
    expect(parsearDNIPorBloques('un millón tres veintiuno')).toBe('1000321');
  });

  test('mezcla de guiones y barras: 20-millones 583/321 → 20583321', () => {
    expect(parsearDNIPorBloques('20-millones 583/321')).toBe('20583321');
  });

  test('variantes con tilde y sin tilde: veintidós millones → 22000000', () => {
    expect(parsearDNIPorBloques('veintidós millones')).toBe('22000000');
    expect(parsearDNIPorBloques('veintidos millones')).toBe('22000000');
  });

  test('forma compuesta con "millon" sin tilde y texto extra → 22000000', () => {
    expect(parsearDNIPorBloques('veintidos millon')).toBe('22000000');
  });

  test('texto intercalado y formatos mixtos → 20583321', () => {
    expect(parsearDNIPorBloques('hola mi dni es veinte-millones y 583 mil, gracias 321')).toBe('20583321');
  });

  test('entrada con barras y espacios extra → 20583321', () => {
    expect(parsearDNIPorBloques('  20 / 583 / 321  ')).toBe('20583321');
  });

  test('solo guiones y palabras vacías → empty', () => {
    expect(parsearDNIPorBloques('- - --')).toBe('');
  });

  test('combinación de palabras irrelevantes al inicio y fin → 20583321', () => {
    expect(parsearDNIPorBloques('por favor validar: veinte millones 583 mil 321 gracias')).toBe('20583321');
  });

});
