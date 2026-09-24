# D-038 Homepage Authoring System — Verification Checkpoint

Date: 2026-09-24

## Scope

This checkpoint records bounded verification for the preset-isolated Homepage Authoring System on branch `work/homepage-authoring-system-spec-20260924`.

It does not claim merge readiness, production deployment, release publication, L3 runtime PASS, L4 browser/a11y PASS, or provider integration PASS.

## Architecture under verification

- Homepage presets use independent Theme-owned typed WordPress references.
- WordPress remains authoritative owner of Page/Post/Category/Media/`wp_block` editorial content.
- Legacy generic Homepage references remain compatibility fallbacks.
- Reference migration is explicit, idempotent and reference-only.
- Preset switching must not mutate editorial content.
- Law 01 Hero follows D-030 with preset-scoped references and draft-first migration.

## PASS — bounded L1/L2 evidence

### Preset registry and migration

Verified on exact branch blobs:

- `inc/theme/settings.php@80d9f52713a1365fb29b1604a49271ee89a5c855`
- `inc/theme/homepage-authoring.php@6eb3607278ff91afd9a12370f8a32fabe8125d62`
- `inc/admin/homepage-migration.php@d3f4ad9e1e236d83fe292531d63f32131ca05a41`
- `tests/offline/homepage-preset-isolation-contract.php@0800844038d5a5e9b4144a3a5937747e2f15f0bb`
- `tests/offline/homepage-preset-migration-contract.php@b2caada5b93b14d19993150b91ba25fe73fcf27a`

Observed PASS:

- Law 01 / Curtain 01 scoped source registry.
- scoped-first resolution with legacy compatibility fallback.
- explicit initialization semantics.
- shared-source overlap detection.
- reference-only migration.
- migration idempotency.
- preservation of pre-existing scoped references.
- no WordPress content mutation APIs in the reference-migration path covered by the contract.

### Quick Edit validation ordering

RED commit:

`b282ac567206aef1ca0748af2e86e0759aa4261d`

Observed RED:

`FAIL: Quick Edit validates featured image after mutating the Page`

GREEN commit:

`983c87d9f334fc886fb3d12bb0cec02c8cf30cb3`

The Page Quick Edit action now validates the submitted featured-image attachment before `wp_update_post()`. The regression is wired into `scripts/verify-v1-core.sh`.

### Scoped Hero + legacy draft migration

Retained D-030 tests were found to contain stale direct-generic-key assertions after D-038 moved Law 01 rendering to the scoped resolver. Production was not reverted.

Contract alignment commits:

- `65b18cf8983b72d6c03a2944de5601c59b39c280` — Law 01 Hero backward compatibility contract.
- `58dbad17ddf923b2733e0b421df1dbe45f8b7ebb` — retained Hero Library contract.

Verified behavior:

- Law 01 Hero action requires explicit `homepage_preset_scope`.
- Hero block and presentation variant write only `homepage_law01_hero_block` and `homepage_law01_hero_variant`.
- generic Hero keys are not directly assigned by the scoped Hero action.
- new Hero content is created as a WordPress `wp_block` draft.
- legacy Hero migration resolves the exact mapped legacy Page.
- migration writes the new Law 01 Hero reference without deleting or rewriting the legacy Page.
- the public renderer resolves Hero content through `homepage_block_reference()`, which accepts only published `wp_block` content; a migrated draft therefore does not replace the current public Hero.
- renderer ordering remains published scoped Hero → mapped legacy Hero Page → historical Site/Front Page fallback.
- `tests/offline/homepage-hero-scoped-contract.php@16cf24aabe7e9e610a1e1a26d51757f71782bfac` remains consistent with current production files.

### Fresh executable reconstruction check — 24/09/2026

Because GitHub Actions jobs are not receiving runners, an exact-main artifact was used as an executable base instead of treating CI failure as code failure.

Base:
- successful X6 artifact `x6-cross-surface-release-evidence` from exact-main run `35888794683`;
- artifact checkout SHA: `9b93fd76f7d3238734bec58e247bf98d61851618`;
- extracted Theme base: `x6/expected/aznet-theme`.

A bounded D-038 core overlay was then linted and exercised locally. Fresh observed output:

```text
No syntax errors detected in inc/theme/settings.php
No syntax errors detected in inc/theme/bootstrap.php
No syntax errors detected in inc/theme/homepage-authoring.php
No syntax errors detected in inc/admin/bootstrap.php
No syntax errors detected in inc/admin/homepage-authoring.php
No syntax errors detected in inc/admin/homepage-hero.php
No syntax errors detected in inc/admin/homepage-migration.php

PASS: D-038 preset-isolated Homepage source registry
PASS: D-038 explicit reference-only preset migration
PASS: Homepage Quick Edit validates featured image before Page mutation
```

