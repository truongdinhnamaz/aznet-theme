const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../browser/homepage-law-01-l4.mjs'), 'utf8');
const match = source.match(/const invalidHeading = headingMetrics\.find\((.*)\);/);
assert.ok(match, 'The browser acceptance predicate must exist.');
const invalid = vm.runInNewContext(match[1]);
const base = {height: 36.28125, lineHeight: 36.288, fontSize: 30.24, whiteSpace: 'normal', clientWidth: 600, scrollWidth: 600};
assert.equal(invalid(base), false, 'A visibly single-line fitting heading must pass even with white-space:normal.');
assert.equal(invalid({...base, whiteSpace: 'nowrap', scrollWidth: 900}), true, 'nowrap with overflow must fail.');
assert.equal(invalid({...base, height: 72.576}), true, 'A two-line desktop heading must fail.');
assert.equal(invalid({...base, height: 0}), true, 'A hidden heading must fail.');
assert.equal(invalid({...base, lineHeight: NaN}), true, 'Unmeasurable geometry must fail closed.');
console.log('PASS: desktop heading geometry predicate, five cases');

// The new reference uses 1.16 leading; reject collapsed or excessively loose text.
const leadingMatch = source.match(/const validHeroLeading = (.*);/);
const legacyLeading = source.match(/if \(!\((heroTitleLine \/ heroTitleFont < 1\.05)\)\)/);
assert.ok(leadingMatch || legacyLeading, 'Hero leading acceptance must exist.');
const validLeading = (heroTitleRatio) => vm.runInNewContext(leadingMatch ? leadingMatch[1] : legacyLeading[1], {heroTitleRatio, heroTitleLine: heroTitleRatio * 40, heroTitleFont: 40});
assert.equal(validLeading(1.16), true, 'The approved reference leading must be accepted.');
assert.equal(validLeading(0.7), false, 'Overlapping lines must fail.');
assert.equal(validLeading(1.5), false, 'Overly loose leading must fail.');
assert.equal(validLeading(NaN), false, 'Unknown leading must fail.');
console.log('PASS: reference Hero leading, four cases');
