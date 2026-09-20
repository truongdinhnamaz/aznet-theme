"""Local CSS regression only: compact Hero leading and four-slot captioned Team montage."""
from pathlib import Path
import json, os, sys
from playwright.sync_api import sync_playwright
root=Path(__file__).resolve().parents[2]
css='\n'.join((root/f).read_text() for f in ['assets/css/tokens.css','style.css','assets/css/components/homepage-law-01.css','assets/css/components/homepage-law-01-variants.css','assets/css/components/homepage-law-01-reference.css'])
slots=''.join(f'<span class="aznet-theme-law01-profile__team-illustration-slot"><img class="aznet-theme-law01-profile__team-illustration-image aznet-theme-law01-profile__team-illustration-image--{i}" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1000\' height=\'320\'%3E%3Crect width=\'1000\' height=\'320\' fill=\'%23ddd\'/%3E%3C/svg%3E" alt=""></span>' for i in range(1,5))
html='<html><meta charset="utf-8"><style>'+css+'</style><body><main class="aznet-theme-homepage aznet-theme-homepage--law-01 aznet-theme-homepage--law-01-burgundy-gold"><section class="aznet-theme-law01-section aznet-theme-law01-hero"><div class="aznet-theme-law01-container aznet-theme-law01-hero__grid"><div class="aznet-theme-law01-hero__content"><p class="aznet-theme-law01-hero__brand">Example practice</p><h1>Clear legal solutions for individuals and businesses</h1></div><div class="aznet-theme-law01-hero__visual"></div></div></section><section class="aznet-theme-law01-section aznet-theme-law01-profile"><div class="aznet-theme-law01-container aznet-theme-law01-profile__container"><div class="aznet-theme-law01-profile__about-grid"><h2>About</h2></div><div class="aznet-theme-law01-profile__team-band"><h2>Team</h2><figure class="aznet-theme-law01-profile__team-illustration"><div class="aznet-theme-law01-profile__team-illustration-grid" aria-hidden="true">'+slots+'</div><figcaption>Illustration only, not staff identities.</figcaption></figure></div></div></section></main></body></html>'
results=[];failures=[]
with sync_playwright() as p:
    opts={'headless':True,'args':['--no-sandbox']};binary=os.getenv('CHROMIUM_PATH','/usr/bin/chromium')
    if Path(binary).is_file(): opts['executable_path']=binary
    browser=p.chromium.launch(**opts)
    for width in [1440,1024,390,320]:
        page=browser.new_page(viewport={'width':width,'height':1000});page.set_content(html)
        m=page.evaluate("""() => {const q=s=>document.querySelector(s),qa=s=>[...document.querySelectorAll(s)],g=s=>getComputedStyle(q(s)); const slots=qa('.aznet-theme-law01-profile__team-illustration-slot').map(n=>n.getBoundingClientRect()); const imgs=qa('.aznet-theme-law01-profile__team-illustration-image').map(n=>n.getBoundingClientRect()); return {width:innerWidth,overflow:document.documentElement.scrollWidth-innerWidth,heroLeading:parseFloat(g('h1').lineHeight)/parseFloat(g('h1').fontSize),figureLeft:parseFloat(g('figure').marginLeft),figureRight:parseFloat(g('figure').marginRight),slotRows:new Set(slots.map(x=>Math.round(x.y))).size,slots:slots.map(x=>({w:x.width,h:x.height})),imgs:imgs.map(x=>({w:x.width,h:x.height}))};}""")
        results.append(m)
        if width>960 and not 1<=m['heroLeading']<1.05: failures.append(f'{width}: Hero leading is not editorially compact')
        if m['figureLeft']!=0 or m['figureRight']!=0: failures.append(f'{width}: editorial media has browser-default side indents')
        if len(m['slots'])!=4 or len(m['imgs'])!=4: failures.append(f'{width}: expected four montage slots')
        for idx,(slot,img) in enumerate(zip(m['slots'],m['imgs']),1):
            ratio=img['w']/slot['w'] if slot['w'] else 0
            if ratio<3.9 or ratio>4.05: failures.append(f'{width}: slot {idx} crop width ratio {ratio:.3f}')
            if abs((slot['h']/slot['w'])-1.25)>0.03: failures.append(f'{width}: slot {idx} must keep portrait 4:5 aspect')
        expected_rows=1 if width>640 else 2
        if m['slotRows']!=expected_rows: failures.append(f'{width}: expected {expected_rows} montage row(s), got {m["slotRows"]}')
        if m['overflow']>1: failures.append(f'{width}: horizontal overflow')
        page.close()
    browser.close()
print(json.dumps({'scope':'isolated CSS regression, not pilot screenshot','cases':results,'failures':failures},indent=2))
sys.exit(1 if failures else 0)
