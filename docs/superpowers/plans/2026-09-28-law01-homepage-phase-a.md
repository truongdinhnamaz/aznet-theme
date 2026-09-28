# Law 01 Homepage Phase A Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the Law 01 Homepage audit/hardening milestone on current canonical main, fix only proven Theme-owned presentation defects, and close Phase A with fresh layer-specific evidence before moving to inner surfaces.

**Architecture:** Preserve the shared `homepage_effective_surface_map('law-01')` as the single effective Homepage composition model. Add only bounded public-presentation filtering for WordPress child Pages that cannot render a meaningful public card, while keeping raw WordPress children available for authoring/admin; keep Analysis/News empty-source behavior fail-soft and verify the accepted Burgundy presentation across the existing five-viewport browser matrix.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, WordPress Core APIs, plain CSS, Node 22 + Playwright/Axe browser QA, GitHub Actions, no bundler.

**Spec:** `docs/superpowers/specs/2026-09-28-law01-completion-audit-design.md`

## Execution subdivision — small recoverable micro-slices

To reduce timeout/progress-loss risk, execute the plan below as independent checkpoints. Never combine two production behaviors into one commit.

- **A0 — L0 audit checkpoint:** evidence/state only. Already separated from production code.
- **A1 — Services RED only:** add the failing runtime/static contract for untitled Service children. No production code.
- **A2 — Services helper GREEN:** add only `homepage_renderable_child_pages()`; run its narrow tests; commit.
- **A3 — Services consumer parity:** switch Services surface/template/profile/admin public-preview decisions to the helper; regression; commit.
- **A4 — Services browser fixture:** add one untitled Service source Page + long-title stress and browser assertions; commit.
- **B1 — Team RED only:** add failing raw-vs-public Team projection tests. No production code.
- **B2 — Team helper GREEN:** add only `team_directory_public_members()`; run narrow tests; commit.
- **B3 — Team public consumers:** Homepage/shared map/public directory use the public projection; admin keeps raw members; regression; commit.
- **B4 — Team browser fixture:** add one untitled Team child and public-card assertions; commit.
- **C1 — Empty editorial runtime regression:** Analysis/News empty-category test only; no production change unless it exposes a real defect.
- **C2 — Homepage surface-order browser regression:** exact Burgundy order assertion only; retain existing viewport/a11y gates.
- **D1 — Final static/core verification:** no fixes bundled; any failure opens its own RED→GREEN corrective micro-slice.
- **D2 — Evidence closure:** update evidence only.
- **D3 — Draft PR review checkpoint:** no merge, release, version bump, or deployment.

Each micro-slice must end recoverably before the next begins. If a deeper failure appears, stop that micro-slice, reproduce it at the shallowest layer, and fix it in a new bounded checkpoint rather than expanding the current slice.

## Global Constraints

- Canonical repository is `truongdinhnamaz/aznet-theme`; implementation starts from the latest valid `main`, not from historical Law 01 PRs.
- SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.
- Theme may consume WordPress/public-safe sources but must not fabricate Page titles, people, credentials, service semantics, category semantics, provider state, or business facts.
- Do not infer identity/routing/meaning from slug, URL, Page ID, author account, title fragments, or other heuristics.
- Do not read RootProfile/ConvertFlow/WooCommerce private option/meta/CPT/table storage.
- Existing accepted Law 01 Burgundy Homepage presentation is retained unless fresh evidence proves a regression.
- Production feature/bugfix work follows RED -> intended failure -> minimal GREEN -> retained regression.
- WordPress 6.9+ and PHP 8.1+ remain the support floor.
- Naming remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- No new bundler/build stack; Law 01 assets remain surface-aware.
- No release, tag, production deployment, provider takeover, destructive content mutation, or version promotion is authorized by this plan.
- Pilot `lstamduchn.vn` is read-only evidence unless a separately approved safe draft/staging path becomes available.

## Review Focus

