# AZnet Theme 1.3.7 — Hero Full-Bleed Client Delivery Candidate

**Date:** 18/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Bugfix PR:** #145  
**Bugfix merge:** `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c`  
**Release PR:** #146  
**Verified release head:** `57d2b5a87c08d5fbb54d6960feed677837e33087`

## Why 1.3.7 exists

The client visual QA screenshot exposed a real presentation regression on wide screens: the Burgundy Law 01 Hero grid stopped at the shared `96rem` shell, leaving cream gutters instead of spanning the viewport.

PR #145 reproduced the bug with a new wide-screen contract and a 1920px browser geometry case, then fixed the shallow CSS root cause by changing the Hero grid from a capped shell to full-bleed width while retaining the approved 48/52 split.

Final PR #145 head `0e2fa28b72f88af3af2f0f1bcc668ff90d117c50` completed **14/14 workflows SUCCESS, 0 failure** and merged to `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c`. Git comparison from verified head to merge commit showed zero file delta.

Because those production bytes were merged after the 1.3.6 client-delivery package had been created, the next installable package is promoted to **1.3.7** rather than reusing the 1.3.6 identity.

## RED -> GREEN release promotion

RED head: `63041afb1161bf6b136a665957c71ea262ad20e1`.

Y5 failed for the intended reason while production metadata still declared 1.3.6:

`Y5 promoted style.css must be exactly 1.3.7`

Minimal GREEN changed only:

- `style.css`: `Version: 1.3.7`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.7`

The Y5/X6 release guards were extended for the exact 1.3.6 -> 1.3.7 promotion boundary.

## Fresh final-head verification

Exact release head `57d2b5a87c08d5fbb54d6960feed677837e33087` completed **14/14 triggered workflows SUCCESS, 0 failure**:

- V1 Core Pull Request CI
- Release Version Consistency Gate
- Y5 Client Delivery Release
- X6 Cross-surface Release Closure
- D-027 Standalone Core Exact Package
- D-027 Retained Full Regression
- F Homepage Native Regression Package
- Law Site Provisioning Browser Quality
- Law Site Provisioning Candidate Package
- R1 Design System Browser Parity
- R6 Performance Baseline
- X3 Media Gallery Embed
- X4 Pagination Navigation
- G Core Lifecycle Update Switch Rollback

## Installable package

File: `aznet-theme-1.3.7.zip`  
Production files: **145**  
Packaged PHP lint: **108/108 PASS**  
SHA-256: `890e94eb1a69abad4e94e4914e3628b83a491d91636d33c9036070d6ee35b3ab`

Y5 run: `35357088021`  
Promoted-package artifact: `10552241981`  
Artifact digest: `sha256:93ed2484c7eeb1add18d4a582386c01ab536ec0141e0bdcd1dc8102b0570faa7`

The exact-package lifecycle built the package twice with byte identity, linted the packaged PHP set, installed the exact ZIP on clean WordPress 6.9 with zero active third-party plugins, and verified switch-away/switch-back continuity.

## Ownership boundary

- Theme owns this presentation correction.
- No provider/domain ownership changed.
- RootProfile authoritative Team integration remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- Git tag, GitHub Release and production deployment remain separate approval gates.
