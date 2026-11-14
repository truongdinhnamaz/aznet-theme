// Exact-JS behavioral contract for the Theme-owned, native-menu disclosure.
// Uses Node built-ins only. This is NOT a public WordPress/browser L4 test.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const scriptPath = process.env.AZT_CATALOG_JS_FILE || path.join(root, 'assets/js/catalog-navigation.js');
const script = fs.readFileSync(scriptPath, 'utf8');

function element(tag, text = '') {
  const handlers = new Map();
  const attrs = new Map();
  const node = {
    tagName: tag.toUpperCase(), children: [], className: '', id: '', hidden: false,
    type: '', text, focusCount: 0,
    get textContent() { return this.text + this.children.map((child) => child.textContent).join(''); },
    set textContent(value) { this.text = String(value); this.children = []; },
    setAttribute(key, value) { attrs.set(key, String(value)); },
    getAttribute(key) { return attrs.get(key) ?? null; },
    matches(selector) { return selector === 'ul.sub-menu' && tag === 'ul' && this.className === 'sub-menu'; },
    appendChild(child) { this.children.push(child); return child; },
    insertBefore(child, before) {
      const i = this.children.indexOf(before);
      assert.ok(i >= 0, 'submenu must already belong to item');
      this.children.splice(i, 0, child);
      return child;
    },
    addEventListener(type, callback) { handlers.set(type, callback); },
    dispatch(type, event = {}) { handlers.get(type)?.(event); },
    focus() { this.focusCount++; },
  };
  return node;
}

function runScenario({ id = '', labels = {} } = {}) {
  const link = element('a', 'Ắc quy xe nâng');
  const sublink = element('a', 'GS Yuasa');
  const leaf = element('li');
  leaf.children.push(sublink);
  const submenu = element('ul');
  submenu.className = 'sub-menu';
  submenu.id = id;
  submenu.children.push(leaf);
  const item = element('li');
  item.children.push(link, submenu);
  const menu = { querySelectorAll(selector) { assert.equal(selector, 'li'); return [item, leaf]; } };
  const document = {
    querySelectorAll(selector) {
      assert.equal(selector, '.aznet-theme-catalog-navigation__menu');
      return [menu];
    },
    createElement(tag) { return element(tag); },
  };
  vm.runInNewContext(script, { document, window: { aznetThemeCatalogNav: labels } }, { timeout: 1000 });
  const button = item.children.find((child) => child.tagName === 'BUTTON');
  assert.ok(button, 'parent term must gain a disclosure button');
  assert.equal(item.children.indexOf(button), 1, 'button must precede submenu');
  assert.equal(button.type, 'button');
  assert.equal(button.className, 'aznet-theme-catalog-navigation__submenu-toggle');
  assert.equal(button.getAttribute('aria-controls'), submenu.id);
  assert.equal(submenu.id, id || 'aznet-theme-catalog-submenu-0-0');
  assert.equal(button.getAttribute('aria-expanded'), 'false');
  assert.equal(button.getAttribute('aria-label'), `${labels.expand || 'Mở danh mục con'}: Ắc quy xe nâng`);
  assert.equal(submenu.hidden, true);
  assert.equal(button.textContent, '', 'decorative +/- must not enter visible button text');
  const icon = button.children[0];
  assert.equal(icon?.tagName, 'SPAN');
  assert.equal(icon.className, 'aznet-theme-catalog-navigation__submenu-icon');
  assert.equal(icon.getAttribute('aria-hidden'), 'true');
  button.dispatch('click');
  assert.equal(submenu.hidden, false);
  assert.equal(button.getAttribute('aria-expanded'), 'true');
  assert.equal(button.getAttribute('aria-label'), `${labels.collapse || 'Thu gọn danh mục con'}: Ắc quy xe nâng`);
  assert.equal(button.textContent, '');
  const event = { key: 'Escape', defaultPrevented: false, propagationStopped: false,
    preventDefault() { this.defaultPrevented = true; }, stopPropagation() { this.propagationStopped = true; } };
  item.dispatch('keydown', event);
  assert.equal(event.defaultPrevented, true);
  assert.equal(event.propagationStopped, true);
  assert.equal(submenu.hidden, true);
  assert.equal(button.getAttribute('aria-expanded'), 'false');
  assert.equal(button.getAttribute('aria-label'), `${labels.expand || 'Mở danh mục con'}: Ắc quy xe nâng`);
  assert.equal(button.focusCount, 1, 'Escape returns focus to disclosure');
  assert.equal(button.textContent, '');
  assert.equal(leaf.children[0].textContent, 'GS Yuasa', 'nested source content remains intact');
}

runScenario();
runScenario({ id: 'provider-public-id', labels: { expand: 'Mở nhóm', collapse: 'Đóng nhóm' } });
console.log('PASS: native catalog disclosure icon, naming, click, Escape and source content (Node-only fixture)');
