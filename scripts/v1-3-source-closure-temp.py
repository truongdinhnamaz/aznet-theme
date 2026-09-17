from pathlib import Path

BASE = 'afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e'
TREE = '020db34ca6f360d5af12e6f15abf4c9ba51179f0'
PR_HEAD = '441b5fdbac3847b8c251b6c492a72883a4e31c7e'
PACKAGE_SHA = '4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e'


def read(path):
    return Path(path).read_text(encoding='utf-8')


def write(path, text):
    Path(path).write_text(text, encoding='utf-8')


def replace_once(text, old, new, label):
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{label}: expected 1 occurrence, found {count}')
    return text.replace(old, new, 1)


evidence = f'''# AZnet Theme v1.3 Technical Closure — 17/09/2026

**State:** **TECHNICAL PASS / PUBLICATION GATED**

## Scope

This checkpoint closes the technical implementation and exact-main verification of **AZnet Theme v1.3 Client Delivery System**. It does not publish an annotated `v1.3.0` tag, create a GitHub Release, deploy to production, or claim provider L5 certification.

Ownership remains unchanged: WordPress owns Page/Post/Menu/Media/query state and lifecycle; AZnet Theme owns presentation/composition and bounded consumer adapters only. No private provider storage/API read, copied domain engine, or mandatory third-party runtime dependency is introduced.

## Canonical merge

- PR #98: `test: close v1.3 client delivery candidate`
- exact PR head: `{PR_HEAD}`
- PR-head tree: `{TREE}`
- owner-approved merge commit / canonical `main`: `{BASE}`
- canonical merge tree: `{TREE}`
- Theme metadata: `1.3.0` in both `style.css` and `AZNET_THEME_VERSION`

The exact PR head and merge commit share the same tree, so the owner-approved merge introduced no content drift.

## Pre-merge promoted Y5 evidence

Exact promoted PR head `{PR_HEAD}` completed the full pull-request verification set successfully. The dedicated **Y5 Client Delivery Release** run `35205992836` passed all three jobs: promoted ownership/static closure, WordPress 6.9 / PHP 8.1 integrated runtime-browser coverage, and deterministic exact-package lifecycle verification.

## Exact-main evidence

### V1 Exact Main Verification

Run `35206627102` — **SUCCESS** on exact `main@{BASE}`.

- `static-contracts`: SUCCESS
- `clean-runtime-browser`: SUCCESS
- static artifact `10489329396`, digest `sha256:65a833786903e45d8778ebcff28a4015516762ca83379d081ec00f5303a7e80b`
- runtime/browser artifact `10489294556`, digest `sha256:150fa55cea948236d59ee05cbddb68863e2b4a67cd856a50546d2dfeb5c1a082`

### Equivalent exact-main Y5 release path

Task 10 permits the Y5 workflow on the merge SHA **or an equivalent explicit exact-main dispatch path defined before merge**. The pre-existing push-to-main **X6 Cross-surface Release Closure** workflow provides that exact-main release path and ran on the merge SHA as run `35206626992` — **SUCCESS**.

Exact-main X6 evidence:

- checkout SHA `{BASE}` / tree `{TREE}`
- WordPress `6.9`, PHP `8.1`, zero active third-party plugins
- Theme metadata `1.3.0` consistent
- retained Core plus Y1/Y2/Y3/Y4/Y5 contracts PASS
- integrated browser/axe matrix: **32/32 PASS**
- deterministic package built twice: `aznet-theme-1.3.0.zip`
- package file count: **144**
- package SHA-256: `{PACKAGE_SHA}`
- exact package/source identity: PASS
- packaged PHP lint: **107 files PASS**
- switch to Twenty Twenty-Five preserved WordPress-owned content; switch back to AZnet Theme and repeated smoke: PASS
- runtime error boundary: PASS
- artifact `10489369655`, digest `sha256:92bb97dc6640040ef0a9e8b77a1261a4a77df75ae1354d50c4586277d5d1ddec`

## Release boundary

The canonical repository technical state is now **v1.3 TECHNICAL PASS / PUBLICATION GATED**. The currently published and production-deployed release remains **v1.2.0** until separate owner approvals change those states.

Provider L5 remains separate and unclaimed. RootProfile/ConvertFlow/WooCommerce domain ownership is unchanged.

## Exact next

Request separate product-owner approval for **v1.3.0 publication disposition — annotated tag + GitHub Release only**. Production deployment remains a later, separate approval gate.
'''
evidence_path = Path('docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md')
if evidence_path.exists():
    raise SystemExit('evidence file unexpectedly already exists')
evidence_path.write_text(evidence, encoding='utf-8')

