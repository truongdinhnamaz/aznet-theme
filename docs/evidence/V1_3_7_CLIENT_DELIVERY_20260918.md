# AZnet Theme 1.3.7 — Shared-Shell Hero Client Delivery Candidate

**Date:** 18/09/2026
**Repository:** `truongdinhnamaz/aznet-theme`
**Bugfix PR:** #145
**Bugfix merge:** `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c`
**Release PR:** #146
**Verified release head:** `57d2b5a87c08d5fbb54d6960feed677837e33087`

## Why 1.3.7 exists

Client visual QA clarified the intended layout contract: the **Hero section surface** may span the viewport, but the **objects inside the section** must share the same constrained vertical shell as comparable Header, Trust, Services, Profile and article content.

PR #145 had interpreted "full width" as a full-bleed inner Hero grid. That intermediate change was verified and merged, but the subsequent client screenshot showed the resulting misalignment: Hero text/image began farther toward the viewport edge than the Services and common content shell.

PR #146 therefore adds a stronger regression contract: at desktop widths, the Hero section itself must span the viewport, while the Hero inner grid must align in both x-position and width with the Trust and Services containers. The minimal CSS correction removes the Burgundy variant's `width`/`max-width` override so the existing shared `.aznet-theme-law01-container` contract is authoritative again, while retaining the approved 48/52 Hero split.

Because the corrected production bytes post-date the 1.3.6 client-delivery package, the installable package remains promoted to **1.3.7** rather than reusing the 1.3.6 identity.

## RED -> GREEN release promotion

RED head: `63041afb1161bf6b136a665957c71ea262ad20e1`.

Y5 failed for the intended reason while production metadata still declared 1.3.6:

`Y5 promoted style.css must be exactly 1.3.7`

Minimal GREEN changed only:

- `style.css`: `Version: 1.3.7`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.7`

The Y5/X6 release guards were extended for the exact 1.3.6 -> 1.3.7 promotion boundary.

## Fresh final-head verification

Pre-clarification release head `57d2b5a87c08d5fbb54d6960feed677837e33087` completed 14/14 triggered workflows SUCCESS. After the shared-shell regression was added, RED head `d0a3b87144d7f4f09342d38d256162debed29b5f` failed the intended static assertion: `Law 01 Hero section may be full-width, but its inner grid must inherit the shared shell width and align with peer section content.` Minimal GREEN production head `e0e2cd9376ab81dbcea4853581627e48361b76bd` then passed the new static contract and Homepage Composer Law 01 browser geometry check, including the 1920px case.

The full final-head matrix is re-run after this evidence/source synchronization; completion is not claimed here until that exact docs head is green.

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
SHA-256: `aa1f2a4798b81c7b579c8a1b2a03adaa1932837fd3c277a0b7cbb72d5fe23e4a`

Y5 run: `35358986591`
Promoted-package artifact: `10553996265`
Artifact digest: `sha256:328948807a5e40a6ece314ca1eb78965c0a3f519839935ff8fb8bf8ab0141bc0`

The exact-package lifecycle built the package twice with byte identity, linted the packaged PHP set, installed the exact ZIP on clean WordPress 6.9 with zero active third-party plugins, and verified switch-away/switch-back continuity.

## Ownership boundary

- Theme owns this presentation correction.
- No provider/domain ownership changed.
- RootProfile authoritative Team integration remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- Git tag, GitHub Release and production deployment remain separate approval gates.
