# Law Site Provisioning v1.1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade Law Site Provisioning so a user can accept safe recommendations, reuse/create the right WordPress-native sources, import starter media, seed enough neutral editorial content for a coherent Law 01 Homepage, and keep indexing safe on both new and active sites.

**Architecture:** Extend the existing explicit/idempotent provisioning subsystem with a pure recommendation layer, a v1.1 blueprint (`law01-v1-1`), new plan operations for starter Posts/media/search visibility, and runner/readiness support. Recommendations remain proposal-only until the immutable change plan is confirmed; WordPress owns every created Page/Post/Category/attachment afterward, and active sites never receive whole-site noindex just to make demo content public.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP Theme + `theme.json`, native WordPress Pages/Posts/Categories/Media/Menu APIs, existing AZnet Theme settings/Content Map, Playwright + axe browser QA, GitHub Actions deterministic package build.

**Spec:** `docs/superpowers/specs/2026-09-09-law-site-provisioning-v1-1-design.md`

## Global Constraints

- Canonical repo is `truongdinhnamaz/aznet-theme`; implementation stays inside AZnet Theme.
- Theme remains presentation owner; WordPress owns Page/Post/Category/Media/publication/index-visibility state after explicit provisioning writes.
- No private Rank Math/Yoast storage/API writes; no new SEO-provider contract in this slice.
- No authoritative mapping by slug, URL, Page ID, fuzzy matching, synonym tables or private storage heuristics.
- A unique exact normalized display-label match may preselect a **suggestion only**; the confirmed change plan remains the write gate.
- Existing user content, featured images, mappings and menu/front-page state are not overwritten silently.
- New/mostly-empty sites may publish starter Posts only when the same confirmed plan also sets WordPress-native `blog_public = 0`; otherwise starter Posts are Draft.
- Existing/active sites never receive whole-site noindex from this workflow; without an accepted per-Post SEO contract, starter Posts remain Draft.
- v1.1 machine key is exactly `law01-v1-1`; existing `law01-v1` provenance remains valid.
- No fake lawyers, organizations, credentials, metrics, testimonials, awards, case outcomes, current-news claims or time-sensitive substantive legal advice.
- Starter media must be project-owned/generated and redistribution-safe; imported runtime source is a WordPress attachment, not a Theme asset URL.
- Feature/bugfix work follows RED → expected failure → minimal GREEN → regression.
- Main merge, tag, GitHub Release and production deployment remain explicit owner gates.

---

### Task 1: Ratify v1.1 source architecture and decision gate

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Test: source/version grep checks in shell

**Interfaces:**
- Consumes: approved spec `docs/superpowers/specs/2026-09-09-law-site-provisioning-v1-1-design.md`
- Produces: Accepted `D-026`, AZT-02 v0.8 provisioning recommendation/media/index boundary, AZT-04 v0.31 execution state, updated manifest/exec map used by all later tasks.

- [ ] **Step 1: Add the source decision before production code**

Add to AZT-04 Decision Log:

```markdown
| **D-026** | **Law Site Provisioning v1.1 may preselect non-authoritative exact-label recommendations, seed bounded WordPress-native starter editorial/media content, and temporarily discourage indexing only on a confirmed new/mostly-empty-site plan; active-site starter content fails safe to Draft without a public per-Post SEO contract, and all authoritative mapping/mutation remains explicit in the confirmed provisioning plan.** | **Accepted** |
```

Update P4-B state/description so v1.1 is the exact next provisioning slice and explicitly records `law01-v1-1`, smart recommendation, starter media/editorial coverage and index-safety. Increment AZT-04 from v0.30 → v0.31.

- [ ] **Step 2: Update architecture owner boundary**

In AZT-02 add a bounded v1.1 subsection that states:

