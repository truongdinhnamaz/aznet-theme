# Tâm Đức Homepage Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the real `tamduchanoi.aznet.vn` Homepage with verified WordPress-owned business/editorial data and AZnet Theme-owned presentation, without hard-coding client data into production Theme code or bypassing release/deployment gates.

**Architecture:** Treat this as a bounded pilot site-operations slice, not a new Theme business subsystem. First capture an authenticated read-only inventory, then mutate only WordPress-owned/site-configuration data whose source is already authoritative on the pilot or explicitly approved, and finally verify the public Homepage. Any missing presentation capability that exists only on unreleased `main` remains blocked behind the Theme release/deployment gate instead of being hot-patched into production.

**Tech Stack:** WordPress Core, AZnet Theme, GitHub Actions, Playwright 1.55.0, Node 22, PHP 8.1 contract tests.

**Spec:** `docs/superpowers/specs/2026-09-08-homepage-composer-law01-design.md`; `docs/superpowers/specs/2026-09-09-law-site-provisioning-v1-1-design.md`; retained P4/P5 production evidence; owner-approved Tâm Đức site-first completion direction in chat on 2026-09-16.

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Work branch: `ops/tamduc-homepage-completion`; never mix this slice into PR #87/X6.
- Theme is PRESENTATION OWNER; WordPress owns Site Title, Tagline, Pages, Posts, Categories, menus, media and native content/query state.
- Do not hard-code `Tâm Đức`, phone/hotline, people, addresses, credentials, outcomes or other client facts into production Theme PHP/CSS/JS.
- Do not infer authoritative business identity/routing from slug, title, URL or Page ID.
- Do not read/write private RootProfile, ConvertFlow, WooCommerce or SEO-provider storage/APIs.
- Current production Theme remains the verified `1.1.0` deployment until a separate release/deployment approval exists.
- Do not upload unreleased `main` bytes, backport unversioned Theme files, promote metadata, tag, publish or deploy Theme in this plan.
- Site mutations must be explicit, bounded, reversible and backed by a pre-mutation snapshot.
- Unknown real-world facts remain UNKNOWN; do not fabricate phone numbers, lawyers, office addresses, testimonials, awards, credentials, case results or current legal claims.
- QA claims are layer-specific; public L4 PASS does not imply provider L5 certification.

---

### Task 1: Authenticated Read-Only Homepage Inventory

**Files:**
- Create: `tests/offline/tamduc-homepage-inventory-contract.php`
- Create: `tests/browser/tamduc-homepage-inventory.mjs`
- Create: `.github/workflows/tamduc-homepage-inventory.yml`
- Create: `docs/evidence/TAMDUC_HOMEPAGE_BASELINE_20260916.md` only after fresh evidence exists

**Interfaces:**
- Consumes: existing repository secrets `PILOT_WP_USER` and `PILOT_WP_PASSWORD`; existing P4 login/Control Center conventions.
- Produces: `/tmp/tamduc-homepage-inventory/summary.json`, screenshots, and an exact branch/head identity for later mutation preconditions.

- [ ] **Step 1: Write the failing static contract**

Create `tests/offline/tamduc-homepage-inventory-contract.php` that asserts the inventory workflow and browser harness exist; the workflow is push-scoped to `ops/tamduc-homepage-completion`; permissions remain `contents: read`; credentials are referenced only through repository secrets; the browser harness contains no save/update/delete/provisioning action markers; and the output includes Theme version, Site Title, Tagline, custom-logo presence, menu assignments, Homepage preset/variant when exposed by the public Theme admin UI, static Front Page identity, mapped Homepage roles, public contact/menu links, and public Homepage section counts.

- [ ] **Step 2: Run the contract and verify intended RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/tamduc-homepage-inventory-contract.php
```

Expected: FAIL because the inventory workflow/harness do not yet exist.

- [ ] **Step 3: Implement the minimal read-only inventory harness/workflow**

Reuse the P4 login function and public-browser error attribution. Do not save forms. Read only visible WordPress/AZnet Theme admin controls and public DOM/REST data. Persist a structured snapshot with explicit `observed`, `not_observed` and `unknown` states rather than heuristically filling gaps.

- [ ] **Step 4: Run focused GREEN locally**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/tamduc-homepage-inventory-contract.php
node --check tests/browser/tamduc-homepage-inventory.mjs
```

