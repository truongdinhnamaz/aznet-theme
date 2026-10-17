import assert from 'node:assert/strict';
import test from 'node:test';
import { parsePilotTargets } from './r6-public-pilot-vitals.mjs';

test('accepts up to three distinct HTTPS targets on the exact approved host', () => {
  assert.deepEqual(parsePilotTargets(
    'https://remquocanh.vn/rem-vai-2-lop/, https://remquocanh.vn/rem-voan/',
    'remquocanh.vn',
  ), ['https://remquocanh.vn/rem-vai-2-lop/', 'https://remquocanh.vn/rem-voan/']);
});

test('rejects credential-bearing, off-host or non-HTTPS requests', () => {
  for (const input of [
    'https://remquocanh.vn.evil.test/path',
    'http://remquocanh.vn/path',
    'https://user:secret@remquocanh.vn/path',
    'https://remquocanh.vn/path?key=private',
  ]) assert.throws(() => parsePilotTargets(input, 'remquocanh.vn'));
});

test('does not run when the allowlist is missing, input is empty or target count exceeds limit', () => {
  assert.throws(() => parsePilotTargets('https://remquocanh.vn/', ''));
  assert.throws(() => parsePilotTargets('', 'remquocanh.vn'));
  const four = ['/a/', '/b/', '/c/', '/d/'].map(x => 'https://remquocanh.vn' + x).join(',');
  assert.throws(() => parsePilotTargets(four, 'remquocanh.vn'));
});