```markdown
- recommendation state is proposal-only and never authoritative mapping;
- exact normalized display-label matching is allowed only to preselect UI suggestions;
- no slug/URL/fuzzy/synonym inference;
- starter Posts/attachments are created only through an explicit confirmed provisioning plan and become WordPress-owned;
- new/mostly-empty-site starter publication is coupled to explicit WordPress-native `blog_public=0` consent;
- active-site whole-site noindex is forbidden;
- no private SEO plugin writes.
```

Increment AZT-02 from v0.7 → v0.8.

- [ ] **Step 3: Update derived execution map and manifest**

Update `AZT-EXEC-MAP.md` to the next derived version and make the exact execution order:

```text
P4-B v1.1 source gate → recommendation/classification → starter editorial → plan/admin UX → media/runner → index/readiness → L3/L4 → candidate/evidence
```

Update `SOURCE_MANIFEST.md` versions for AZT-02/AZT-04 and D-001…D-026.

- [ ] **Step 4: Verify source gate**

Run:

```bash
grep -F "v0.8" docs/source/AZT-02-architecture.md
grep -F "v0.31" docs/source/AZT-04-roadmap-qa-decisions.md
grep -F "D-026" docs/source/AZT-04-roadmap-qa-decisions.md
grep -F "law01-v1-1" docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md
grep -F "D-026" docs/source/SOURCE_MANIFEST.md
```

Expected: every grep exits 0 and source text preserves the owner/index-safety boundaries above.

- [ ] **Step 5: Commit**

```bash
git add docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: ratify Law Site Provisioning v1.1"
```

---

### Task 2: Add site classification and non-authoritative recommendations

**Files:**
- Create: `inc/theme/provisioning-recommendations.php`
- Modify: `inc/theme/provisioning-discovery.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/provisioning-recommendations-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `provisioning_discovery(): array`, existing Content Map/settings, `provisioning_find_owned_role()`.
- Produces: `provisioning_site_mode(array $discovery): string`, `provisioning_normalize_label(string $label): string`, `provisioning_recommendations(string $blueprint_key, array $discovery): array`.

- [ ] **Step 1: Write the RED recommendation contract**

Create `tests/offline/provisioning-recommendations-contract.php` asserting these exact cases against source/API behavior:

```php
assert( function_exists( 'AZnet\\Theme\\provisioning_site_mode' ) );
assert( function_exists( 'AZnet\\Theme\\provisioning_recommendations' ) );
assert( function_exists( 'AZnet\\Theme\\provisioning_normalize_label' ) );

assert( 'dân sự' === \AZnet\Theme\provisioning_normalize_label( "  DÂN   SỰ  " ) );

$new = [
    'prior_blueprints' => [], 'show_on_front' => 'posts', 'front_page_id' => 0,
    'primary_menu_id' => 0, 'published_post_count' => 0, 'page_count' => 2,
];
assert( 'NEW_OR_MOSTLY_EMPTY' === \AZnet\Theme\provisioning_site_mode( $new ) );

$active = $new;
$active['published_post_count'] = 1;
assert( 'EXISTING_ACTIVE' === \AZnet\Theme\provisioning_site_mode( $active ) );

$previous = $active;
$previous['prior_blueprints'] = [ 'law01-v1' ];
assert( 'PREVIOUSLY_PROVISIONED' === \AZnet\Theme\provisioning_site_mode( $previous ) );
```

Also assert source guards forbid `sanitize_title`, slug/URL lookup and fuzzy functions inside the recommendation module.

- [ ] **Step 2: Run RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-recommendations-contract.php
```

Expected: FAIL because the new recommendation functions/module do not exist.

- [ ] **Step 3: Extend discovery with only observable state needed by classification**

Modify `provisioning-discovery.php` so the returned payload includes:

```php
'published_post_count' => (int) ( wp_count_posts( 'post' )->publish ?? 0 ),
'prior_blueprints'      => provisioning_discovered_blueprints(),
```

Add `provisioning_discovered_blueprints(): array` that recognizes only AZnet provenance values `law01-v1` and `law01-v1-1`; do not infer prior setup from titles/slugs.

