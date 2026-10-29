/** Read-only DOM check shared by real-browser and isolated inner-Page QA. */
export function probeWellnessInnerLayout() {
  const failures = [];
  const article = document.querySelector('main > .aznet-theme-page');
  const header = article?.querySelector('.aznet-theme-page__section-inner--header');
  const content = article?.querySelector('.aznet-theme-page__content');
  if (!article || !header || !content) return { failures: ['native Page frame missing'] };
  const rect = (e) => {
    const r = e.getBoundingClientRect();
    return { x: r.x, y: r.y + scrollY, width: r.width, height: r.height, right: r.right, bottom: r.bottom + scrollY };
  };
  const clientWidth = document.documentElement.clientWidth;
  const h = rect(header), c = rect(content);
  if (Math.abs(h.x - c.x) > 2 || Math.abs(h.width - c.width) > 2) failures.push('title/body frames do not align');
  if (clientWidth >= 1000 && c.width < Math.min(1000, clientWidth - 80)) failures.push('desktop composition remains cramped');
  const first = content.firstElementChild;
  const gap = first ? rect(first).y - h.bottom : null;
  if (gap === null || gap < 16 || gap > 48) failures.push('title/body transition gap outside 16–48px');
  const clipped = [...article.querySelectorAll('p,h1,h2,h3,figure,img,.wp-block-group,.wp-block-columns,.wp-block-column,.wp-block-button__link')]
    .filter((e) => { const s = getComputedStyle(e); return s.display !== 'none' && s.visibility !== 'hidden'; })
    .map((e) => ({ tag: e.tagName, text: (e.innerText || e.getAttribute('alt') || '').slice(0, 55), ...rect(e) }))
    .filter((r) => r.width > 0 && (r.x < -1 || r.right > clientWidth + 1));
  if (clipped.length) failures.push(`${clipped.length} elements outside viewport`);
  if (document.documentElement.scrollWidth > clientWidth + 1) failures.push('horizontal document overflow');
  const columns = [...content.querySelectorAll('.wp-block-columns')].map((e) => ({
    ...rect(e), children: [...e.children].map(rect),
  }));
  if (clientWidth >= 1200 && columns.some((e) => e.children.some((r) => r.width < (e.children.length >= 3 ? 240 : 200)))) failures.push('desktop native columns remain cramped');
  if (clientWidth < 782 && columns.some((e) => e.children.some((r) => Math.abs(r.x - e.x) > 2 || Math.abs(r.width - e.width) > 2)))
    failures.push('small-screen native columns fail to stack');
  const images = [...content.querySelectorAll('img')].map((e) => ({
    alt: e.alt, loaded: e.complete && e.naturalWidth > 0, ...rect(e), authoredRatio: e.style.aspectRatio,
  }));
  const portraitRows = [...content.querySelectorAll(':scope > .wp-block-columns')]
    .filter((e) => e.querySelector('.wp-block-column > .wp-block-image') && !e.matches('.are-vertically-aligned-top,.are-vertically-aligned-bottom'))
    .map((e) => ({ alignment: getComputedStyle(e).alignItems, columns: [...e.children].map((column) => {
      const boxes = [...column.children].map(rect).filter((r) => r.height > 0);
      const y = Math.min(...boxes.map((r) => r.y)), bottom = Math.max(...boxes.map((r) => r.bottom));
      return { y, bottom, height: bottom - y };
    }) }));
  if (clientWidth >= 782 && portraitRows.some((row) => row.columns.some((r) =>
    Math.abs(r.y + r.height / 2 - row.columns[0].y - row.columns[0].height / 2) > 2))) failures.push('portrait/text columns not vertically balanced');
  for (const im of images) {
    if (!im.loaded) failures.push(`image did not decode: ${im.alt}`);
    if (im.authoredRatio) {
      const parts = im.authoredRatio.split('/').map(Number);
      const expected = parts[0] / (parts[1] || 1);
      if (Math.abs(im.width / im.height - expected) > 0.015) failures.push(`authored image ratio changed: ${im.alt}`);
    }
  }
  const rgb = (s) => (s.match(/[\d.]+/g) || []).slice(0, 3).map(Number);
  const luminance = (s) => rgb(s).map((x) => { x /= 255; return x <= .04045 ? x / 12.92 : ((x + .055) / 1.055) ** 2.4; })
    .reduce((sum, x, i) => sum + x * [.2126, .7152, .0722][i], 0);
  const buttons = [...content.querySelectorAll('.wp-block-button__link')].map((e) => {
    const s = getComputedStyle(e);
    let parent = e, background = s.backgroundColor;
    while ((background === 'rgba(0, 0, 0, 0)' || background === 'transparent') && parent.parentElement) {
      parent = parent.parentElement; background = getComputedStyle(parent).backgroundColor;
    }
    const a = luminance(s.color), b = luminance(background);
    const contrast = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
    if (!Number.isFinite(contrast) || contrast < 4.5) failures.push(`button contrast below 4.5: ${e.innerText}`);
    return { text: e.innerText, foreground: s.color, background, contrast, ...rect(e) };
  });
  const children = [...content.children];
  const pairable = '.wp-block-group.has-background:not(.alignfull)';
  const adjacentCards = children.filter((e, i) => e.matches(pairable) &&
    (children[i - 1]?.matches(pairable) || children[i + 1]?.matches(pairable))).map(rect);
  if (clientWidth >= 1024 && adjacentCards.length >= 2 && Math.abs(adjacentCards[0].y - adjacentCards[1].y) > 2)
    failures.push('consecutive desktop cards remain a narrow vertical list');
  if (clientWidth < 1024 && adjacentCards.some((r, i) => Math.abs(r.width - c.width) > 2 || (i > 0 && r.y < adjacentCards[i - 1].bottom - 2)))
    failures.push('small-screen cards fail to stack');
  return { url: location.pathname, viewport: { width: innerWidth, clientWidth }, header: h, content: c, transitionGap: gap,
    css: document.querySelector('#aznet-theme-page-wellness-01-css')?.getAttribute('href') || null,
    columns, images, portraitRows, buttons, adjacentCards, clipped, failures };
}
