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

### Law 01 Hero simple-form authoring — 24/09/2026

User-approved bounded authoring slice: common Hero edits must be possible from AZnet Theme Control Center without requiring Gutenberg expertise. WordPress `wp_block` remains the editorial owner; Theme settings still store only presentation/reference state.

RED evidence:
- `e483bba0c341c8470dedc26022155993a81f8fd5` — managed Hero role/form contract; first failure: missing `aznet-hero-eyebrow` metadata.
- `0d26ccc76e60510b33974a4c801a2012c0be7b5c` — relative CTA-link contract; observed failure because HTML `type=url` rejected site-relative paths such as `/lien-he/`.

GREEN:
- `553ed75cd97ba35c59167f9597f8a01f4da78b44` — role-managed Law 01 Hero simple form.
- Form fields: eyebrow, title, value line, lead, two CTA labels/links, Hero image, four trust lines, and presentation variant.
- Content is written back to the mapped WordPress `wp_block`; no parallel Hero-copy store was added to Theme settings.
- Save is scoped to `law-01`, validates exact mapped source, capability, nonce, image type, and a content hash to reject stale concurrent edits.
- Only role-marked blocks are updated; unrelated advanced blocks are preserved.
- Existing pre-form default scaffold is upgraded only by an explicit action and only when its exact known scaffold hash matches.
- Customized legacy Hero content is left untouched and remains editable through WordPress.
- CTA links accept absolute and site-relative URLs and are sanitized server-side.
- The retained D-030 mutation assertion was narrowed to `handle_homepage_hero_apply()`; the new explicit form handler is allowed to update WordPress-owned Hero content.

Fresh local verification against the delivery tree:
```text
PASS: Law 01 Hero simple-form contract
PASS: Hero simple form accepts relative links safely
PASS: D-038 scoped Law 01 Hero migration contract
PASS: Law 01 Hero backward compatibility contract
PHP lint: 133 production PHP files, 0 syntax errors
ZIP integrity: no errors
```

Delivery candidate:
- `AZnet Theme 1.3.33`
- ZIP SHA-256: `8a1af2e764f268986fc970ff68515faf3e761cff060340a33c3636cc2de8d71c`

This remains bounded L1/L2 + package integrity evidence. Full repository V1, disposable L3 WordPress runtime, L4 browser/visual and release gates remain separate.

### Hero simple-form public handoff bug — 24/09/2026

Production symptom reproduced from the user workflow: changing the Hero image in the simple form persisted the mapped `wp_block` content but the public Homepage continued showing the previous Hero image.

Root cause:
- the simple form can edit a mapped Hero candidate in `draft` state;
- `handle_homepage_hero_form_save()` previously updated only `post_content` and preserved `draft`;
- public `homepage_block_reference()` is intentionally publish-only;
- therefore the frontend correctly ignored the edited draft and continued the legacy/fallback Hero.

RED:
- `bb32b57d3fcfab26c53c0c12bd7d7f47339cd92a` added the publish-handoff regression;
- after correcting a test-string interpolation defect in `f6b5b89f5b4b3b5fe0f706406fa2444ddd90226d`, the regression failed on the missing explicit draft-state handling as expected.

GREEN:
- `eaa032e3ec3817fd9a67e2eae137b0bb072adf69` makes simple-form save publish the Hero when the mapped Hero is still a draft;
- publishing requires `publish_posts`; already-published Hero blocks remain ordinary content updates;
- public resolver remains publish-only.

Regression hardening:
- `e68f92033f0f0f7a4e6fa3cc49c27d76f4a3c583` makes the test formatting-agnostic and wires the regression into V1 verification.

Fresh local verification:
```text
PASS: Law 01 Hero simple-form contract
PASS: Hero simple form accepts relative links safely
PASS: Hero simple form publishes draft before public handoff
PASS: D-038 scoped Law 01 Hero migration contract
PASS: Law 01 Hero backward compatibility contract
PHP lint: 133 production PHP files, 0 syntax errors
ZIP integrity: no errors
```

Delivery candidate:
- AZnet Theme `1.3.34`
- SHA-256 `0db7c15d74d4292ac0247e9a3cf3076cd9fe02d1090cb3e72cd358b8bc2f6678`

### Hero form placeholder-handoff regression — 24/09/2026

User-reported production symptom after 1.3.34:
- the newly selected Hero image became public successfully;
- however the public Hero switched to the managed wp_block while that block still contained default placeholder copy (`Thông điệp mở đầu`, `Viết tiêu đề Hero của bạn tại đây`, etc.), replacing the previously correct public Hero content.

Runtime evidence from `lstamduchn.vn`:
- mapped managed Hero block: ID `273`;
- selected new image: attachment `274`;
- legacy/public Hero Page: ID `225`, title `Giải pháp pháp lý rõ ràng cho cá nhân và doanh nghiệp`;
- Page 225 also contains the existing excerpt/body copy that previously rendered on the public Hero.

Immediate live recovery:
- Page 225 featured image was updated to attachment 274;
- block 273 was returned to `draft`;
- WordPress object cache was flushed;
- recheck: block 273 = `draft`, Page 225 = `publish`, featured_media = `274`.
This restores the previous public content path while preserving the newly selected image.

Root cause:
- 1.3.34 correctly fixed draft→publish handoff;
- but the draft being published was originally created from the generic scaffold defaults instead of inheriting the current public Hero content.

RED:
- `47dced6632cd29ba63446e1f0bd384f3538e03bb` adds a regression requiring managed Hero drafts to seed from current public sources.
- Observed local RED: `Missing Hero seed helper: homepage_hero_form_seed_model_from_public_sources`.

GREEN:
- `490b9793ef866830205c258884ac92d788c3d444`;
- new drafts are created from the current public Hero model rather than placeholder copy;
- an existing draft that still contains the untouched placeholder model displays seeded current public content in the simple form, while retaining its selected image;
- seed priority: mapped legacy Hero Page → static front Page fallback;
- site title, Page title, excerpt/body/tagline, CTA destinations and image are carried into the managed form where available;
- no mutation of the legacy Page occurs as part of seeding.

Fresh local verification:
```text
PASS: Law 01 Hero simple-form contract
PASS: Hero simple form accepts relative links safely
PASS: Hero simple form publishes draft before public handoff
PASS: Hero simple form seeds from current public Hero
PASS: D-038 scoped Law 01 Hero migration contract
PASS: Law 01 Hero backward compatibility contract
No syntax errors detected in inc/admin/homepage-hero.php
No syntax errors detected in inc/admin/homepage.php
```

Delivery candidate:
- AZnet Theme `1.3.35`
- 133 production PHP files lint clean
- ZIP integrity clean
- SHA-256 `16811c50ba81ceb8ed0bec78cb09882c6d50f9d4ab196e092192e0d4eddd26fc`

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