- A published Service child Page with an empty title must never render a blank public card or be replaced by a title inferred from its slug; the public six-card limit should be filled from later valid siblings when available.
- A published Team child Page with an empty title must remain available to WordPress/admin authoring but must not render as an unnamed public person card on Homepage or Team directory.
- Empty Analysis/Legal News categories must omit those sections cleanly without placeholder legal content, while unrelated Homepage surfaces retain their expected order.
- The shared effective surface map, admin readiness/map, and public templates must not drift into separate source-resolution rules for the same Law 01 surface.
- If OneShield/WPVibe/runner availability prevents exact pilot L3/L4 proof, record BLOCKED/UNKNOWN at that layer; never substitute fixture PASS for live pilot PASS.

---

### Task 1: Revalidate canonical state and record the Homepage audit matrix

**Files:**
- Create: `docs/evidence/LAW01_HOMEPAGE_PHASE_A_AUDIT_20260928.md`
- Read only: `docs/source/AZT-EXEC-MAP.md`
- Read only: `docs/source/AZT-03-baseline-provenance.md`
- Read only: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Read only: `inc/theme/homepage-surface-map.php`
- Read only: `inc/theme/homepage-composer.php`
- Read only: `template-parts/homepage/law-01/**`

**Interfaces:**
- Consumes: latest canonical `main`; approved design spec; `homepage_effective_surface_map('law-01')`.
- Produces: one evidence matrix with columns `Surface | Effective source | Render gate | Pilot source state | Classification | Fresh QA layer | Notes`, covering Header, Hero, Profile/Trust, Services, About, Team, Latest, Topics, Analysis, Legal News, Process, FAQ, Final CTA, Footer.

- [ ] **Step 1: Rebase the execution baseline onto the latest canonical main**

Run:

```bash
git fetch origin main
git rev-parse origin/main
git status --short
grep -n '^Version:' style.css
grep -n 'AZNET_THEME_VERSION' functions.php
```

Expected: clean working tree before implementation; both Theme version declarations agree. If `origin/main` is newer than the design-time base `6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`, update the branch safely before any production edit and record the newer SHA in the evidence file.

- [ ] **Step 2: Run the retained Homepage contracts before changing production code**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-premium-presentation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-client-ready-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/law01-effective-source-parity-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-directory-contract.php
```

Expected: all PASS. Any failure is a fresh pre-existing regression and becomes the first RED micro-slice; do not proceed by masking it.

- [ ] **Step 3: Revalidate the pilot read-only state**

Check `lstamduchn.vn` through the currently available read-only WordPress/site connector. Record:

```text
active Theme version
front page ID
mapped Services/Team/Analysis/News source availability
whether the previously observed untitled Service/Team child Pages still exist
whether Analysis/News still have zero published Posts
WPVibe Connect active/inactive
OneShield/browser challenge state
```

Expected: evidence records actual current state only. Do not mutate pilot content.

- [ ] **Step 4: Write the audit matrix**

For each Phase A surface, classify exactly one of:

```text
PASS_RETAINED
THEME_DEFECT
SOURCE_GAP
BLOCKED_EXTERNAL_CONTRACT
QA_UNKNOWN
```

The matrix must explicitly identify the current known card-title risk: raw published child Pages are accepted by the existing WordPress queries even when their titles are empty.

- [ ] **Step 5: Commit the L0 audit checkpoint**

Run:

```bash
git add docs/evidence/LAW01_HOMEPAGE_PHASE_A_AUDIT_20260928.md
git commit -m "docs: audit Law 01 Homepage Phase A"
```

Expected: documentation-only recoverable checkpoint; no production file changed.

---

### Task 2: Make Services consume only renderable public child Pages

**Files:**
- Modify: `inc/theme/homepage-content-map.php`
- Modify: `inc/theme/homepage-surface-map.php`
- Modify: `template-parts/homepage/law-01/services.php`
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `inc/admin/homepage.php` only where a Services readiness/public-preview decision is derived from child renderability
- Modify: `tests/offline/homepage-content-map-contract.php`
- Create: `tests/runtime/law01-homepage-source-gaps.php`
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: `homepage_direct_published_children(int $parent_id, int $limit): array` as the raw WordPress child query.
- Produces: `homepage_renderable_child_pages(int $parent_id, int $limit): array<int,\WP_Post>`.
- Public renderability requires: published direct child Page, non-empty trimmed WordPress title, and a non-empty public permalink. It must not derive title/meaning from slug or other metadata.

- [ ] **Step 1: Write the RED runtime contract for an untitled Service child**

In `tests/runtime/law01-homepage-source-gaps.php`, create a temporary published parent Page and seven direct published children:

```php
$untitled_id = wp_insert_post([
    'post_type'   => 'page',
    'post_status' => 'publish',
    'post_title'  => '',
    'post_name'   => 'lao-dong',
    'post_parent' => $parent_id,
    'menu_order'  => 0,
], true);

