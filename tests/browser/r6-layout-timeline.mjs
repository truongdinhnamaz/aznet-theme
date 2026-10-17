/**
 * Bounded, read-only DOM geometry probe for R6 QA. This is not Theme runtime code.
 * It captures public presentation state, never DOM text, element IDs, attribute values,
 * storage, input values or query strings. Safe to serialize into addInitScript.
 */
export function installR6LayoutTimeline() {
  const selectors = {
    product: '.single-product #main .product',
    gallery: '.single-product #main .woocommerce-product-gallery',
    wrapper: '.single-product #main .woocommerce-product-gallery__wrapper',
    viewport: '.single-product #main .flex-viewport',
    summary: '.single-product #main .summary.entry-summary',
    journey: '.choiceguide-product-journey',
    mainImage: '.single-product #main .woocommerce-product-gallery__image img',
  };
  const state = {
    availability: { mutations: false, layoutShift: false },
    samples: [],
    shifts: [],
    mutations: [],
    samplesTruncated: false,
    shiftsTruncated: false,
    mutationsTruncated: false,
    stopped: false,
  };
  globalThis.__aznetR6LayoutTimeline = state;
  const round = (n) => Number((Number(n) || 0).toFixed(2));
  const maxDurationMs = 10000;
  const minSampleGapMs = 80;
  let previousSampleAt = -Infinity;
  let frameId = null;
  let attached = false;
  const rectInfo = (rect) => rect ? {
    x: round(rect.x), y: round(rect.y),
    width: round(rect.width), height: round(rect.height),
    bottom: round(rect.bottom ?? (rect.y + rect.height)),
    right: round(rect.right ?? (rect.x + rect.width)),
  } : null;
  const safeName = (node) => {
    if (!node || !node.tagName) return null;
    const names = typeof node.className === 'string' ? node.className.split(/\s+/) : [];
    return {
      tag: String(node.tagName).toLowerCase().slice(0, 25),
      classes: names.filter(name => /^[a-zA-Z][a-zA-Z0-9_-]{0,49}$/.test(name)).slice(0, 3),
    };
  };
  const geometry = (selector) => {
    const node = document.querySelector(selector);
    if (!node || !node.isConnected) return null;
    try {
      const rect = rectInfo(node.getBoundingClientRect());
      const css = getComputedStyle(node);
      return {
        node: safeName(node),
        rect,
        layoutWidth: Number(node.offsetWidth || 0),
        layoutHeight: Number(node.offsetHeight || 0),
        image: selector === selectors.mainImage ? { complete: Boolean(node.complete), naturalWidth: Number(node.naturalWidth || 0), naturalHeight: Number(node.naturalHeight || 0) } : undefined,
        boxCount: typeof node.getClientRects === 'function' ? node.getClientRects().length : null,
        offscreen: Boolean(rect && (
          rect.bottom <= 0 || rect.y >= innerHeight ||
          rect.right <= 0 || rect.x >= innerWidth
        )),
        style: {
          display: String(css.display || ''),
          visibility: String(css.visibility || ''),
          opacity: String(css.opacity || ''),
          position: String(css.position || ''),
          overflow: String(css.overflow || ''),
          transform: css.transform && css.transform !== 'none' ? 'applied' : 'none',
          gridTemplateColumns: css.gridTemplateColumns && css.gridTemplateColumns !== 'none' ? 'defined' : 'none',
        },
      };
    } catch {
      return null;
    }
  };
  const snapshot = () => Object.fromEntries(Object.entries(selectors).map(([name, selector]) => [name, geometry(selector)]));
  const addSample = (source) => {
    if (state.samples.length >= 200) { state.samplesTruncated = true; return; }
    state.samples.push({ tMs: round(performance.now()), source, scrollY: round(globalThis.scrollY), nodes: snapshot() });
  };
  const onFrame = () => {
    const now = performance.now();
    if (now >= maxDurationMs || state.samples.length >= 200) {
      state.stopped = true;
      if (state.samples.length >= 200) state.samplesTruncated = true;
      return;
    }
    if (now - previousSampleAt >= minSampleGapMs) {
      addSample('raf');
      previousSampleAt = now;
    }
    frameId = requestAnimationFrame(onFrame);
  };
  const begin = () => {
    if (attached) return;
    attached = true;
    try {
      const product = document.querySelector(selectors.product);
      if (product && typeof MutationObserver === 'function') {
        const observer = new MutationObserver((records) => {
          for (const record of records) {
            if (state.mutations.length >= 120) { state.mutationsTruncated = true; break; }
            if (record.type !== 'attributes' && record.type !== 'childList') continue;
            state.mutations.push({
              tMs: round(performance.now()),
              type: record.type,
              attribute: record.type === 'attributes' && ['class','style'].includes(record.attributeName) ? record.attributeName : null,
              node: safeName(record.target),
              addedCount: record.type === 'childList' ? Math.min(20, record.addedNodes?.length || 0) : 0,
              removedCount: record.type === 'childList' ? Math.min(20, record.removedNodes?.length || 0) : 0,
            });
          }
        });
        observer.observe(product, { attributes: true, attributeFilter: ['style','class'], childList: true, subtree: true });
        state.availability.mutations = true;
      }
    } catch { /* instrumentation must not change public rendering */ }
    try {
      if (typeof PerformanceObserver === 'function') {
        new PerformanceObserver((list) => {
          for (const entry of list.getEntries()) {
            if (state.shifts.length >= 60) { state.shiftsTruncated = true; continue; }
            state.shifts.push({
              tMs: round(entry.startTime),
              value: Number((Number(entry.value) || 0).toFixed(5)),
              hadRecentInput: Boolean(entry.hadRecentInput),
              scrollY: round(globalThis.scrollY),
              snapshot: snapshot(),
            });
          }
        }).observe({ type: 'layout-shift', buffered: true });
        state.availability.layoutShift = true;
      }
    } catch { /* best effort */ }
    frameId = requestAnimationFrame(onFrame);
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', begin, { once: true });
  else begin();
}
