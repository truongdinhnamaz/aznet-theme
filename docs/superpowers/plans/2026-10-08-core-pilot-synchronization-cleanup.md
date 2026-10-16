# Core / Pilot Synchronization Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove pilot/site-specific presentation coupling from AZnet Theme generic Core, preserve approved visual parity, and re-establish fresh three-pilot synchronization evidence for Law 01, Curtain 01 and Industrial 01.

**Architecture:** Use migration-by-extraction. Keep generic Core in `assets/css/components/page.css` and shared orchestration files free of pilot identity, move Rèm-specific Page presentation into Curtain 01 scoped assets, retain authored WordPress classes for compatibility, then prove the D-043 extension contract and D-042 peer-pilot regression at increasing QA depth. Pilot deployment/version alignment is last and remains separately gated.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, CSS, GitHub Actions, WP-CLI, Playwright/axe through existing repository workflows, WPVibe for authenticated pilot readback/preview/deployment.

**Spec:** `docs/superpowers/specs/2026-10-08-core-pilot-synchronization-cleanup-design.md`

## Global Constraints

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Current implementation/state is resolved from GitHub `main` and fresh evidence; accepted AZT source documents own architecture/ownership.
- Source owns data. Theme owns presentation. Integration contracts connect them.
- WordPress owns Post/Page/Menu/Media/query/editorial lifecycle.
- WooCommerce owns product/price/stock/variation/cart/order/commerce state.
- RootProfile and ConvertFlow retain their authoritative domain semantics and state.
- No direct provider option/meta/CPT/table/private-class reads.
- No authoritative routing by slug, title, URL, Page ID or site heuristic.
- Theme architecture remains hybrid PHP + `theme.json`; minimum WordPress 6.9+, PHP 8.1+.
- Naming remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- Shared Core changes affecting multiple templates require D-042 peer-pilot regression.
- Feature/bugfix behavior changes use RED -> minimal GREEN -> regression.
- Production deploy, release publication, destructive operations and takeover remain explicit gates.
- Do not redesign previously approved pilot presentation unless a separately scoped visual defect is reproduced.
- Do not update a pilot merely to equalize version numbers.

## Review Focus

1. **Authored RQA classes without active Curtain 01 preset:** they must not gain Rèm-specific styling from generic Core; add a static asset-scope test in Task 2 and runtime absence assertion in Task 4.
2. **Curtain 01 preset active on a non-Rèm site:** styling must follow the preset contract rather than hostname/Page ID; add a synthetic fixture assertion in Task 3.
3. **Legacy Page-ID CSS specificity:** extraction must not silently change full-width/inner-container, dark contrast, forms, or mobile geometry; migrate existing RQA contracts in Task 2 and browser parity in Task 4.
4. **Visual preset editor parity:** the active Curtain 01 editor stylesheet path must continue exposing the same scoped presentation where supported; verify `editor_stylesheets()`/visual preset asset behavior in Task 3.
5. **Pilot drift during rollout:** if a pilot cannot prove exact candidate identity or browser parity, record BLOCKED/UNKNOWN and do not infer synchronization; enforce in Task 6 evidence checklist.

---

### Task 1: Exact leakage inventory and RED Core-boundary contract

**Files:**
- Create: `tests/offline/core-preset-isolation-contract.php`
- Modify: `scripts/verify-v1-core.sh`
- Create: `docs/evidence/CORE_PILOT_SYNC_INVENTORY_20261008.md`

**Interfaces:**
- Consumes: accepted D-042/D-043/D-048 source rules; current `main`; current production file tree.
- Produces: exact allow/deny boundary for generic Core paths and a reproducible inventory of current pilot-specific leakage.

- [ ] **Step 1: Record exact current leakage inventory**

Search production Core paths for:
- `page-id-`
- `rqa-`
- pilot host/domain names
- pilot display names
- generic template-id branches outside approved manifest/compatibility locations.

Write the exact current matches, file paths and classification to `docs/evidence/CORE_PILOT_SYNC_INVENTORY_20261008.md`.

