/**
 * D-064 L4 production verification — read only.
 * Run against the actual public website, no injected CSS, no fixture menus,
 * no mutations and no authentication. Verifies live content and computed CSS.
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';

const out = '/tmp/d064-production-l4';
mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ headless: true });
const base = 'https://aznet.vn';
const screens = [
  { url: '/', width: 1705 }, { url: '/', width: 1440 },
  { url: '/', width: 1280 }, { url: '/', width: 1120 },
  { url: '/', width: 820 }, { url: '/', width: 768 },
  { url: '/', width: 390 }, { url: '/', width: 360 },
  { url: '/gioi-thieu', width: 1705 }, { url: '/gioi-thieu', width: 390 },
  { url: '/lien-he', width: 390 }
];
const expected = [
  ['https://aznet.vn/lien-he', 'Liên hệ và tư vấn'],
  ['https://aznet.vn/bang-gia', 'Bảng giá dịch vụ'],
  ['https://aznet.vn/thiet-ke-website', 'Thiết kế website'],
  ['https://aznet.vn/dich-vu-seo', 'SEO website'],
  ['https://aznet.vn/dich-vu-cham-soc-website', 'Chăm sóc website'],
  ['https://aznet.vn/nang-cap-website', 'Nâng cấp website'],
  ['https://aznet.vn/dao-tao', 'Đào tạo'],
  ['https://www.facebook.com/aznet.official', 'Facebook AZnet'],
  ['https://aznet.vn/chinh-sach-ve-quyen-rieng-tu', 'Quyền riêng tư'],
  ['https://aznet.vn/thoa-thuan-su-dung', 'Thỏa thuận sử dụng'],
  ['https://aznet.vn/chinh-sach-bao-hanh-website', 'Bảo hành website']
];
const failures=[],checks=[];
try {
  for(const {url,width} of screens) {
    const context = await browser.newContext({
      viewport: {width,height:960}, deviceScaleFactor:1, reducedMotion:'reduce'
    });
    const page=await context.newPage();
    const errors=[];
    let row={url,width};
    try {
      const response=await page.goto(base+url,{waitUntil:'domcontentloaded',timeout:45000});
      if(!response?.ok()) throw Error('public HTTP '+response?.status());
      const footer=page.locator('footer.aznet-theme-site-footer[data-aznet-theme-site-footer]');
      await footer.waitFor({state:'visible',timeout:15000});
      await footer.scrollIntoViewIfNeeded();
      await page.evaluate(() => document.fonts.ready);
      row=await page.evaluate(()=>{
        const footer=document.querySelector('footer.aznet-theme-site-footer[data-aznet-theme-site-footer]');
        const inner=footer?.querySelector('.aznet-theme-site-footer__inner');
        const main=inner?.querySelector('.aznet-theme-site-footer__main');
        const bottom=inner?.querySelector('.aznet-theme-site-footer__bottom');
        if(!footer||!inner||!main||!bottom)throw Error('Footer hierarchy absent');
        const fr=footer.getBoundingClientRect(),mr=main.getBoundingClientRect();
        const p=getComputedStyle(footer), g=getComputedStyle(main);
        const sections=[...main.children].map(el=>{
          const r=el.getBoundingClientRect();
          return {className:el.className,x:r.x,y:r.y,w:r.width,h:r.height};
        });
        const navs=[...footer.querySelectorAll('nav')].map(el=>el.getAttribute('aria-label'));
        const links=[...footer.querySelectorAll('a[href]')].map(el=>({href:el.href,label:el.textContent.trim(),target:el.target,rel:el.rel}));
        const img=footer.querySelector('.aznet-theme-site-footer__logo');
        const cssLinks=[...document.querySelectorAll('link[rel="stylesheet"]')].map(el=>el.href).filter(u=>/site-footer|digital-01|cache\/min/.test(u));
        return {
          preset:document.body.className,
          innerBottom:bottom.parentElement===inner,
          innerMain:main.parentElement===inner,
          landmarkCount:document.querySelectorAll('footer[role="contentinfo"]').length,
          footerW:fr.width,footerScrollW:footer.scrollWidth,footerClientW:footer.clientWidth,
          mainW:mr.width,grid:g.gridTemplateColumns,
          accent:p.borderTopColor,borderTopWidth:p.borderTopWidth,
          backgroundImage:p.backgroundImage,
          footerSurfaceColor:p.backgroundColor,
          links,navs,sections,
          imgPresent:!!img,imgComplete:!!img?.complete,
          copyright:footer.querySelector('.aznet-theme-site-footer__copyright')?.textContent?.trim(),
          cssLinks
        };
      });
      if(!row.preset.includes('aznet-theme-preset--digital-01'))errors.push('Digital 01 body class missing');
      if(!row.innerBottom||!row.innerMain||row.landmarkCount!==1)errors.push('Footer structure/landmark corrupted');
      if(!row.imgPresent)errors.push('WordPress logo image absent');
      if(!row.copyright?.includes('AZnet'))errors.push('Native copyright absent');
      if(row.footerScrollW>row.footerClientW+3)errors.push('Footer horizontal overflow '+row.footerScrollW+'/'+row.footerClientW);
      if(row.sections.length!==4)errors.push('Expected exactly four source-backed main columns: got '+row.sections.length);
      const n=row.grid.trim().split(/\s+/).length;
      const expectedColumns=width<=768?1:width<=1120?2:4;
      if(n!==expectedColumns)errors.push('Computed responsive columns '+n+' expected '+expectedColumns);
      if(Number.parseFloat(row.borderTopWidth)<3)errors.push('New Footer CSS not applied: top border '+row.borderTopWidth);
      if(!row.backgroundImage.includes('radial-gradient'))errors.push('New Footer background not applied: '+row.backgroundImage);
      for(const [href,label] of expected){
        if(!row.links.some(x=>x.href===href&&x.label===label))errors.push('Published WordPress menu item absent: '+label);
      }
      if(!row.navs.includes('Chính sách'))errors.push('Missing policy navigation landmark');
      for(let i=0;i<row.sections.length;i++)for(let j=i+1;j<row.sections.length;j++){
        const a=row.sections[i],b=row.sections[j];
        const dx=Math.min(a.x+a.w,b.x+b.w)-Math.max(a.x,b.x);
        const dy=Math.min(a.y+a.h,b.y+b.h)-Math.max(a.y,b.y);
        if(dx>2&&dy>2)errors.push('Overlapping columns '+i+'/'+j+' '+dx+'x'+dy);
      }
      const facebook=row.links.find(x=>x.href==='https://www.facebook.com/aznet.official');
      if(!facebook?.rel.includes('noopener'))errors.push('External social link missing noopener');
      if(facebook?.target!=='_blank')errors.push('Social target mismatch');
      await footer.locator('nav a').first().focus();
      const focused=await footer.locator('nav a').first().evaluate(el=>({style:getComputedStyle(el).outlineStyle,width:getComputedStyle(el).outlineWidth}));
      row.focus=focused;
      if(focused.style==='none'||Number.parseFloat(focused.width)<2)errors.push('Keyboard focus outline absent');
      row.url=url;row.width=width;row.errors=errors;
      console.log('D064_LIVE '+JSON.stringify({url,width,columns:n,expectedColumns,border:row.borderTopWidth,background:row.backgroundImage,links:row.links.length,footerOverflow:row.footerScrollW>row.footerClientW+3,focus:focused,errors}));
      if([1705,1120,768,390].includes(width)) await footer.screenshot({path:out+'/footer-'+url.replace(/\W/g,'_')+'-'+width+'.png',animations:'disabled'});
    }catch(e){errors.push(String(e));row={url,width,errors};console.log('D064_ERROR '+JSON.stringify(row));}
    if(errors.length)failures.push({url,width,errors});
    checks.push(row);
    await context.close();
  }
} finally {await browser.close();}
writeFileSync(out+'/measurements.json',JSON.stringify({production:true,base,screens,checks,failures},null,2));
console.log('D064_LIVE_SUMMARY '+JSON.stringify({cases:checks.length,failedCases:failures.length,failures}));
if(failures.length)process.exit(1);
console.log('PASS D-064 PRODUCTION L4: original served CSS, WordPress menu assignments, keyboard focus and geometry');
