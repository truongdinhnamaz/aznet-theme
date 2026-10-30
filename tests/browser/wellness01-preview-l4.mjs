/**
 * Standalone Wellness 01 design-reference verification, NOT Theme-in-WordPress L4.
 * The preview is only a responsive fixture and is intentionally never a booking service.
 */
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const previewURL = process.env.WELLNESS_PREVIEW_URL || 'http://127.0.0.1:8765/docs/demos/wellness01/preview.html';
const out = process.env.WELLNESS_PREVIEW_EVIDENCE_DIR || '/tmp/wellness01-preview';
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch({
  headless: true,
  ...(process.env.WELLNESS_CHROMIUM_PATH ? { executablePath: process.env.WELLNESS_CHROMIUM_PATH } : {}),
  args: process.env.WELLNESS_BROWSER_LOCAL === '1' ? ['--no-sandbox','--disable-dev-shm-usage','--no-zygote'] : [],
});
const problems = [];
const widths = [1440, 1200, 1024, 960, 768, 600, 480, 390, 360, 320];
const evidence = [];
for (const width of widths) {
  const page = await browser.newPage({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', error => exceptions.push(error.message));
  await page.goto(previewURL, { waitUntil: 'load' });
  const metrics = await page.evaluate(() => {
    const all = [...document.querySelectorAll('[id]')];
    const ids = all.map(node => node.id);
    const anchors = [...document.querySelectorAll('a[href^="#"]')].filter(a => a.getAttribute('href') !== '#');
    const noName = selector => [...document.querySelectorAll(selector)]
      .filter(el => !(el.textContent || '').trim() && !(el.getAttribute('aria-label') || '').trim()).length;
    return {
      width: innerWidth, documentWidth: document.documentElement.scrollWidth,
      lang: document.documentElement.lang, headingCount: document.querySelectorAll('h1').length,
      duplicateIds: ids.filter((id, index) => ids.indexOf(id) !== index),
      brokenAnchors: anchors.filter(a => !document.getElementById(a.getAttribute('href').substring(1))).map(a => a.getAttribute('href')),
      buttonsWithoutName: noName('button'), linksWithoutName: noName('a'),
    };
  });
  if (metrics.documentWidth > width + 1) problems.push(`${width}px horizontal overflow ${metrics.documentWidth}px`);
  if (metrics.lang !== 'vi' || metrics.headingCount !== 1) problems.push(`${width}px language / H1 structure`);
  if (metrics.duplicateIds.length || metrics.brokenAnchors.length || metrics.buttonsWithoutName || metrics.linksWithoutName) {
    problems.push(`${width}px IDs / links / names: ${JSON.stringify(metrics)}`);
  }
  const titles = [];
  for (const variant of ['yoga', 'fitness', 'mindful']) {
    const button = page.locator(`[data-preset-choice="${variant}"]`);
    await button.click();
    if (await button.getAttribute('aria-pressed') !== 'true') problems.push(`${width}px ${variant} aria-pressed`);
    titles.push((await page.locator('#hero-title-a').innerText()).trim());
    if (await page.locator('#schedule-items > *').count() === 0) problems.push(`${width}px ${variant} missing demo rows`);
    const pictures = await page.evaluate(async () => {
      const imgs = [...document.querySelectorAll('.wellness-ai-photo')];
      await Promise.all(imgs.map(img => img.decode().catch(() => {})));
      return imgs.map(img => ({ role: img.dataset.photoRole, loaded: img.complete && img.naturalWidth > 0, url: img.currentSrc }));
    });
    if (pictures.length !== 7 || pictures.some(img => !img.loaded || !img.url.includes('/assets/starter/wellness01/'))) problems.push(`${width}px ${variant} missing or broken local photo: ${JSON.stringify(pictures)}`);
    if (!await page.locator('.photo-disclosure').count()) problems.push(`${width}px ${variant} missing AI-photo disclosure`);
  }
  if (new Set(titles).size !== 3) problems.push(`${width}px variants have identical hero`);
  const hero = page.locator('[data-photo-role="hero"]');
  if (!await hero.count()) problems.push(`${width}px missing hero photo`);
  await page.locator('.day-tab').last().click();
  if (await page.locator('.day-tab[aria-pressed=true]').count() !== 1) problems.push(`${width}px day state`);
  await page.locator('#contact-button').click();
  const status = page.locator('#contact-status');
  if (await status.count() !== 1 || await status.getAttribute('role') !== 'status') problems.push(`${width}px simulated booking must not submit`);
  if (width < 769) {
    const toggle = page.locator('.menu-toggle');
    await toggle.click();
    if (await toggle.getAttribute('aria-expanded') !== 'true') problems.push(`${width}px mobile menu did not open`);
    await toggle.click();
    if (await toggle.getAttribute('aria-expanded') !== 'false') problems.push(`${width}px mobile menu did not close`);
  }
  await page.keyboard.press('Tab');
  const focused = await page.evaluate(() => document.activeElement !== document.body && document.activeElement !== document.documentElement);
  if (!focused) problems.push(`${width}px keyboard focus did not move`);
  if (exceptions.length) problems.push(`${width}px JS errors: ${exceptions.join('; ')}`);
  if ([1440, 1024, 390].includes(width)) await page.screenshot({ path: path.join(out, `${width}.png`), fullPage: true });
  evidence.push({ ...metrics, variants: titles.length, jsExceptions: exceptions });
  await page.close();
}
await browser.close();
fs.writeFileSync(path.join(out, 'verification.json'), JSON.stringify({ scope: 'standalone preview only', evidence, problems }, null, 2));
if (problems.length) {
  console.error('FAIL: Wellness 01 preview fixture\n' + problems.join('\n'));
  process.exit(1);
}
console.log(`PASS: Wellness 01 reference preview: ${widths.length} viewports, three industry variants, tabs, demo CTA and keyboard navigation`);