// Then create six titled siblings with menu_order 10,20,30,40,50,60.

$public = \AZnet\Theme\homepage_renderable_child_pages($parent_id, 6);
assert(6 === count($public));
assert(! in_array($untitled_id, array_map(static fn($p) => (int) $p->ID, $public), true));
foreach ($public as $page) {
    assert('' !== trim((string) get_the_title($page)));
}
```

Clean the temporary Pages in a `finally` block.

- [ ] **Step 2: Run the runtime contract and confirm the intended RED**

Run after the workflow WordPress fixture is installed:

```bash
wp eval-file tests/runtime/law01-homepage-source-gaps.php --path=/tmp/wp --allow-root
```

Expected: FAIL because `homepage_renderable_child_pages()` does not exist yet. The failure must not come from WordPress setup.

- [ ] **Step 3: Implement `homepage_renderable_child_pages(int $parent_id, int $limit): array`**

In `inc/theme/homepage-content-map.php`:

- clamp `$limit` to 1..24;
- read up to 24 raw direct published children through `homepage_direct_published_children($parent_id, 24)`;
- retain only `WP_Post` children whose trimmed `get_the_title($post)` is non-empty and whose `get_permalink($post)` resolves to a non-empty string;
- return the first `$limit` valid Pages with `array_slice`;
- do not inspect `post_name`, slug, URL fragments, custom fields, provider storage, or user/author data.

- [ ] **Step 4: Make every public Services decision consume the new projection**

Replace the public-card source in:

```text
inc/theme/homepage-surface-map.php         Services surface items
template-parts/homepage/law-01/services.php rendered Service cards
template-parts/homepage/law-01/profile.php  public service_count fact
inc/admin/homepage.php                      Services READY/EMPTY and public-preview child summaries only
```

Keep raw `homepage_direct_published_children()` for operations whose purpose is WordPress authoring/copying rather than deciding what can render publicly.

- [ ] **Step 5: Extend static contracts**

In `tests/offline/homepage-content-map-contract.php`, assert:

```php
str_contains($source, 'function homepage_renderable_child_pages(');
! str_contains($renderableHelperBody, 'post_name');
```

In the existing Law 01 static contract, assert the Services template and shared surface map reference `homepage_renderable_child_pages(`.

- [ ] **Step 6: Put the source gap into the browser fixture**

In `.github/workflows/homepage-law-01-browser.yml` fixture creation:

- add one published untitled Service child at `menu_order = 0`;
- retain six titled Service children after it.

In `tests/browser/homepage-law-01-l4.mjs`, keep the expected public Service card count at six and add:

```js
const serviceTitles = await page.locator('.aznet-theme-law01-services .aznet-theme-law01-card h3').allTextContents();
if (serviceTitles.length !== 6 || serviceTitles.some((title) => !title.trim())) {
  throw new Error(`Services rendered an unnamed public card: ${JSON.stringify(serviceTitles)}`);
}
```

This proves the limit is filled from later valid siblings rather than from an inferred label.

- [ ] **Step 7: Run GREEN verification**

Run:

```bash
php -l inc/theme/homepage-content-map.php
php -l inc/theme/homepage-surface-map.php
php -l template-parts/homepage/law-01/services.php
php -l template-parts/homepage/law-01/profile.php
php -l inc/admin/homepage.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-content-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
```

Expected: PASS. The runtime/browser workflow must also pass when the runner is available.

- [ ] **Step 8: Commit the bounded Services slice**

Run:

```bash
git add inc/theme/homepage-content-map.php inc/theme/homepage-surface-map.php template-parts/homepage/law-01/services.php template-parts/homepage/law-01/profile.php inc/admin/homepage.php tests/offline/homepage-content-map-contract.php tests/runtime/law01-homepage-source-gaps.php tests/browser/homepage-law-01-l4.mjs .github/workflows/homepage-law-01-browser.yml
git commit -m "fix: fail soft for untitled Law 01 services"
```

---

### Task 3: Keep untitled Team children editable but remove them from public person cards

**Files:**
- Modify: `inc/theme/team-directory.php`
- Modify: `inc/theme/homepage-surface-map.php`
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `template-parts/team/page.php`
- Modify: `tests/offline/team-directory-contract.php`
- Modify: `tests/offline/team-page-presentation-contract.php`
- Modify: `tests/runtime/team-directory-parity.php`
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Modify: `tests/browser/homepage-law-01-l4.mjs`

**Interfaces:**
- Consumes: `team_directory_members(int $limit = 0): array` as the raw published WordPress child list used by authoring/admin.
- Produces: `team_directory_public_members(int $limit = 0): array<int,\WP_Post>` for public cards only.
- Public member renderability requires a non-empty WordPress title and non-empty permalink; no person identity is inferred from slug, author, Page ID, avatar, or provider-private data.

- [ ] **Step 1: Write the RED public-member assertions**

Extend the Law 01 workflow fixture with one additional published Team child:

```php
$untitled_member = law01_page('', 'ls-khong-ten', '', '', $team, 5);
```

Keep the existing six titled members at their later menu orders.

Update `tests/runtime/team-directory-parity.php` so the raw and public projections are tested separately:

```php
$raw = \AZnet\Theme\team_directory_members();
$homepage = \AZnet\Theme\team_directory_public_members(4);
$public = \AZnet\Theme\team_directory_public_members();

assert(7 === count($raw));      // six titled + one untitled published child
assert(4 === count($homepage));
assert(6 === count($public));
foreach ($public as $member) {
    assert('' !== trim((string) get_the_title($member)));
}
```

- [ ] **Step 2: Run the runtime test and confirm the intended RED**

Run:

```bash
wp eval-file tests/runtime/team-directory-parity.php --path=/tmp/wp --allow-root
```

Expected: FAIL because `team_directory_public_members()` does not exist yet.

- [ ] **Step 3: Implement `team_directory_public_members(int $limit = 0): array`**

In `inc/theme/team-directory.php`:

- call `team_directory_members()` without a public limit so an untitled early child cannot consume the public slot budget;
- filter to `WP_Post` members with non-empty trimmed `get_the_title($post)` and non-empty string permalink;
- apply `array_slice` only after filtering when `$limit > 0`;
- do not change the raw `team_directory_members()` function.

- [ ] **Step 4: Route public Team presentation through the new projection**

Use `team_directory_public_members()` in:

```text
inc/theme/homepage-surface-map.php        profile.items
template-parts/homepage/law-01/profile.php Homepage four-card Team band
template-parts/team/page.php              public Team directory grid
```

Do not replace `team_directory_members()` in `inc/admin/homepage.php`; the admin authoring surface must still expose the untitled WordPress Page so the author can repair it.

- [ ] **Step 5: Extend ownership/static contracts**

In `tests/offline/team-directory-contract.php`, assert both raw and public functions exist and that the module still contains no forbidden slug/private-storage shortcuts.

In `tests/offline/team-page-presentation-contract.php`, assert the public directory template uses `team_directory_public_members()`.

- [ ] **Step 6: Extend browser assertions**

Keep the expected public Homepage Team count at four and Team directory count at six. Add:

```js
const homepageTeamNames = await page.locator('.aznet-theme-law01-team-card h3').allTextContents();
if (homepageTeamNames.some((name) => !name.trim())) throw new Error('Homepage rendered unnamed Team member');

const directoryNames = await directoryPage.locator('.aznet-theme-team-card h3').allTextContents();
if (directoryNames.some((name) => !name.trim())) throw new Error('Team directory rendered unnamed Team member');
```

The browser test must not assert or invent a name for `ls-khong-ten`.

- [ ] **Step 7: Run GREEN verification**

Run:

```bash
php -l inc/theme/team-directory.php
php -l inc/theme/homepage-surface-map.php
php -l template-parts/homepage/law-01/profile.php
php -l template-parts/team/page.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-page-presentation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-directory-contract.php
```

Expected: PASS. Runtime/browser tests must PASS when their environment is available.

- [ ] **Step 8: Commit the bounded Team slice**

Run:

```bash
git add inc/theme/team-directory.php inc/theme/homepage-surface-map.php template-parts/homepage/law-01/profile.php template-parts/team/page.php tests/offline/team-directory-contract.php tests/offline/team-page-presentation-contract.php tests/runtime/team-directory-parity.php tests/browser/homepage-law-01-l4.mjs .github/workflows/homepage-law-01-browser.yml
git commit -m "fix: omit unnamed Team cards from public Law 01"
```

---

### Task 4: Lock empty editorial fail-soft behavior and full Homepage surface order

**Files:**
- Create: `tests/runtime/law01-empty-editorial-surfaces.php`
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Modify: `tests/browser/homepage-law-01-l4.mjs`
- Modify production only if the new regression proves a real Theme defect.

**Interfaces:**
- Consumes: `homepage_effective_surface_map('law-01')`, populated Law 01 browser fixture, WordPress Category/Post APIs.
- Produces: executable proof that empty Analysis/News sources disappear without placeholders and that the fully populated Burgundy fixture renders its Theme-owned surfaces in the accepted order.

- [ ] **Step 1: Add the empty-category runtime regression**

In `tests/runtime/law01-empty-editorial-surfaces.php`:

1. snapshot `aznet_theme_settings`;
2. create two empty native Categories;
3. update only `homepage_case_analysis_term` and `homepage_legal_news_term` in the snapshot;
4. call `homepage_effective_surface_map('law-01')` in this fresh PHP process;
5. collect surface keys;
6. restore the original settings in `finally`.

Assert:

```php
assert(! in_array('analysis', $keys, true));
assert(! in_array('news', $keys, true));
foreach (['latest', 'topics', 'process', 'faq', 'final-cta'] as $required) {
    assert(in_array($required, $keys, true));
}
```

Do not create fallback Posts or text.

- [ ] **Step 2: Run the new runtime test**

Run:

```bash
wp eval-file tests/runtime/law01-empty-editorial-surfaces.php --path=/tmp/wp --allow-root
```

Expected on current architecture: PASS. If it fails, stop and create a RED-specific micro-slice at `inc/theme/homepage-surface-map.php` or the affected template before proceeding.

- [ ] **Step 3: Assert full populated-fixture surface order in the browser test**

In `tests/browser/homepage-law-01-l4.mjs`, read:

```js
const renderedSurfaceOrder = await page.locator('[data-aznet-homepage-surface]').evaluateAll(
  (nodes) => nodes.map((node) => node.getAttribute('data-aznet-homepage-surface'))
);
```

For the populated Burgundy fixture assert exactly:

```js
[
  'hero',
  'services',
  'profile',
  'latest',
  'topics',
  'analysis',
  'news',
  'process',
  'faq',
  'final-cta',
]
```

The native Front Page `the_content()` boundary is intentionally not represented as a Theme-owned `data-aznet-homepage-surface` item.

- [ ] **Step 4: Retain the five viewport/a11y gates**

Do not remove the existing browser assertions for:

```text
1920-class desktop
1440 desktop
1024 compact desktop/tablet
390 mobile
320 narrow mobile
horizontal overflow <= 1px
visible keyboard skip-link focus
mobile nav open/Escape close
zero serious/critical Axe findings
zero console/page/request failures
Header/Footer shell alignment
```

- [ ] **Step 5: Wire the new runtime test into the Law 01 browser workflow**

Add:

```bash
wp eval-file tests/runtime/law01-empty-editorial-surfaces.php --path=/tmp/wp --allow-root
```

after the typed fixture exists and before browser execution. Also add both new runtime files to the workflow \`paths\` trigger so their changes cannot bypass this workflow.

- [ ] **Step 6: Commit the audit-regression slice**

Run:

```bash
git add tests/runtime/law01-empty-editorial-surfaces.php tests/browser/homepage-law-01-l4.mjs .github/workflows/homepage-law-01-browser.yml
git commit -m "test: lock Law 01 Homepage source-gap behavior"
```

---

### Task 5: Close Phase A evidence and open the reviewable PR

**Files:**
- Modify: `docs/evidence/LAW01_HOMEPAGE_PHASE_A_AUDIT_20260928.md`
- Modify source docs only if implementation created a new durable architecture/public-contract/governance decision; otherwise leave AZT source unchanged.
- No version metadata change.
- No release/deployment file change.

**Interfaces:**
- Consumes: exact final branch head from Tasks 1-4.
- Produces: fresh L0-L2 evidence, L3/L4 evidence where actually executable, explicit pilot BLOCKED/UNKNOWN states, rollback information, and one reviewable PR.

- [ ] **Step 1: Run final static/contract regression**

Run:

```bash
php -l inc/theme/homepage-content-map.php
php -l inc/theme/homepage-surface-map.php
php -l inc/theme/team-directory.php
php -l template-parts/homepage/law-01/services.php
php -l template-parts/homepage/law-01/profile.php
php -l template-parts/team/page.php

php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-content-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-surface-map-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-premium-presentation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-client-ready-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-directory-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-team-directory-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/team-page-presentation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/law01-effective-source-parity-contract.php
```

Expected: PASS with zero failures.

- [ ] **Step 2: Run the reusable core gate**

Run:

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS. A failure must be reproduced at the shallowest relevant test before any fix.

- [ ] **Step 3: Run/inspect Law 01 runtime-browser evidence**

Use the existing `Homepage Composer Law 01 Browser Quality` workflow on the exact final head.

Expected when runner infrastructure is available:

```text
WordPress 6.9 runtime fixture PASS
source-gap runtime tests PASS
five browser viewport cases PASS
zero horizontal overflow
zero blocking Axe findings
zero Theme-caused console/page/request errors
six named Services cards despite one untitled source Page
four named Homepage Team cards and six named Team directory cards despite one untitled source Page
populated surface order exactly matches the accepted Burgundy sequence
```

If GitHub runners are unavailable, record `BLOCKED_EXTERNAL_RUNNER`; do not claim L3/L4 PASS.

- [ ] **Step 4: Recheck pilot read-only without publishing**

If the pilot remains accessible read-only, record its current source gaps and active Theme version again. If OneShield still blocks browser proof or WPVibe remains inactive, record pilot L3/L4 as `QA_UNKNOWN/BLOCKED`. Do not patch live files.

- [ ] **Step 5: Finalize the Phase A evidence**

Update `docs/evidence/LAW01_HOMEPAGE_PHASE_A_AUDIT_20260928.md` with:

```text
PASS — exact Theme-owned slices and QA layers proven
BLOCKED/UNKNOWN — pilot/browser/provider claims not proven and why
EVIDENCE — branch, commits, workflow runs/artifacts, changed files
ROLLBACK — revert the bounded Services/Team commits independently
NEXT — owner review/merge of the Phase A PR; after canonical integration, write the separate Phase B inner-surface plan from the then-current main
```

Do not state that blank WordPress source titles were “fixed”; state that public Theme presentation now fails soft while WordPress retains the incomplete source Pages.

- [ ] **Step 6: Commit evidence**

Run:

```bash
git add docs/evidence/LAW01_HOMEPAGE_PHASE_A_AUDIT_20260928.md
git commit -m "docs: close Law 01 Homepage Phase A evidence"
```

- [ ] **Step 7: Open a PR against `main`**

PR scope must contain only:

```text
Phase A audit evidence
Services renderable-child fail-soft projection
Team public-member fail-soft projection
empty Analysis/News regression
Homepage order/browser regression
```

Do not merge, release, publish, deploy, or bump Theme version in this task.
