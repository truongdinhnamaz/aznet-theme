/** Fixed QA-only style hypotheses; never shipped as a Theme stylesheet. */
const CSS_BY_TRIAL = Object.freeze({
  control: '',
  'slide-viewport': '@media (max-width: 767px) { .single-product #main .woocommerce-product-gallery__image { max-width: 100vw !important; } }',
  'slide-container': '.single-product #main .woocommerce-product-gallery { container-type: inline-size; } .single-product #main .woocommerce-product-gallery__image { max-inline-size: 100cqi !important; }',
  'slide-container-float': '.single-product #main .woocommerce-product-gallery { container-type: inline-size; } .single-product #main .woocommerce-product-gallery__image { max-inline-size: 100cqi !important; } .single-product #main .flex-viewport .woocommerce-product-gallery__image { float: left; }',
});

export function cssForR6Trial(name) {
  if (!Object.hasOwn(CSS_BY_TRIAL, name)) throw new Error('Unapproved R6 CSS trial');
  return CSS_BY_TRIAL[name];
}

export function parseR6TrialVariants(raw) {
  const variants = String(raw || '').split(',').map(value => value.trim()).filter(Boolean);
  if (variants.length < 1 || variants.length > 3 || new Set(variants).size !== variants.length) throw new Error('Expected 1-3 distinct R6 CSS trials');
  for (const variant of variants) cssForR6Trial(variant);
  return variants;
}

export function parseR6Viewports(raw) {
  const valid = { '390x844': {width:390, height:844, label:'390x844'}, '1440x900': {width:1440,height:900,label:'1440x900'} };
  const labels = String(raw || '').split(',').map(value => value.trim()).filter(Boolean);
  if (labels.length < 1 || labels.length > 2 || new Set(labels).size !== labels.length || labels.some(value => !Object.hasOwn(valid,value))) throw new Error('Unapproved R6 browser viewport');
  return labels.map(label => valid[label]);
}
