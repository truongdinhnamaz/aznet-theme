import assert from 'node:assert/strict';
import {
  previewTokenFromEnv, requestedAuditUrl, samePreviewRoute,
  auditedBodyClass, redactReportSecrets,
} from '../browser/industrial01-preview-route-guard.mjs';

const base = 'https://anpvietnam.vn/xe-nang-cu-nhat-ban/';
const token = 'AznetPreviewToken_20261101';
assert.equal(previewTokenFromEnv(''), '');
assert.equal(previewTokenFromEnv(token), token);
for (const invalid of ['short', 'aaa?bbcccccccccccc', 'aaa&other=evil', 'a'.repeat(129), 'ab cd 1234567890', 'x%2520abcdefghij']) {
  assert.throws(() => previewTokenFromEnv(invalid), /preview token/i);
}
assert.equal(requestedAuditUrl(base, ''), base);
const requested = requestedAuditUrl(base, token);
assert.equal(new URL(requested).pathname, new URL(base).pathname);
assert.equal(new URL(requested).searchParams.get('wpvibe_preview'), token);
assert.equal(new URL(requested).searchParams.size, 1);
assert.equal(samePreviewRoute(requested, requested, token), true);
assert.equal(samePreviewRoute(requested, requested.replace('/xe-nang-cu-nhat-ban/', '/xe-nang-cu-nhat-ban'), token), true);
for (const wrong of [
  base,
  'https://anpvietnam.vn/',
  'https://anpvietnam.vn/xe-nang-cu-nhat-ban/?wpvibe_preview=DifferentToken_20261101',
  requested + '&debug=1',
  requested + '#hidden',
  'https://not-anpvietnam.vn/xe-nang-cu-nhat-ban/?wpvibe_preview=' + token,
]) {
  assert.equal(samePreviewRoute(requested, wrong, token), false, wrong);
}
assert.equal(samePreviewRoute(requested, requested, ''), false);
assert.equal(auditedBodyClass(''), 'wp-theme-aznet-theme');
assert.equal(auditedBodyClass(token), 'wp-theme-aznet-theme-wpvibe-draft');
assert.throws(() => requestedAuditUrl(base + '?unexpected=1', token), /query|public/i);
assert.throws(() => requestedAuditUrl('http://other.example/secret', token), /https|public/i);
const record = {
  base: 'https://anpvietnam.vn/',
  previewMode: true,
  views: [{ finalUrl: requested, html: `<a href="${requested}">link</a>`, note: `token=${token}` }],
};
const log = redactReportSecrets(record, token);
assert.equal(log.includes(token), false);
assert.equal(log.includes('wpvibe_preview=[REDACTED_PREVIEW_TOKEN]'), true);
assert.equal(log.includes('"previewMode": true'), true);
assert.equal(JSON.parse(log).views.length, 1);
assert.equal(redactReportSecrets({x: 1}, ''), '{\n  "x": 1\n}\n');
console.log('PASS: preview route whitelist, draft identity and token-redacted report');