Expected classification for the known defect: RQA/Page-ID presentation in `assets/css/components/page.css` is migration debt; historical docs/evidence and preset-scoped files are not violations.

- [ ] **Step 2: Write the failing isolation contract**

Create `tests/offline/core-preset-isolation-contract.php` to scan only generic production Core files/directories.

Assertions:
- `assets/css/components/page.css` contains no `.page-id-[0-9]+`.
- `assets/css/components/page.css` contains no `rqa-`.
- generic Core production PHP/JS/CSS does not contain `remquocanh.vn`, `lstamduchn.vn`, `minhnguyen.vn`.
- preset-scoped files under `assets/css/presets/` and template manifests are explicitly excluded from the ban.
- historical `docs/` and `tests/` fixtures are excluded from production-code scanning.

- [ ] **Step 3: Wire the RED contract into core verification**

Add:
`php -d zend.assertions=1 -d assert.exception=1 tests/offline/core-preset-isolation-contract.php`

to the retained Core/static section of `scripts/verify-v1-core.sh`.

- [ ] **Step 4: Run the RED contract**

Run:
`php -d zend.assertions=1 -d assert.exception=1 tests/offline/core-preset-isolation-contract.php`

Expected: **FAIL** because `assets/css/components/page.css` still contains Page-ID/RQA selectors.

- [ ] **Step 5: Run retained boundary tests to establish pre-migration baseline**

Run:
- `php tests/offline/rqa-quote-page-balance-contract.php`
- `php tests/offline/rqa-contact-page-presentation-contract.php`
- `php tests/offline/template-three-pilot-regression-contract.php`
- `php tests/offline/template-fourth-template-proof-contract.php`

Expected: existing tests PASS before extraction.

- [ ] **Step 6: Commit Task 1**

Commit message:
`test: define Core preset isolation boundary`

---

### Task 2: Extract Rèm Page presentation from generic Page CSS

**Files:**
- Modify: `assets/css/components/page.css`
- Modify: `assets/css/presets/curtain-01.css`
- Modify: `tests/offline/rqa-quote-page-balance-contract.php`
- Modify: `tests/offline/rqa-contact-page-presentation-contract.php`
- Test: `tests/offline/core-preset-isolation-contract.php`

**Interfaces:**
- Consumes: Task 1 RED boundary and current authored `rqa-*` class vocabulary.
- Produces: generic Page CSS free of RQA/Page-ID coupling; Curtain 01 scoped CSS preserving the existing approved RQA presentation.

- [ ] **Step 1: Update the RQA contracts to point at the Curtain 01 scoped asset**

Change both RQA presentation tests to read:
`assets/css/presets/curtain-01.css`

instead of:
`assets/css/components/page.css`.

Do not weaken the existing assertions for contrast, cards, forms, full-width sections, constrained content, process, trust, proof or FAQ.

- [ ] **Step 2: Add scope assertions before moving CSS**

Extend the RQA contracts so they require a Curtain 01 preset scope/root selector and reject dependence on:
- `.page-id-726`
- `.page-id-45`

The exact root should follow the existing visual-preset body class:
`.aznet-theme-preset--curtain-01`.

Expected after test edit, before CSS move: **FAIL**.

- [ ] **Step 3: Move the quote-page presentation block**

Move the complete RQA quote-page rules from `page.css` into `curtain-01.css`.

Replace Page-ID qualification with:
`.aznet-theme-preset--curtain-01`

plus the existing authored `rqa-quote-*` classes where needed.

Keep the shared full-width/inner-container mechanics in generic `page.css`; only keep Curtain-specific refinements in `curtain-01.css`.

- [ ] **Step 4: Move the contact-page presentation block**

Move the complete RQA contact-page rules from `page.css` into `curtain-01.css`.

Replace Page-ID qualification with Curtain preset scope plus authored `rqa-contact-*` classes.

Do not move Contact Form 7 domain behavior into Theme; only presentation rules move.

