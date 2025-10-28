/* eslint-env jest */
const { TextEncoder, TextDecoder } = require('util');
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;
/**
 * @jest-environment jsdom
 *
 * Tests para confirmarSerieModal:
 * - muestra texto correcto
 * - al click en Aceptar llama a registrarSerie y cierra modal
 * - al click en Cancelar muestra mensaje y cierra modal
 * - reconoce y configura webkitSpeechRecognition correctamente
 * - con comando de voz "aceptar" llama a registrarSerie
 * - con comando de voz "cancelar" muestra mensaje
 *
 * Nota: este test no depende de la implementación interna de bootstrap.Modal,
 * se provee un mock mínimo que dispara los eventos shown.bs.modal / hidden.bs.modal.
 */

const fs = require('fs');
const path = require('path');

beforeAll(() => {
  // Cargar HTML de modal mínimo en el DOM para los tests
  document.body.innerHTML = `
    <div id="modalConfirmarSerie" class="modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div id="modalConfirmarSerieBody"></div>
          <div class="modal-footer">
            <button id="btnCancelarSerie" class="btn">Cancelar</button>
            <button id="btnAceptarSerie" class="btn">Aceptar</button>
          </div>
        </div>
      </div>
    </div>
  `;

  // Mock mínimo de bootstrap.Modal que expone show() y hide() y dispara eventos shown/hidden
  global.bootstrap = {
    Modal: class {
      constructor(el) { this.el = el; }
      show() {
        // añadir clase show y disparar evento shown.bs.modal
        this.el.classList.add('show');
        const ev = new Event('shown.bs.modal', { bubbles: true });
        this.el.dispatchEvent(ev);
      }
      hide() {
        // quitar clase show y disparar evento hidden.bs.modal
        this.el.classList.remove('show');
        const ev = new Event('hidden.bs.modal', { bubbles: true });
        this.el.dispatchEvent(ev);
      }
      static getInstance(el) { return new bootstrap.Modal(el); }
    }
  };

  // Mock global mostrarMensajeKiosco y registrarSerie (serán espiados en tests)
  window.mostrarMensajeKiosco = jest.fn();
  window.registrarSerie = jest.fn();

  // Mock webkitSpeechRecognition constructor
  // Permitimos inspeccionar la última instancia creada mediante window.__lastRecogInstance
  class MockRecog {
    constructor() {
      this.lang = undefined;
      this.continuous = undefined;
      this.interimResults = undefined;
      this.onresult = null;
      this.onerror = null;
      this._started = false;
      window.__lastRecogInstance = this;
      MockRecog._instances.push(this);
    }
    start() { this._started = true; MockRecog._startSpy && MockRecog._startSpy(); }
    stop() { this._started = false; }
    abort() { this._started = false; MockRecog._abortSpy && MockRecog._abortSpy(); }
    static _reset() { MockRecog._instances = []; MockRecog._startSpy = null; MockRecog._abortSpy = null; window.__lastRecogInstance = null; }
    // helper to emulate onresult call
    static emitResult(instance, transcript) {
      const fakeEvent = {
        results: [
          [{ transcript }]
        ]
      };
      instance.onresult && instance.onresult(fakeEvent);
    }
    // helper to emulate onerror call
    static emitError(instance, error) {
      const ev = { error };
      instance.onerror && instance.onerror(ev);
    }
  }
  MockRecog._reset();
  global.webkitSpeechRecognition = MockRecog;
});

afterAll(() => {
  // limpiar mocks globales
  delete global.bootstrap;
  delete global.webkitSpeechRecognition;
  delete window.__lastRecogInstance;
});

beforeEach(() => {
  // reset spies and instances
  jest.clearAllMocks();
  if (global.webkitSpeechRecognition && global.webkitSpeechRecognition._reset) {
    global.webkitSpeechRecognition._reset();
  }
});

