from pathlib import Path

BASE = 'afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e'
TREE = '020db34ca6f360d5af12e6f15abf4c9ba51179f0'
PACKAGE_SHA = '4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e'


def update(path, replacements):
    p = Path(path)
    text = p.read_text(encoding='utf-8')
    for old, new, label in replacements:
        count = text.count(old)
        if count != 1:
            raise SystemExit(f'{path} {label}: expected 1 occurrence, found {count}')
        text = text.replace(old, new, 1)
    p.write_text(text, encoding='utf-8')


update(
    'docs/source/SOURCE_MANIFEST.md',
    [(
        'Canonical source is reconciled through the completed v1.2 publication/production-deployment checkpoint and the approved v1.3 Client Delivery System design. v1.2 remains the published and verified production release; v1.3 is source-ratified as a WordPress-native Theme milestone with Y1-Y5 implementation still pending. Provider compatibility/certification tracks remain separate.',
        f'Canonical source is reconciled through the completed v1.3 Client Delivery System technical closure at `main@{BASE}`, tree `{TREE}`. v1.3 is TECHNICAL PASS / PUBLICATION GATED; v1.2 remains the published and verified production release until separate v1.3 publication/deployment approvals. Provider compatibility/certification tracks remain separate.',
        'manifest intro',
    )],
)

update(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    [(
        '## 10A. v1.3 Client Delivery System\n\n**State:** **RATIFIED / Y1 READY.**',
        '## 10A. v1.3 Client Delivery System\n\n**State:** **TECHNICAL PASS / PUBLICATION GATED.**',
        'AZT-04 v1.3 state',
    )],
)

old_checkpoint = '> **Canonical-main checkpoint:** live `main` HEAD is resolved from GitHub at execution time. X6 technical integration is verified at `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`, tree `70c04929afe38e87d317bad3414157e9e2bd6f74`; fresh V1 exact-main run `35129331899` and X6 push-to-main run `35129331927` completed SUCCESS. Published release anchor is annotated `v1.2.0` -> exact technical commit `c6b1ebac...`; GitHub Release `390148740` carries package SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`. Owner-approved production deployment of the exact published `1.2.0` package is verified PASS on `tamduchanoi.aznet.vn` by deployment run `35135759389` plus fresh independent read-only run `35136876330`. Optional integrations remain additive and ownership boundaries are unchanged.'
new_checkpoint = f'> **Canonical-main checkpoint:** live `main` HEAD is resolved from GitHub at execution time. The current repository technical baseline is Theme `1.3.0` at owner-approved PR #98 merge `main@{BASE}`, tree `{TREE}`; V1 exact-main run `35206627102` and the pre-existing X6 push-to-main release path run `35206626992` completed SUCCESS on that exact merge SHA. Published/GitHub-Release and production-deployed release remain verified `v1.2.0` until separate v1.3 owner approvals. Optional integrations remain additive, provider L5 remains unclaimed, and ownership boundaries are unchanged.'
old_completed = '**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> P5 v1.1 publication/deployment PASS -> X1 -> X2 -> X3 -> X4 -> X5 -> X6 exact-final-head PASS -> PR #87 merge -> exact-main V1/X6 PASS -> owner-approved v1.2.0 publication PASS -> owner-approved v1.2.0 production deployment PASS`.'
new_completed = '**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> P5 v1.1 publication/deployment PASS -> X1 -> X2 -> X3 -> X4 -> X5 -> X6 exact-final-head PASS -> PR #87 merge -> exact-main V1/X6 PASS -> owner-approved v1.2.0 publication PASS -> owner-approved v1.2.0 production deployment PASS -> Y1 -> Y2 -> Y3 -> Y4 -> Y5 -> PR #98 merge -> exact-main V1/X6 release-path PASS`.'
update(
    'docs/source/AZT-EXEC-MAP.md',
    [
        (old_checkpoint, new_checkpoint, 'EXEC canonical checkpoint'),
        (old_completed, new_completed, 'EXEC completed execution'),
    ],
)

# Assert stale current-state markers are gone from the three current-state surfaces.
checks = {
    'docs/source/SOURCE_MANIFEST.md': ['Y1-Y5 implementation still pending'],
    'docs/source/AZT-04-roadmap-qa-decisions.md': ['**State:** **RATIFIED / Y1 READY.**'],
    'docs/source/AZT-EXEC-MAP.md': ['Current state: `v1.2.0 PUBLICATION + PRODUCTION DEPLOYMENT PASS`; v1.3 Client Delivery System is source-ratified and Y1-ready'],
}
for path, forbidden in checks.items():
    text = Path(path).read_text(encoding='utf-8')
    for marker in forbidden:
        if marker in text:
            raise SystemExit(f'{path}: stale marker remains: {marker}')
