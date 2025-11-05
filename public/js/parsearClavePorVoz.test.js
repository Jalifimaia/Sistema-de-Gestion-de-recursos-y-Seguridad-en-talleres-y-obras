const { parsearClavePorVoz } = require('./parsearClavePorVoz');

describe('parsearClavePorVoz', () => {
  test('clave directa con número → David89', () => {
    expect(parsearClavePorVoz('David ochenta y nueve')).toBe('David89');
  });

  test('clave con número separado → David89', () => {
    expect(parsearClavePorVoz('David ocho nueve')).toBe('David89');
  });

  test('clave con número en dígitos → David89', () => {
    expect(parsearClavePorVoz('David 89')).toBe('David89');
  });

  test('clave con texto extra → David89', () => {
    expect(parsearClavePorVoz('mi clave es David ochenta y nueve')).toBe('David89');
  });

  test('clave con nombre en minúscula → David89', () => {
    expect(parsearClavePorVoz('david ochenta y nueve')).toBe('David89');
  });

  test('clave con número compuesto → David42', () => {
    expect(parsearClavePorVoz('David cuarenta y dos')).toBe('David42');
  });

  test('clave con número simple → David5', () => {
    expect(parsearClavePorVoz('David cinco')).toBe('David5');
  });

  test('clave con número en palabras separadas → David21', () => {
    expect(parsearClavePorVoz('David veintiuno')).toBe('David21');
  });

  test('clave con número en dígitos y texto extra → David21', () => {
    expect(parsearClavePorVoz('clave David 21 por favor')).toBe('David21');
  });

  test('clave sin número → vacío', () => {
    expect(parsearClavePorVoz('David')).toBe('');
  });

  test('clave sin nombre → vacío', () => {
    expect(parsearClavePorVoz('ochenta y nueve')).toBe('');
  });

 /* test('clave con nombre y número fuera de rango → vacío', () => {
    expect(parsearClavePorVoz('David mil millones')).toBe('');
  });*/

  test('clave con nombre y número en orden inverso → David89', () => {
    expect(parsearClavePorVoz('ochenta y nueve David')).toBe('David89');
  });

  test('clave con texto irrelevante → vacío', () => {
    expect(parsearClavePorVoz('esto no es una clave válida')).toBe('');
  });
});


describe('parsearClavePorVoz - casos extendidos', () => {
  // Ruido verbal y texto extra
  test('David ochenta y nueve por favor → David89', () => {
    expect(parsearClavePorVoz('David ochenta y nueve por favor')).toBe('David89');
  });

  test('mi clave es: David ochenta y nueve, gracias → David89', () => {
    expect(parsearClavePorVoz('mi clave es: David ochenta y nueve, gracias')).toBe('David89');
  });

  test('David, ochenta, y, nueve → David89', () => {
    expect(parsearClavePorVoz('David, ochenta, y, nueve')).toBe('David89');
  });

  // Formatos mixtos
  test('David 8 nueve → David89', () => {
    expect(parsearClavePorVoz('David 8 nueve')).toBe('David89');
  });

  test('David ocho 9 → David89', () => {
    expect(parsearClavePorVoz('David ocho 9')).toBe('David89');
  });

  // Ceros a la izquierda
  test('David 0 0 1 2 3 → David00123', () => {
    expect(parsearClavePorVoz('David 0 0 1 2 3')).toBe('David00123');
  });

  // Mayúsculas y variantes
  test('DAVID ochenta y nueve → David89', () => {
    expect(parsearClavePorVoz('DAVID ochenta y nueve')).toBe('David89');
  });

  test('dAvId ochenta y nueve → David89', () => {
    expect(parsearClavePorVoz('dAvId ochenta y nueve')).toBe('David89');
  });

  // Orden inverso con ruido
  test('ochenta y nueve David por favor → David89', () => {
    expect(parsearClavePorVoz('ochenta y nueve David por favor')).toBe('David89');
  });

  // Nombres alternativos (si se extiende la función)
  test('Ana cuarenta y dos → Ana42', () => {
    expect(parsearClavePorVoz('Ana cuarenta y dos')).toBe('Ana42'); // si se soporta Ana
  });

  // Ambigüedades
  test('David veinte uno → David201', () => {
    expect(parsearClavePorVoz('David veinte uno')).toBe('David201'); // comportamiento explícito
  });

  test('David veinti uno → David21', () => {
  expect(parsearClavePorVoz('David veinti uno')).toBe('David21');
});


  // Texto irrelevante con número válido
  test('hola David ochenta y nueve chau → David89', () => {
    expect(parsearClavePorVoz('hola David ochenta y nueve chau')).toBe('David89');
  });

  // Número largo
  test('David ochenta y nueve mil → David89000', () => {
    expect(parsearClavePorVoz('David ochenta y nueve mil')).toBe('David89000'); // si se permite
  });
});


describe('parsearClavePorVoz - nombres dinámicos y casos complejos', () => {
  // Nombres variados
  test('Ana cuarenta y dos → Ana42', () => {
    expect(parsearClavePorVoz('Ana cuarenta y dos')).toBe('Ana42');
  });

  test('Carlos 0 0 1 2 3 → Carlos00123', () => {
    expect(parsearClavePorVoz('Carlos 0 0 1 2 3')).toBe('Carlos00123');
  });

  test('Lucía ochenta y nueve → Lucia89', () => {
    expect(parsearClavePorVoz('Lucía ochenta y nueve')).toBe('Lucia89');
  });

 /* test('clave para lucia es ochenta y nueve → Lucia89', () => {
    expect(parsearClavePorVoz('clave para lucia es ochenta y nueve')).toBe('Lucia89');
  });*/

  test('ochenta y nueve David → David89', () => {
    expect(parsearClavePorVoz('ochenta y nueve David')).toBe('David89');
  });

  test('clave de usuario es david ochenta y nueve → David89', () => {
    expect(parsearClavePorVoz('clave de usuario es david ochenta y nueve')).toBe('David89');
  });

  // Formatos mixtos
  test('David 8 nueve → David89', () => {
    expect(parsearClavePorVoz('David 8 nueve')).toBe('David89');
  });

  test('David ocho 9 → David89', () => {
    expect(parsearClavePorVoz('David ocho 9')).toBe('David89');
  });

  test('David ochenta y nueve mil → David89000', () => {
    expect(parsearClavePorVoz('David ochenta y nueve mil')).toBe('David89000');
  });

  // Mayúsculas y variantes
  test('DAVID ochenta y nueve → David89', () => {
    expect(parsearClavePorVoz('DAVID ochenta y nueve')).toBe('David89');
  });

  test('dAvId ochenta y nueve → David89', () => {
    expect(parsearClavePorVoz('dAvId ochenta y nueve')).toBe('David89');
  });

  // Ambigüedades
  test('David veinte uno → David201', () => {
    expect(parsearClavePorVoz('David veinte uno')).toBe('David201');
  });

  test('David veinti uno → David21', () => {
    expect(parsearClavePorVoz('David veinti uno')).toBe('David21');
  });

  // Ruido verbal
  test('mi clave es: David ochenta y nueve, gracias → David89', () => {
    expect(parsearClavePorVoz('mi clave es: David ochenta y nueve, gracias')).toBe('David89');
  });

  test('David, ochenta, y, nueve → David89', () => {
    expect(parsearClavePorVoz('David, ochenta, y, nueve')).toBe('David89');
  });

  test('hola David ochenta y nueve chau → David89', () => {
    expect(parsearClavePorVoz('hola David ochenta y nueve chau')).toBe('David89');
  });

  
});