- [ ] **Step 4: Implement pure recommendation logic**

Create `provisioning-recommendations.php` with constants/states and these signatures:

```php
function provisioning_site_mode( array $discovery ): string;
function provisioning_normalize_label( string $label ): string;
function provisioning_recommendations( string $blueprint_key, array $discovery ): array;
```

`provisioning_normalize_label()` must trim, collapse Unicode whitespace and lowercase with `mb_strtolower()` when available; fallback to `strtolower()` without removing Vietnamese diacritics.

Recommendation precedence for each Page/Category role must be exactly:

```text
CURRENT valid Content Map → PROVISIONED_REUSE → one exact normalized label match → AMBIGUOUS/SUGGESTED_CREATE
```

Return each role in a stable shape:

```php
[
    'state' => 'SUGGESTED_REUSE',
    'action' => 'reuse',
    'object_id' => 18,
    'candidate_ids' => [ 18 ],
    'reason' => 'unique_exact_label',
]
```

- [ ] **Step 5: Require module and make RED GREEN**

Require `inc/theme/provisioning-recommendations.php` from `inc/theme/bootstrap.php`, add the new offline test to `scripts/verify-v1-core.sh`, then run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-recommendations-contract.php
bash scripts/verify-v1-core.sh
```

Expected: recommendation contract PASS and retained core regression PASS in the supported PHP environment.

- [ ] **Step 6: Commit**

```bash
git add inc/theme/provisioning-recommendations.php inc/theme/provisioning-discovery.php inc/theme/bootstrap.php tests/offline/provisioning-recommendations-contract.php scripts/verify-v1-core.sh
git commit -m "feat: recommend safe Law provisioning sources"
```

---

### Task 3: Define v1.1 starter editorial blueprint and coverage rules

**Files:**
- Modify: `inc/theme/provisioning-blueprints.php`
- Create: `inc/theme/provisioning-editorial.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/provisioning-starter-editorial-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `provisioning_blueprint()`, Category role IDs selected later by the plan.
- Produces: v1.1 blueprint `law01-v1-1`, `provisioning_editorial_definition(string $role): ?array`, `provisioning_category_public_post_count(int $term_id): int`, `provisioning_editorial_coverage_recommendations(array $recommendations, array $discovery): array`.

- [ ] **Step 1: Write RED contract for the v1.1 pack**

Create `tests/offline/provisioning-starter-editorial-contract.php` asserting:

```php
$bp = \AZnet\Theme\provisioning_blueprint( 'law01-v1-1' );
assert( is_array( $bp ) );
assert( 'law-01' === $bp['homepage_preset'] );
assert( 8 === count( $bp['editorial_examples']['items'] ) );
assert( isset( $bp['editorial_examples']['items']['knowledge_business'] ) );
assert( isset( $bp['editorial_examples']['items']['legal_news'] ) );
```

For every starter item assert required keys:

```php
[ 'title', 'excerpt', 'content', 'category_role', 'media_role' ]
```

and source-guard that content contains no `Điều <number>`, fake firm/person names, current-date/news claims, testimonial/ranking/award language.

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-starter-editorial-contract.php
```

Expected: FAIL because `law01-v1-1` and the eight items do not exist.

- [ ] **Step 3: Add the v1.1 blueprint without invalidating v1**

Modify `provisioning_blueprint_keys()` to return:

```php
return [ 'law01-v1', 'law01-v1-1' ];
```

Keep existing `law01-v1` bytes/behavior compatible. For `law01-v1-1`, reuse the structural Page/Category/menu definitions and add eight editorial items using the titles approved in the spec. Each body must be neutral evergreen starter copy with H2/H3 structure and roughly 400–700 Vietnamese words.

- [ ] **Step 4: Add editorial coverage helper**

Create `provisioning-editorial.php` with:

```php
function provisioning_editorial_definition( string $role ): ?array;
function provisioning_category_public_post_count( int $term_id ): int;
function provisioning_editorial_coverage_recommendations( array $recommendations, array $discovery ): array;
```

Use native `WP_Query`/`get_posts` against `post_type=post`, `post_status=publish`, selected category ID, `posts_per_page=1`, `no_found_rows=true`. If a selected existing category already has ≥1 public Post, return `SKIP_HAS_PUBLIC_CONTENT`; if it will be created or exists empty, return `SUGGESTED_CREATE`.

- [ ] **Step 5: Make contract GREEN and retain regressions**

Require the new module, add test to `scripts/verify-v1-core.sh`, run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-starter-editorial-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add inc/theme/provisioning-blueprints.php inc/theme/provisioning-editorial.php inc/theme/bootstrap.php tests/offline/provisioning-starter-editorial-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add Law starter editorial coverage"
```

