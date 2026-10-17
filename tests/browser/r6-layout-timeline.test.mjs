import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { installR6LayoutTimeline } from './r6-layout-timeline.mjs';

function fixture() {
  let now = 100, raf = null, observer = null, mutation = null;
  const make = (tag, cls, rect, style = {}) => ({
    tagName: tag, nodeType: 1, className: cls, isConnected: true,
    offsetHeight: rect.height, offsetWidth: rect.width,
    getBoundingClientRect: () => ({ ...rect, bottom: rect.y + rect.height, right: rect.x + rect.width }),
    getClientRects: () => [rect],
    matches: (s) => s.split(',').some(x => x.trim().includes('.summary.entry-summary') && cls.includes('summary') || x.trim().endsWith('.woocommerce-product-gallery') && cls.includes('woocommerce-product-gallery')),
    closest: () => null,
    __style: {display:'block', visibility:'visible', opacity:'1', position:'static', overflow:'visible', transform:'none', gridTemplateColumns:'none', ...style},
  });
  const gallery = make('DIV','woocommerce-product-gallery',{x:0,y:100,width:390,height:400});
  const summary = make('DIV','summary entry-summary',{x:0,y:600,width:390,height:210});
  const product = make('DIV','product',{x:0,y:100,width:390,height:710});
  const nodes = { '.single-product #main .product':product, '.single-product #main .woocommerce-product-gallery':gallery, '.single-product #main .summary.entry-summary':summary };
  const doc = { readyState:'complete', querySelector:(sel)=>nodes[sel]??null, documentElement:{tagName:'HTML'}, addEventListener:()=>{} };
  const sandbox = {
    document:doc, innerHeight:844, innerWidth:390, scrollY:0,
    performance:{ now:()=>now },
    getComputedStyle:(n)=>n.__style,
    requestAnimationFrame:(callback)=>{raf=callback;return 1},
    cancelAnimationFrame:()=>{},
    MutationObserver:class { constructor(fn){mutation=fn;} observe(){} disconnect(){} },
    PerformanceObserver:class { constructor(fn){observer=fn;} observe(){} disconnect(){} },
  };
  vm.runInNewContext('(' + installR6LayoutTimeline.toString() + ')()',sandbox);
  return {sandbox,summary,gallery,product, tick:(t)=>{now=t; const f=raf; if(f) f(t);}, shift:(value=0.2)=>observer({getEntries:()=>[{value,startTime:now,hadRecentInput:false,sources:[]}]}), mutate:()=>mutation([{type:'attributes',target:gallery,attributeName:'style'}])};
}

test('records both viewport rect and nonzero layout size even when node moves out of view', () => {
  const f = fixture();
  f.tick(300);
  f.summary.getBoundingClientRect = () => ({x:0,y:1200,width:390,height:210,bottom:1410,right:390});
  f.tick(410);
  const t = JSON.parse(JSON.stringify(f.sandbox.__aznetR6LayoutTimeline));
  const first = t.samples[0].nodes.summary;
  const second = t.samples.at(-1).nodes.summary;
  assert.equal(first.rect.y,600);
  assert.equal(second.rect.y,1200);
  assert.equal(second.offscreen,true);
  assert.equal(second.layoutHeight,210);
  assert.equal(second.style.display,'block');
});

test('records computed CSS and layout/viewport geometry around a shift', () => {
  const f = fixture();
  f.tick(250);
  f.gallery.__style.display='none';
  f.gallery.offsetHeight=0;
  f.gallery.getBoundingClientRect=()=>({x:0,y:0,width:0,height:0,bottom:0,right:0});
  f.shift(0.31);
  const s=JSON.parse(JSON.stringify(f.sandbox.__aznetR6LayoutTimeline));
  assert.equal(s.shifts.length,1);
  assert.equal(s.shifts[0].value,0.31);
  assert.equal(s.shifts[0].snapshot.gallery.style.display,'none');
  assert.equal(s.shifts[0].snapshot.gallery.layoutHeight,0);
});

test('records only fixed-schema style/class mutation events and no private attribute values', () => {
  const f=fixture();
  f.mutate();
  const t=JSON.stringify(f.sandbox.__aznetR6LayoutTimeline);
  assert.match(t,/"attribute":"style"/);
  assert.doesNotMatch(t,/privateToken|secret=|innerHTML/);
});

test('bounded sampling, shift observations and event limits cannot grow without bound',()=>{
  const f=fixture();
  for(let i=0;i<600;i++){f.tick(250+i*90);}
  const state=f.sandbox.__aznetR6LayoutTimeline;
  assert.ok(state.samples.length<=200);
  assert.equal(state.stopped,true);
  assert.equal(state.samplesTruncated,false);
});
