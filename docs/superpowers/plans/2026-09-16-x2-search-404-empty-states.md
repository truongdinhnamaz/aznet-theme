# X2 Search / 404 / Empty States Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn AZnet Theme's existing native WordPress search, 404 and empty-listing fallbacks into clear, responsive, accessible recovery/discovery experiences without taking over WordPress query or routing semantics.

**Architecture:** Keep WordPress Core as the source of truth for the main query, search term, archive state and 404 routing. Reuse the existing listing/content shell and generic-content CSS; add one bounded Theme-owned recovery template part used only by native Theme templates. No custom query, redirect resolver, ranking engine or provider integration is introduced.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP Theme + `theme.json`, existing AZnet Theme tokens/primitives, GitHub Actions, WP-CLI, Playwright 1.55, axe-core 4.10.

**Spec:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`

## Global Constraints

- Product scope is AZnet Theme only; Theme remains presentation owner.
- WordPress owns search query semantics, the main query, archive state and 404 routing.
- Theme metadata remains exactly `1.1.0` throughout X2; no version promotion.
- Do not add RootProfile, ConvertFlow, WooCommerce or other provider integration work.
- Do not call private provider APIs or read provider option/meta/CPT/table storage.
- Do not add `WP_Query`, `get_posts()`, custom ranking, redirect inference, slug/title/Page-ID/URL heuristics or a parallel search datastore.
- Retain the existing hybrid PHP + `theme.json` architecture and naming prefixes.
- Production behavior follows RED -> intended failure -> minimal GREEN -> retained regression -> L3 -> L4.
- Rollback must remove only Theme presentation and leave all WordPress-owned content/query/routing state untouched.

---

## File Structure

**Create**
- `template-parts/content/recovery.php` — bounded Theme-owned recovery/empty-state markup primitive; consumes an explicit Theme context and optional query label, but never performs a query or redirect.
- `tests/offline/x2-search-404-empty-contract.php` — X2 ownership/markup/static contract.
- `tests/runtime/x2-search-404-empty.php` — deterministic WordPress fixture for result, no-result, empty archive and 404 routes.
- `tests/browser/x2-search-404-empty-l4.mjs` — responsive/keyboard/overflow/axe browser matrix.
- `.github/workflows/x2-search-404-empty.yml` — isolated X2 L1-L4 workflow.
- `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md` — created only after fresh functional closure evidence exists.

**Modify**
- `search.php` — keep native main loop/query, compose recovery primitive for no-result state, improve result heading hierarchy.
- `404.php` — preserve WordPress 404 truth, compose recovery primitive and native search form.
- `archive.php` — keep native main loop/query, replace minimal no-content paragraph with shared recovery primitive.
- `assets/css/components/generic-content.css` — add inert recovery/search-form selectors using existing tokens; no new global asset handle.
- `tests/offline/editorial-listing-search-contract.php` — retain existing P3 requirements while allowing the bounded recovery template composition.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — after X2 closure only: X2 PASS / X3 NEXT.
- `docs/source/AZT-EXEC-MAP.md` — after X2 closure only: derived state update.
- `docs/source/SOURCE_MANIFEST.md` — after X2 closure only: source version/current-next alignment.

---

### Task 1: Establish the X2 RED ownership and presentation contract

**Files:**
- Create: `tests/offline/x2-search-404-empty-contract.php`
- Inspect: `search.php`, `404.php`, `archive.php`, `assets/css/components/generic-content.css`

**Interfaces:**
- Consumes: current native WordPress search/archive loops and existing generic content/listing classes.
- Produces: a deterministic contract defining the minimum X2 markup and forbidden ownership takeovers.

- [ ] **Step 1: Write the failing X2 contract**

The contract must read `search.php`, `404.php`, `archive.php`, `template-parts/content/recovery.php`, `assets/css/components/generic-content.css` and `inc/theme/assets.php`.

Require these post-X2 markers:

```php
$required_search = [
    "get_search_query()",
    "have_posts()",
    "the_post()",
    "get_template_part( 'template-parts/content/card' )",
    "get_template_part( 'template-parts/content/recovery', null,",
    "'context' => 'search-empty'",
];

$required_404 = [
    "get_template_part( 'template-parts/content/recovery', null,",
    "'context' => '404'",
    "get_search_form()",
];

$required_archive = [
    "have_posts()",
    "the_post()",
    "the_posts_pagination()",
    "get_template_part( 'template-parts/content/recovery', null,",
    "'context' => 'archive-empty'",
];