---

### Task 4: Upgrade Step 2 recommendations and immutable change-plan operations

**Files:**
- Modify: `inc/admin/provisioning.php`
- Modify: `assets/css/admin/control-center.css`
- Modify: `inc/theme/provisioning-plan.php`
- Create: `tests/offline/provisioning-v1-1-plan-contract.php`
- Modify: `tests/offline/provisioning-admin-a11y-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `provisioning_recommendations()`, `provisioning_editorial_coverage_recommendations()`.
- Produces: Step 2 recommendation cards and new allowed plan operations: `create_post`, `import_media`, `assign_featured_media`, `set_search_visibility`.

- [ ] **Step 1: Write RED plan/UI contracts**

Create `tests/offline/provisioning-v1-1-plan-contract.php` asserting `provisioning_validate_plan()` allows exactly the new operation types in addition to v1 types:

```php
'create_post', 'import_media', 'assign_featured_media', 'set_search_visibility'
```

Assert `set_search_visibility` operation carries:

```php
[ 'target' => 0, 'reason' => 'temporary_new_site_starter_publication' ]
```

and `create_post` carries role/category/media references plus explicit `post_status`.

Update admin a11y/static contract to require text/actions:

```text
Dùng các thiết lập được khuyến nghị
Tùy chỉnh nâng cao
Gợi ý — chưa áp dụng
Cần xác nhận
Tạm thời ngăn công cụ tìm kiếm lập chỉ mục website
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-v1-1-plan-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-admin-a11y-contract.php
```

Expected: FAIL on missing v1.1 operations/UX.

- [ ] **Step 3: Render recommendation-first Step 2**

Replace the long raw selector-first experience with compact cards driven by recommendation state. Each card must still submit explicit typed values:

```html
<input type="hidden" name="categories[knowledge_civil][action]" value="reuse">
<input type="hidden" name="categories[knowledge_civil][object_id]" value="18">
```

The primary path displays the recommendation and a user-change control; full typed selects remain under `<details>` “Tùy chỉnh nâng cao”. `AMBIGUOUS` must not submit a silent reuse choice.

- [ ] **Step 4: Extend plan builder and validator**

For `law01-v1-1`, after Page/Category/menu mapping operations, append starter operations only when selected:

```php
[
  'id' => 'post:knowledge_civil:create',
  'type' => 'create_post',
  'role' => 'starter_post:knowledge_civil',
  'category_role' => 'knowledge_civil',
  'media_role' => 'editorial-2',
  'post_status' => 'publish', // new site with confirmed search discouragement only; otherwise draft
  'effect' => 'CREATE starter Post “…” → Dân sự',
]
```

Add media operations and one `set_search_visibility` operation only for `NEW_OR_MOSTLY_EMPTY` + publish-starter consent. If search discouragement is declined, generate Draft `create_post` operations and no visibility mutation.

- [ ] **Step 5: Make Step 3 summary semantic**

Group displayed operations under:

```text
Sử dụng nội dung hiện có
Sẽ tạo mới
Sẽ import media
Sẽ thay đổi cấu hình
```

Require an explicit confirmation checkbox before “Áp dụng thiết lập”. Keep plan fingerprint validation unchanged.

- [ ] **Step 6: Run GREEN/regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-v1-1-plan-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-admin-a11y-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add inc/admin/provisioning.php assets/css/admin/control-center.css inc/theme/provisioning-plan.php tests/offline/provisioning-v1-1-plan-contract.php tests/offline/provisioning-admin-a11y-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add recommended provisioning change plan"
```

