import assert from 'node:assert/strict';
import test from 'node:test';
import vm from 'node:vm';
import { installR6VitalsObserver, normalizeR6Resource, publicR6ResourceTiming } from './r6-vitals-diagnostics.mjs';

function observedPage({ supported = true } = {}) {
  const callbacks = new Map();
  const observeOptions = [];
  class Observer {
    constructor(callback) {
      this.callback = callback;
    }
    observe(options) {
      if (!supported) throw new Error('Unsupported entry type');
      callbacks.set(options.type, this.callback);
      observeOptions.push(options);
    }
  }
  const sandbox = { PerformanceObserver: Observer, URL };
  vm.runInNewContext('(' + installR6VitalsObserver.toString() + ')()', sandbox);
  return {
    state: sandbox.__aznetR6Vitals,
    observeOptions,
    emit(type, entries) {
      const callback = callbacks.get(type);
      if (!callback) throw new Error('Missing observer for ' + type);
      callback({ getEntries: () => entries });
    },
  };
}

function shiftingNode() {
  return { tagName: 'MAIN', className: 'single-product product-grid private:metadata', id: 'ignored-identifier' };
}

test('captures DOM shift sources and layout rectangles without DOM text or identifying ids', () => {
  const page = observedPage();
  assert.deepEqual(Array.from(page.observeOptions, ({ type, buffered }) => [type, buffered]), [
    ['layout-shift', true],
    ['largest-contentful-paint', true],
  ]);
  page.emit('layout-shift', [{
    value: 0.125, startTime: 850, hadRecentInput: false,
    sources: [{
      node: shiftingNode(),
      previousRect: { x: 0, y: 90, width: 390, height: 340 },
      currentRect: { x: 0, y: 210, width: 390, height: 340 },
    }],
  }]);
  const state = JSON.parse(JSON.stringify(page.state));
  assert.equal(state.availability.layoutShift, true);
  assert.equal(state.layoutShifts.length, 1);
  assert.equal(state.layoutShifts[0].value, 0.125);
  assert.equal(state.layoutShifts[0].hadRecentInput, false);
  assert.deepEqual(state.layoutShifts[0].sources[0].node, { tag: 'main', classes: ['single-product', 'product-grid'] });
  assert.deepEqual(state.layoutShifts[0].sources[0].previousRect, { x: 0, y: 90, width: 390, height: 340 });
  assert.deepEqual(state.layoutShifts[0].sources[0].currentRect, { x: 0, y: 210, width: 390, height: 340 });
  assert.equal(JSON.stringify(state).includes('ignored-identifier'), false);
  assert.equal(JSON.stringify(state).includes('private:metadata'), false);
});

test('captures latest LCP element/time and strips URL queries and fragments', () => {
  const page = observedPage();
  page.emit('largest-contentful-paint', [{
    startTime: 2350.127, renderTime: 2400.9, loadTime: 2240,
    size: 23000, url: 'https://example.test/photo.png?private_token=secret#anchor',
    element: { tagName: 'IMG', className: 'wp-post-image' },
  }]);
  page.emit('largest-contentful-paint', [{
    startTime: 2800.228, renderTime: 2801, loadTime: 2650, size: 24000,
    url: 'https://user:password@example.test/new.jpg?utm=abc',
    element: { tagName: 'IMG', className: 'product-image' },
  }]);
  assert.equal(page.state.lcp.startTimeMs, 2800.23);
  assert.equal(page.state.lcp.resourceUrl, 'https://example.test/new.jpg');
  assert.equal(page.state.lcp.element.tag, 'img');
  assert.equal(JSON.stringify(page.state).includes('private_token'), false);
  assert.equal(JSON.stringify(page.state).includes('password'), false);
});

test('gracefully reports unavailable PerformanceObserver instead of failing QA', () => {
  const page = observedPage({ supported: false });
  assert.equal(page.state.availability.layoutShift, false);
  assert.equal(page.state.availability.lcp, false);
  assert.equal(page.state.layoutShifts.length, 0);
  assert.equal(page.state.lcp, null);
});

test('caps observations and sources to bound QA artifact size', () => {
  const page = observedPage();
  const entry = {
    value: 0.002, startTime: 123, hadRecentInput: false,
    sources: Array.from({ length: 8 }, () => ({ node: shiftingNode() })),
  };
  page.emit('layout-shift', Array.from({ length: 60 }, () => entry));
  assert.equal(page.state.layoutShifts.length, 50);
  assert.equal(page.state.layoutShifts[0].sources.length, 5);
  assert.equal(page.state.layoutShiftsTruncated, true);
});

test('resource normalization preserves original R6 size data and adds waterfall timing', () => {
  const value = normalizeR6Resource({
    name: 'https://example.test/a.png?ver=1',
    initiatorType: 'img', transferSize: 2048, encodedBodySize: 1900,
    decodedBodySize: 3200, duration: 422.234,
    startTime: 100.125, fetchStart: 105, responseStart: 190.667, responseEnd: 522.359,
  });
  assert.deepEqual(value, {
    url: 'https://example.test/a.png?ver=1', initiatorType: 'img',
    transferSize: 2048, encodedBodySize: 1900, decodedBodySize: 3200,
    durationMs: 422.23, startTimeMs: 100.13, fetchStartMs: 105,
    responseStartMs: 190.67, responseEndMs: 522.36,
  });
});

test('recorded waterfall URLs omit query, fragment, and credentials but retain timing', () => {
  const output = publicR6ResourceTiming({
    url: 'https://user:password@example.test/images/hero.webp?nonce=secret#preview',
    startTimeMs: 201, responseEndMs: 500,
    transferSize: 2024,
  });
  assert.deepEqual(output, {
    url: 'https://example.test/images/hero.webp',
    startTimeMs: 201, responseEndMs: 500, transferSize: 2024,
  });
});
