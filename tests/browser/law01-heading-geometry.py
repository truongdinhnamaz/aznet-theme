"""Exercise the Law 01 heading predicate against native browser geometry."""
from pathlib import Path
import json, os, re, sys
from playwright.sync_api import sync_playwright
root=Path(__file__).resolve().parents[2]
source=(root/'tests/browser/homepage-law-01-l4.mjs').read_text()
match=re.search(r'const invalidHeading = headingMetrics.find\(\(item\)\s*=>\s*(.*?)\s*\);',source,re.S)
assert match,'Law 01 browser heading predicate missing'
cases=[('normal-one-line','width:600px;white-space:normal','Short heading',True),('nowrap-one-line','width:600px;white-space:nowrap','Short heading',True),('normal-wrapped','width:75px;white-space:normal','Several words must wrap over lines',False),('nowrap-overflow','width:75px;white-space:nowrap','A title too long for its parent',False),('hidden','width:600px;white-space:nowrap;display:none','Hidden heading',False),('clipped-overflow','width:75px;white-space:nowrap;overflow:hidden','Clipping is not successful layout',False)]
results=[]
with sync_playwright() as p:
    opts={'headless':True,'args':['--no-sandbox']}
    binary=os.getenv('CHROMIUM_PATH','/usr/bin/chromium')
    if Path(binary).is_file():opts['executable_path']=binary
    browser=p.chromium.launch(**opts);page=browser.new_page(viewport={'width':1024,'height':800})
    for name,style,text,wanted in cases:
        page.set_content(f'<h2 style="font:24px/30px serif;margin:0;{style}">{text}</h2>')
        m=page.locator('h2').evaluate("""n=>{const r=n.getBoundingClientRect(),s=getComputedStyle(n);return {text:n.textContent.trim(),height:r.height,width:r.width,lineHeight:parseFloat(s.lineHeight),fontSize:parseFloat(s.fontSize),whiteSpace:s.whiteSpace,clientWidth:n.clientWidth,scrollWidth:n.scrollWidth,clientHeight:n.clientHeight,scrollHeight:n.scrollHeight};}""")
        invalid=page.evaluate('(arg)=>new Function("item","return ("+arg.predicate+")")(arg.metric)',{'predicate':match.group(1),'metric':m})
        results.append({'case':name,'expected_fit':wanted,'actual_fit':not invalid,'metric':m})
    browser.close()
failures=[r['case'] for r in results if r['expected_fit']!=r['actual_fit']]
print(json.dumps({'cases':results,'failures':failures},indent=2))
sys.exit(1 if failures else 0)
