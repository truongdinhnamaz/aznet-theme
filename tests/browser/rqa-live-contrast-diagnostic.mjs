import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const targets = [
  ['collection', 'https://remquocanh.vn/bo-suu-tap/'],
  ['knowledge', 'https://remquocanh.vn/kien-thuc/'],
];

const browser = await chromium.launch({ headless: true });
for (const [name, url] of targets) {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  await page.goto(url, { waitUntil: 'networkidle', timeout: 120000 });
  const results = await new AxeBuilder({ page }).withRules(['color-contrast']).analyze();
  console.log(JSON.stringify({
    name,
    url,
    violations: results.violations.map((v) => ({
      id: v.id,
      impact: v.impact,
      description: v.description,
      nodes: v.nodes.map((n) => ({
        target: n.target,
        html: n.html,
        failureSummary: n.failureSummary,
      })),
    })),
  }, null, 2));
  await page.close();
}
await browser.close();
