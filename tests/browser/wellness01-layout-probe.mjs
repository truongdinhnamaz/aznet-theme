/** Pure read-only DOM probe shared by connected-browser QA and isolated WordPress CI. */
export function inspectWellnessLayout(rootSelector = '.aznet-theme-wellness01') {
  const root = document.querySelector(rootSelector);
  const box = el => {
    const r = el.getBoundingClientRect();
    return { x: r.x, right: r.right, width: r.width, height: r.height };
  };
  const rgb = value => (value.match(/[\d.]+/g) || []).slice(0, 3).map(Number);
  const luminance = color => {
    const v = rgb(color).map(x => x / 255).map(x => x <= .04045 ? x / 12.92 : ((x + .055) / 1.055) ** 2.4);
    return .2126 * v[0] + .7152 * v[1] + .0722 * v[2];
  };
  const contrast = (fg, bg) => {
    const a = luminance(fg), b = luminance(bg);
    return (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
  };
  const background = el => {
    for (let node = el; node; node = node.parentElement) {
      const value = getComputedStyle(node).backgroundColor;
      if (value !== 'transparent' && value !== 'rgba(0, 0, 0, 0)') return value;
    }
    return 'rgb(255, 255, 255)';
  };
  const clientWidth = document.documentElement.clientWidth;
  const failures = [];
  const inBounds = (label, bounds) => {
    if (bounds.x < -.75 || bounds.right > clientWidth + .75) failures.push(`${label} clipped: ${bounds.x.toFixed(2)}..${bounds.right.toFixed(2)} outside 0..${clientWidth}`);
  };
  if (!root) return { failures: ['missing Wellness 01 root'] };
  const rootBox = box(root);
  inBounds('root', rootBox);
  const sections = [...root.querySelectorAll(':scope > .wp-block-group.alignwide')].map(el => {
    const bounds = box(el), name = el.className.replace(/.*(aznet-theme-wellness01__\S+).*/, '$1');
    inBounds(name, bounds);
    return { name, ...bounds, transform: getComputedStyle(el).transform };
  });
  const buttons = [...root.querySelectorAll('.wp-block-button__link')].map(el => {
    const style = getComputedStyle(el), fg = style.color, bg = background(el), ratio = contrast(fg, bg), bounds = box(el);
    const name = el.textContent.trim();
    inBounds(`button ${name}`, bounds);
    if (ratio < 4.5) failures.push(`button ${name} contrast ${ratio.toFixed(2)}:1 < 4.5:1 (${fg} on ${bg})`);
    return { name, foreground: fg, background: bg, contrast: ratio, ...bounds };
  });
  return {
    viewport: innerWidth, clientWidth, documentWidth: document.documentElement.scrollWidth,
    themeStyles: [...document.querySelectorAll('link[rel="stylesheet"]')].map(el => el.href).filter(url => url.includes('/themes/')),
    robots: document.querySelector('meta[name="robots"]')?.content,
    root: rootBox, sections, buttons,
    images: [...root.querySelectorAll('img')].map(el => ({ alt: el.alt, source: el.currentSrc, loaded: el.complete && el.naturalWidth > 0, inline: el.getAttribute('style'), ratio: getComputedStyle(el).aspectRatio, ...box(el) })),
    failures,
  };
}
