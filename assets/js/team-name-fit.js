/* Resize Team member names only when needed, never truncate authored text. */
(() => {
  const selectors = [
    '.aznet-theme-team-directory__grid .aznet-theme-team-card__name',
    '.aznet-theme-law01-profile__members .aznet-theme-team-card__name',
    '.aznet-theme-law01-grid--team .aznet-theme-law01-team-card__body h3'
  ].join(',');
  function fit(el) {
    el.style.fontSize = '';
    const available = el.clientWidth;
    if (!available) return;
    const original = parseFloat(getComputedStyle(el).fontSize);
    if (!Number.isFinite(original)) return;
    let size = original;
    while (el.scrollWidth > available + 1 && size > 6) {
      size = Math.max(6, size - .5);
      el.style.fontSize = size + 'px';
    }
  }
  const all = () => document.querySelectorAll(selectors).forEach(fit);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', all, { once: true });
  else all();
  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(() => all());
    const attach = () => document.querySelectorAll(selectors).forEach(el => observer.observe(el.parentElement || el));
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach, { once: true });
    else attach();
  } else window.addEventListener('resize', all);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(all);
})();
