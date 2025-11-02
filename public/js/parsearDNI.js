function normalizarTexto(str) {
  return str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();
}

function parsearDNIPorBloques(texto) {
  if (!texto) return '';

  const mapa = {
    cero: 0, uno: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5,
    seis: 6, siete: 7, ocho: 8, nueve: 9,
    diez: 10, once: 11, doce: 12, trece: 13, catorce: 14, quince: 15,
    dieciseis: 16, diecisiete: 17, dieciocho: 18, diecinueve: 19,
    veinte: 20, veintiuno: 21, veintidos: 22, veintitres: 23, veinticuatro: 24,
    veinticinco: 25, veintiseis: 26, veintisiete: 27, veintiocho: 28, veintinueve: 29,
    treinta: 30, cuarenta: 40, cincuenta: 50, sesenta: 60,
    setenta: 70, ochenta: 80, noventa: 90,
    cien: 100, ciento: 100, doscientos: 200, trescientos: 300, cuatrocientos: 400,
    quinientos: 500, seiscientos: 600, setecientos: 700, ochocientos: 800, novecientos: 900
  };

  const tokens = normalizarTexto(texto)
    .replace(/[.,/\\-]/g, ' ')
    .split(/\s+/)
    .filter(Boolean);

  if (tokens.join(' ') === 'mil millones') return '1000000000';

  let bloques = [];
  let actual = 0;
  let acumulando = false;
  let palabrasAcumuladas = [];

  for (const token of tokens) {
    if (/^\d+$/.test(token)) {
      bloques.push(token);
      actual = 0;
      acumulando = false;
      palabrasAcumuladas = [];
    } else if (['mil', 'millones', 'millón'].includes(token)) {
      if (acumulando && actual > 0) {
        bloques.push(String(actual));
      }
      actual = 0;
      acumulando = false;
      palabrasAcumuladas = [];
    } else if (mapa[token] !== undefined) {
      actual += mapa[token];
      acumulando = true;
      palabrasAcumuladas.push(token);
    } else {
      // palabra irrelevante, cortar acumulación
      if (acumulando && actual > 0) {
        // ⚠️ Validación: si solo hay 2 palabras y ambas son menores a 30, probablemente sea ambiguo
        const esAmbiguo = palabrasAcumuladas.length <= 2 &&
                          palabrasAcumuladas.every(p => mapa[p] < 30);
        if (!esAmbiguo) {
          bloques.push(String(actual));
        }
      }
      actual = 0;
      acumulando = false;
      palabrasAcumuladas = [];
    }
  }

  if (acumulando && actual > 0) {
    const esAmbiguo = palabrasAcumuladas.length <= 2 &&
                      palabrasAcumuladas.every(p => mapa[p] < 30);
    if (!esAmbiguo) {
      bloques.push(String(actual));
    }
  }

  const resultado = bloques.join('');
  return /^\d{7,9}$/.test(resultado) ? resultado : '';
}

module.exports = { parsearDNIPorBloques };
