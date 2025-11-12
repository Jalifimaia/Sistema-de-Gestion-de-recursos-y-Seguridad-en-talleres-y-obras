function parsearClavePorVoz(texto) {
  if (!texto) return '';

  const mapa = {
    cero: '0',
    uno: '1', dos: '2', tres: '3', cuatro: '4', cinco: '5',
    seis: '6', siete: '7', ocho: '8', nueve: '9',
    diez: '10', once: '11', doce: '12', trece: '13', catorce: '14', quince: '15',
    dieciseis: '16', diecisiete: '17', dieciocho: '18', diecinueve: '19',
    veinte: '20', veintiuno: '21', veintidos: '22', veintitres: '23', veinticuatro: '24',
    veinticinco: '25', veintiseis: '26', veintisiete: '27', veintiocho: '28', veintinueve: '29',
    treinta: '30', cuarenta: '40', cincuenta: '50', sesenta: '60',
    setenta: '70', ochenta: '80', noventa: '90',
    mil: '000',
    cuarentaidos: '42', // tolerancia a errores de reconocimiento
    cuarentaitres: '43',
    cincuentayuno: '51',
    cuarentayuno: '41',
    cuarentaycuatro: '44',
    cincuentaydos: '52',
    cincuentaytres: '53',
    sesentayseis: '66',
    setentaysiete: '77',
    ochentayocho: '88',
    noventaynueve: '99',

    treintayuno: '31',
    treintaydos: '32',
    treintaytres: '33',
    treintaycuatro: '34',
    treintaycinco: '35',
    treintayseis: '36',
    treintaysiete: '37',
    treintayocho: '38',
    treintaynueve: '39',

    cuarentaycinco: '45',
    cuarentayseis: '46',
    cuarentaysiete: '47',
    cuarentayocho: '48',
    cuarentaynueve: '49',

    cincuentaycuatro: '54',
    cincuentaycinco: '55',
    cincuentayseis: '56',
    cincuentaysiete: '57',
    cincuentayocho: '58',
    cincuentaynueve: '59',

    sesentayuno: '61',
    sesentaydos: '62',
    sesentaytres: '63',
    sesentaycuatro: '64',
    sesentaycinco: '65',
    sesentaysiete: '67',
    sesentayocho: '68',
    sesentaynueve: '69',

    setentayuno: '71',
    setentaydos: '72',
    setentaytres: '73',
    setentaycuatro: '74',
    setentaycinco: '75',
    setentayseis: '76',
    setentayocho: '78',
    setentaynueve: '79',

    ochentayuno: '81',
    ochentaydos: '82',
    ochentaytres: '83',
    ochentaycuatro: '84',
    ochentaycinco: '85',
    ochentayseis: '86',
    ochentaysiete: '87',
    ochentaynueve: '89',

    noventayuno: '91',
    noventaydos: '92',
    noventaytres: '93',
    noventaycuatro: '94',
    noventaycinco: '95',
    noventayseis: '96',
    noventaysiete: '97',
    noventayocho: '98',
    cien: '100',


  // ... ya existentes ...
  noventasiete: '97',
  noventaocho: '98',
  noventanueve: '99',
  treintauno: '31',
  treintados: '32',
  treintatres: '33',
  treintacuatro: '34',
  treintacinco: '35',
  treintaseis: '36',
  treintasiete: '37',
  treintaocho: '38',
  treintanueve: '39',
  cuarentauno: '41',
  cuarentados: '42',
  // ... y así hasta noventanueve
  // ... tu mapa actual ...
  // Treinta
  treintauno: '31',
  treintados: '32',
  treintatres: '33',
  treintacuatro: '34',
  treintacinco: '35',
  treintaseis: '36',
  treintasiete: '37',
  treintaocho: '38',
  treintanueve: '39',
  // Cuarenta
  cuarentauno: '41',
  cuarentados: '42',
  cuarentatres: '43',
  cuarentacuatro: '44',
  cuarentacinco: '45',
  cuarentaseis: '46',
  cuarentasiete: '47',
  cuarentaocho: '48',
  cuarentanueve: '49',
  // Cincuenta
  cincuentauno: '51',
  cincuentados: '52',
  cincuentatres: '53',
  cincuentacuatro: '54',
  cincuentacinco: '55',
  cincuentaseis: '56',
  cincuentasiete: '57',
  cincuentaocho: '58',
  cincuentanueve: '59',
  // Sesenta
  sesentauno: '61',
  sesentados: '62',
  sesentatres: '63',
  sesentacuatro: '64',
  sesentacinco: '65',
  sesentaseis: '66',
  sesentasiete: '67',
  sesentaocho: '68',
  sesentanueve: '69',
  // Setenta
  setentauno: '71',
  setentados: '72',
  setentatres: '73',
  setentacuatro: '74',
  setentacinco: '75',
  setentaseis: '76',
  setentasiete: '77',
  setentaocho: '78',
  setentanueve: '79',
  // Ochenta
  ochentauno: '81',
  ochentados: '82',
  ochentatres: '83',
  ochentacuatro: '84',
  ochentacinco: '85',
  ochentaseis: '86',
  ochentasiete: '87',
  ochentaocho: '88',
  ochentanueve: '89',
  // Noventa
  noventauno: '91',
  noventados: '92',
  noventatres: '93',
  noventacuatro: '94',
  noventacinco: '95',
  noventaseis: '96',
  noventasiete: '97',
  noventaocho: '98',
  noventanueve: '99'

  };

  const conectoresIgnorados = new Set([
    'del', 'de', 'la', 'el', 'los', 'las',
    'eh', 'por', 'favor', 'gracias', 'porfavor',
    'hola', 'soy', 'clave', 'para', 'es',
    'mi', 'un', 'una', 'usuario', 'nombre', 'identificador',
    'dame', 'decime', 'quiero', 'necesito',
    'mostrar', 'mostrarme', 'ingresar', 'ingrese',
    'comando', 'codigo', 'contraseña', 'como',
    'contraseña', 'contrasena', 'contrasenia'

  ]);



  const normalizar = str =>
    str.toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[.,/\\-]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

  texto = normalizar(texto)
    .replace(/\bveinti\s+uno\b/g, 'veintiuno')
    .replace(/\bveinti\s+dos\b/g, 'veintidos')
    .replace(/\bveinti\s+tres\b/g, 'veintitres')
    .replace(/\bveinti\s+cuatro\b/g, 'veinticuatro')
    .replace(/\bveinti\s+cinco\b/g, 'veinticinco')
    .replace(/\bveinti\s+seis\b/g, 'veintiseis')
    .replace(/\bveinti\s+siete\b/g, 'veintisiete')
    .replace(/\bveinti\s+ocho\b/g, 'veintiocho')
    .replace(/\bveinti\s+nueve\b/g, 'veintinueve');


    ///

// 🔽 INSERTÁ ACÁ el bloque de limpieza de frase inicial
const frasesInicioClave = [
  'ingresa clave', 'ingresar clave', 'clave es',
  'mi clave es', 'clave de usuario es', 'la clave es',
  'por favor ingresa la clave', 'por favor ingresar clave'
];

const fraseValida = frasesInicioClave.find(frase => texto.startsWith(frase));
if (!fraseValida) return ''; // ❌ No se dijo la frase requerida

texto = texto.replace(fraseValida, '').trim(); // ✅ Limpiar la frase inicial

for (const frase of frasesInicioClave) {
  if (texto.startsWith(frase)) {
    texto = texto.replace(frase, '').trim();
    break;
  }
}


  const tokens = texto.split(' ');
  let numero = '';
  let candidatos = [];

  for (let i = 0; i < tokens.length; i++) {
    const t = tokens[i];

    if (/^\d+$/.test(t)) {
      numero += t;
      continue;
    }

    const v = mapa[t];
    if (v !== undefined) {
      // decena + unidad
      if (parseInt(v) >= 30 && parseInt(v) % 10 === 0 && i + 1 < tokens.length) {
        const next = tokens[i + 1];
        if (next === 'y' && i + 2 < tokens.length && mapa[tokens[i + 2]]) {
          numero += String(parseInt(v) + parseInt(mapa[tokens[i + 2]]));
          i += 2;
          continue;
        } else if (mapa[next]) {
          numero += String(parseInt(v) + parseInt(mapa[next]));
          i++;
          continue;
        }
      }

      numero += v;
      continue;
    }

    // palabra no numérica ni reconocida → candidata a nombre
    if (!conectoresIgnorados.has(t)) {
      candidatos.push({ palabra: t, index: i });
    }
  }

  // elegir nombre más confiable: primer candidato antes del número
  const centro = tokens.findIndex(t => mapa[t] || /^\d+$/.test(t));
  const candidatosAntes = candidatos.filter(c => c.index < centro);
  const mejor = candidatosAntes.length > 0 ? candidatosAntes[0] : candidatos[0];
  if (!mejor || !numero) return '';

  // validación: evitar números excesivos
  if (numero.length > 6 || parseInt(numero) > 999999) return '';

  const nombreCapitalizado = mejor.palabra.charAt(0).toUpperCase() + mejor.palabra.slice(1);
  return nombreCapitalizado + numero;
}

// Export CommonJS para tests
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { parsearClavePorVoz };
}
