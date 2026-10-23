"""Isolated browser regression for equal-weight Law 01 Latest cards. Not a live-site screenshot."""
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
        "assets/css/components/homepage.css",
        "assets/css/components/homepage-law-01.css",
        "assets/css/components/homepage-law-01-variants.css",
        "assets/css/components/homepage-law-01-reference.css",
    ]
)
cards = "".join(
    f"""<article class="aznet-theme-law01-article-card">
      <div class="aznet-theme-law01-article-card__media"><a class="aznet-theme-law01-article-card__media-link" href="#p{i}"><img class="aznet-theme-law01-article-card__image" width="600" height="400" alt="" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='400'%3E%3Crect width='600' height='400' fill='%23ddd'/%3E%3C/svg%3E"></a></div>
      <div class="aznet-theme-law01-article-card__body"><div class="aznet-theme-law01-article-card__meta"><span>Knowledge</span><time>20/09/2026</time></div><h3 id="p{i}"><a href="#p{i}">Example legal article {i}</a></h3><p>Short source-backed excerpt for a legal knowledge article card.</p><a class="aznet-theme-law01-text-link" href="#p{i}">Read more</a></div>
    </article>"""
    for i in range(1, 4)
)
html = """<!doctype html><html><meta charset="utf-8"><style>""" + css + """</style><body>
<main class="aznet-theme-homepage aznet-theme-homepage--law-01 aznet-theme-homepage--law-01-burgundy-gold">
<section class="aznet-theme-law01-section aznet-theme-law01-articles"><div class="aznet-theme-law01-container">
<div class="aznet-theme-law01-section-heading"><div><p class="aznet-theme-law01-eyebrow">Knowledge</p><h2>Latest articles</h2></div></div>
<div class="aznet-theme-law01-grid aznet-theme-law01-grid--articles">""" + cards + """</div>
</div></section></main></body></html>"""

cases, failures = [], []
with sync_playwright() as p:
    opts = {"headless": True, "args": ["--no-sandbox"]}
    binary = os.getenv("CHROMIUM_PATH", "/usr/bin/chromium")
    if Path(binary).is_file():
        opts["executable_path"] = binary
    browser = p.chromium.launch(**opts)
    for width in [1440, 1024, 390, 320]:
        page = browser.new_page(viewport={"width": width, "height": 1000})
        page.set_content(html)
        metric = page.evaluate("""() => {
          const cards=[...document.querySelectorAll('.aznet-theme-law01-article-card')];
          const titles=cards.map(c=>c.querySelector('h3'));
          return {
            width: innerWidth,
            overflow: document.documentElement.scrollWidth-innerWidth,
            titleSizes: titles.map(n=>parseFloat(getComputedStyle(n).fontSize)),
            cardWidths: cards.map(n=>n.getBoundingClientRect().width),
            cardRows: new Set(cards.map(n=>Math.round(n.getBoundingClientRect().y))).size,
          };
        }""")
        cases.append(metric)
        if metric["overflow"] > 1:
            failures.append(f"{width}: horizontal overflow")
        if max(metric["titleSizes"]) - min(metric["titleSizes"]) > 0.5:
            failures.append(f"{width}: Latest title hierarchy diverged: {metric['titleSizes']}")
        if width >= 1024 and metric["cardRows"] != 1:
            failures.append(f"{width}: expected three Latest cards in one row")
        if width >= 1024 and max(metric["cardWidths"]) - min(metric["cardWidths"]) > 1:
            failures.append(f"{width}: Latest card widths are not equal")
        page.close()
    browser.close()

print(json.dumps({"scope":"isolated Latest-card browser regression, not live WordPress","cases":cases,"failures":failures}, indent=2))
sys.exit(1 if failures else 0)
