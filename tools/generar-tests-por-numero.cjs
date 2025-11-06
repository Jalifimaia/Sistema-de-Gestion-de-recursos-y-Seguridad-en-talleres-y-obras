// tools/generar-tests-por-numero.js
const fs = require('fs');
const path = require('path');

// CONFIG ------------------------------------------------
const OUT = path.resolve(__dirname, '../public/js/parsearClavePorVoz.autogen.test.js');
const activadora = 'ingresar clave ';
const nombres = [
  'Carlos', 'Lucia', 'Pedro', 'Juan', 'Valentina', 'Diego',
  'Sofia', 'Martina', 'Ana', 'Hector', 'Luis', 'Maria'
];
// Si querés tests con ceros a la izquierda para 2 dígitos: poner true
const generarConCeros = false;
// FIN CONFIG --------------------------------------------

// Conversor de número a palabras (ESP) 0..100
function numeroATexto(n) {
  if (n === 100) return 'cien';
  const unidades = [
    'cero','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
    'diez','once','doce','trece','catorce','quince','dieciseis','diecisiete','dieciocho','diecinueve'
  ];
  if (n < 20) return unidades[n];
  const decenasPal = {
    20: 'veinte', 30: 'treinta', 40: 'cuarenta', 50: 'cincuenta',
    60: 'sesenta', 70: 'setenta', 80: 'ochenta', 90: 'noventa'
  };
  if (n < 30) {
    // 20..29 -> 'veinte' or 'veintiuno' / also produce spaced variant 'veinti uno'
    if (n === 20) return 'veinte';
    return 'veinti' + unidades[n - 20];
  }
  const dec = Math.floor(n / 10) * 10;
  const uni = n % 10;
  if (uni === 0) return decenasPal[dec];
  return decenasPal[dec] + ' y ' + unidades[uni];
}

// Variantes escriturales que queremos generar por número
function variantesNumero(n) {
  const v = [];
  const palabra = numeroATexto(n);
  // 1) Forma palabra normal (con 'y' cuando aplica)
  v.push(palabra);
  // 2) Forma compacta sin espacios ni 'y' para casos reconocidos por ASR (e.g., veinti uno -> veintiuno)
  v.push(palabra.replace(/\s+/g, '').replace(/y/g, ''));
  // 3) Forma "espaciada" para veinti uno -> 'veinti uno' (solo si contiene 'veinti' o es compuesta)
  if (/\bveinti/.test(palabra) || /\s+y\s+/.test(palabra)) {
    v.push(palabra.replace(/y/g, '').replace(/\s+/g, ' ').trim());
  }
  // 4) Forma en dígitos
  v.push(String(n));
  // 5) Si pedís ceros a la izquierda, agregá '05' para n<10, o '005' si querés 3 dígitos
  if (generarConCeros) {
    if (n < 10) v.push('0' + n);
    if (n < 100) v.push(n.toString().padStart(2, '0'));
  }
  // dedupe
  return [...new Set(v)];
}

// Normalizar un texto a una expectativa esperada: Nombre + número sin pads (ej: 'Carlos05' -> 'Carlos5' si no pediste ceros)
function expectedFor(nombre, n) {
  // si generás con ceros, podría esperarse padding; aquí asumimos sin padding en el esperado
  const pad = generarConCeros ? String(n).padStart( (n<10 ? 2: (n<100?2:3)), '0') : String(n);
  // preferir sin padding para consistencia con parser existente
  return nombre + String(n).padStart( (String(n).length === 1 && !generarConCeros) ? 1 : String(n).length, '0').replace(/^0+/, '') || String(n);
}

// Construcción del archivo de tests
let out = '';
out += "// Auto-generado por tools/generar-tests-por-numero.js - no editar a mano\n";
out += "/* global parsearClavePorVoz */\n";
out += "describe('parsearClavePorVoz - autogenerado nombres × números 0-100 (activadora requerida)', () => {\n";

nombres.forEach((nombre) => {
  for (let n = 0; n <= 100; n++) {
    const vars = variantesNumero(n);
    const esperado = nombre + String(n);
    // Para cada variante generamos un test con la activadora al inicio
    vars.forEach((v) => {
      // evitar tests con la misma entrada si variante es idéntica a "nombre"
      const frase = `${activadora}${nombre} ${v}`;
      const testName = `${frase} → ${nombre}${String(n)}`;
      out += `  test(${JSON.stringify(testName)}, () => {\n`;
      // comparacion estricta con expected: normalizamos el esperado como nombre + número sin ceros a la izquierda
      const expectedNormalized = nombre + String(n);
      out += `    expect(parsearClavePorVoz(${JSON.stringify(frase)})).toBe(${JSON.stringify(expectedNormalized)});\n`;
      out += "  });\n";
    });

    // También generamos variante con número antes del nombre (orden inverso) y activadora
    const ordenInv = `${activadora}${vars[0]} ${nombre}`;
    out += `  test(${JSON.stringify(ordenInv + ' → orden inverso')}, () => {\n`;
    out += `    expect(parsearClavePorVoz(${JSON.stringify(ordenInv)})).toBe(${JSON.stringify(nombre + String(n))});\n`;
    out += "  });\n";
  }
});

out += "});\n";

// Guardar
fs.mkdirSync(path.dirname(OUT), { recursive: true });
fs.writeFileSync(OUT, out, 'utf8');
console.log('Generado:', OUT);
console.log('Tests generados:', nombres.length * 101 *  (/*approx variantes*/ 4) );