# AZT-03: provenance owner.
path = 'docs/source/AZT-03-baseline-provenance.md'
text = read(path)
text = replace_once(text, '**Version:** v0.41', '**Version:** v0.42', 'AZT-03 version')
text = replace_once(
    text,
    '- Canonical `main` is Theme version `1.2.0` after X6 technical integration and docs-only source closure.',
    f'- Canonical repository technical baseline is Theme version `1.3.0` at owner-approved PR #98 merge `main@{BASE}`, tree `{TREE}`; v1.3 is TECHNICAL PASS / PUBLICATION GATED.',
    'AZT-03 canonical baseline',
)
floor = '- WordPress floor: `6.9+`.'
technical = f'''- v1.3 exact-main verification: V1 Exact Main run `35206627102` SUCCESS plus pre-existing X6 push-to-main release path run `35206626992` SUCCESS on `{BASE}`; exact `aznet-theme-1.3.0.zip` SHA-256 `{PACKAGE_SHA}`, 144 package files, 107 packaged PHP lint PASS, 32/32 browser/axe PASS, switch-away/switch-back lifecycle PASS. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.
- Published/GitHub-Release and production-deployed release remain `v1.2.0`; v1.3 publication and deployment are separate future owner gates. Provider L5 remains unproven.'''
text = replace_once(text, floor, technical + '\n' + floor, 'AZT-03 technical provenance insertion')
write(path, text)

# AZT-04: roadmap / release-gate owner.
path = 'docs/source/AZT-04-roadmap-qa-decisions.md'
text = read(path)
text = replace_once(text, '**Version:** v0.53', '**Version:** v0.54', 'AZT-04 version')
old_row = '| Y | v1.3 Client Delivery System | RATIFIED / Y1 READY | Approved design + implementation plan define Y1 Page Experience 2.0 -> Y2 Professional Page Kits -> Y3 Footer System 2.0 -> Y4 Professional Services Starter Blueprint -> Y5 Client Delivery QA & Release Closure; Theme stays presentation owner, WordPress keeps native content/state, provider L5 remains separate |'
new_row = f'| Y | v1.3 Client Delivery System | TECHNICAL PASS / PUBLICATION GATED | Y1-Y5 merged through owner-approved PR #98 at `main@{BASE}`; fresh exact-main V1 + X6 release-path verification PASS; exact deterministic `aznet-theme-1.3.0.zip` SHA-256 `{PACKAGE_SHA}`; publication/deployment remain separate owner gates; provider L5 remains separate |'
text = replace_once(text, old_row, new_row, 'AZT-04 Y row')
marker = '## 14. Exact next'
if text.count(marker) != 1:
    raise SystemExit(f'AZT-04 exact-next heading count={text.count(marker)}')
prefix, tail = text.split(marker, 1)
if '\n## ' in tail:
    raise SystemExit('AZT-04 exact-next is not final section; refusing broad rewrite')
checkpoint = f'''### v1.3 technical closure checkpoint — 17/09/2026

Owner-approved PR #98 merged Y5/v1.3 to canonical `main@{BASE}`, tree `{TREE}`. Fresh exact-main V1 run `35206627102` and the pre-existing X6 push-to-main release path run `35206626992` both completed SUCCESS. The exact main package is deterministic `aznet-theme-1.3.0.zip`, SHA-256 `{PACKAGE_SHA}`, with 144 files, 107 packaged PHP files linted, 32/32 browser/axe cases PASS and switch-away/switch-back lifecycle continuity PASS. This closes D-029 implementation technically without changing domain ownership or claiming provider L5. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.

The published and production-deployed release remains `v1.2.0`. v1.3 publication and production deployment are not implied by technical closure.

'''
prefix = prefix.rstrip() + '\n\n' + checkpoint
text = prefix + '''## 14. Exact next

**NEXT — request separate product-owner approval for v1.3.0 publication disposition: annotated tag + GitHub Release only. Do not publish or deploy without that approval. Production deployment remains a later separate owner gate; provider L5 remains separate and unclaimed.**
'''
write(path, text)

# Derived execution map.
path = 'docs/source/AZT-EXEC-MAP.md'
text = read(path)
text = replace_once(text, '**Version:** v0.45', '**Version:** v0.46', 'EXEC version')
old_completed = 'Current state: `v1.2.0 PUBLICATION + PRODUCTION DEPLOYMENT PASS`; v1.3 Client Delivery System is source-ratified and Y1-ready after this source slice merges.'
new_completed = f'Current repository technical state: `v1.3 TECHNICAL PASS / PUBLICATION GATED` at `main@{BASE}`, tree `{TREE}`. Published and production-deployed release remains `v1.2.0`; v1.3 publication and production deployment remain separate owner gates.'
text = replace_once(text, old_completed, new_completed, 'EXEC top current state')
old_row = '| Y v1.3 Client Delivery System | RATIFIED / Y1 READY | Y1 Page Experience -> Y2 Page Kits -> Y3 Footer -> Y4 generic Professional Services provisioning -> Y5 integrated delivery/release closure; WordPress-native data ownership retained; metadata remains `1.2.0` until Y5 owner gate |'
new_row = f'| Y v1.3 Client Delivery System | TECHNICAL PASS / PUBLICATION GATED | Y1-Y5 merged through PR #98; exact-main V1 + X6 release-path PASS at `{BASE}`; Theme `1.3.0`; deterministic package SHA-256 `{PACKAGE_SHA}`; publication/deployment remain separate gates; provider L5 stays separate |'
text = replace_once(text, old_row, new_row, 'EXEC Y row')
old_canonical = 'Canonical `main` is exact Theme `1.2.0` after owner-approved X6 technical integration. GitHub publication `v1.2.0` is PASS with exact verified package bytes, and owner-approved production deployment of those published `1.2.0` bytes is PASS at the verified Theme-owned/site-operations scope.'
new_canonical = f'Canonical repository technical baseline is exact Theme `1.3.0` at owner-approved PR #98 merge `main@{BASE}`, tree `{TREE}`. Fresh exact-main V1 + X6 release-path verification is PASS. GitHub publication and production deployment remain at verified `v1.2.0` until separate v1.3 owner approvals.'
text = replace_once(text, old_canonical, new_canonical, 'EXEC canonical state')