---

### Task 5: Add project-owned starter media import and attachment provenance

**Files:**
- Create: `assets/starter/law01/hero.webp`
- Create: `assets/starter/law01/editorial-1.webp`
- Create: `assets/starter/law01/editorial-2.webp`
- Create: `assets/starter/law01/editorial-3.webp`
- Create: `assets/starter/law01/editorial-4.webp`
- Create: `inc/theme/provisioning-media.php`
- Modify: `inc/theme/provisioning-provenance.php`
- Modify: `inc/theme/provisioning-runner.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/provisioning-media-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: plan `import_media` / `assign_featured_media` operations and packaged asset paths.
- Produces: `provisioning_media_definitions(): array`, `provisioning_import_media(string $media_role, string $blueprint, string $run_id): int`, attachment provenance lookup/reuse, current-run rollback support.

- [ ] **Step 1: Generate redistribution-safe starter images**

Use the image-generation capability to create five project-owned images with no text/logo/person identity:

```text
hero: premium legal office/library architecture, dark navy and warm bronze, landscape 16:9, no people, no signage, no readable documents
editorial-1: legal documents and fountain pen on refined desk, neutral professional light
editorial-2: architectural courthouse/detail abstraction, no identifiable institution signage
editorial-3: books, paper and restrained brass detail, no readable text
editorial-4: professional meeting-table/detail still life, no people, no logos
```

Save optimized WebP assets under `assets/starter/law01/` and record their generated/project-owned provenance in the evidence doc later; do not use third-party stock downloads.

- [ ] **Step 2: Write RED media contract**

Create `tests/offline/provisioning-media-contract.php` asserting:

```php
$defs = \AZnet\Theme\provisioning_media_definitions();
assert( isset( $defs['hero'], $defs['editorial-1'], $defs['editorial-2'], $defs['editorial-3'], $defs['editorial-4'] ) );
foreach ( $defs as $def ) {
    assert( str_starts_with( $def['relative_path'], 'assets/starter/law01/' ) );
    assert( '' !== $def['alt'] );
}
```

Also guard that Homepage templates continue using `get_the_post_thumbnail*`/WordPress attachments and do not hard-code starter Theme asset URLs.

- [ ] **Step 3: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-media-contract.php
```

Expected: FAIL because media module/definitions do not exist.

- [ ] **Step 4: Implement media import through WordPress APIs**

Create `provisioning-media.php` with:

```php
function provisioning_media_definitions(): array;
function provisioning_import_media( string $media_role, string $blueprint, string $run_id ): int;
```

`provisioning_import_media()` must:

```text
1. reuse surviving attachment provenance for blueprint+media role;
2. resolve the packaged file under the Theme directory;
3. copy it to a temporary file;
4. use supported WordPress media handling (`media_handle_sideload` or equivalent public API) to create an attachment in uploads;
5. set alt text and bounded provenance;
6. return attachment ID;
7. throw a bounded RuntimeException on failure so runner can fail-soft or rollback according to the plan.
```

No frontend rendering may use the Theme asset path after import.

- [ ] **Step 5: Extend provenance and runner**

Extend `provisioning_find_owned_role()` to support `post` and `attachment`. In `provisioning_apply_plan()` add handling for:

```php
'create_post'
'import_media'
'assign_featured_media'
```

Created starter Posts must use `wp_insert_post()`, `wp_set_post_categories()`, `set_post_thumbnail()` and `provisioning_mark_post()`; attachments must be added to `$receipt['created']`. Extend failed-run rollback to delete current-run `post` and `attachment` IDs, but never delete reused/user-owned media.

