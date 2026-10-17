/**
 * Browser-only, observational R6 diagnostics. Does not change Theme or Woo rendering.
 * Serialize installR6VitalsObserver with .toString() for Playwright.addInitScript.
 */
export function installR6VitalsObserver() {
  const round = (value) => Number((Number(value) || 0).toFixed(2));
  const describeNode = (node) => {
    if (!node || !node.tagName) return null;
    const classes = typeof node.className === 'string' ? node.className.split(/\s+/) : [];
    return {
      tag: String(node.tagName).toLowerCase().slice(0, 30),
      classes: classes.filter((name) => /^[a-zA-Z0-9_-]{1,60}$/.test(name)).slice(0, 3),
    };
  };
  const describeRect = (rect) => rect ? {
    x: round(rect.x), y: round(rect.y),
    width: round(rect.width), height: round(rect.height),
  } : null;
  const publicResourceUrl = (raw) => {
    try {
      const parsed = new URL(String(raw));
      if (!['http:', 'https:'].includes(parsed.protocol)) return '';
      parsed.username = '';
      parsed.password = '';
      parsed.search = '';
      parsed.hash = '';
      return parsed.href.slice(0, 400);
    } catch {
      return '';
    }
  };
  const state = {
    availability: { layoutShift: false, lcp: false },
    layoutShifts: [],
    layoutShiftsTruncated: false,
    lcp: null,
  };
  globalThis.__aznetR6Vitals = state;
  if (typeof PerformanceObserver === 'undefined') return;

  try {
    new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (state.layoutShifts.length >= 50) {
          state.layoutShiftsTruncated = true;
          continue;
        }
        state.layoutShifts.push({
          startTimeMs: round(entry.startTime),
          value: Number((Number(entry.value) || 0).toFixed(5)),
          hadRecentInput: Boolean(entry.hadRecentInput),
          sources: Array.from(entry.sources || []).slice(0, 5).map((source) => ({
            node: describeNode(source.node),
            previousRect: describeRect(source.previousRect),
            currentRect: describeRect(source.currentRect),
          })),
        });
      }
    }).observe({ type: 'layout-shift', buffered: true });
    state.availability.layoutShift = true;
  } catch {
    // Some browser engines do not expose LayoutShift entries.
  }
  try {
    new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        state.lcp = {
          startTimeMs: round(entry.startTime),
          renderTimeMs: round(entry.renderTime),
          loadTimeMs: round(entry.loadTime),
          size: Number(entry.size) || 0,
          resourceUrl: publicResourceUrl(entry.url),
          element: describeNode(entry.element),
        };
      }
    }).observe({ type: 'largest-contentful-paint', buffered: true });
    state.availability.lcp = true;
  } catch {
    // LCP entry attribution is advisory, not a hard R6 pass condition.
  }
}

/** Preserve existing R6 resource data while adding timing offsets for a waterfall. */
export function normalizeR6Resource(entry) {
  const rounded = (value) => Number((Number(value) || 0).toFixed(2));
  return {
    url: entry.name,
    initiatorType: entry.initiatorType,
    transferSize: Number(entry.transferSize || 0),
    encodedBodySize: Number(entry.encodedBodySize || 0),
    decodedBodySize: Number(entry.decodedBodySize || 0),
    durationMs: rounded(entry.duration),
    startTimeMs: rounded(entry.startTime),
    fetchStartMs: rounded(entry.fetchStart),
    responseStartMs: rounded(entry.responseStart),
    responseEndMs: rounded(entry.responseEnd),
  };
}

/** Redact query/credentials from newly persisted waterfall URLs. */
export function publicR6ResourceTiming(entry) {
  let url = '';
  try {
    const parsed = new URL(String(entry.url));
    if (['http:', 'https:'].includes(parsed.protocol)) {
      parsed.username = '';
      parsed.password = '';
      parsed.search = '';
      parsed.hash = '';
      url = parsed.href.slice(0, 400);
    }
  } catch {
    // Invalid and non-network URLs are not persisted in QA artifacts.
  }
  return { ...entry, url };
}