$required_recovery = [
    'aznet-theme-recovery',
    'aznet-theme-recovery__title',
    'aznet-theme-recovery__message',
    'aznet-theme-recovery__actions',
    "home_url( '/' )",
];
```

Forbid takeover markers across X2 PHP sources:

```php
$forbidden = [
    'new WP_Query',
    'WP_Query(',
    'get_posts(',
    'query_posts(',
    '$wpdb',
    'wp_redirect(',
    'wp_safe_redirect(',
    'get_option(',
    'get_post_meta(',
    'application/ld+json',
    'rel="canonical"',
];
```

Require CSS markers:

```php
$required_css = [
    '.aznet-theme-recovery',
    '.aznet-theme-recovery__actions',
    '.aznet-theme-recovery .search-form',
    ':focus-visible',
];
```

Also assert that `functions.php` and `style.css` remain outside X2 version changes in the workflow diff gate.

- [ ] **Step 2: Run the contract and verify intended RED**

Run:

```bash
php tests/offline/x2-search-404-empty-contract.php
```

Expected: FAIL because `template-parts/content/recovery.php` does not exist yet. The failure must be the missing X2 recovery primitive, not syntax/infrastructure.

- [ ] **Step 3: Commit the RED contract**

```bash
git add tests/offline/x2-search-404-empty-contract.php
git commit -m "test: define X2 search recovery contract"
```

---

### Task 2: Implement the minimal native recovery primitive and template composition

**Files:**
- Create: `template-parts/content/recovery.php`
- Modify: `search.php`
- Modify: `404.php`
- Modify: `archive.php`
- Modify: `assets/css/components/generic-content.css`
- Modify: `tests/offline/editorial-listing-search-contract.php`
- Test: `tests/offline/x2-search-404-empty-contract.php`

**Interfaces:**
- Consumes: `$args['context']` with one of `search-empty`, `archive-empty`, `404`; optional `$args['label']` for a public-safe display string.
- Produces: semantic `.aznet-theme-recovery` markup, optional native `get_search_form()` on search/404 contexts, and a home recovery action using `home_url( '/' )`.

- [ ] **Step 1: Add the minimal recovery template part**

Implement `template-parts/content/recovery.php` with no data lookup beyond the explicit args and WordPress-native presentation helpers.

Required behavior:

```php
$context = isset( $args['context'] ) && is_string( $args['context'] ) ? $args['context'] : 'archive-empty';
$label   = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : '';
```

Copy mapping:
- `search-empty`: title `No results found`; message includes the escaped search label when present; render `get_search_form()` plus Home link.
- `404`: title `Page not found`; message explains the requested page is unavailable; render `get_search_form()` plus Home link.
- `archive-empty`: title `No content found`; concise message; Home link only.

Markup must use one heading inside the recovery section and native links/forms only. Do not create redirects or query helpers.

- [ ] **Step 2: Compose the primitive from `search.php`**

Preserve the existing native main loop exactly for result truth. For the no-results branch call:

```php
get_template_part(
    'template-parts/content/recovery',
    null,
    [
        'context' => 'search-empty',
        'label'   => get_search_query(),
    ]
);
```

The page-level search heading continues to use `get_search_query()` and must remain escaped.

- [ ] **Step 3: Compose the primitive from `404.php`**

Keep WordPress 404 routing untouched. Replace the minimal body with:

```php
get_template_part(
    'template-parts/content/recovery',
    null,
    [ 'context' => '404' ]
);
```

Do not infer a destination from the missing URL.

- [ ] **Step 4: Compose the primitive from the empty branch of `archive.php`**

Keep the archive main loop and pagination untouched; only replace the no-content paragraph:

```php
get_template_part(
    'template-parts/content/recovery',
    null,
    [ 'context' => 'archive-empty' ]
);
```

- [ ] **Step 5: Add bounded recovery styles to `generic-content.css`**

Use existing tokens only. Add selectors equivalent to:

```css
.aznet-theme-recovery {
    display: grid;
    gap: var(--aznet-theme-space-4);
    max-width: var(--aznet-theme-container-content);
    padding: var(--aznet-theme-space-5);
    color: var(--aznet-theme-text);
    background: var(--aznet-theme-surface);
    border: 1px solid var(--aznet-theme-border);
    border-radius: var(--aznet-theme-radius-card);
}

.aznet-theme-recovery__title,
.aznet-theme-recovery__message { margin: 0; }

.aznet-theme-recovery__message { color: var(--aznet-theme-muted); }

.aznet-theme-recovery__actions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--aznet-theme-space-3);
    align-items: end;
}

.aznet-theme-recovery .search-form {
    display: flex;
    flex-wrap: wrap;
    gap: var(--aznet-theme-space-2);
    min-width: 0;
}

