// Industrial 01 Theme-owned presentation of WordPress-native front-page blocks.
// Do not replace WordPress Page source, or infer business roles from slug/ID.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const css = fs.readFileSync(path.join(root, 'assets/css/components/homepage-industrial-01.css'), 'utf8');
const scope = 'body.aznet-theme-preset--industrial-01 .aznet-theme-entry--front-page[data-aznet-homepage-surface="front-page-content"]';

assert.ok(css.includes(scope), 'RED: Industrial 01 must target the actual sibling WordPress front-page content surface');
assert.match(css, /\.aznet-theme-main--front-page\s*>\s*\.aznet-theme-entry--front-page\[data-aznet-homepage-surface="front-page-content"\]\s*\{\s*width:\s*100%/,
  'Native article must receive full-width section styling without changing native content');
assert.match(css, /\.wp-block-group\s*>\s*\.wp-block-columns\s*\{\s*display:\s*grid/,
  'Native WordPress columns must use an explicitly styled grid in the Theme');
assert.match(css, /\.wp-block-group\s*>\s*\.wp-block-columns\s*>\s*\.wp-block-column\s*\{\s*display:\s*flex/,
  'Each native WordPress column must become a Theme presentation card');
assert.ok(css.includes('grid-template-columns: repeat(3, minmax(0, 1fr));'), 'Desktop must use 3 columns');
assert.ok(css.includes('grid-template-columns: repeat(2, minmax(0, 1fr));'), 'Tablet must use 2 columns');
assert.ok(css.includes('grid-template-columns: minmax(0, 1fr);'), 'Mobile must stack columns');
assert.match(css, /\.wp-block-column\s*>\s*p\s+a\s*\{\s*display:\s*inline-flex/,
  'Native block links need styled, keyboard reachable CTA surfaces');
assert.match(css, /\.wp-block-column\s*>\s*p\s+a:focus-visible\s*\{\s*outline:/,
  'CTA must retain an accessible focus indicator');
assert.ok(!css.includes('anp-home-service-guide'), 'No hardcoded ANP-only content class in Theme CSS');
assert.ok(!css.includes('https://anpvietnam.vn'), 'Theme must not embed client URLs');
console.log('PASS: Industrial 01 native WordPress front-page content presentation contract');
