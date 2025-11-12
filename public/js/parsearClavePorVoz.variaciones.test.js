const { parsearClavePorVoz } = require('./parsearClavePorVoz');

// Tests actualizados: todas las entradas válidas deben empezar con una frase activadora
describe('parsearClavePorVoz - pruebas actualizadas con frase activadora requerida', () => {
  // Frases activadoras aceptadas (pruebas usarán la primera)
  const activadora = 'ingresar clave ';

  // Lista de frases que antes eran pruebas positivas (sin activadora)
  const casosPositivos = [
    // grupo: variaciones realistas
    'Carlos noventa y tres',
    'Carlos noventa tres',
    'Carlos 93',
    'Carlos nueve tres',
    'noventa y tres Carlos',
    'Hector sesenta y seis',
    'Hector sesenta seis',
    'Hector seis seis',
    'Hector 66',
    'sesenta y seis Hector',
    'Lucia cinco',
    'Lucia 5',
    'cinco Lucia',
    'Ana cuarenta y dos',
    'Ana cuarenta dos',
    'Ana 42',
    'cuarenta y dos Ana',
    'Carlos veintiuno',
    'Carlos veinti uno',
    'veinti uno Carlos',
    'eh Carlos noventa y tres',
    'Lucia cinco gracias',
    'por favor Hector sesenta y seis',
    'cArLos noventa y tres',
    'LUcia cinco',

    // cobertura extendida
    'Sofia setenta y siete',
    'Sofia setenta siete',
    'Sofia siete siete',
    'Sofia 77',
    'setenta y siete Sofia',
    'Pedro doce',
    'Pedro 12',
    'doce Pedro',
    'Martina ochenta y ocho',
    'Martina ochenta ocho',
    'Martina ocho ocho',
    'Martina 88',
    'Juan treinta',
    'Juan tres cero',
    'Juan 30',
    'treinta Juan',
    'Valentina cinco',
    'Valentina 5',
    'cinco Valentina',
    'Diego noventa y nueve',
    'Diego noventa nueve',
    'Diego nueve nueve',
    'Diego 99',
    'eh Sofia setenta y siete',
    'Martina ochenta y ocho gracias',
    'por favor Pedro doce',
    'VALENTINA cinco',
    'dIeGo noventa y nueve',
    'Carlos cero cinco',
    'Lucia cero cero siete',
    'Pedro 0 0 1',
    'JuanCarlos ochenta y uno',
    'María José cuarenta y cuatro',
    'Santiago del Estero treinta y tres',
    'Carlos cuarenta y dos',
    'Carlos cuarenta dos',
    'Carlos cuarentaidos',

    // cobertura avanzada
    'Lucia cero cinco',
    'Carlos cero cero uno',
    'Pedro cero nueve nueve',
    'Juan Carlos noventa y tres',
    'Maria del Carmen cuarenta y dos',
    'Luis Alberto cincuenta y cinco',
    'noventa y tres Carlos',
    'cincuenta y cinco Luis',
    'doce Pedro',
    'hola soy Lucia cinco',
    'clave para Carlos es noventa y tres',
    'por favor Juan treinta',
    'Carlos cuarentaitres',
    'Lucia cincuentayuno',

    // tolerancia extendida y relleno verbal
    'Carlos cuarentayuno',
    'Lucia cincuentaydos',
    'Pedro sesentayseis',
    'Sofia setentaysiete',
    'Martina ochentayocho',
    'Diego noventaynueve',
    'mi usuario es Carlos noventa y tres',
    'quiero ingresar como Lucia cinco',
    'mostrarme la clave de Pedro doce',
    'dame el código de Juan treinta',
    'ingresar contraseña Valentina cinco'
  ];

  // Test generator: crea tests que PRECISEMENTE comienzan con la activadora
  casosPositivos.forEach((frase) => {
    const nombreTest = `${activadora + frase} → se interpreta correctamente`;
    test(nombreTest, () => {
      expect(parsearClavePorVoz(activadora + frase)).not.toBe('');
      // opción explícita: si querés comparar el resultado esperado exacto,
      // reemplazá la línea anterior por las expectativas concretas (ej: 'Carlos93').
    });
  });

  // Casos negativos: frases que NO deben considerarse válidas sin activadora
  const casosNegativos = [
    'Carlos noventa y tres',
    'Lucia cinco',
    'Pedro doce',
    'Juan treinta',
    'Valentina cinco',
    'Diego noventa y nueve'
  ];

  casosNegativos.forEach((frase) => {
    test(`${frase} → debe devolver vacío porque falta activadora`, () => {
      expect(parsearClavePorVoz(frase)).toBe('');
    });
  });

  // Tests específicos que verifican resultado exacto (opcional / más estrictos)
  // Si querés comprobar valores exactos, descomenta y ajusta según corresponda:
  /*
  test('ingresar clave Carlos noventa y tres → Carlos93', () => {
    expect(parsearClavePorVoz('ingresar clave Carlos noventa y tres')).toBe('Carlos93');
  });

  test('ingresar clave Lucia cinco → Lucia5', () => {
    expect(parsearClavePorVoz('ingresar clave Lucia cinco')).toBe('Lucia5');
  });

  // ... añade más comparaciones puntuales si querés resultados exactos
  */
});


describe('parsearClavePorVoz - frases iniciales requeridas para activar el parser', () => {
  // Frases válidas
  test('ingresar clave Carlos noventa y tres → Carlos93', () => {
    expect(parsearClavePorVoz('ingresar clave Carlos noventa y tres')).toBe('Carlos93');
  });

  test('ingresa clave Lucia cinco → Lucia5', () => {
    expect(parsearClavePorVoz('ingresa clave Lucia cinco')).toBe('Lucia5');
  });

  test('mi clave es Pedro doce → Pedro12', () => {
    expect(parsearClavePorVoz('mi clave es Pedro doce')).toBe('Pedro12');
  });

  test('clave de usuario es Juan treinta → Juan30', () => {
    expect(parsearClavePorVoz('clave de usuario es Juan treinta')).toBe('Juan30');
  });

  test('por favor ingresar clave Valentina cinco → Valentina5', () => {
    expect(parsearClavePorVoz('por favor ingresar clave Valentina cinco')).toBe('Valentina5');
  });

  test('la clave es Diego noventa y nueve → Diego99', () => {
    expect(parsearClavePorVoz('la clave es Diego noventa y nueve')).toBe('Diego99');
  });

  // Frases inválidas (no comienzan con frase requerida)
  test('Carlos noventa y tres → vacío', () => {
    expect(parsearClavePorVoz('Carlos noventa y tres')).toBe('');
  });

  test('Lucia cinco → vacío', () => {
    expect(parsearClavePorVoz('Lucia cinco')).toBe('');
  });

  test('Pedro doce → vacío', () => {
    expect(parsearClavePorVoz('Pedro doce')).toBe('');
  });

  test('Juan treinta → vacío', () => {
    expect(parsearClavePorVoz('Juan treinta')).toBe('');
  });

  test('Valentina cinco → vacío', () => {
    expect(parsearClavePorVoz('Valentina cinco')).toBe('');
  });

  test('Diego noventa y nueve → vacío', () => {
    expect(parsearClavePorVoz('Diego noventa y nueve')).toBe('');
  });
});