- [ ] **Step 6: Make contract GREEN and regress**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-media-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add assets/starter/law01 inc/theme/provisioning-media.php inc/theme/provisioning-provenance.php inc/theme/provisioning-runner.php inc/theme/bootstrap.php tests/offline/provisioning-media-contract.php scripts/verify-v1-core.sh
git commit -m "feat: import Law starter media into WordPress"
```

---

### Task 6: Enforce publication/index safety and launch cleanup ownership

**Files:**
- Modify: `inc/theme/provisioning-runner.php`
- Modify: `inc/theme/provisioning-readiness.php`
- Modify: `inc/admin/provisioning.php`
- Create: `tests/offline/provisioning-index-safety-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: `set_search_visibility` plan operation and v1.1 starter provenance/fingerprint.
- Produces: receipt ownership fields for search visibility, `provisioning_starter_post_is_untouched(int $post_id): bool`, readiness warnings, explicit restore-index action guarded by provisioning ownership.

- [ ] **Step 1: Write RED index-safety contract**

Create `tests/offline/provisioning-index-safety-contract.php` asserting source/API rules:

```text
NEW_OR_MOSTLY_EMPTY + publish starter => plan must also contain set_search_visibility target=0.
EXISTING_ACTIVE => plan must never contain set_search_visibility solely for starter protection.
If search discouragement is absent/declined => new starter post_status=draft.
No Rank Math/Yoast private meta keys appear in production provisioning files.
```

Assert the receipt schema includes:

```php
'pre' => [ 'blog_public' => 1 ],
'search_visibility_changed_by_run' => false,
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-index-safety-contract.php
```

Expected: FAIL because runner/readiness do not yet own these fields/actions.

- [ ] **Step 3: Capture/rollback search visibility**

In `provisioning_initial_receipt()` capture:

```php
'blog_public' => (int) get_option( 'blog_public', 1 ),
```

and root receipt flag:

```php
'search_visibility_changed_by_run' => false,
```

When applying `set_search_visibility`, use `update_option( 'blog_public', 0 )` only when the confirmed operation exists; set the flag only if pre-value was not already 0. Failed-run rollback restores the captured pre-value.

- [ ] **Step 4: Store starter fingerprint for diagnostics only**

Add a provenance meta constant:

```php
const PROVISIONING_META_STARTER_FINGERPRINT = '_aznet_theme_starter_fingerprint';
```

After creating a starter Post, store a fingerprint over initial title/excerpt/content/category/featured-image ID. Implement:

```php
function provisioning_starter_post_is_untouched( int $post_id ): bool;
```

It may only power readiness warnings; it must never trigger automatic content overwrite/sync.

- [ ] **Step 5: Add explicit launch cleanup action**

In Step 4/readiness, if and only if the successful receipt proves provisioning changed `blog_public` from 1 → 0, show:

```text
Tôi đã hoàn thiện nội dung
Website đang tạm ngăn công cụ tìm kiếm lập chỉ mục do Thiết lập nhanh.
Cho phép lập chỉ mục trở lại
```

Create an authenticated `admin_post_aznet_theme_provisioning_restore_index` handler with `manage_options` + nonce. Before update, re-read provenance/receipt ownership; only then call `update_option( 'blog_public', 1 )`. If pre-run `blog_public` was already 0, do not show/allow auto-restore.

- [ ] **Step 6: Update readiness warnings**

`provisioning_launch_warnings()` must recognize both `law01-v1` and `law01-v1-1`, warn for untouched starter Posts, starter Hero media still in use, missing real contact/team data and temporary indexing discouragement owned by provisioning.