- [ ] **Step 5: Remove the old generic RQA/Page-ID rules**

Delete the migrated RQA/Page-ID CSS from `assets/css/components/page.css`.

Do not change unrelated Page, Law 01 or Industrial 01 rules.

- [ ] **Step 6: Run the RED isolation contract and migrated RQA contracts**

Run:
- `php tests/offline/core-preset-isolation-contract.php`
- `php tests/offline/rqa-quote-page-balance-contract.php`
- `php tests/offline/rqa-contact-page-presentation-contract.php`

Expected: all **PASS**.

- [ ] **Step 7: Run generic Page regression**

Run:
- `php tests/offline/y1-page-experience-contract.php`
- `php tests/offline/page-full-bleed-sections-contract.php`
- `php tests/offline/page-section-inner-width-contract.php`
- `php tests/offline/fullwidth-shell-standard-contract.php`

Expected: all PASS.

- [ ] **Step 8: Commit Task 2**

Commit message:
`refactor: scope Rèm page presentation to Curtain 01`

---

### Task 3: Prove manifest/preset asset isolation across all templates

**Files:**
- Modify: `tests/offline/template-three-pilot-regression-contract.php`
- Modify: `tests/offline/template-fourth-template-proof-contract.php`
- Modify if required by failing test only: `inc/theme/templates/curtain-01/manifest.php`
- Modify if required by failing test only: `inc/theme/assets.php`
- Modify if required by failing test only: `inc/theme/design-system.php`

**Interfaces:**
- Consumes: Curtain 01 visual preset asset path and D-043 manifest registry.
- Produces: proof that scoped Page presentation is resolved by preset/manifest, not host/Page ID, without leaking into Law/Industrial/synthetic template paths.

- [ ] **Step 1: Add three-pilot asset isolation assertions**

In `template-three-pilot-regression-contract.php`, assert:
- Curtain 01 resolves `visual_preset=curtain-01`.
- Law 01 does not resolve Curtain 01 visual preset.
- Industrial 01 does not resolve Curtain 01 visual preset.
- Curtain 01 scoped Page CSS is part of the visual preset/editor stylesheet path through existing `visual_preset()` / `editor_stylesheets()` behavior, not a new parallel loader.

- [ ] **Step 2: Add synthetic fourth-template non-leakage assertion**

In `template-fourth-template-proof-contract.php`, register/use the existing synthetic fourth template and assert it does not require or receive Curtain-specific presentation identifiers.

- [ ] **Step 3: Run tests before production changes**

Run:
- `php tests/offline/template-three-pilot-regression-contract.php`
- `php tests/offline/template-fourth-template-proof-contract.php`

If both already PASS with the moved `curtain-01.css`, make no production-code change. If a real gap appears, modify only the shallowest existing asset/resolver path; do not create another preset system.

- [ ] **Step 4: Verify editor visual-preset path**

Run the relevant design-system/static contract that exercises `editor_stylesheets()` and confirm Curtain 01 includes `assets/css/presets/curtain-01.css` only when its visual preset is active.

- [ ] **Step 5: Run the retained D-043 chain**

Run:
- `php tests/offline/template-extension-contract.php`
- `php tests/offline/template-presentation-registry-contract.php`
- `php tests/offline/template-homepage-assets-manifest-contract.php`
- `php tests/offline/template-three-pilot-regression-contract.php`
- `php tests/offline/template-fourth-template-proof-contract.php`

Expected: all PASS.

- [ ] **Step 6: Commit Task 3**

Commit message:
`test: retain preset asset isolation across templates`

---

### Task 4: Repository runtime/browser parity for Page presentation

**Files:**
- Modify only if required: existing browser fixtures/workflows for Y1, Curtain 01 and D-042 peer regression.
- Create if no suitable existing cross-preset browser runner exists: `tests/browser/core-pilot-page-isolation.mjs`
- Modify if created: the narrowest existing workflow that owns three-pilot/shared Page presentation coverage.

