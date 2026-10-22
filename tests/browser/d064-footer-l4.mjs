/**
 * D-064: AZnet Digital 01 Footer visual regression (fixture only).
 *
 * Public WordPress 1.3.96 serves the correct real logo/identity & stylesheet
 * tokens. Its Footer menu locations are currently unassigned and its PHP
 * nesting is incorrect. This L4 ONLY checks CSS presentation by rearranging
 * that one Footer-bottom node and injecting *published WordPress menu link*
 * projections. It is NOT live WordPress menu integration or deployment proof.
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';
const root='/tmp/d064-footer-l4';
mkdirSync(root,{recursive:true});
const cases=[1705,1440,1280,1120,1024,820,768,390,360];
const errors=[],results=[];
const site='https://aznet.vn/gioi-thieu';
const menus={
 contact:[['Liên hệ và tư vấn','https://aznet.vn/lien-he'],['Bảng giá dịch vụ','https://aznet.vn/bang-gia']],
 services:[['Thiết kế website','https://aznet.vn/thiet-ke-website'],['SEO website','https://aznet.vn/dich-vu-seo'],['Chăm sóc website','https://aznet.vn/dich-vu-cham-soc-website'],['Nâng cấp website','https://aznet.vn/nang-cap-website'],['Đào tạo','https://aznet.vn/dao-tao']],
 social:[['Facebook AZnet','https://www.facebook.com/aznet.official']],
 policies:[['Quyền riêng tư','https://aznet.vn/chinh-sach-ve-quyen-rieng-tu'],['Thỏa thuận sử dụng','https://aznet.vn/thoa-thuan-su-dung'],['Bảo hành website','https://aznet.vn/chinh-sach-bao-hanh-website']]
};
const browser=await chromium.launch({headless:true});
try {
 for(const width of cases){
  const context=await browser.newContext({viewport:{width,height:970},deviceScaleFactor:1,reducedMotion:'reduce'});
  const page=await context.newPage();
  let r;
  try{
    const response=await page.goto(site,{waitUntil:'domcontentloaded',timeout:45000});
    if(!response?.ok()) throw Error('Public WordPress HTTP '+response?.status());
    await page.locator('[data-aznet-theme-site-footer]').waitFor({state:'attached',timeout:15000});
    await page.evaluate((sources)=>{
      const footer=document.querySelector('[data-aznet-theme-site-footer]');
      if(!footer)throw Error('Footer absent');
      const inner=footer.querySelector('.aznet-theme-site-footer__inner');
      const main=footer.querySelector('.aznet-theme-site-footer__main');
      const bottom=footer.querySelector('.aznet-theme-site-footer__bottom');
      if(!inner||!main||!bottom)throw Error('Footer structure absent');
      // Simulate only the minimal PHP nesting fix already verified by PHP L2.
      inner.append(bottom);
      const add=(key,cls,heading,ulCls)=>{
        const nav=document.createElement('nav');nav.className=cls;
        nav.setAttribute('aria-label',heading);nav.dataset.d064QaFixture='true';
        const h=document.createElement('h2');h.className='aznet-theme-site-footer__heading';h.textContent=heading;
        const ul=document.createElement('ul');ul.className=ulCls;
        for(const [text,href] of sources[key]){
          const li=document.createElement('li'), a=document.createElement('a');
          a.textContent=text;a.href=href;
          if(key==='social'){a.target='_blank';a.rel='noopener noreferrer';}
          li.append(a);ul.append(li);
        }
        nav.append(h,ul);main.append(nav);
      };
      add('contact','aznet-theme-site-footer__contact','Thông tin liên hệ','aznet-theme-site-footer__contact-menu');
      add('services','aznet-theme-site-footer__navigation','Liên kết nhanh','aznet-theme-site-footer__menu');
      add('social','aznet-theme-site-footer__social-column aznet-theme-site-footer__social','Kết nối với chúng tôi','aznet-theme-site-footer__social-menu');
      const policies=document.createElement('div');policies.className='aznet-theme-site-footer__bottom-nav';
      const nav=document.createElement('nav');nav.className='aznet-theme-site-footer__policies';nav.setAttribute('aria-label','Chính sách');
      const ul=document.createElement('ul');ul.className='aznet-theme-site-footer__policy-menu';
      for(const [name,url] of sources.policies){const li=document.createElement('li'),a=document.createElement('a');a.href=url;a.textContent=name;li.append(a);ul.append(li);}
      nav.append(ul);policies.append(nav);bottom.append(policies);
    },menus);
    await page.addStyleTag({path:'assets/css/components/site-footer.css'});
    await page.evaluate(()=>document.fonts.ready);
    r=await page.evaluate(()=>{
      const el=document.querySelector('[data-aznet-theme-site-footer]'),inner=el.querySelector('.aznet-theme-site-footer__inner');
      const main=inner.querySelector('.aznet-theme-site-footer__main'),bottom=inner.querySelector('.aznet-theme-site-footer__bottom');
      const grid=getComputedStyle(main).gridTemplateColumns;
      const sections=[...main.children].map(node=>{const b=node.getBoundingClientRect();return{className:node.className,x:b.x,y:b.y,w:b.width,h:b.height}});
      const box=el.getBoundingClientRect();
      return{bodyPreset:document.body.className.includes('aznet-theme-preset--digital-01'),
        footerWidth:box.width,scrollWidth:el.scrollWidth,clientWidth:el.clientWidth,
        innerWidth:inner.getBoundingClientRect().width,grid,
        sections,
        bottomInside:bottom.parentElement===inner,
        logo:!!el.querySelector('.aznet-theme-site-footer__logo'),
        copyright:el.querySelector('.aznet-theme-site-footer__copyright')?.textContent?.trim(),
        links:el.querySelectorAll('a').length,
        headings:[...el.querySelectorAll('h2')].map(n=>n.textContent.trim())
      };
    });
    const problems=[];
    if(!r.bodyPreset)problems.push('Digital 01 preset absent');
    if(!r.logo)problems.push('WordPress-owned logo absent');
    if(!r.bottomInside)problems.push('Copyright escaped inner');
    if(r.sections.length!==4)problems.push('Expected four source-backed regions');
    if(r.links<11)problems.push('Missing linked WordPress sources');
    if(r.scrollWidth>r.clientWidth+3)problems.push('Footer overflow '+r.scrollWidth+' > '+r.clientWidth);
    const count=r.grid.trim().split(/\s+/).length;
    const expected=width<=768?1:width<=1120?2:4;
    if(count!==expected)problems.push('Grid columns '+count+' expected '+expected);
    for(const s of r.sections){if(s.w<1||s.h<1)problems.push('Zero-size section: '+s.className);}
    for(let i=0;i<r.sections.length;i++)for(let j=i+1;j<r.sections.length;j++){
      const a=r.sections[i],b=r.sections[j];
      const ow=Math.min(a.x+a.w,b.x+b.w)-Math.max(a.x,b.x);
      const oh=Math.min(a.y+a.h,b.y+b.h)-Math.max(a.y,b.y);
      if(ow>2&&oh>2)problems.push('Overlapping regions '+i+' '+j+' w='+ow+' h='+oh);
    }
    await page.locator('.aznet-theme-site-footer__navigation a').first().focus();
    const focus=await page.locator('.aznet-theme-site-footer__navigation a').first().evaluate(el=>({width:getComputedStyle(el).outlineWidth,style:getComputedStyle(el).outlineStyle}));
    if(focus.style==='none'||Number.parseFloat(focus.width)<2)problems.push('No visible keyboard focus outline');
    const accessible=await page.locator('.aznet-theme-site-footer__social-column a[target="_blank"]').first().getAttribute('rel');
    if(!accessible?.includes('noopener'))problems.push('Social external link lacks noopener');
    r={width,...r,focus,problems};
    results.push(r);
    console.log('D064_L4 '+JSON.stringify({width,grid:r.grid,columns:count,footerOverflow:r.scrollWidth>r.clientWidth+3,sections:r.sections.map(s=>({x:s.x,y:s.y,w:s.w})),focus,problems}));
    if(problems.length)errors.push({width,problems});
    await page.screenshot({path:root+'/footer-'+width+'.png',fullPage:true});
  }catch(e){errors.push({width,error:String(e)});console.log('D064_ERROR '+width+' '+String(e));}
  finally{await context.close();}
 }
}finally{await browser.close();}
writeFileSync(root+'/measurements.json',JSON.stringify({input:'public AZnet page + real WordPress menu entries as L4-only fixture',results,errors},null,2));
console.log('D064_SUMMARY '+JSON.stringify({cases:cases.length,errors}));
if(errors.length)process.exit(1);
console.log('PASS D-064: Digital 01 Footer fixture L4 geometry, focus and responsive (NOT menu integration)');