- [ ] **Step 7: Run GREEN/regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-index-safety-contract.php
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add inc/theme/provisioning-runner.php inc/theme/provisioning-provenance.php inc/theme/provisioning-readiness.php inc/admin/provisioning.php tests/offline/provisioning-index-safety-contract.php scripts/verify-v1-core.sh
git commit -m "feat: protect starter publication and indexing"
```

---

### Task 7: Prove empty-site, active-site, rerun, rollback and browser quality

**Files:**
- Create: `tests/runtime/law-site-provisioning-v1-1.php`
- Create: `tests/browser/law-site-provisioning-v1-1-l4.mjs`
- Modify: `.github/workflows/law-site-provisioning-browser.yml`
- Modify: `.github/workflows/law-site-provisioning-candidate.yml`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: completed v1.1 production modules.
- Produces: fresh L3/L4 evidence proving the real WordPress behaviors and retained v1/Law 01 regressions.

- [ ] **Step 1: Add runtime RED scenarios**

Create `tests/runtime/law-site-provisioning-v1-1.php` with separate resettable scenarios:

```text
A. NEW_OR_MOSTLY_EMPTY recommended plan:
   - creates/reuses full structural roles;
   - creates 8 starter Posts;
   - starter Posts are publish;
   - blog_public becomes 0 because the confirmed plan contains that operation;
   - Hero attachment exists in uploads and is Front Page featured image;
   - Homepage editorial sources each have at least one renderable public item.

B. EXISTING_ACTIVE:
   - existing Front Page/menu/content/featured image remain unchanged unless explicitly selected;
   - exact unique Category label may be preselected as SUGGESTED_REUSE but is not mutated before apply;
   - empty selected editorial category gets Draft starter Post;
   - blog_public remains 1.

C. AMBIGUOUS exact labels:
   - no authoritative auto-reuse is produced.

D. RERUN:
   - no duplicate Page/Category/Post/attachment/menu item.

E. FAILURE INJECTION:
   - current-run Page/Post/Category/attachment/menu creations are removed;
   - theme settings/front page/menu location/blog_public restore to pre-run snapshot.

F. USER MODIFICATION:
   - edited starter Post remains untouched on rerun;
   - edited/replaced featured image remains untouched.