**Interfaces:**
- Consumes: Tasks 2-3 GREEN source.
- Produces: fresh L3/L4 evidence that generic Page, Curtain 01 Page, Law 01 and Industrial 01 do not regress or leak styles.

- [ ] **Step 1: Inventory existing browser coverage before adding anything**

Prefer existing:
- Y1 Page Experience workflow;
- Curtain 01 browser workflow;
- D-027 L3/L4 retained regression;
- any existing D-042 three-pilot browser fixture.

Create a new browser test only if no existing test can assert the required cross-preset condition without distortion.

- [ ] **Step 2: Assert generic Page isolation**

On WordPress-clean/default preset fixture:
- Page renders without `rqa-*` styling dependency;
- no horizontal overflow at 1440/1024/390;
- generic full-width section background and constrained inner content remain correct.

- [ ] **Step 3: Assert Curtain 01 Page parity**

On Curtain 01 fixture:
- authored RQA quote/contact classes receive the migrated styling;
- no Page-ID dependency is necessary;
- full-width section surface remains edge-to-edge;
- inner content remains constrained;
- dark-section contrast, cards/forms and mobile reflow remain equivalent;
- one meaningful H1 and hero-first behavior remain valid where applicable;
- axe critical/serious findings = 0.

- [ ] **Step 4: Assert Law 01 and Industrial 01 non-leakage**

For representative Page surfaces:
- no Curtain-specific computed styling is applied to unrelated content;
- no new horizontal overflow;
- existing Page heading/hero-first behavior is unchanged;
- axe critical/serious findings = 0.

- [ ] **Step 5: Run repository L3/L4 workflows**

Run/trigger the existing relevant workflows on the exact candidate head.

Minimum expected:
- V1 Core PR CI PASS;
- Y1 Page Experience PASS;
- D-027 retained regression PASS;
- D-027 browser/L4 PASS where triggered;
- Curtain 01 relevant browser workflow PASS;
- no new Law/Industrial regression.

- [ ] **Step 6: Commit Task 4 only if test/workflow files changed**

Commit message:
`test: verify cross-preset Page presentation isolation`

---

### Task 5: Whole-branch review and canonical merge candidate

**Files:**
- No required production file.
- Update if warranted by exact evidence only: `docs/evidence/CORE_PILOT_SYNC_INVENTORY_20261008.md`

**Interfaces:**
- Consumes: Tasks 1-4 exact candidate.
- Produces: reviewed, merge-ready candidate with no Critical/Important code-review findings.

- [ ] **Step 1: Run full Core verification**

Run:
`bash scripts/verify-v1-core.sh`

Expected: PASS with the new isolation contract included.

- [ ] **Step 2: Run ownership/static scans**

Confirm:
- no `.page-id-[0-9]+` in generic `page.css`;
- no `rqa-` in generic `page.css`;
- no provider-private storage access added;
- no new template-id branching in generic Core;
- RQA authored classes remain only in Curtain-scoped CSS/tests/content evidence.

- [ ] **Step 3: Request whole-branch code review**

Reviewer inputs:
- base SHA: current canonical `main` at branch creation;
- head SHA: final Task 4/5 candidate;
- requirements: approved synchronization design/spec.

Fix every Critical or Important finding before proceeding. Minor findings may be documented if they do not affect acceptance.

- [ ] **Step 4: Re-run affected verification after review fixes**

At minimum:
- new isolation contract;
- RQA quote/contact contracts;
- D-043 three-pilot/fourth-template contracts;
- V1 Core CI.

- [ ] **Step 5: Prepare PR**

PR description must state:
- migration-by-extraction only;
- no WordPress/provider ownership change;
- exact files moved;
- RED evidence;
- GREEN/regression evidence;
- pilot deployment not included.

Do not merge until owner approval if current governance still requires explicit merge approval.

---

### Task 6: Fresh three-pilot production reconciliation