Expected: PASS.

- [ ] **Step 5: Commit the bounded RED→GREEN slice**

```bash
git add tests/offline/tamduc-homepage-inventory-contract.php tests/browser/tamduc-homepage-inventory.mjs .github/workflows/tamduc-homepage-inventory.yml
git commit -m "test: inventory Tâm Đức homepage state"
```

- [ ] **Step 6: Let the branch push workflow execute and inspect fresh evidence**

Required exit: authenticated login succeeds; no mutation markers; exact production Theme version is recorded; current Homepage configuration/content gaps are enumerated without inference.

- [ ] **Step 7: Record baseline evidence**

Create `docs/evidence/TAMDUC_HOMEPAGE_BASELINE_20260916.md` with run ID, artifact ID/digest, exact head SHA, observed current state, missing/unknown data, and explicit separation of site-content gaps from Theme-presentation gaps.

---

### Task 2: Build an Explicit Mutation Plan from Authoritative Pilot Data

**Files:**
- Create: `tests/offline/tamduc-homepage-mutation-plan-contract.php`
- Create: `ops/tamduc-homepage/mutation-plan.json`
- Update: `docs/evidence/TAMDUC_HOMEPAGE_BASELINE_20260916.md`

**Interfaces:**
- Consumes: Task 1 `summary.json` and public WordPress/site data observed on the real pilot.
- Produces: a deterministic mutation plan containing only fields with authoritative source values and rollback snapshots.

- [ ] **Step 1: Write the failing mutation-plan contract**

The contract must require each mutation item to contain: `owner`, `field`, `before`, `after`, `source`, `reversible`, and `rollback`. Allowed owners are `wordpress_core` and `aznet_theme_presentation_config`. Reject `rootprofile_private`, `convertflow_private`, `woocommerce_private`, `seo_private`, inferred identity/routing, and any person/contact/business fact whose source is `unknown`.

