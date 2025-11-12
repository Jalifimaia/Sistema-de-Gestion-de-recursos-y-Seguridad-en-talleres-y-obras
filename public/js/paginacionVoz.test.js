const { TextEncoder, TextDecoder } = require('util');
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;
/**
 * paginacionVoz.test.js
 *
 * Pruebas de integración para paginación por voz y comando "volver"
 * - Ajusta `modulePath` si tu terminal.js está en otra ruta.
 *
 * Ejecutar: npx jest paginacionVoz.test.js
 */

const fs = require('fs');
const path = require('path');

describe('Integración: paginación por voz y comando volver', () => {
  let terminal;
  const modulePath = path.resolve(__dirname, './terminal.js'); // AJUSTAR si es necesario

  // Helpers para montar DOM mínimo
  function crearStep(id) {
    const el = document.createElement('div');
    el.id = id;
    el.className = 'step';
    return el;
  }

  function activarStep(id) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
  }

  beforeAll(() => {
    if (!fs.existsSync(modulePath)) {
      throw new Error(`No se encontró terminal.js en ${modulePath}. Ajustá modulePath en el test.`);
    }
  });

  beforeEach(() => {
    // limpiar DOM
    document.body.innerHTML = '';

    // pasos
    document.body.appendChild(crearStep('step1'));
    document.body.appendChild(crearStep('step2'));
    document.body.appendChild(crearStep('step3'));
    document.body.appendChild(crearStep('step5'));
    document.body.appendChild(crearStep('step6'));
    document.body.appendChild(crearStep('step7'));
    document.body.appendChild(crearStep('step8'));

    // contenedores paginación/buttons
    const subCont = document.createElement('div'); subCont.id = 'subcategoria-buttons'; document.body.appendChild(subCont);
    const subPag = document.createElement('div'); subPag.id = 'paginadorSubcategorias'; document.body.appendChild(subPag);

    const recCont = document.createElement('div'); recCont.id = 'recurso-buttons'; document.body.appendChild(recCont);
    const recPag = document.createElement('div'); recPag.id = 'paginadorRecursos'; document.body.appendChild(recPag);

    const serCont = document.createElement('div'); serCont.id = 'serie-buttons'; document.body.appendChild(serCont);
    const serPag = document.createElement('div'); serPag.id = 'paginadorSeries'; document.body.appendChild(serPag);

    // modal resources (simple)
    const modal = document.createElement('div'); modal.id = 'modalRecursos'; modal.className = 'modal fade'; document.body.appendChild(modal);
    const tabEpp = document.createElement('button'); tabEpp.id = 'tab-epp'; tabEpp.setAttribute('aria-selected', 'true'); document.body.appendChild(tabEpp);
    const tabHerr = document.createElement('button'); tabHerr.id = 'tab-herramientas'; tabHerr.setAttribute('aria-selected', 'false'); document.body.appendChild(tabHerr);
    const tablaEPP = document.createElement('tbody'); tablaEPP.id = 'tablaEPP'; document.body.appendChild(tablaEPP);
    const pagEPP = document.createElement('div'); pagEPP.id = 'paginadorEPP'; document.body.appendChild(pagEPP);
    const tablaHerr = document.createElement('tbody'); tablaHerr.id = 'tablaHerramientas'; document.body.appendChild(tablaHerr);
    const pagHerr = document.createElement('div'); pagHerr.id = 'paginadorHerramientas'; document.body.appendChild(pagHerr);

    // ------- definir mocks/spies en window ANTES de cargar el módulo -------
    window.renderSubcategoriasPaginadas = jest.fn((list, page) => {
      const cont = document.getElementById('subcategoria-buttons');
      cont.innerHTML = '';
      const start = (page - 1) * 5;
      const visibles = (list || []).slice(start, start + 5);
      visibles.forEach((s, idx) => {
        const b = document.createElement('button');
        b.dataset.subcategoriaId = s.id;
        b.textContent = `Opción ${idx + 1}\n${s.nombre}`;
        cont.appendChild(b);
      });
      document.getElementById('paginadorSubcategorias').textContent = `page:${page}`;
    });

    window.renderRecursosPaginados = jest.fn((list, page) => {
      const cont = document.getElementById('recurso-buttons');
      cont.innerHTML = '';
      const start = (page - 1) * 5;
      const visibles = (list || []).slice(start, start + 5);
      visibles.forEach((r, idx) => {
        const b = document.createElement('button');
        b.dataset.recursoId = r.id;
        b.textContent = `Opción ${idx + 1}\n${r.nombre}`;
        cont.appendChild(b);
      });
      document.getElementById('paginadorRecursos').textContent = `page:${page}`;
    });

    window.renderSeriesPaginadas = jest.fn((list, page) => {
      const cont = document.getElementById('serie-buttons');
      cont.innerHTML = '';
      const start = (page - 1) * 5;
      const visibles = (list || []).slice(start, start + 5);
      visibles.forEach((s, idx) => {
        const b = document.createElement('button');
        b.dataset.serieId = s.id;
        b.textContent = `Opción ${idx + 1}\n${s.nro_serie || s.id}`;
        cont.appendChild(b);
      });
      document.getElementById('paginadorSeries').textContent = `page:${page}`;
    });

    window.renderTablaRecursos = jest.fn((tablaId, recursos, pagina) => {
      const pagId = tablaId === 'tablaEPP' ? 'paginadorEPP' : 'paginadorHerramientas';
      document.getElementById(pagId).textContent = `page:${pagina}`;
    });

    window.mostrarMensajeKiosco = jest.fn();

    // normalizarTexto fallback si no existe
    if (typeof window.normalizarTexto !== 'function') {
      window.normalizarTexto = (s='') => (''+s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    // Si ya existía terminal en cache, limpiamos módulos para forzar nuevo require
    jest.resetModules();
    terminal = require(modulePath);

    // --- Inyectar mocks en el módulo cargado para que use las funciones jest.fn() ---
    if (terminal) {
      terminal.renderSubcategoriasPaginadas = window.renderSubcategoriasPaginadas;
      terminal.renderRecursosPaginados     = window.renderRecursosPaginados;
      terminal.renderSeriesPaginadas      = window.renderSeriesPaginadas;
      terminal.renderTablaRecursos        = window.renderTablaRecursos;
      terminal.mostrarMensajeKiosco       = window.mostrarMensajeKiosco;

      if (terminal.normalizarTexto) window.normalizarTexto = terminal.normalizarTexto;
      if (terminal.matchTextoBoton)  window.matchTextoBoton  = terminal.matchTextoBoton;
      if (terminal.matchOpcion)      window.matchOpcion      = terminal.matchOpcion;

      if (terminal.procesarComandoVoz) window.procesarComandoVoz = terminal.procesarComandoVoz;
    }

    // reset spies calls (por si el módulo hizo renders al cargarse)
    jest.clearAllMocks();

    // reset step activo a step6 por defecto
    activarStep('step6');
  });

  afterEach(() => {
    jest.clearAllMocks();
    jest.resetModules();
  });

  test('numeroDesdeToken convierte palabras y dígitos', () => {
    const n = terminal.numeroDesdeToken || window.numeroDesdeToken;
    expect(typeof n).toBe('function');
    expect(n('3')).toBe(3);
    expect(n('tres')).toBe(3);
    expect(n('diez')).toBe(10);
    expect(Number.isNaN(n('ciento'))).toBe(true);
  });

  describe('Paginación por voz en subcategorías (step6)', () => {
    beforeEach(() => {
      activarStep('step6');
      window.subcategoriasActuales = Array.from({ length: 12 }, (_, i) => ({ id: i + 1, nombre: `S${i + 1}` }));
      // render inicial en página 1 (simulado) si tu módulo lo requiere
      window.renderSubcategoriasPaginadas(window.subcategoriasActuales, 1);
      jest.clearAllMocks();
    });

    test('decir "pagina 2" llama renderSubcategoriasPaginadas con página 2', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina 2'));
      expect(window.renderSubcategoriasPaginadas).toHaveBeenCalled();
      const last = window.renderSubcategoriasPaginadas.mock.calls[window.renderSubcategoriasPaginadas.mock.calls.length - 1];
      expect(last[1]).toBe(2);
      expect(document.getElementById('paginadorSubcategorias').textContent).toBe('page:2');
    });

    test('decir "pagina dos" (palabra) llama renderSubcategoriasPaginadas con página 2', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina dos'));
      expect(window.renderSubcategoriasPaginadas).toHaveBeenCalled();
      const last = window.renderSubcategoriasPaginadas.mock.calls[window.renderSubcategoriasPaginadas.mock.calls.length - 1];
      expect(last[1]).toBe(2);
    });

    test('decir "volver" en step6 ejecuta nextStep(5)', () => {
      window.nextStep = jest.fn();
      window.procesarComandoVoz(window.normalizarTexto('volver'));
      expect(window.nextStep).toHaveBeenCalledWith(5);
    });
  });

  describe('Paginación por voz en recursos (step7)', () => {
    beforeEach(() => {
      activarStep('step7');
      window.recursosActuales = Array.from({ length: 11 }, (_, i) => ({ id: i + 1, nombre: `R${i + 1}` }));
      window.renderRecursosPaginados(window.recursosActuales, 1);
      jest.clearAllMocks();
    });

    test('decir "pagina 3" llama renderRecursosPaginados con página 3', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina 3'));
      expect(window.renderRecursosPaginados).toHaveBeenCalled();
      const last = window.renderRecursosPaginados.mock.calls[window.renderRecursosPaginados.mock.calls.length - 1];
      expect(last[1]).toBe(3);
    });

    test('decir "volver" en step7 ejecuta nextStep(6)', () => {
      window.nextStep = jest.fn();
      window.procesarComandoVoz(window.normalizarTexto('volver'));
      expect(window.nextStep).toHaveBeenCalledWith(6);
    });
  });

  describe('Paginación por voz en series (step8)', () => {
    beforeEach(() => {
      activarStep('step8');
      window.seriesActuales = Array.from({ length: 18 }, (_, i) => ({ id: i + 1, nro_serie: `SER${i + 1}` }));
      window.renderSeriesPaginadas(window.seriesActuales, 1);
      jest.clearAllMocks();
    });

    test('decir "pagina 4" dentro del rango llama renderSeriesPaginadas con 4', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina 4'));
      expect(window.renderSeriesPaginadas).toHaveBeenCalled();
      const last = window.renderSeriesPaginadas.mock.calls[window.renderSeriesPaginadas.mock.calls.length - 1];
      expect(last[1]).toBe(4);
    });

    test('decir "pagina 100" fuera de rango muestra mensaje de advertencia', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina 100'));
      expect(window.mostrarMensajeKiosco).toHaveBeenCalled();
    });

    test('decir "volver" en step8 ejecuta nextStep(7)', () => {
      window.nextStep = jest.fn();
      window.procesarComandoVoz(window.normalizarTexto('volver'));
      expect(window.nextStep).toHaveBeenCalledWith(7);
    });
  });

  describe('Paginación por voz en modal "ver recursos asignados"', () => {
    beforeEach(() => {
      activarStep('step2');
      document.getElementById('tab-epp').setAttribute('aria-selected', 'true');
      document.getElementById('tab-herramientas').setAttribute('aria-selected', 'false');

      window.recursosEPP = Array.from({ length: 13 }, (_, i) => ({ id: i + 1, recurso: `EPP${i + 1}` }));
      window.recursosHerramientas = Array.from({ length: 9 }, (_, i) => ({ id: i + 1, recurso: `H${i + 1}` }));

      window.renderTablaRecursos('tablaEPP', window.recursosEPP, 1);
      window.renderTablaRecursos('tablaHerramientas', window.recursosHerramientas, 1);
      jest.clearAllMocks();
    });

    test('decir "pagina 3" con tab EPP activo llama renderTablaRecursos tablaEPP,3', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina 3'));
      expect(window.renderTablaRecursos).toHaveBeenCalled();
      const last = window.renderTablaRecursos.mock.calls[window.renderTablaRecursos.mock.calls.length - 1];
      expect(last[0]).toBe('tablaEPP');
      expect(last[1]).toBe(window.recursosEPP);
      expect(last[2]).toBe(3);
    });

    test('decir "pagina herramientas 2" llama renderTablaRecursos tablaHerramientas,2', () => {
      window.procesarComandoVoz(window.normalizarTexto('pagina herramientas 2'));
      expect(window.renderTablaRecursos).toHaveBeenCalled();
      const last = window.renderTablaRecursos.mock.calls[window.renderTablaRecursos.mock.calls.length - 1];
      expect(last[0]).toBe('tablaHerramientas');
      expect(last[1]).toBe(window.recursosHerramientas);
      expect(last[2]).toBe(2);
    });
  });

  describe('Matcher de texto contra botón (matchTextoBoton) y selección por "opción N"', () => {
    beforeEach(() => {
      activarStep('step6');
      const cont = document.getElementById('subcategoria-buttons');
      cont.innerHTML = '';
      const b1 = document.createElement('button'); b1.textContent = 'Opción 1\nChaleco'; cont.appendChild(b1);
      const b2 = document.createElement('button'); b2.textContent = 'Opción 2\nCasco Azul'; cont.appendChild(b2);
    });

    test('matchTextoBoton debe encontrar botón por texto', () => {
      const mt = terminal.matchTextoBoton || window.matchTextoBoton;
      const cont = document.getElementById('subcategoria-buttons');
      const b2 = cont.querySelectorAll('button')[1];
      expect(mt('casco azul', b2)).toBeTruthy();
      expect(mt('chaleco', cont.querySelectorAll('button')[0])).toBeTruthy();
    });

    test('decir "opción 2" hace click en el segundo botón (simulado)', () => {
      const cont = document.getElementById('subcategoria-buttons');
      const btn2 = cont.querySelectorAll('button')[1];
      const spy = jest.fn();
      btn2.addEventListener('click', spy);
      window.procesarComandoVoz(window.normalizarTexto('opcion 2'));
      expect(spy).toHaveBeenCalled();
    });
  });
});