.aznet-theme-recovery :where(a, input, button):focus-visible {
    outline: 2px solid var(--aznet-theme-focus);
    outline-offset: 2px;
}
```

Inputs/buttons must not force horizontal overflow at 390px.

- [ ] **Step 6: Retain and narrow the P3 listing/search contract**

Keep all current assertions that preserve the WordPress main query and result card composition. Update only the no-results expectation from an inline `get_search_form()` marker in `search.php` to the new bounded recovery template composition plus a check that `recovery.php` itself contains `get_search_form()`.

Do not weaken existing `WP_Query`, `get_posts`, storage or SEO takeover prohibitions.

- [ ] **Step 7: Run GREEN and retained regressions**

Run:

```bash
php tests/offline/x2-search-404-empty-contract.php
php tests/offline/editorial-listing-search-contract.php
php tests/offline/d027-provisioning-independence-contract.php
php tests/offline/x1-comments-surface-contract.php
php -l search.php
php -l 404.php
php -l archive.php
php -l template-parts/content/recovery.php
```

Expected: all PASS.

- [ ] **Step 8: Commit minimal GREEN**

```bash
git add search.php 404.php archive.php template-parts/content/recovery.php assets/css/components/generic-content.css tests/offline/editorial-listing-search-contract.php
git commit -m "feat: complete native search recovery surfaces"
```

---

### Task 3: Prove real WordPress search, empty archive and 404 behavior at L3/L4

**Files:**
- Create: `tests/runtime/x2-search-404-empty.php`
- Create: `tests/browser/x2-search-404-empty-l4.mjs`
- Create: `.github/workflows/x2-search-404-empty.yml`

**Interfaces:**
- Consumes: exact Theme candidate, clean WordPress 6.9, zero active plugins.
- Produces: deterministic URLs for one result search, one empty search, one empty native category archive and one native 404; browser summary and screenshots/artifacts.

- [ ] **Step 1: Create the deterministic WordPress fixture**

`tests/runtime/x2-search-404-empty.php` must:

1. insert/update one published native Post titled `X2 Recovery Needle` with comments irrelevant to this slice;
2. create an empty category `x2-empty-category` and do not attach the fixture Post to it;
3. emit JSON containing:

```json
{
  "result_search_url": "http://127.0.0.1:8080/?s=X2+Recovery+Needle",
  "empty_search_url": "http://127.0.0.1:8080/?s=x2-no-results-sentinel",
  "empty_archive_url": "http://127.0.0.1:8080/?cat=<category_id>",
  "not_found_url": "http://127.0.0.1:8080/?p=99999999"
}
```

The fixture may create WordPress test content only; it must not alter Theme settings or provider state.

- [ ] **Step 2: Add L3 HTML assertions in the workflow**

On clean WordPress 6.9 / PHP 8.1 / zero plugins, curl all four routes and assert:

- result search contains `X2 Recovery Needle` and does **not** contain `.aznet-theme-recovery`;
- empty search contains `.aznet-theme-recovery`, native `.search-form`, and Home action;
- empty archive contains `.aznet-theme-recovery` and no fabricated cards;
- 404 returns HTTP 404, contains `.aznet-theme-recovery`, native `.search-form`, and Home action;
- each route has exactly one document `<main>`;
- no X2 code changes provider integration directories;
- Theme metadata remains `1.1.0`.

- [ ] **Step 3: Add the browser matrix**

`tests/browser/x2-search-404-empty-l4.mjs` must test all four routes at:

- `1440x1000`;
- `1024x900`;
- `390x844`.

Expected matrix: **12/12** route × viewport checks.

For every route enforce:

```js
// pseudocode-level assertions the script must implement with Playwright APIs
expectOneMain();
expectNoHorizontalOverflow();
expectNoConsoleErrors();
expectNoPageErrors();
expectNoFailedNonDocumentSubresources();
expectNoCriticalOrSeriousAxeViolations();
```

Additional route assertions:
- result search: visible result card and keyboard-visible focus on result link;
- empty search: recovery title/message, native search input + submit, keyboard-visible focus on both search input and Home action;
- empty archive: recovery title/message and keyboard-visible Home action;
- 404: recovery title/message, native search input + submit, Home action, HTTP 404 retained.

Save one screenshot per route/viewport and `summary.json` under `/tmp/x2-search-404-l4`.

- [ ] **Step 4: Add the X2 GitHub Actions workflow**

`.github/workflows/x2-search-404-empty.yml` must:

1. trigger on X2 branch/PR paths and support `workflow_dispatch`;
2. checkout exact candidate with `fetch-depth: 0`;
3. run X2 + retained offline contracts and PHP lint;
4. verify `git diff --check origin/main...HEAD`;
5. reject deltas under `inc/integrations/` and `assets/css/integrations/`;
6. reject version changes to `functions.php`/`style.css`;
7. install clean WordPress 6.9 + exact Theme + zero plugins;
8. seed fixture;
9. run L3 curl assertions;
10. install Playwright/axe and run 12/12 matrix;
11. reject Theme runtime fatal/parse/uncaught markers, while retaining only the already documented WordPress-core warning allowance if it appears;
12. upload runtime/browser evidence.

- [ ] **Step 5: Run until exact functional head is green**

Expected final workflow result: SUCCESS with L1/L2/L3/L4 all green on the same exact head.

Any deeper failure must first be reduced to the shallowest stable reproduction before changing production code.

- [ ] **Step 6: Commit the harness**

```bash
git add tests/runtime/x2-search-404-empty.php tests/browser/x2-search-404-empty-l4.mjs .github/workflows/x2-search-404-empty.yml
git commit -m "test: verify X2 recovery surfaces"
```

---

### Task 4: Close X2 evidence and authoritative execution state

**Files:**
- Create: `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: exact successful X2 functional workflow/run IDs, artifact IDs/digests, exact head SHA.
- Produces: authoritative state `X2 PASS / X3 NEXT`; no product/architecture/public-contract change.