- [ ] **Step 2: Verify RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/tamduc-homepage-mutation-plan-contract.php
```

Expected: FAIL because `ops/tamduc-homepage/mutation-plan.json` does not yet exist.

- [ ] **Step 3: Populate only sourced values**

Use exact values already approved/observed on the site. The owner-approved identity copy may include Site Title `Tâm Đức – Hà Nội` and Site Tagline `Trọn Tâm với khách – Vẹn Đức với nghề`. Phone/hotline, logo, office image, personnel, address and credentials may be changed only when Task 1 or an existing authoritative WordPress source exposes the exact real value. Otherwise mark them `unknown` and exclude them from mutation.

- [ ] **Step 4: Verify GREEN**

Run the focused contract and inspect the JSON manually for zero fabricated values.

- [ ] **Step 5: Commit**

```bash
git add tests/offline/tamduc-homepage-mutation-plan-contract.php ops/tamduc-homepage/mutation-plan.json docs/evidence/TAMDUC_HOMEPAGE_BASELINE_20260916.md
git commit -m "docs: define sourced Tâm Đức homepage mutations"
```

---

### Task 3: Apply Bounded WordPress-Owned Site Configuration

**Files:**
- Create: `tests/offline/tamduc-homepage-apply-contract.php`
- Create: `tests/browser/tamduc-homepage-apply.mjs`
- Create: `.github/workflows/tamduc-homepage-apply.yml`
- Create: `docs/evidence/TAMDUC_HOMEPAGE_APPLY_20260916.md`

**Interfaces:**
- Consumes: Task 2 mutation plan and Task 1 before-snapshot.
- Produces: one authenticated mutation run, one after-snapshot, and a deterministic rollback plan.

- [ ] **Step 1: Write the failing apply-safety contract**

Require the apply workflow to run only on `ops/tamduc-homepage-completion`, use repository secrets, load the checked-in mutation plan, verify all `before` values before saving, abort on drift, avoid Theme/plugin update/install/delete screens, avoid provisioning unless the plan explicitly contains a currently supported Theme-owned configuration action, and upload before/after evidence even on failure.

- [ ] **Step 2: Verify intended RED**

Expected: FAIL because apply workflow/harness are missing.

- [ ] **Step 3: Implement minimal UI mutation**

Use normal WordPress admin UI only. Save only listed WordPress-owned/site-configuration fields. Never mutate client data in Theme source. Never touch RootProfile/ConvertFlow private data. If a planned control is unavailable in deployed Theme `1.1.0`, record `BLOCKED_RELEASE_CAPABILITY` and skip it rather than hot-patching production.

- [ ] **Step 4: Verify GREEN locally**

Run the static contract and `node --check`.

- [ ] **Step 5: Commit and trigger the apply run**

```bash
git add tests/offline/tamduc-homepage-apply-contract.php tests/browser/tamduc-homepage-apply.mjs .github/workflows/tamduc-homepage-apply.yml
git commit -m "ops: apply sourced Tâm Đức homepage configuration"
```

- [ ] **Step 6: Inspect the real run before claiming PASS**

Required: exact before values matched; only planned fields changed; after values match plan; no unrelated WordPress/plugin/provider changes; rollback data is complete.

---

### Task 4: Public Homepage L4 Verification After Mutation

**Files:**
- Create: `tests/browser/tamduc-homepage-final-l4.mjs`
- Create: `tests/offline/tamduc-homepage-final-contract.php`
- Create: `.github/workflows/tamduc-homepage-final.yml`
- Create: `docs/evidence/TAMDUC_HOMEPAGE_FINAL_20260916.md`

**Interfaces:**
- Consumes: applied site state from Task 3.
- Produces: fresh public Homepage evidence across 1440/1024/390/320.

- [ ] **Step 1: Write RED contract for final Homepage QA**

Require checks for exactly one `<main>`, at least one H1, zero horizontal overflow, zero Theme-owned console/page/request errors, zero unowned critical/serious Axe findings, working internal CTA links, visible primary navigation, and responsive section geometry. Record section presence rather than inventing expected business facts.

- [ ] **Step 2: Verify intended RED**

Expected: FAIL because final L4 workflow/harness are absent.

- [ ] **Step 3: Implement public browser matrix**

Test the real `/` at 1440x1000, 1024x900, 390x844 and 320x800. Capture screenshots and `summary.json`. If a remaining defect is authored content, Theme presentation, or unreleased capability, classify it explicitly by owner.

- [ ] **Step 4: Run static GREEN and push for live L4**

Do not claim success until the GitHub Actions run is fresh on the exact head.

- [ ] **Step 5: Record final evidence**

The final evidence must separate:

- `PASS`: site configuration/content actually completed and verified;
- `BLOCKED_RELEASE_CAPABILITY`: desired Burgundy/Tâm Đức presentation features that exist only in unreleased canonical source;
- `UNKNOWN_CONTENT`: real client facts/assets still unavailable;
- `NOT_CLAIMED`: RootProfile/ConvertFlow provider L5 and Theme release/deployment.

---

### Task 5: Cleanup and Handoff

**Files:**
- Update: `docs/evidence/TAMDUC_HOMEPAGE_FINAL_20260916.md`
- Delete temporary mutation workflow/harness only after evidence is preserved and rollback is no longer needed; retain read-only QA only if it has ongoing value.

**Interfaces:**
- Consumes: Task 4 final evidence.
- Produces: recoverable checkpoint and exactly one next action.

- [ ] **Step 1: Re-run relevant static contracts after cleanup**

Ensure temporary site-mutation mechanics do not accidentally become production Theme runtime behavior.

- [ ] **Step 2: Compare branch against `main`**

Production PHP/CSS/JS should be unchanged unless Task 4 discovered a genuine reusable Theme presentation defect and a separately reviewed Theme code slice was opened. Site-specific client data must not appear in production Theme files.

- [ ] **Step 3: Final checkpoint**

If all site-owned work possible on deployed `1.1.0` is complete, stop at the true hard gate: separate explicit approval for Theme release/deployment of the unreleased presentation capability. Do not merge or deploy merely because the site-ops slice is green.