```

- [ ] **Step 2: Run runtime test before workflow changes**

Run in disposable WordPress 6.9 / PHP 8.1:

```bash
php tests/runtime/law-site-provisioning-v1-1.php
```

Expected: initially expose any missing integration behavior; fix only production root causes, then rerun to PASS.

- [ ] **Step 3: Add browser L4 test**

Create `tests/browser/law-site-provisioning-v1-1-l4.mjs` to authenticate, run the wizard through the recommendation path, inspect Step 3 summary, confirm apply, then verify at 1440x1000, 1024x900, 390x844 and 320x760:

```text
- recommendation cards are readable and keyboard reachable;
- “Dùng các thiết lập được khuyến nghị” works;
- change plan visibly lists reuse/create/media/search-visibility effects;
- explicit consent is required before apply;
- resulting Law 01 Hero uses a WordPress uploads attachment, not Theme asset URL;
- Services = 6 cards;
- Knowledge/Analysis/News contain visible post cards;
- no horizontal overflow;
- one H1;
- axe critical/serious violations = 0;
- no console/page/request failures attributable to Theme.
```

- [ ] **Step 4: Update persistent workflows**

Extend `.github/workflows/law-site-provisioning-browser.yml` to run retained v1 L4 plus v1.1 runtime/browser tests on WP 6.9/PHP 8.1/MySQL 8. Keep the existing exact known WordPress-core `WP_Query` warning classification only if freshly reproduced; every other PHP warning/fatal/parse/uncaught remains blocking.

Extend candidate workflow to run all new offline/runtime contracts before deterministic build.

- [ ] **Step 5: Run full verification**

Run locally where supported:

```bash
bash scripts/verify-v1-core.sh
```

Push the bounded implementation branch and require both persistent GitHub workflows to complete `SUCCESS` on the exact implementation/test head.

- [ ] **Step 6: Commit**

```bash
git add tests/runtime/law-site-provisioning-v1-1.php tests/browser/law-site-provisioning-v1-1-l4.mjs .github/workflows/law-site-provisioning-browser.yml .github/workflows/law-site-provisioning-candidate.yml scripts/verify-v1-core.sh
git commit -m "test: verify Law provisioning v1.1 runtime and browser"
```

---

### Task 8: Close candidate evidence and produce the installable ZIP

**Files:**
- Create: `docs/evidence/LAW_SITE_PROVISIONING_V1_1_L4.md`
- Modify: `docs/source/AZT-03-baseline-provenance.md` only to record the verified branch candidate/evidence without claiming canonical-main promotion
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md` to mark the implemented branch slice PASS only at the proven QA layers
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md` only if source versions change in this closure
- Output artifact: `/mnt/data/aznet-theme-<version>-law-site-provisioning-v1-1-candidate.zip`

**Interfaces:**
- Consumes: exact successful workflow run IDs/artifact IDs/head SHA from Task 7.
- Produces: recoverable branch checkpoint, deterministic installable candidate, hash, evidence doc and one exact next gate.

- [ ] **Step 1: Verify exact head before claiming completion**

Record:

```bash
git rev-parse HEAD
git status --short
```

Then fetch GitHub workflow runs on that exact SHA and confirm required candidate + browser workflows are `completed/success`.

- [ ] **Step 2: Download and inspect candidate artifact**

Download the deterministic candidate artifact from the successful exact-head package run. Extract the inner installable Theme ZIP to `/mnt/data/aznet-theme-<version>-law-site-provisioning-v1-1-candidate.zip`.

Verify:

```bash
sha256sum /mnt/data/aznet-theme-*-law-site-provisioning-v1-1-candidate.zip
unzip -t /mnt/data/aznet-theme-*-law-site-provisioning-v1-1-candidate.zip
unzip -l /mnt/data/aznet-theme-*-law-site-provisioning-v1-1-candidate.zip | grep -E 'assets/starter/law01/hero.webp|inc/theme/provisioning-recommendations.php|inc/theme/provisioning-media.php'
```

Expected: one canonical top-level `aznet-theme/`, no zip errors, all v1.1 production assets present.

- [ ] **Step 3: Write evidence doc**

`docs/evidence/LAW_SITE_PROVISIONING_V1_1_L4.md` must record:

```text
L0: D-026/source versions
L1/L2: RED→GREEN contracts and retained regression
L3: new/active/rerun/rollback/user-modification scenarios
L4: 1440/1024/390/320, axe, overflow, uploads-based Hero/media, visible editorial cards
Package: exact verified head, run IDs, artifact IDs, inner ZIP SHA-256, file count/version
UNKNOWN/BLOCKED: only claims not proven; retain known WP-core warning classification if still reproduced
Rollback: prior verified Premium/Provisioning candidate and branch checkpoint
Not implied: main merge, tag, Release, production deploy, SEO-provider certification
```

- [ ] **Step 4: Update source checkpoint carefully**

Update AZT-03/AZT-04/EXEC-MAP only with facts supported by fresh evidence. Do not rewrite canonical `main` as though these draft stacked branches were merged. If source closure commits are added after the verified production/test head, compare the final branch head to the verified head and prove only docs/source/evidence bytes changed.

- [ ] **Step 5: Final verification-before-completion**

Re-read the exact candidate hash/integrity output and workflow conclusions. Only then report:

```text
PASS — v1.1 candidate at L0-L4 + deterministic package
BLOCKED/UNKNOWN — live-pilot confirmation until installed on user site; merge/release/deploy gated
EVIDENCE — branch/head/runs/artifacts/hash
NEXT — install candidate on pilot and visually confirm; no merge without explicit owner approval
```

- [ ] **Step 6: Commit evidence/source closure**

```bash
git add docs/evidence/LAW_SITE_PROVISIONING_V1_1_L4.md docs/source/AZT-03-baseline-provenance.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/AZT-EXEC-MAP.md docs/source/SOURCE_MANIFEST.md
git commit -m "docs: close Law provisioning v1.1 candidate evidence"
```