Fresh contract commands:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-isolation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-migration-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-quick-edit-validation-order-contract.php
```

Evidence limitation:
- this is a bounded executable reconstruction, not a substitute for a complete exact-branch checkout;
- exact byte identity was confirmed for the key settings/bootstrap/Hero/migration files already recorded through GitHub evidence;
- two locally materialized core files differed from their GitHub blob size by one byte and are therefore **not** promoted as exact-byte branch evidence by this local run;
- full `scripts/verify-v1-core.sh` is still not claimed because the local checkout is not a complete exact-branch reconstruction.

The fresh local results strengthen the existing L1/L2 checkpoint but do not advance L3/L4 or release state.

### Curtain 01 Hero source-selector regression — 24/09/2026

Bounded static review found a functional gap in the preset-aware Control Center: Curtain 01 rendered a Hero section card, but the preset-scoped source selector omitted the `hero` slot. The card's `Đổi nguồn` action therefore pointed to a diagnostics/source form that could not actually change `homepage_curtain01_hero_block`.

RED:
- commit `f3ba39178f3cd897a8e712c8bd244d798da3eb1c`;
- new contract: `tests/offline/homepage-curtain01-hero-source-control-contract.php`;
- observed failure: `FAIL: Curtain 01 Hero is not available in the preset-scoped source selector`.

GREEN:
- commit `3390f615d80c70299df78298970f176070540382`;
- Curtain 01 source slots now include `hero` before `proof`;
- the new regression is wired into `scripts/verify-v1-core.sh`;
- bounded local execution observed: `PASS: Curtain 01 Hero has a preset-scoped source selector`.

This fix changes only Theme-owned source-selection presentation/configuration. It does not mutate WordPress content and does not widen any external provider contract.

## BLOCKED / UNKNOWN

### GitHub Actions execution

GitHub Actions cannot currently supply fresh CI evidence.

V1 Core PR CI run `35947060015` was attempted twice. Both jobs ended before any workflow step with no assigned runner (`steps=[]`, `runner_id=0`). The later run `35954746280` was also rerun once and again completed before steps were allocated (`steps=null`).

After the Hero contract commits, V1 run `35954418385` showed the same condition: jobs ended with no workflow steps. Adjacent PR workflows also exhibited pre-step failure.

Status: **BLOCKED_EXTERNAL_EXECUTION**.

This checkpoint does not interpret the runner-level condition as a code-test failure.

### Full V1 executable-checkout blocker — 24/09/2026

A fresh full `scripts/verify-v1-core.sh` run was attempted but could not be started on an exact branch checkout:

- container `git clone` of the canonical repository failed before checkout because the execution environment could not resolve `github.com`;
- exact-main PASS artifacts were inspected as the safer fallback;
- `v1-main-static-contracts` contains only `checkout-sha.txt`;
- `v1-main-clean-runtime-browser` contains runtime/browser evidence, not repository source;
- `x6-cross-surface-release-evidence` contains the packaged Theme tree but does not contain repository `tests/` or `scripts/`;
- the third exact-main workflow had no downloadable artifact;
- no connector action is available to export/clone a repository archive directly into the execution container.

Therefore reconstructing the entire repository file-by-file would be a high-cost synthetic checkout and is intentionally not used as evidence for the full V1 gate.

Status remains **BLOCKED_EXTERNAL_EXECUTION** for the full L1/L2 suite. Existing bounded exact-blob/local contract evidence remains valid but is not promoted to full V1 PASS.

### Not yet verified

- full `scripts/verify-v1-core.sh` on a complete executable checkout;
- L3 real WordPress runtime behavior;
- L4 Control Center/browser/a11y behavior;
- full Law 01/Curtain 01 visual regression;
- merge/release/deployment.

## Rollback / safety

- No production deployment was performed.
- No release was published.
- No merge to `main` was performed.
- Legacy generic Homepage keys remain available.
- WordPress-native draft Hero/content created by the authoring system remains independent of Theme rollback.

## Exact next

Run one bounded Task 10 verification slice on a complete executable checkout when runner/local execution is available:

1. `bash scripts/verify-v1-core.sh`
2. PHP lint on touched production PHP
3. only if L1/L2 are green, proceed to disposable L3 WordPress runtime evidence

Do not update AZT-03 implementation provenance or claim D-038 completion before the required fresh QA layers pass.