**Files:**
- Create: `docs/evidence/CORE_PILOT_SYNC_THREE_PILOT_20261008.md`
- Update only if current-state source owner actually changes: `docs/source/AZT-03-baseline-provenance.md`
- Update only if roadmap/decision state actually changes: `docs/source/AZT-04-roadmap-qa-decisions.md`

**Interfaces:**
- Consumes: merged canonical source candidate from Task 5.
- Produces: current per-pilot truth for version, preset settings, runtime/browser evidence, identity status and exact rollout blockers.

- [ ] **Step 1: Fresh authenticated read-only inventory on all three peers**

Read:
- active Theme version;
- active visual/homepage preset;
- relevant template settings/mappings;
- WordPress/PHP versions;
- rollback Theme availability;
- connection readiness.

Record facts separately for:
- `lstamduchn.vn`
- `remquocanh.vn`
- `minhnguyen.vn`

- [ ] **Step 2: Determine exact source/byte identity status**

For each pilot:
- compare available production Theme files/hashes against the exact canonical candidate where tooling permits;
- if exact proof cannot be established, record `UNKNOWN`;
- do not infer equality from version metadata.

- [ ] **Step 3: Fresh public runtime/browser checks without deployment**

Verify current live state on representative routes and viewports.

This step records the pre-deployment baseline only; it does not authorize updating a pilot.

- [ ] **Step 4: Classify each pilot**

Use exactly one:
- `PASS_CURRENT` — already on exact candidate with required runtime/browser evidence;
- `UPDATE_CANDIDATE` — safe candidate available but live is older/different;
- `BLOCKED_EXTERNAL` — access/host/security/provider path blocks safe update;
- `UNKNOWN_IDENTITY` — version may match but exact bytes cannot be proven.

- [ ] **Step 5: Apply D-044**

A blocked or deferred pilot does not invalidate Core completion if Theme-owned repository gates are PASS.

Do not write a workaround that violates ownership merely to make all three show the same version.

- [ ] **Step 6: Commit evidence/source updates**

Commit message:
`docs: reconcile three-pilot synchronization state`

---

### Task 7: Pilot rollout, only where separately approved

**Files:**
- No repository production change unless deployment exposes a Theme-owned regression that requires a new RED->GREEN slice.
- Append exact deployment evidence to `docs/evidence/CORE_PILOT_SYNC_THREE_PILOT_20261008.md`.

**Interfaces:**
- Consumes: Task 6 per-pilot classification and exact canonical candidate.
- Produces: pilot-specific deployment evidence; does not redefine Core readiness.

- [ ] **Step 1: Select one pilot at a time**

Rollout order follows current D-042 unless a pilot is already exact/current:
1. Law 01
2. Curtain 01
3. Industrial 01

Skip any `PASS_CURRENT` pilot.

- [ ] **Step 2: Confirm backup/rollback immediately before deployment**

Require fresh site-specific backup/rollback evidence.

Do not reuse a backup assertion from another pilot.

- [ ] **Step 3: Deploy exact candidate only after explicit production approval**

Never deploy an arbitrary draft whose byte identity differs from the approved candidate.

- [ ] **Step 4: Post-deploy readback**

Verify:
- active version;
- preset;
- expected Theme files/candidate identity where possible;
- representative runtime routes;
- no fatal/critical error.

- [ ] **Step 5: Browser/a11y verification**

Run representative desktop/mobile checks:
- overflow;
- Page full-width/inner-container;
- hero-first/H1;
- preset-specific presentation;
- accessibility critical/serious findings;
- no cross-preset leakage.

- [ ] **Step 6: Roll back on Theme-owned regression**

If a Theme-owned regression is reproduced:
- restore the pilot backup;
- record failure evidence;
- open a new bounded RED->GREEN bugfix slice at the shallowest reproducible layer.

- [ ] **Step 7: Close synchronization evidence**

Only after all non-blocked approved pilots have their required evidence, record the final synchronization disposition.

Do not claim simultaneous deployment or byte equality for a pilot whose evidence remains UNKNOWN/BLOCKED.
