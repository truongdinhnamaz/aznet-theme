/**
 * Public Page inventory. Read-only published URLs and their authored CSS markers.
 * Absence of a matching Theme CSS selector is a REVIEW SIGNAL, not proof of
 * an unstyled frontend (site-local CSS may exist).
 */
const fs = require('node:fs');
const root = 'https://remquocanh.vn';
const output = 'rqa-page-diagnostics';
const get = async url => {
  const signal = AbortSignal.timeout(25000);
  const response = await fetch(url, { signal, headers: { Accept: 'application/json' } });
  return { response, text: await response.text() };
};

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const sourceFiles = [
    'assets/css/presets/curtain-01.css',
    'assets/css/components/page.css',
    'assets/css/components/generic-content.css'
  ];
  const css = sourceFiles.map(file => fs.readFileSync(file, 'utf8')).join('\n');
  const api = root + '/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,link,title,template,status';
  const {response, text} = await get(api);
  if (!response.ok) throw new Error('Public Page inventory API HTTP ' + response.status);
  const all = JSON.parse(text);
  if (!Array.isArray(all)) throw new Error('Public Page inventory payload malformed');
  const limit = 25;
  const pages = all.slice(0, limit);
  const records = [];
  for (const item of pages) {
    const record = { id: item.id, slug: item.slug, link: item.link, template: item.template };
    try {
      const fetched = await get(item.link);
      record.status = fetched.response.status;
      const html = fetched.text;
      const main = html.match(/<main\b[^>]*>[\s\S]*?<\/main>/i);
      record.mainExists = Boolean(main);
      const markup = main ? main[0] : '';
      const classMatches = [...markup.matchAll(/\bclass=["']([^"']+)["']/gi)]
        .flatMap(match => match[1].split(/\s+/))
        .filter(cls => /^rqa-[a-z0-9-]+$/i.test(cls));
      const classes = [...new Set(classMatches)];
      record.rqaClasses = classes.length;
      record.unmatchedClasses = classes.filter(name =>
        !css.includes('.' + name) && !css.includes('--' + name)
      ).slice(0, 35);
      record.rootGroups = [...new Set(classes.map(name => name.split('-').slice(0,2).join('-')))];
      record.hasCustomCss = /id=['"]wp-custom-css['"]/.test(html);
      const tag = (markup.match(/<h1\b/gi) || []).length;
      record.h1 = tag;
    } catch (error) {
      record.error = String(error.message).slice(0,300);
    }
    records.push(record);
    console.log('RQA_PAGE_INVENTORY ' + JSON.stringify(record));
  }
  const report = { site: root, observedAt: new Date().toISOString(),
    publicPagesReturned: all.length, checked: pages.length, limit,
    coverage: all.length <= limit ? 'all-returned' : 'capped', records };
  fs.writeFileSync(output + '/inventory.json', JSON.stringify(report, null, 2) + '\n');
  console.log('RQA_PAGE_INVENTORY_SUMMARY ' + JSON.stringify({
    publicPagesReturned: all.length, checked: pages.length, coverage: report.coverage,
    candidates: records.filter(item => item.unmatchedClasses?.length > 0).length
  }));
})().catch(error => { console.error('RQA_PUBLIC_INVENTORY_BLOCKED: ' + error.message); process.exitCode = 1; });
