/**
 * RQA-PROJECTS-LIVE-01 — public, strictly read-only browser preview.
 * The only DOM mutation is injecting the reviewed PR #593 CSS into an
 * ephemeral browser page. No login, POST, click, upload, WP writes or deploy.
 */
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { chromium } from 'playwright';

const url = 'https://remquocanh.vn/';
const selector = '.aznet-theme-curtain01-projects';
const cssFile = 'assets/css/components/homepage-curtain-01.css';
const expectedCssSha = 'c6bdafa55db929fe7b40cd0f5b54ed63f7eb2b82a0273fcc009473bc4fc8dfb5';
const css = fs.readFileSync(cssFile, 'utf8');
const actualCssSha = crypto.createHash('sha256').update(css).digest('hex');
if (actualCssSha !== expectedCssSha) {
  throw new Error('PR593 CSS SHA-256 drift: ' + actualCssSha);
}
const outDir = process.env.RQA_PR593_OUT || 'rqa-pr593-live-photo-evidence';
fs.mkdirSync(outDir, {recursive:true});
const expected = [
  {fragment:'khach-san-pullman-saigon-centre-576x1024.jpg', width:576, height:1024},
  {fragment:'khach-san-sofitel-saigon-plaza-681x1024.jpg', width:681, height:1024},
  {fragment:'rem-cuon-panama-pa106-van-phong-1024x576.jpg', width:1024, height:576}
];
const cases = [
  {name:'desktop', width:1440, height:1000},
  {name:'tablet', width:1024, height:900},
  {name:'mobile', width:390, height:844},
  {name:'narrow', width:320, height:720}
];
const browser = await chromium.launch({headless:true});
const summary = {
  scope:'live RQA Project Showcase; browser CSS injection only, no site writes',
  cssSource:'PR593 954274e7c1266e3536e371e69c4485e7f95e1a15',
  cssGitBlob:'32e828a1c3c06ea9720c5eedba310143e229e690',
  cssSha256:actualCssSha,
  testedAt:new Date().toISOString(),
  cases:[], failures:[]
};
const near = (a,b,tol=2) => Math.abs(a-b)<=tol;
function snapshot(){
  const root=document.querySelector('.aznet-theme-curtain01-projects');
  if(!root) return {missing:true};
  const rootBox=root.getBoundingClientRect();
  const rect=(el)=>{
    if(!el) return null;
    const r=el.getBoundingClientRect();
    return {x:+r.x.toFixed(2),y:+r.y.toFixed(2),width:+r.width.toFixed(2),height:+r.height.toFixed(2),left:+r.left.toFixed(2),right:+r.right.toFixed(2)};
  };
  return {
    viewport:document.documentElement.clientWidth,
    scrollWidth:document.documentElement.scrollWidth,
    root:rect(root),
    sectionHeading:root.querySelector('h2')?.textContent?.trim()||'',
    cards:[...root.querySelectorAll('.aznet-theme-curtain01-project-card')].map(card=>{
      const media=card.querySelector('.aznet-theme-curtain01-project-card__media');
      const img=card.querySelector('.aznet-theme-curtain01-project-card__image');
      const title=card.querySelector('h3 a');
      return {
        media:rect(media),
        image:rect(img),
        card:rect(card),
        imagePosition:img?getComputedStyle(img).position:'',
        objectFit:img?getComputedStyle(img).objectFit:'',
        naturalWidth:img?.naturalWidth||0,
        naturalHeight:img?.naturalHeight||0,
        imageComplete:!!img?.complete,
        imageSrc:img?.getAttribute('src')||'',
        imageCurrentSrc:img?.currentSrc||'',
        imageAlt:img?.getAttribute('alt')||'',
        mediaHref:media?.getAttribute('href')||'',
        title:title?.textContent?.trim()||'',
        titleHref:title?.href||'',
        excerpt:card.querySelector('.aznet-theme-curtain01-project-card__body p')?.textContent?.trim()||''
      };
    })
  };
}
function identity(card){
  return [card.imageSrc,card.imageAlt,card.mediaHref,card.title,card.titleHref,card.excerpt,card.naturalWidth,card.naturalHeight];
}
try {
  for(const item of cases){
    const context=await browser.newContext({
      viewport:{width:item.width,height:item.height}, deviceScaleFactor:1, locale:'vi-VN'
    });
    const page=await context.newPage();
    const jsErrors=[];
    page.on('pageerror',e=>jsErrors.push(String(e).slice(0,250)));
    const caseRecord={name:item.name,width:item.width,height:item.height,errors:[]};
    try {
      const response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});
      caseRecord.httpStatus=response?.status()||0;
      caseRecord.finalUrl=page.url();
      if(!response || response.status()!==200 || !page.url().startsWith('https://remquocanh.vn/')){
        caseRecord.errors.push('Unexpected public homepage response/final URL');
      }
      const root=page.locator(selector);
      await root.waitFor({state:'attached',timeout:20000});
      for(let i=0;i<3;i++){
        const image=root.locator('.aznet-theme-curtain01-project-card__image').nth(i);
        await image.scrollIntoViewIfNeeded();
        await page.waitForFunction((index)=>{
          const imgs=document.querySelectorAll('.aznet-theme-curtain01-projects .aznet-theme-curtain01-project-card__image');
          return imgs.length===3 && imgs[index]?.complete && imgs[index]?.naturalWidth>0;
        },i,{timeout:18000});
      }
      caseRecord.before=await page.evaluate(snapshot);
      await root.screenshot({path:path.join(outDir,item.name+'-before.png'),timeout:18000});
      await page.addStyleTag({content:css});
      await page.waitForTimeout(170);
      caseRecord.after=await page.evaluate(snapshot);
      await root.screenshot({path:path.join(outDir,item.name+'-after.png'),timeout:18000});
      if(caseRecord.before.cards.length!==3 || caseRecord.after.cards.length!==3){
        caseRecord.errors.push('Expected exactly three real WordPress Project cards');
      }else{
        for(let i=0;i<3;i++){
          const before=caseRecord.before.cards[i], after=caseRecord.after.cards[i];
          const req=expected[i];
          if(!before.imageSrc.includes(req.fragment) || before.naturalWidth!==req.width || before.naturalHeight!==req.height) {
            caseRecord.errors.push('Real image source/dimension changed or failed to load at card '+(i+1));
          }
          if(JSON.stringify(identity(before))!==JSON.stringify(identity(after))) {
            caseRecord.errors.push('WordPress post/photo/ALT/link content changed after CSS overlay at card '+(i+1));
          }
          if(after.objectFit!=='cover'||after.imagePosition!=='absolute') {
            caseRecord.errors.push('Candidate frame image not absolute/object-fit cover at card '+(i+1));
          }
          if(!near(after.image.width,after.media.width,2)||!near(after.image.height,after.media.height,2)){
            caseRecord.errors.push('Image is not constrained to its media frame at card '+(i+1));
          }
          if(after.media.left < -1 || after.media.right > item.width+1) {
            caseRecord.errors.push('Project media escapes viewport at card '+(i+1));
          }
          if(item.width<=390){
            const ratio=after.media.width/after.media.height;
            if(!near(ratio,1.6,0.025))caseRecord.errors.push('Mobile media ratio not 16:10 at card '+(i+1)+': '+ratio);
          }else if(i<2){
            if(after.media.height<270||after.media.height>385){
              caseRecord.errors.push('Desktop/tablet first-row media not bounded at card '+(i+1));
            }
          }else if(after.media.height<351){
            caseRecord.errors.push('Split-layout desktop media minimum height missing');
          }
        }
        caseRecord.firstTwoBeforeHeights=caseRecord.before.cards.slice(0,2).map(x=>x.media.height);
        caseRecord.firstTwoAfterHeights=caseRecord.after.cards.slice(0,2).map(x=>x.media.height);
      }
      if(caseRecord.after.root.left < -1 || caseRecord.after.root.right>item.width+1) {
        caseRecord.errors.push('Project Showcase section overflows horizontal viewport');
      }
      if(caseRecord.before.sectionHeading!==caseRecord.after.sectionHeading){
        caseRecord.errors.push('Project Showcase section heading changed');
      }
      caseRecord.pageErrors=jsErrors;
    }catch(error){
      caseRecord.errors.push('Browser inspection error: '+String(error).slice(0,400));
    }finally{
      summary.cases.push(caseRecord);
      if(caseRecord.errors.length)summary.failures.push({viewport:item.name,errors:caseRecord.errors});
      console.log('RQA_PR593_CASE '+JSON.stringify({
        viewport:item.name, width:item.width, status:caseRecord.httpStatus,
        before:caseRecord.firstTwoBeforeHeights,
        after:caseRecord.firstTwoAfterHeights,
        errors:caseRecord.errors
      }));
      await context.close();
    }
  }
}finally{
  await browser.close();
}
fs.writeFileSync(path.join(outDir,'summary.json'),JSON.stringify(summary,null,2)+'\n');
if(summary.failures.length){
  console.error('FAIL RQA_PR593 live photo read-only visual overlay: '+summary.failures.length+' viewport(s)');
  process.exitCode=1;
}else{
  console.log('PASS RQA_PR593 live photos: 3 images x 4 viewports, exact pinned CSS, bounded frames, identity/ALT/links preserved; read-only');
}