heading = '## 13A. v1.3 Client Delivery System'
start = text.find(heading)
if start < 0:
    raise SystemExit('EXEC v1.3 section missing')
next_heading = text.find('\n## 14.', start)
if next_heading < 0:
    raise SystemExit('EXEC v1.3 section boundary missing')
section = text[start:next_heading]
section = replace_once(section, '**State:** **RATIFIED / Y1 READY.**', '**State:** **TECHNICAL PASS / PUBLICATION GATED.**', 'EXEC v1.3 state')
old_next = '**Exact Next:** after source-ratification merge, execute Y1 only; do not start Y2 before Y1 exit is evidenced unless a bounded dependency change is required.'
new_next = '**Exact Next:** request separate owner approval for `v1.3.0` annotated-tag + GitHub-Release publication only. Production deployment remains a later separate gate; provider L5 remains separate.'
section = replace_once(section, old_next, new_next, 'EXEC v1.3 exact next')
y5_exit = '**Y5 exit:** zero-plugin clean-site client-delivery matrix, deterministic exact package/source identity, packaged PHP lint, update/theme-switch/rollback, exact-final-head and exact-main gates. Promotion to `1.3.0`, tag/Release and production deploy remain separate owner approvals.'
closure = f'''{y5_exit}

**Y1-Y5 technical closure:** PASS. PR #98 merged to `main@{BASE}`, tree `{TREE}`. V1 Exact Main `35206627102` and X6 exact-main release path `35206626992` SUCCESS; `aznet-theme-1.3.0.zip` SHA-256 `{PACKAGE_SHA}`, 144 files, 107 packaged PHP lint PASS, 32/32 browser/axe PASS, lifecycle continuity PASS. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.'''
section = replace_once(section, y5_exit, closure, 'EXEC Y5 closure')
text = text[:start] + section + text[next_heading:]

marker = '## 16. Exact next'
if text.count(marker) != 1:
    raise SystemExit(f'EXEC final exact-next heading count={text.count(marker)}')
prefix, tail = text.split(marker, 1)
if '\n## ' in tail:
    raise SystemExit('EXEC exact-next is not final section; refusing broad rewrite')
text = prefix + '''## 16. Exact next

**NEXT — v1.3 Client Delivery System is TECHNICAL PASS / PUBLICATION GATED. Request separate product-owner approval for `v1.3.0` annotated tag + GitHub Release only. Do not publish or deploy before that approval; production deployment is a later separate gate and provider L5 remains separate/unclaimed.**
'''
write(path, text)

# Source manifest.
path = 'docs/source/SOURCE_MANIFEST.md'
text = read(path)
text = replace_once(
    text,
    '| `AZT-03-baseline-provenance.md` | v0.41 | Canonical provenance through v1.2.0 publication + verified production deployment |',
    '| `AZT-03-baseline-provenance.md` | v0.42 | Canonical provenance through v1.3 technical merge + exact-main verification; v1.2.0 remains published/production |',
    'manifest AZT-03 row',
)
text = replace_once(
    text,
    '| `AZT-04-roadmap-qa-decisions.md` | v0.53 | v1.2.0 release/deployment PASS + D-029 v1.3 Client Delivery System ratified; Y1 next |',
    '| `AZT-04-roadmap-qa-decisions.md` | v0.54 | D-029 v1.3 Client Delivery System TECHNICAL PASS / PUBLICATION GATED; v1.2.0 release/deployment retained |',
    'manifest AZT-04 row',
)
text = replace_once(
    text,
    '| `AZT-EXEC-MAP.md` | v0.45 | Derived execution map with v1.3 Y1-Y5 sequence ratified and Y1 ready |',
    '| `AZT-EXEC-MAP.md` | v0.46 | Derived execution map with v1.3 Y1-Y5 technical closure and publication gate |',
    'manifest EXEC row',
)
manifest_marker = '\nApproved design companion:'
manifest_note = f'''\nv1.3 technical closure evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`. Canonical repository technical baseline is `1.3.0` at `main@{BASE}` with exact-main V1 + X6 release-path PASS and deterministic package SHA-256 `{PACKAGE_SHA}`. Published/GitHub-Release and production-deployed release remain `v1.2.0` until separate owner approvals; provider L5 remains unclaimed.\n'''
text = replace_once(text, manifest_marker, manifest_note + manifest_marker, 'manifest closure note')
write(path, text)