//
// Cargar el archivo que contiene confirmarSerieModal, registrarSerie y mostrarMensajeKiosco
// Ajustá la ruta si tu terminal.js está en otro lugar relativo al test.
// En tu repo era public/js/terminal.js en tests anteriores.
const terminalPath = path.resolve(__dirname, '../../public/js/terminal.js');
if (fs.existsSync(terminalPath)) {
  // require una vez para exponer funciones en window (el archivo debe asignarlas a window)
  require(terminalPath);
} else {
  // Si la ruta no existe, fallamos rápido para que sepas ajustar la ruta
  throw new Error(`No se encontró terminal.js en ${terminalPath}. Ajustá la ruta del test.`);
}

test('muestra el modal con texto correcto', () => {
  // abrir modal con texto
  window.confirmarSerieModal(99, 'Serie 99', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const body = document.getElementById('modalConfirmarSerieBody');
  expect(body.textContent).toContain('Serie 99');

  const modal = document.getElementById('modalConfirmarSerie');
  expect(modal.classList.contains('show')).toBe(true);
});

test('al hacer clic en aceptar llama a registrarSerie', () => {
  window.confirmarSerieModal(99, 'Serie 99', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const modal = document.getElementById('modalConfirmarSerie');
  const aceptar = document.getElementById('btnAceptarSerie');

  // simular click
  aceptar.click();

  expect(window.registrarSerie).toHaveBeenCalledWith(99);
  // modal debe haberse ocultado
  expect(modal.classList.contains('show')).toBe(false);
});

test('al hacer clic en cancelar muestra mensaje de cancelación', () => {
  window.confirmarSerieModal(101, 'Serie 101', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const modal = document.getElementById('modalConfirmarSerie');
  const cancelar = document.getElementById('btnCancelarSerie');

  cancelar.click();

  expect(window.mostrarMensajeKiosco).toHaveBeenCalledWith('Solicitud cancelada.', 'info');
  expect(modal.classList.contains('show')).toBe(false);
});

test('reconocimiento de voz se inicia y configura correctamente', () => {
  // espiar start en constructor mock
  const startSpy = jest.fn();
  global.webkitSpeechRecognition._startSpy = startSpy;

  window.confirmarSerieModal(200, 'Serie 200', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const recog = window.__lastRecogInstance;
  expect(recog).toBeDefined();
  expect(recog.lang).toBe('es-ES');
  expect(recog.continuous).toBe(true);
  expect(recog.interimResults).toBe(false);
  expect(startSpy).toHaveBeenCalled();
});

test('voz: al decir "aceptar" se cierra el modal y se registra el recurso', () => {
  window.confirmarSerieModal(502, 'Serie 502', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const modal = document.getElementById('modalConfirmarSerie');
  const aceptarSpy = jest.spyOn(document.getElementById('btnAceptarSerie'), 'click');

  const recog = window.__lastRecogInstance;
  expect(recog).toBeDefined();

  // emitir resultado de voz "aceptar"
  global.webkitSpeechRecognition.emitResult(recog, 'aceptar');

  expect(aceptarSpy).toHaveBeenCalled();
  expect(window.registrarSerie).toHaveBeenCalledWith(502);
  expect(modal.classList.contains('show')).toBe(false);

  aceptarSpy.mockRestore();
});

test('voz: al decir "cancelar" se cierra el modal y se muestra mensaje', () => {
  window.confirmarSerieModal(503, 'Serie 503', { registrarSerie: window.registrarSerie, mostrarMensajeKiosco: window.mostrarMensajeKiosco });

  const modal = document.getElementById('modalConfirmarSerie');
  const cancelarSpy = jest.spyOn(document.getElementById('btnCancelarSerie'), 'click');

  const recog = window.__lastRecogInstance;
  expect(recog).toBeDefined();

  global.webkitSpeechRecognition.emitResult(recog, 'cancelar');

  expect(cancelarSpy).toHaveBeenCalled();
  expect(window.mostrarMensajeKiosco).toHaveBeenCalledWith('Solicitud cancelada.', 'info');
  expect(modal.classList.contains('show')).toBe(false);

  cancelarSpy.mockRestore();
});
