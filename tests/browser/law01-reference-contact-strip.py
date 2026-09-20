"""Isolated browser regression for the Law 01 Hero contact rail. Not a live-site screenshot."""
from pathlib import Path
import json
import os
import sys
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[2]
css = "\n".join(
    (ROOT / f).read_text()
    for f in [
        "assets/css/tokens.css",
        "style.css",
        "assets/css/components/homepage-law-01.css",
        "assets/css/components/homepage-law-01-variants.css",
        "assets/css/components/homepage-law-01-reference.css",
    ]
)
html = """<!doctype html><html><meta charset="utf-8"><style>""" + css + """</style>
<body><main class="aznet-theme-homepage aznet-theme-homepage--law-01 aznet-theme-homepage--law-01-burgundy-gold">
<section class="aznet-theme-law01-section aznet-theme-law01-hero">
<div class="aznet-theme-law01-container aznet-theme-law01-hero__grid">
<div class="aznet-theme-law01-hero__content">
<p class="aznet-theme-law01-eyebrow">Law office</p>
<p class="aznet-theme-law01-hero__brand">EXAMPLE BRAND</p>
<h1>Clear legal support</h1>
<nav class="aznet-theme-law01-hero__contact-nav" aria-label="Quick contact">
<ul class="aznet-theme-law01-hero__contact-list">
<li><a href="/contact">62 Example Street</a></li>
<li><a href="tel:+842400000000">024 0000 0000</a></li>
<li><a href="mailto:hello@example.test">hello@example.test</a></li>
</ul>
</nav>
</div>
<div class="aznet-theme-law01-hero__visual"></div>
</div></section></main></body></html>"""

failures, cases = [], []
with sync_playwright() as p:
    opts = {"headless": True, "args": ["--no-sandbox"]}
    binary = os.getenv("CHROMIUM_PATH", "/usr/bin/chromium")
    if Path(binary).is_file():
        opts["executable_path"] = binary
    browser = p.chromium.launch(**opts)
    for width in [1440, 390, 320]:
        page = browser.new_page(viewport={"width": width, "height": 900})
        page.set_content(html)
        metric = page.evaluate("""() => {
          const list=document.querySelector('.aznet-theme-law01-hero__contact-list');
          const links=[...list.querySelectorAll('a')];
          const rows=new Set(links.map(a=>Math.round(a.getBoundingClientRect().y))).size;
          const cols=getComputedStyle(list).gridTemplateColumns.split(' ').filter(Boolean).length;
          const icons=links.map(a=>getComputedStyle(a,'::before'));
          return {
            width: innerWidth,
            overflow: document.documentElement.scrollWidth-innerWidth,
            rows,
            cols,
            minHeights: links.map(a=>a.getBoundingClientRect().height),
            iconContents: icons.map(x=>x.content),
            iconWidths: icons.map(x=>parseFloat(x.width)),
          };
        }""")
        cases.append(metric)
        if metric["overflow"] > 1:
            failures.append(f"{width}: horizontal overflow")
        if width == 1440:
            if metric["cols"] != 3 or metric["rows"] != 1:
                failures.append(f"{width}: expected one three-column row, got cols={metric['cols']} rows={metric['rows']}")
        else:
            if metric["cols"] != 1 or metric["rows"] != 3:
                failures.append(f"{width}: expected three stacked contact rows, got cols={metric['cols']} rows={metric['rows']}")
        if any(h < 55 for h in metric["minHeights"]):
            failures.append(f"{width}: contact target height below intended 3.5rem rail")
        if any(content in ("none", "normal", '""') for content in metric["iconContents"]):
            failures.append(f"{width}: missing decorative contact icon")
        if any(w < 16 for w in metric["iconWidths"]):
            failures.append(f"{width}: contact icon rail too small")
        page.close()
    browser.close()

print(json.dumps({"scope":"isolated contact-strip browser regression, not live WordPress","cases":cases,"failures":failures}, indent=2))
sys.exit(1 if failures else 0)
