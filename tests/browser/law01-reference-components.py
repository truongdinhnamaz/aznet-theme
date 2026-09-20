"""Isolated component fixture, never a substitute for live WordPress visual QA."""
from pathlib import Path
import json
import os
import sys
from playwright.sync_api import sync_playwright
ROOT = Path(__file__).resolve().parents[2]
files = ['assets/css/tokens.css', 'style.css', 'assets/css/components/site-header.css', 'assets/css/components/site-footer.css', 'assets/css/components/homepage.css', 'assets/css/components/homepage-law-01.css', 'assets/css/components/homepage-law-01-variants.css']
if '--baseline' not in sys.argv:
    files.append('assets/css/components/homepage-law-01-reference.css')
css = '\n'.join((ROOT / f).read_text() for f in files)
html = '''<!doctype html><html lang="en"><meta charset="utf-8"><style>body{font-family:var(--aznet-theme-font-family-base);font-size:16px}''' + css + '''</style><body><main class="aznet-theme-homepage aznet-theme-homepage--law-01 aznet-theme-homepage--law-01-burgundy-gold"><section class="aznet-theme-law01-hero"><div class="aznet-theme-law01-container aznet-theme-law01-hero__grid"><div class="aznet-theme-law01-hero__content"><p class="aznet-theme-law01-eyebrow">Law office</p><p class="aznet-theme-law01-hero__brand">EXAMPLE BRAND</p><h1>Clear legal solutions for individuals and businesses</h1><p class="aznet-theme-law01-hero__value">A supporting description, not a second headline.</p></div><div class="aznet-theme-law01-hero__visual"></div></div></section><section class="aznet-theme-law01-section aznet-theme-law01-profile"><div class="aznet-theme-law01-container aznet-theme-law01-profile__container"><div class="aznet-theme-law01-profile__about-grid"><h2>About the office</h2></div><div class="aznet-theme-law01-profile__team-band"><h2>Team presentation with a deliberately long heading</h2></div></div></section><section class="aznet-theme-law01-section aznet-theme-law01-articles"><div class="aznet-theme-law01-container"><div class="aznet-theme-law01-grid aznet-theme-law01-grid--articles"><article class="aznet-theme-law01-article-card"><div class="aznet-theme-law01-article-card__media"><a class="aznet-theme-law01-article-card__media-link" href="#article"><img width="512" height="341" class="aznet-theme-law01-article-card__image" style="display:block;width:100%;height:auto" alt="Component test" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='512' height='341'%3E%3Crect width='512' height='341' fill='%23ddd'/%3E%3C/svg%3E"></a></div><div class="aznet-theme-law01-article-card__body"><h3 id="article">Example article</h3><p>Public content remains editable.</p><a class="aznet-theme-law01-text-link" href="#article">Read more</a></div></article></div></div></section></main></body></html>'''
results, errors = [], []
with sync_playwright() as p:
    path = os.environ.get('CHROMIUM_PATH', '/usr/bin/chromium')
    options = {'headless': True, 'args': ['--no-sandbox']}
    if Path(path).is_file():
        options['executable_path'] = path
    browser = p.chromium.launch(**options)
    for width in [1440, 1024, 390, 320]:
        page = browser.new_page(viewport={'width': width, 'height': 1000})
        page.set_content(html)
        metric = page.evaluate('''() => {const el=s=>document.querySelector(s);const rect=s=>el(s).getBoundingClientRect();const cs=s=>getComputedStyle(el(s));return {width:innerWidth,scroll:document.documentElement.scrollWidth,font:cs('main').fontFamily,brand:parseFloat(cs('.aznet-theme-law01-hero__brand').fontSize),headline:parseFloat(cs('h1').fontSize),support:parseFloat(cs('.aznet-theme-law01-hero__value').fontSize),profileRight:rect('.aznet-theme-law01-profile__team-band').right,containerRight:rect('.aznet-theme-law01-profile__container').right,mediaRatio:rect('.aznet-theme-law01-article-card__media').width/rect('.aznet-theme-law01-article-card__media').height};}''')
        results.append(metric)
        if metric['scroll'] > width + 1: errors.append(f'{width}: horizontal overflow')
        if metric['profileRight'] > metric['containerRight'] + 1: errors.append(f'{width}: profile columns exceed container')
        if width == 1440:
            if not metric['font'].lower().startswith('roboto'): errors.append(f"1440: agreed Roboto typography missing: {metric['font']}")
            if metric['brand'] <= metric['headline']: errors.append('1440: brand is not larger than headline')
            if metric['support'] >= metric['headline'] * .7: errors.append('1440: supporting copy too large')
            if not 2.2 <= metric['mediaRatio'] <= 2.5: errors.append('1440: article image lacks reference landscape crop')
        page.close()
    browser.close()
print(json.dumps({'scope': 'isolated component fixture, NOT live WordPress', 'measurements': results, 'failures': errors}, indent=2))
sys.exit(1 if errors else 0)
