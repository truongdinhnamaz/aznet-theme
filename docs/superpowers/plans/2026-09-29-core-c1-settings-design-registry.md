# Core C1 Settings + Design Registry Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make AZnet Theme visual/Homepage preset validity registry-driven so future templates can attach presentation IDs without new template-name allow-lists in generic Core.

**Architecture:** Reuse merged D-043/C0 Template Manifest Registry. Add deterministic local manifest loading and real manifests, expose registry projection helpers for presentation IDs, then make settings/design consume those helpers. C1 does not migrate Homepage composition, authoring, assets, or provisioning dispatch.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, offline PHP contracts, GitHub Actions, three connected WordPress pilots.

**Spec:** `docs/superpowers/specs/2026-09-29-core-template-extension-contract-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Theme owns presentation only; WordPress/plugins/providers retain authoritative data/domain semantics.
- Keep one `aznet_theme_settings` store.
- No direct plugin private storage/API reads.
- Fail soft when optional providers/capabilities are absent.
- Template switching must not mutate authoritative content.
- C0 PR #305 / D-043 is retained PASS; do not reimplement it.
- RED -> expected fail -> minimal GREEN -> regression.
- Pilot results are independent; no cross-pilot PASS inference.

## Preconditions

- Resolve latest `main` before execution.
- Industrial 01 PR #301 must be merged/reconciled before final C1 acceptance so all three real template presets exist on one canonical baseline.
- Revalidate #301 exact head before merge; do not assume a stale SHA.

## File Structure

**Create**
- `inc/theme/template-loader.php` — local Theme manifest loader.
- `inc/theme/templates/law-01/manifest.php`
- `inc/theme/templates/curtain-01/manifest.php`
- `inc/theme/templates/industrial-01/manifest.php`
- `tests/offline/template-presentation-registry-contract.php`
- `docs/evidence/CORE_C1_SETTINGS_DESIGN_REGISTRY_20260929.md` after verified rollout.

**Modify**
- `inc/theme/template-registry.php`
- `inc/theme/bootstrap.php`
- `inc/theme/settings.php`
- `inc/theme/design-system.php`
- `scripts/verify-v1-core.sh`
- `docs/source/AZT-04-roadmap-qa-decisions.md` only after evidence.

## Interfaces

Existing C0 functions stay unchanged:
- `normalize_template_manifest(array $manifest): ?array`
- `register_template_manifest(array $manifest): bool`
- `template_manifests(): array`
- `template_manifest_ids(): array`
- `template_manifest(string $id): ?array`

New C1 functions:
- `load_local_template_manifests(): array`
- `template_presentation_ids(string $slot): array`
- `visual_preset_ids(): array`
- `homepage_preset_ids(): array`

Real presentation values:
- Law 01: Homepage `law-01`; no dedicated visual preset.
- Rèm 01: Homepage `curtain-01`; visual `curtain-01`.
- Industrial 01: Homepage `industrial-01`; visual `industrial-01`.

Core visual presets remain `default`, `editorial`, `commerce`; Core Homepage fallback remains `off`.

## Review Focus

1. Unknown saved template IDs must fall back without deleting unrelated settings.
2. Invalid nested presentation IDs must not become valid settings.
3. Duplicate presentation values must deduplicate deterministically.
4. Core visual presets must remain valid with zero registered templates.
5. Law Homepage registration must not accidentally imply a Law visual preset.

---

### Task 1: RED contract

**Files:** create `tests/offline/template-presentation-registry-contract.php`; modify `scripts/verify-v1-core.sh`.

- [ ] Add assertions that the four new C1 functions exist.
- [ ] Assert Law/Rèm/Industrial Homepage IDs and Rèm/Industrial visual IDs after local manifest load.
- [ ] Assert Law is not a visual preset.
- [ ] Assert zero-template Core defaults, invalid nested values, duplicate projection values, unsupported projection slot, and unknown saved-value fallback.
- [ ] Add the test to reusable Core verification after the retained C0 contract.
- [ ] Run the focused test and observe the intended FAIL because C1 loader/projection behavior is missing.
- [ ] Commit: `test: define C1 template presentation registry contract`.

### Task 2: Local template manifest loader

**Files:** create `inc/theme/template-loader.php` and three manifest files; modify `inc/theme/bootstrap.php`.

**Produces:** `load_local_template_manifests(): array`.

- [ ] Load only local `inc/theme/templates/*/manifest.php` files, sorted deterministically.
- [ ] Each manifest file returns one array; loader calls `register_template_manifest()`.
- [ ] Invalid/duplicate manifests are rejected without fatal and reported in the returned summary.
- [ ] Add Law manifest with Homepage `law-01`.
- [ ] Add Rèm manifest with Homepage + visual `curtain-01`.
- [ ] Add Industrial manifest with Homepage + visual `industrial-01` and presentation capabilities only.
- [ ] Reorder Core bootstrap so registry + loader/manifests are ready before settings consumers.
- [ ] Run C0 contract: PASS.
- [ ] Run C1 contract: expected to advance but still fail on projection/settings.
- [ ] Commit: `feat: load local template manifests`.

### Task 3: Presentation projection contract

**Files:** modify `inc/theme/template-registry.php`; test C0 + C1 contracts.

**Produces:** `template_presentation_ids(string $slot): array`.

- [ ] Normalize only C1 presentation keys `visual_preset` and `homepage_preset` as lowercase bounded slugs.
- [ ] Reject unknown/invalid nested presentation values.
- [ ] Implement projection for the two supported slots; unsupported slot returns `[]`.
- [ ] Preserve first-registration order and deduplicate values.
- [ ] Run C0 contract: PASS.
- [ ] Run C1 contract: expected to fail only on settings/design migration.
- [ ] Commit: `feat: project template presentation ids`.

### Task 4: Registry-driven Theme settings

**Files:** modify `inc/theme/settings.php`; update `tests/offline/r1-settings-contract.php` and C1 contract.

**Produces:** `visual_preset_ids(): array`, `homepage_preset_ids(): array`.

- [ ] `visual_preset_ids()` returns Core IDs plus registered template visual IDs.
- [ ] `homepage_preset_ids()` returns `off` plus registered template Homepage IDs.
- [ ] Replace visual preset hard-coded template allow-list in `normalize_settings()`.
- [ ] Replace Homepage preset hard-coded template allow-list in `normalize_settings()`.
- [ ] Keep header/footer/Woo component preset lists unchanged in C1.
- [ ] Test known registered IDs and unknown removed-template fallback.
- [ ] Run R1 settings + C1 contracts: PASS.
- [ ] Commit: `refactor: derive template presets from registry`.

### Task 5: Remove duplicate Design System validity list

**Files:** modify `inc/theme/design-system.php`; update `tests/offline/r1-visual-preset-contract.php`.

- [ ] Add an assertion that Design System no longer enumerates template visual IDs for validity.
- [ ] Keep `visual_preset(): string` signature, but return the already normalized setting with safe `default` fallback.
- [ ] Run R1 visual + C1 contracts: PASS.
- [ ] Commit: `refactor: trust registry-normalized visual preset`.

### Task 6: C1 regression gate

**Files:** tests/scripts only if needed.

- [ ] Add static assertions that generic settings/design files do not enumerate Law/Rèm/Industrial as validity allow-lists.
- [ ] Run C0, C1, R1 settings and R1 visual contracts.
- [ ] Run `scripts/verify-v1-core.sh`.
- [ ] PHP-lint all changed production PHP files.
- [ ] Fix only failures attributable to C1 at the shallowest reproducible layer.
- [ ] Commit any final bounded test adjustment.

### Task 7: PR and canonical merge

- [ ] Open a bounded C1 PR; explicitly exclude C2-C6 behavior.
- [ ] Require all triggered C1-relevant workflows GREEN.
- [ ] Do not infer pilot PASS from CI.
- [ ] Merge only when required CI is GREEN.
- [ ] Record canonical merge SHA and exact candidate/package provenance.

### Task 8: Three-pilot rollout

**Order:** Law 01 `lstamduchn.vn` -> Rèm 01 `remquocanh.vn` -> Industrial 01 `minhnguyen.vn`.

For each site independently:

- [ ] Confirm authenticated access, active Theme identity/version and a recent recoverable site/database backup or host snapshot.
- [ ] Confirm current preset values before update.
- [ ] Update only with exact C1 candidate bytes.
- [ ] Verify Homepage 200, representative inner routes, active preset unchanged, no Theme-caused PHP fatal/warning, desktop/mobile smoke, no obvious horizontal overflow, and Theme admin loads.
- [ ] Rèm/Industrial: additionally verify Woo catalogue/product surfaces remain non-fatal and Woo remains authoritative.
- [ ] Stop promotion to the next pilot if a regression appears.

After all three:
- [ ] Write `docs/evidence/CORE_C1_SETTINGS_DESIGN_REGISTRY_20260929.md`.
- [ ] Record independent PASS/BLOCKED/UNKNOWN, rollback reference and exact source/package provenance.
- [ ] Update AZT-04 with C1 closure only after evidence exists.
- [ ] Commit evidence/source closure.

## C1 Exit Gate

C1 is PASS only when:
- C0 remains PASS;
- local manifests load deterministically;
- visual/Homepage validity is registry-driven;
- generic settings/design validity no longer needs template-name allow-lists;
- Core defaults work with zero templates;
- unknown template IDs fail safe;
- all three current template settings preserve behavior;
- required repository CI is GREEN;
- three pilot results are recorded independently;
- no domain ownership moved into Theme.

**Exact Next:** C2 — Template Library Registry.
