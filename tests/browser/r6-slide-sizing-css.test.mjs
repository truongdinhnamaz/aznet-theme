import test from 'node:test';
import assert from 'node:assert/strict';
import { cssForR6Trial, parseR6TrialVariants, parseR6Viewports } from './r6-slide-sizing-css.mjs';

test('no CSS mutation in control group', () => {
  assert.equal(cssForR6Trial('control'), '');
});

test('mobile slide cap scopes native Woo slides without changing domain logic', () => {
  const css = cssForR6Trial('slide-viewport');
  assert.match(css, /max-width:\s*767px/);
  assert.match(css, /\.single-product #main \.woocommerce-product-gallery__image/);
  assert.match(css, /max-width:\s*100vw\s*!important/);
  assert.doesNotMatch(css, /\.woocommerce-product-gallery__image\s+img/);
});

test('container query cap constrains slides to gallery instead of slider wrapper', () => {
  const css = cssForR6Trial('slide-container');
  assert.match(css, /\.single-product #main \.woocommerce-product-gallery\s*\{\s*container-type:\s*inline-size/);
  assert.match(css, /\.single-product #main \.woocommerce-product-gallery__image/);
  assert.match(css, /max-inline-size:\s*100cqi\s*!important/);
  assert.doesNotMatch(css, /height:\s*\d+px/);
});

test('rejects unknown trial names and unbounded variant list', () => {
  assert.deepEqual(parseR6TrialVariants('control,slide-viewport,slide-container'), ['control','slide-viewport','slide-container']);
  assert.throws(() => cssForR6Trial('hide-gallery'));
  assert.throws(() => parseR6TrialVariants('control,arbitrary'));
  assert.throws(() => parseR6TrialVariants(''));
  assert.throws(() => parseR6TrialVariants('control,slide-viewport,slide-container,control'));
});

test('only approved fixed responsive viewports are accepted', () => {
  assert.deepEqual(parseR6Viewports('390x844,1440x900'), [{ width:390,height:844,label:'390x844' },{width:1440,height:900,label:'1440x900'}]);
  assert.throws(() => parseR6Viewports('320x640'));
  assert.throws(() => parseR6Viewports(''));
});


test('float-only-after-FlexViewport trial preserves native slider ownership and responsive cap', () => {
  const css=cssForR6Trial('slide-container-float');
  assert.match(css,/\.single-product #main \.woocommerce-product-gallery\s*\{\s*container-type:\s*inline-size/);
  assert.match(css,/\.single-product #main \.flex-viewport \.woocommerce-product-gallery__image\s*\{\s*float:\s*left/);
  assert.match(css,/max-inline-size:\s*100cqi\s*!important/);
  assert.doesNotMatch(css,/display:\s*none|position:\s*absolute|height:\s*\d+px/);
});
