import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
const baseUrl=(process.env.HOMEPAGE_AUTHORING_BASE_URL||'http://127.0.0.1:8080').replace(/\/$/,'');
const adminUser=process.env.HOMEPAGE_AUTHORING_ADMIN_USER||'admin';
const adminPass=process.env.HOMEPAGE_AUTHORING_ADMIN_PASS||'';
const stateDir=process.env.HOMEPAGE_AUTHORING_STATE_DIR||'/tmp/homepage-authoring-l4';
fs.mkdirSync(stateDir,{recursive:true});
if(!adminPass)throw new Error('HOMEPAGE_AUTHORING_ADMIN_PASS is required');
async function login(page){await page.goto(baseUrl+'/wp-login.php',{waitUntil:'domcontentloaded'});await page.locator('#user_login').fill(adminUser);await page.locator('#user_pass').fill(adminPass);await Promise.all([page.waitForURL(/\/wp-admin\//,{timeout:20000}),page.locator('#wp-submit').click()]);}
async function openHomepage(page){await page.goto(baseUrl+'/wp-admin/admin.php?page=aznet-theme&section=homepage',{waitUntil:'domcontentloaded'});await page.locator('.aznet-theme-control-center').waitFor({state:'visible',timeout:20000});}
async function choosePreset(page,preset){const form=page.locator('form.aznet-theme-panel').filter({has:page.getByRole('heading',{name:'Mẫu trang chủ'})});await form.locator('select[name="aznet_theme_settings[homepage_preset]"]').selectOption(preset);await Promise.all([page.waitForURL(/updated=1/,{timeout:20000}),form.getByRole('button',{name:'Lưu mẫu trang chủ'}).click()]);await openHomepage(page);}
const browser=await chromium.launch({headless:true});const failures=[];
try{const context=await browser.newContext({viewport:{width:1440,height:1000}});const page=await context.newPage();try{await login(page);await openHomepage(page);await choosePreset(page,'law-01');if(!(await page.locator('.aznet-theme-homepage-authoring h2').innerText()).includes('Mẫu đang chỉnh: Luật 01'))throw new Error('Law heading missing');if(await page.locator('.aznet-theme-homepage-section-card').count()!==10)throw new Error('Law cards mismatch');await choosePreset(page,'curtain-01');if(!(await page.locator('.aznet-theme-homepage-authoring h2').innerText()).includes('Mẫu đang chỉnh: Rèm 01'))throw new Error('Curtain heading missing');if(await page.locator('.aznet-theme-homepage-section-card').count()!==7)throw new Error('Curtain cards mismatch');const axe=await new AxeBuilder({page}).include('.aznet-theme-control-center').analyze();fs.writeFileSync(path.join(stateDir,'axe.json'),JSON.stringify(axe,null,2));const serious=axe.violations.filter(v=>['critical','serious'].includes(v.impact||''));if(serious.length)throw new Error('axe: '+serious.map(v=>v.id).join(','));}catch(e){failures.push(e instanceof Error?e.message:String(e));}finally{await context.close();}}finally{await browser.close();}
if(failures.length){console.error(failures.join('\n'));process.exit(1);}console.log('PASS: D-038 Homepage authoring Control Center L4');
