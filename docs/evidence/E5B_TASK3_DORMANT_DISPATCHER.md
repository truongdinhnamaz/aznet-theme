# E5-B Task 3 — Dormant current-surface dispatcher evidence

Date: 2026-09-03

## Scope

Task 3 only: add a dormant context-to-existing-renderer dispatcher. No WordPress route/request takeover hook is registered.

## Baseline

- Branch: `work/e5b-current-surface-consumer`
- Parent checkpoint before Task 3: `619f73d4d291885272d458995a6826e0e60a1876`
- E4 source artifact used for isolated local reconstruction: `aznet-theme-0.1.0-alpha.7-e4-candidate.zip`
- E4 artifact SHA-256 verified: `5ee905ba2788fa99334c36da512e193c9e63d8d9b7225aa658164cdc2840e483`
- Current Task 1/2 production files were reconstructed byte-identically from the active GitHub branch before Task 3 testing.

## TDD evidence

RED command:

```bash
php tests/offline/e5-current-surface-dispatcher-contract.php
```

Observed before implementation:

```text
FAIL: dispatcher module does not exist
RED_EXIT=1
```

GREEN verification after implementation:

```bash
php tests/offline/e5-current-surface-dispatcher-contract.php
php -l inc/theme/rootprofile-current-surface.php
php -l inc/theme/bootstrap.php
```

Observed:

```text
PASS: E5 dormant current-surface dispatcher
No syntax errors detected in inc/theme/rootprofile-current-surface.php
No syntax errors detected in inc/theme/bootstrap.php
```

Dormant wiring scan also passed:

- dispatcher contains no `add_action(` or `add_filter(`;
- bootstrap only `require_once`s the dispatcher;
- bootstrap does not register `render_current_rootprofile_surface()`.

## Production delta

- Added `inc/theme/rootprofile-current-surface.php`
- Modified `inc/theme/bootstrap.php` only to require the dispatcher module
- Added source-only `tests/offline/e5-current-surface-dispatcher-contract.php`

Task 3 production/test commit on the active branch: `953bc6a6f32727ea283c1f0496dc25211d9d758e`

## Verified behavior

- valid `person_profile` context -> Profile model, Profile CSS, Profile template, returns `true`;
- valid `organization_profile` context -> Profile model, Profile CSS, Profile template, returns `true`;
- valid `contact` context -> Contact model, optional public Organization enrichment via existing Provider v1 consumer, Contact CSS, Contact template, returns `true`;
- malformed or unsupported context -> no template, no surface CSS, returns `false`;
- no slug/query/Page resolver is required by dispatcher tests.

## Layer status

- E5-B Task 3: PASS local L0-L2 only.
- E5-B full ownership/no-takeover gate: not yet PASS; Task 4 is next.
- E5-A RootProfile current-surface publisher: BLOCKED/UNKNOWN until canonical RootProfile repository is accessible for source-owned implementation.
- E5-C WordPress runtime/browser/a11y: UNKNOWN.
- E5-D production takeover: LOCKED and not implemented.

## Next

Task 4 — Ownership / no-takeover regression gate and repeatable `scripts/verify-e5b.sh`.
