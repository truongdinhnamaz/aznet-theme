"""Match actual CSS selectors/cascade against native-block controls, without a browser.

This checks selector scope/declarations only; it is not a rendered layout or WP runtime test.
"""
import json
import re
import sys
import hashlib
from pathlib import Path
from xml.etree import ElementTree

import cssselect2
import tinycss2

ROOT = Path(__file__).resolve().parents[2]
SOURCE_PATH = Path(sys.argv[1]) if len(sys.argv) > 1 else ROOT / 'assets/css/components/page-wellness-01.css'
SOURCE = SOURCE_PATH.read_text()
HTML = '''<html><body><main><article class="aznet-theme-page aznet-theme-page--standard"><section>
<div id="standard" class="aznet-theme-page__content aznet-theme-entry__content">
<div id="full-a" class="wp-block-group has-background alignfull"/>
<div id="full-b" class="wp-block-group has-background alignfull"/>
<div id="solo-after-full" class="wp-block-group has-background"/><h2/>
<div id="pair-a" class="wp-block-group has-background"/><div id="pair-b" class="wp-block-group has-background"/>
<figure id="landscape" class="wp-block-image"/>
<div class="wp-block-columns"><div class="wp-block-column"><figure id="column-image" class="wp-block-image"/>
<figure id="column-wide" class="wp-block-image alignwide"/><figure id="column-left" class="wp-block-image alignleft"/></div></div>
<div id="top" class="wp-block-columns are-vertically-aligned-top"><div class="wp-block-column"><figure class="wp-block-image"/></div></div>
<div id="bottom" class="wp-block-columns are-vertically-aligned-bottom"><div class="wp-block-column"><figure class="wp-block-image"/></div></div>
</div></section></article>
<article class="aznet-theme-page aznet-theme-page--landing"><div id="landing" class="aznet-theme-page__content"/></article>
</main></body></html>'''


def matcher_at(width):
    matcher = cssselect2.Matcher()

    def add(rules):
        for rule in rules:
            if rule.type == 'at-rule' and rule.lower_at_keyword == 'media':
                query = tinycss2.serialize(rule.prelude)
                bounds = re.findall(r'(min|max)-width:\s*([\d.]+)(rem|px)', query)
                if all(width >= float(v) * (16 if unit == 'rem' else 1) if side == 'min'
                       else width <= float(v) * (16 if unit == 'rem' else 1) for side, v, unit in bounds):
                    add(tinycss2.parse_rule_list(rule.content, skip_whitespace=True, skip_comments=True))
            elif rule.type == 'qualified-rule':
                declarations = [d for d in tinycss2.parse_declaration_list(rule.content, skip_whitespace=True, skip_comments=True) if d.type == 'declaration']
                for selector in cssselect2.compile_selector_list(tinycss2.serialize(rule.prelude)):
                    matcher.add_selector(selector, declarations)

    add(tinycss2.parse_stylesheet(SOURCE, skip_whitespace=True, skip_comments=True))
    return matcher


def declarations(width):
    matcher = matcher_at(width)
    result = {}
    root = cssselect2.ElementWrapper.from_html_root(ElementTree.fromstring(HTML))
    for element in root.iter_subtree():
        identity = element.etree_element.get('id')
        if not identity:
            continue
        winning = {}
        for specificity, order, _pseudo, rules in matcher.match(element):
            for index, declaration in enumerate(rules):
                rank = (declaration.important, specificity, order, index)
                if declaration.lower_name not in winning or rank >= winning[declaration.lower_name][0]:
                    winning[declaration.lower_name] = rank, tinycss2.serialize(declaration.value).strip()
        result[identity] = {key: value[1] for key, value in winning.items()}
    return result


records = []
for width in [1440, 768, 390]:
    values = declarations(width)
    failures = []
    check = lambda condition, message: failures.append(message) if not condition else None
    for identity in ['full-a', 'full-b', 'solo-after-full']:
        check(values[identity].get('grid-column') == '1 / -1', f'{identity} incorrectly paired with a full-width Group')
    for identity in ['pair-a', 'pair-b']:
        check(values[identity].get('grid-column') == ('span 1' if width >= 1024 else '1 / -1'), f'{identity} incorrect desktop/mobile span')
    for identity in ['landscape', 'column-wide', 'column-left']:
        check(values[identity].get('max-width') != '22.5rem', f'{identity} incorrectly capped as portrait')
    check(values['column-image'].get('max-width') == '22.5rem', 'normal image column lost bounded size')
    for identity in ['top', 'bottom']:
        check('align-items' not in values[identity], f'authored {identity} alignment overridden')
    check(values['landing'].get('display') != 'grid', 'Landing received inner-Page grid')
    records.append({'width': width, 'declarations': values, 'failures': failures})
print(json.dumps({'scope': 'static selector/cascade controls only; no rendered browser claim',
                  'css_sha256': hashlib.sha256(SOURCE.encode()).hexdigest(),
                  'tools': {'cssselect2': cssselect2.__version__, 'tinycss2': tinycss2.__version__},
                  'records': records}, indent=2))
raise SystemExit(1 if any(record['failures'] for record in records) else 0)