- [ ] **Step 1: Record evidence only from observed successful runs**

Evidence must include:
- RED run and intended failure reason;
- GREEN/static retained regression run;
- exact L3/L4 functional closure head SHA;
- WordPress 6.9 / PHP 8.1 / zero-plugin state;
- 12/12 browser matrix result;
- artifact IDs and SHA-256 digests;
- explicit ownership boundary and rollback;
- `UNKNOWN / not claimed`: provider L5, X3, v1.2 release/package closure.

Do not invent run IDs or digests.

- [ ] **Step 2: Update authoritative/derived source state**

After fresh X2 evidence exists:
- AZT-04: bump one source version, set `X2 PASS / X3 NEXT`, cite evidence and exact functional head.
- AZT-EXEC-MAP: bump one derived-map version, mark X2 PASS and X3 NEXT.
- SOURCE_MANIFEST: align both versions and exact-next text.
- Do not change AZT-05/AZT-01/AZT-02 unless implementation exposed a genuinely new architecture/ownership/public-contract decision.

- [ ] **Step 3: Run source/diff consistency checks**

```bash
git diff --check origin/main...HEAD
grep -F 'X2 PASS' docs/source/AZT-04-roadmap-qa-decisions.md
grep -F 'X3' docs/source/AZT-EXEC-MAP.md
grep -F 'X3' docs/source/SOURCE_MANIFEST.md
```

Verify final diff contains no temporary source-editing workflow/script.

- [ ] **Step 4: Run the exact final-head X2 workflow again if source/evidence paths trigger it**

Expected: SUCCESS. Do not infer PASS from an earlier functional tree if the final PR head differs and the active exit gate requires fresh exact-head evidence.

- [ ] **Step 5: Commit X2 closure**

```bash
git add docs/evidence/X2_SEARCH_404_EMPTY_20260916.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: close X2 search recovery surfaces"
```

---

### Task 5: PR gate and merge handoff

**Files:**
- No new production files unless a verified Theme-owned regression requires a bounded fix.

**Interfaces:**
- Consumes: final X2 branch head with exact-head green verification.
- Produces: one owner-reviewable PR into `main`; merge remains an explicit owner gate.

- [ ] **Step 1: Compare final branch against canonical `main`**

Confirm:
- only X2 production/test/evidence/source files are changed;
- no provider integration file changed;
- `style.css` and `AZNET_THEME_VERSION` remain `1.1.0`;
- no temporary workflow/script remains;
- branch is mergeable without conflict.

- [ ] **Step 2: Re-read exact-head workflow status**

All X2 required jobs must be completed SUCCESS on the exact PR head.

- [ ] **Step 3: Open the X2 implementation PR**

Suggested title:

```text
feat: complete X2 search and recovery surfaces
```

PR body must summarize WordPress ownership, X2 Theme-owned presentation changes, RED/GREEN/L3/L4 evidence, 12/12 browser matrix, version discipline and X3 exact next.

- [ ] **Step 4: Stop at owner merge gate**

Do not merge `main` without explicit owner approval. Do not delete the branch without separate explicit destructive approval.

---

## Self-Review

- **Spec coverage:** Search result/no-result, 404 and generic empty archive presentation are covered; WordPress query/routing authority is retained; responsive/focus/a11y and surface boundaries are explicitly tested; X3 is the sole Next.
- **No placeholders:** No TBD/TODO/"implement later" instructions remain.
- **Type/interface consistency:** `recovery.php` consumes only `context` and optional `label`; all template callers use the same keys.
- **YAGNI:** No new search engine, query helper, routing resolver, JS application layer, provider integration or new build system.
- **Rollback:** Reverting X2 template/CSS commits restores prior minimal presentation without altering WordPress data, query state, archive state or 404 routing.
