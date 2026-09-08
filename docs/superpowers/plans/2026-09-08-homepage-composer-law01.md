# Homepage Composer + Law 01 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver a downloadable AZnet Theme candidate that adds a fail-soft Homepage Composer and the first `law-01` presentation preset without moving WordPress, RootProfile or ConvertFlow domain ownership into the Theme.

**Architecture:** The existing static Front Page and `the_content()` boundary remain authoritative for native/provider-filtered body content. The Theme adds a versioned presentation/reference schema, validates typed WordPress Page/Category references, composes additional Theme-owned presentation sections around that boundary, and renders `law-01` only when explicitly selected. Applying the preset never creates, edits, deletes or republishes WordPress content.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, Classic Editor for native Post/Page, existing AZnet Theme scripts/workflows, plain CSS, no bundler.

**Spec:** `docs/superpowers/specs/2026-09-08-homepage-composer-law01-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Implementation starts from `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d` or a freshly rebased equivalent; revalidate before coding.
- Theme is presentation owner only. WordPress owns Post/Page/Category/query/publication data.
- RootProfile identity/profile/contact semantics stay behind accepted public/versioned contracts.
- ConvertFlow Journey/resolver/state/conversion semantics stay outside the Theme.
- No slug/title/URL heuristic for semantic source discovery.
- No direct option/meta/CPT/table/private API reads from providers.
- No proprietary Homepage content store, custom Homepage CPT, FAQ store, lawyer store or query-builder engine.
- Keep Classic Editor policy for native `post` and `page`.
- Preserve `front-page.php` WordPress loop and exactly one `the_content()` execution for the configured Front Page.
- Feature/behavior work uses RED -> verify expected failure -> minimal GREEN -> retained regression.
- Minimum floors: WordPress 6.9+, PHP 8.1+.
- Asset loading is surface-aware; Law 01 CSS loads only on the Front Page when `homepage_preset=law-01`.
- Main merge, release tag, deploy and production takeover remain owner approval gates.

---

## File Structure

Create or modify these focused units:

- `docs/source/AZT-02-architecture.md` — ratify Homepage Composer architecture and typed-reference allowance.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — add D-024 and the bounded implementation/QA slice.
- `docs/source/SOURCE_MANIFEST.md` — bump source versions/decision range after source changes.
- `docs/source/AZT-EXEC-MAP.md` — derived exact-next map for this approved slice.
- `inc/theme/settings.php` — schema v2 normalization and Homepage presentation/reference keys only.
- `inc/theme/homepage-content-map.php` — typed reference validation and bounded WordPress read models.
- `inc/theme/homepage-composer.php` — preset activation, section orchestration, request-local post ledger and fail-soft checks.
- `inc/theme/bootstrap.php` — require/wire Homepage modules only.
- `inc/theme/assets.php` — conditionally enqueue Law 01 frontend CSS.
- `inc/admin/homepage.php` — Homepage Control Center selectors/diagnostics.
- `inc/admin/bootstrap.php` — require admin Homepage module.
- `inc/admin/control-center.php` — add `Trang chủ` tab and delegate rendering.
- `front-page.php` — preserve the Loop/`the_content()` boundary while invoking composer before/after the Page body according to the preset contract.
- `template-parts/homepage/law-01/*.php` — focused presentation sections.
- `assets/css/components/homepage-law-01.css` — scoped responsive Law 01 presentation.
- `tests/offline/homepage-composer-settings-contract.php` — schema/allow-list/migration contract.
- `tests/offline/homepage-content-map-contract.php` — typed Page/Category validation and no-heuristic contract.
- `tests/offline/homepage-composer-contract.php` — front-page boundary, fail-soft orchestration and no-provider-takeover contract.
- `tests/offline/homepage-control-center-contract.php` — Homepage tab/type-safe selectors/diagnostics/no mutation controls.
- `tests/offline/homepage-law-01-contract.php` — section order, CSS scoping, no fabricated claims and source-aware asset gate.
- `tests/browser/homepage-law-01-l4.mjs` — browser/responsive/a11y matrix.
- `.github/workflows/homepage-law-01-browser.yml` — disposable WordPress L3-L4 workflow.
- `scripts/verify-v1-core.sh` — replace superseded Homepage blueprint contracts with retained Composer/Law 01 contracts while preserving unrelated pattern regressions.
- `docs/evidence/HOMEPAGE_COMPOSER_LAW01_L4.md` — fresh evidence after runtime/browser verification.

Do not implement Law Site Provisioning in this package. It is a separate follow-on slice.

---

### Task 1: Source/State Gate — Ratify Homepage Composer Before Production Code

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`

**Interfaces:**
- Consumes: approved design spec.
- Produces: accepted architecture/roadmap authority for Tasks 2-8.

- [ ] **Step 1: Revalidate canonical state**

Run:

```bash
git fetch origin main
git rev-parse origin/main
git log -1 --oneline origin/main
```

Expected baseline at plan creation: `12a480311dcb59f3d7a152ef7e72ca927c2f632d`. If main moved, compare first and rebase the work branch only when ownership/source decisions are not invalidated.

- [ ] **Step 2: Amend AZT-02 with an explicit Homepage Composer v1 section**

Add language with these exact architectural assertions:

```markdown
## Homepage Composer v1

Homepage Composer is Theme-owned presentation composition, not a content/domain engine.

- Content Map stores typed references to existing WordPress Pages/Categories only; it does not copy title/body/excerpt/contact/profile/query-result data.
- Presentation Preset stores Theme-owned layout/visual behavior and may be changed without changing WordPress content.
- `front-page.php` preserves the configured Front Page WordPress Loop and exactly one `the_content()` execution so public WordPress/provider filters keep their contract boundary.
- Theme may render mapped WordPress-native presentation sections around that body boundary, but must not reconstruct ConvertFlow Journey semantics.
- Reference validation is type/publication based. Missing/invalid references fail soft; title/slug/URL heuristics are forbidden.
- Law 01 v1 has no RootProfile team-collection integration because no accepted public collection contract exists for this use case.
- Applying a Homepage preset must not create, rewrite, delete, publish or reclassify WordPress content.
```

Bump AZT-02 source version from v0.5 to v0.6.

- [ ] **Step 3: Record D-024 in AZT-04**

Add the accepted decision:

```markdown
| **D-024** | **Homepage template switching uses a stable typed Content Map plus Theme-owned Presentation Preset; Classic Editor remains for native Post/Page; provisioning is separate from preset application; the Front Page keeps exactly one `the_content()` boundary and no provider/domain semantics are cloned into Theme.** | **Accepted** |
```

Add a bounded implementation slice with gates L1 static, L2 contract RED/GREEN, L3 real WordPress runtime, L4 browser/visual/a11y, then package-candidate evidence. Main merge/release/deploy remain approval-gated.

Bump AZT-04 from v0.28 to v0.29.

- [ ] **Step 4: Reconcile manifest/execution map**

Update `SOURCE_MANIFEST.md` to reflect AZT-02 v0.6, AZT-04 v0.29 and accepted D-001..D-024. Update `AZT-EXEC-MAP.md` so exact next is Homepage Composer Core + Law 01 implementation, not the superseded manual Homepage Block Pattern workflow.

- [ ] **Step 5: Commit source gate**

```bash
git add docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/SOURCE_MANIFEST.md docs/source/AZT-EXEC-MAP.md
git commit -m "docs: ratify homepage composer law01 architecture"
```

Acceptance: no production PHP/CSS changes are in this commit.

---

### Task 2: Settings Schema v2 — Presentation + Typed References

**Files:**
- Create: `tests/offline/homepage-composer-settings-contract.php`
- Modify: `inc/theme/settings.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Produces: normalized keys `homepage_preset`, `homepage_services_page`, `homepage_about_page`, `homepage_team_page`, `homepage_knowledge_terms`, `homepage_case_analysis_term`, `homepage_legal_news_term`, `homepage_process_page`, `homepage_faq_page`, `homepage_contact_page`.
- `homepage_preset` allowed values: `off|law-01`; default `off` for backwards-safe activation/update.
- Single-reference keys normalize to positive integer or `0`.
- `homepage_knowledge_terms` normalizes to a stable unique array of positive integer IDs.

- [ ] **Step 1: Write RED contract**

Create a test that requires `inc/theme/settings.php` and asserts:

```php
$defaults = AZnet\Theme\settings_defaults();
assert($defaults['schema_version'] === 2);
assert($defaults['homepage_preset'] === 'off');
assert($defaults['homepage_services_page'] === 0);
assert($defaults['homepage_knowledge_terms'] === []);

$normalized = AZnet\Theme\normalize_settings([
    'homepage_preset' => 'law-01',
    'homepage_services_page' => '12',
    'homepage_about_page' => -5,
    'homepage_knowledge_terms' => ['9', 9, 3, 0, -2, 'bad'],
    'foreign_homepage_state' => ['must' => 'drop'],
]);
assert($normalized['homepage_preset'] === 'law-01');
assert($normalized['homepage_services_page'] === 12);
assert($normalized['homepage_about_page'] === 0);
assert($normalized['homepage_knowledge_terms'] === [9, 3]);
assert(! array_key_exists('foreign_homepage_state', $normalized));
```

Also assert every existing R1-R4/R5/Woo setting remains present and normalized exactly as before except `schema_version=2`.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-settings-contract.php
```

Expected failure: missing Homepage keys/schema v2.

- [ ] **Step 3: Minimal GREEN in `settings.php`**

Add strict normalizers:

```php
$normalize_id = static function ( mixed $value ): int {
    $id = is_numeric( $value ) ? (int) $value : 0;
    return $id > 0 ? $id : 0;
};

$normalize_ids = static function ( mixed $value ) use ( $normalize_id ): array {
    if ( ! is_array( $value ) ) {
        return [];
    }
    $out = [];
    foreach ( $value as $candidate ) {
        $id = $normalize_id( $candidate );
        if ( $id > 0 && ! in_array( $id, $out, true ) ) {
            $out[] = $id;
        }
    }
    return $out;
};
```

Normalize only the exact keys listed above; unknown keys are dropped.

- [ ] **Step 4: Verify GREEN + retained settings regressions**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-settings-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r1-visual-preset-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r3-header-settings-contract.php
```

Update retained tests whose only expected change is `schema_version` from 1 to 2; do not weaken other assertions.

- [ ] **Step 5: Add the contract to reusable verification and commit**

```bash
git add inc/theme/settings.php tests/offline/homepage-composer-settings-contract.php scripts/verify-v1-core.sh tests/offline
git commit -m "feat: add homepage composer settings schema"
```

---

### Task 3: Content Map Resolver — Typed WordPress Sources Only

**Files:**
- Create: `tests/offline/homepage-content-map-contract.php`
- Create: `inc/theme/homepage-content-map.php`
- Modify: `inc/theme/bootstrap.php`

**Interfaces:**
- Produces:
  - `homepage_page_reference( int $id ): ?WP_Post`
  - `homepage_category_reference( int $id ): ?WP_Term`
  - `homepage_category_references( array $ids ): array`
  - `homepage_direct_published_children( int $parent_id, int $limit ): array`
  - `homepage_latest_posts( array $category_ids, int $limit, array $exclude_ids = [] ): array`
- Page reference accepts only published `page` objects.
- Category reference accepts only `category` terms.
- No lookup by title/name/slug/URL.

- [ ] **Step 1: Write RED behavior contract with WordPress function stubs**

Define small global stubs for `get_post()`, `get_term()`, `get_posts()` and `get_children()`/`WP_Query` equivalent, then assert valid IDs resolve and wrong types/draft/missing objects return `null` or empty arrays.

The test must also scan the production module and reject semantic-discovery calls such as:

```php
get_page_by_title(
get_page_by_path(
get_term_by( 'slug'
url_to_postid(
```

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-content-map-contract.php
```

Expected failure: production module/functions missing.

- [ ] **Step 3: Implement minimal resolver**

Use public WordPress APIs with exact IDs only. Query arguments for latest Posts must be bounded and explicit:

```php
[
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'category__in'        => $valid_category_ids,
    'post__not_in'        => $exclude_ids,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'posts_per_page'      => $limit,
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
]
```

Direct service children use `post_parent=$parent_id`, `post_type=page`, `post_status=publish`, `orderby=menu_order title`, bounded by the preset limit.

- [ ] **Step 4: Verify GREEN and lint**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-content-map-contract.php
php -l inc/theme/homepage-content-map.php
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/homepage-content-map.php inc/theme/bootstrap.php tests/offline/homepage-content-map-contract.php
git commit -m "feat: add typed homepage content map resolver"
```

---

### Task 4: Homepage Composer — Preserve `the_content()` and Fail Soft

**Files:**
- Create: `tests/offline/homepage-composer-contract.php`
- Create: `inc/theme/homepage-composer.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `front-page.php`
- Create directory: `template-parts/homepage/law-01/`

**Interfaces:**
- Produces:
  - `homepage_preset(): string`
  - `homepage_composer_active(): bool`
  - `homepage_ledger_reset(): void`
  - `homepage_ledger_add( array $post_ids ): void`
  - `homepage_ledger_ids(): array`
  - `render_homepage_before_content(): void`
  - `render_homepage_after_content(): void`
- `homepage_composer_active()` is true only on Front Page with `homepage_preset=law-01`.

- [ ] **Step 1: Write RED contract**

Assert `front-page.php` still contains exactly one `the_content();`, one Theme `<main id="main">`, and the normal WordPress Loop. Assert composer calls exist only around the content boundary and the previous ConvertFlow/public WordPress filter opportunity is not bypassed.

Assert `homepage_preset=off` produces no composer section output and missing/invalid references never throw.

Assert ledger is request-local static memory only; production files must not call `set_transient`, `update_option`, `update_post_meta` or other persistence from Homepage Composer modules.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-contract.php
```

Expected failure: composer functions/modules absent.

- [ ] **Step 3: Implement minimal composer shell**

Keep the template shape:

```php
while ( have_posts() ) {
    the_post();
    if ( \AZnet\Theme\homepage_composer_active() ) {
        \AZnet\Theme\homepage_ledger_reset();
        \AZnet\Theme\render_homepage_before_content();
    }
    the_content();
    if ( \AZnet\Theme\homepage_composer_active() ) {
        \AZnet\Theme\render_homepage_after_content();
    }
}
```

`render_homepage_before_content()` initially renders only valid Theme presentation sections whose sources resolve; `render_homepage_after_content()` handles the later editorial/CTA sequence. No section may fabricate placeholder cards on the public surface.

- [ ] **Step 4: Verify GREEN + retained F contracts**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/f2-homepage-public-body-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/f5-homepage-absent-failsoft-contract.php
```

If exact retained filenames differ, use the existing F2/F5 contract files that protect `the_content()` and absent-provider fail-soft behavior; do not delete those guarantees.

- [ ] **Step 5: Commit**

```bash
git add front-page.php inc/theme/homepage-composer.php inc/theme/bootstrap.php tests/offline/homepage-composer-contract.php
git commit -m "feat: add fail-soft homepage composer shell"
```

---

### Task 5: Control Center — Homepage Preset, Typed Mapping and Diagnostics

**Files:**
- Create: `tests/offline/homepage-control-center-contract.php`
- Create: `inc/admin/homepage.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/control-center.php`
- Modify: `assets/css/admin/control-center.css`

**Interfaces:**
- Produces:
  - `render_homepage_settings(): void`
  - `homepage_slot_statuses(): array`
- Uses existing `aznet_theme_save_settings` action and the same normalized `aznet_theme_settings` Theme Mod store; no second save action/store is created.

- [ ] **Step 1: Write RED contract**

Assert:

```text
Trang chủ tab exists.
Preset choices are off and law-01.
Page-backed slots use WordPress Page selectors.
Category-backed slots use Category selectors.
knowledge_terms is multi-select.
No free-form slug/URL/manual-ID fields exist.
Diagnostics expose READY, EMPTY, UNMAPPED, INVALID, PROVIDER_UNAVAILABLE.
No admin action creates/deletes/updates Posts, Pages or terms.
```

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
```

Expected failure: Homepage tab/module missing.

- [ ] **Step 3: Implement minimal admin module**

Use public WordPress reads such as `get_pages()` and `get_categories()` only for rendering select choices. Save values through the existing settings form and strict normalizer. Render a Law 01 card with description and active state, then the source mapping form and a read-only diagnostic list.

Do not add one-click provisioning or auto-repair.

- [ ] **Step 4: Verify GREEN + R5 regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r5-control-center-static-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-blueprint-control-center-contract.php
```

The last historical contract may need to be superseded only where PR #57 already changed the workflow; retain its no-auto-create/no-overwrite guarantees.

- [ ] **Step 5: Commit**

```bash
git add inc/admin/homepage.php inc/admin/bootstrap.php inc/admin/control-center.php assets/css/admin/control-center.css tests/offline/homepage-control-center-contract.php
git commit -m "feat: add homepage mapping control center"
```

---

### Task 6: Law 01 Presentation — Sections, Responsive CSS and Truthful Fallbacks

**Files:**
- Create: `tests/offline/homepage-law-01-contract.php`
- Create: `template-parts/homepage/law-01/hero.php`
- Create: `template-parts/homepage/law-01/services.php`
- Create: `template-parts/homepage/law-01/about.php`
- Create: `template-parts/homepage/law-01/team.php`
- Create: `template-parts/homepage/law-01/topics.php`
- Create: `template-parts/homepage/law-01/latest.php`
- Create: `template-parts/homepage/law-01/analysis.php`
- Create: `template-parts/homepage/law-01/news.php`
- Create: `template-parts/homepage/law-01/process.php`
- Create: `template-parts/homepage/law-01/faq.php`
- Create: `template-parts/homepage/law-01/final-cta.php`
- Create: `assets/css/components/homepage-law-01.css`
- Modify: `inc/theme/homepage-composer.php`
- Modify: `inc/theme/assets.php`

**Interfaces:**
- Section order matches the approved spec.
- Service grid uses direct published children of mapped Services Page.
- `team` is an editorial Page teaser, not synthetic Person cards.
- `latest`, `analysis`, `news` share the request-local dedupe ledger.
- Process/FAQ only use source structures that can be rendered without inventing semantics; otherwise they degrade to Page preview/CTA or hide.

- [ ] **Step 1: Write RED contract**

Assert required root/section classes and forbidden fabricated defaults. The production preset must not contain literal claims such as `500+`, `98%`, `1000+`, default lawyer identities, testimonials, certifications or hard-coded phone/email values.

Assert Law 01 stylesheet selectors are rooted under:

```css
.aznet-theme-homepage--law-01
```

and asset code only enqueues it for the Front Page when `homepage_preset=law-01`.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
```

Expected failure: Law 01 files/classes absent.

- [ ] **Step 3: Implement minimal semantic HTML renderers**

Use `get_template_part()` with local query vars/read models; escape output with `esc_html`, `esc_url`, `wp_kses_post` only where authored Page content is intentionally rendered.

Hero uses the configured Front Page title/excerpt/featured image. About/Team use mapped Page title/excerpt/image/permalink. Service cards use native child Page title/permalink/excerpt. Topic cards use Category name/permalink. Editorial article cards use native Post title/date/excerpt/featured image/category metadata without inventing author role/expertise.

For Process/FAQ v1, do not parse free-form Classic Editor prose into authoritative steps/questions. If structured source is not explicitly available, render a polished Page preview + CTA rather than inferring semantics.

- [ ] **Step 4: Implement responsive CSS**

Cover at least desktop/tablet/mobile, with no horizontal overflow, visible focus, safe wrapping for Vietnamese titles, and `prefers-reduced-motion` respected. Do not introduce JS/carousels for initial Law 01.

- [ ] **Step 5: Verify GREEN + lint/static chain**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
bash scripts/verify-v1-core.sh
```

- [ ] **Step 6: Commit**

```bash
git add template-parts/homepage/law-01 assets/css/components/homepage-law-01.css inc/theme/homepage-composer.php inc/theme/assets.php tests/offline/homepage-law-01-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add law01 homepage presentation preset"
```

---

### Task 7: L3-L4 Disposable WordPress Runtime, Browser and A11y

**Files:**
- Create: `tests/browser/homepage-law-01-l4.mjs`
- Create: `.github/workflows/homepage-law-01-browser.yml`
- Create: `docs/evidence/HOMEPAGE_COMPOSER_LAW01_L4.md`

**Interfaces:**
- Workflow provisions a disposable WordPress 6.9+/PHP 8.1-compatible runtime, activates exact branch bytes, creates representative native Pages/Categories/Posts by fixture, maps their IDs through Theme settings, selects `law-01`, and renders the real Front Page.
- Test fixture may create content because it owns the disposable environment; production Theme code may not.

- [ ] **Step 1: Write browser test before workflow fixture is GREEN**

Test 1440, 1024, 390 and 320 px. At each viewport assert:

```text
one main#main
one H1
Header and Footer present
Law 01 root present
mapped Services/About/Team/Topics/Latest/Analysis/News sections render when fixture data exists
no empty placeholder cards
no horizontal overflow
a visible primary CTA only when mapped contact exists
keyboard-focusable links/buttons
axe critical=0 and serious=0
no console/page errors caused by Theme
```

- [ ] **Step 2: Run initial workflow/test and preserve RED reason**

Expected RED should be a missing runtime fixture/section behavior from this feature, not syntax/tooling failure.

- [ ] **Step 3: Add disposable WordPress fixture and workflow**

Use WP-CLI to create:

```text
Front Page
Giới thiệu
Dịch vụ pháp lý + 6 direct child Pages
Đội ngũ luật sư
Quy trình tư vấn
Câu hỏi thường gặp
Liên hệ
Knowledge categories
Phân tích vụ việc
Tin pháp luật
Representative Posts with overlapping taxonomy to exercise dedupe
```

Set `show_on_front=page`, `page_on_front=<front-id>`, and the Theme Mod mapping through a controlled test helper/WP-CLI eval. Do not use title/slug discovery inside production Theme code.

- [ ] **Step 4: Verify GREEN and retained workflows**

Run the branch workflow plus reusable core verification. Record workflow run ID, commit SHA, browser summary, axe result and any screenshot artifact IDs in `docs/evidence/HOMEPAGE_COMPOSER_LAW01_L4.md`.

- [ ] **Step 5: Commit evidence/workflow**

```bash
git add tests/browser/homepage-law-01-l4.mjs .github/workflows/homepage-law-01-browser.yml docs/evidence/HOMEPAGE_COMPOSER_LAW01_L4.md
git commit -m "test: verify homepage composer law01 runtime"
```

---

### Task 8: Supersession Cleanup, Full Regression and Downloadable Candidate Package

**Files:**
- Modify/remove only superseded P4 Homepage Pattern files proven unused by current product direction.
- Modify: `scripts/verify-v1-core.sh`
- Modify: `docs/source/AZT-03-baseline-provenance.md` after fresh evidence exists.
- Modify: `docs/source/SOURCE_MANIFEST.md` if AZT-03 version changes.
- Create/update final evidence checkpoint as needed.

**Interfaces:**
- Historical evidence/spec files remain historical; do not rewrite them.
- Removing old `homepage-professional-services` pattern is allowed only after code/test search proves no active supported flow needs it.

- [ ] **Step 1: Search superseded pattern dependencies**

```bash
git grep -n "homepage-professional-services\|aznet-theme-homepage-blueprint"
```

Classify each hit as production dependency, active regression, historical spec/evidence, or obsolete workflow/test. Do not delete historical evidence.

- [ ] **Step 2: If safe, RED-protect the new direction then remove obsolete production pattern/workflow pieces**

The replacement contracts must prove:

```text
Classic Editor policy remains.
Homepage Setup no longer requires Block Inserter.
Composer/Law 01 is the supported Homepage presentation flow.
Woo/native generic pattern library still works independently.
```

If any retained supported consumer still needs the old pattern, leave it dormant and mark superseded instead of deleting it.

- [ ] **Step 3: Full local/static regression**

```bash
bash scripts/verify-v1-core.sh
```

Expected: all reusable contracts PASS with no PHP lint errors.

- [ ] **Step 4: Update AZT-03 only from fresh evidence**

Record exact branch/head SHA, QA layers actually proven, workflow run IDs/artifacts, and explicit remaining UNKNOWN/BLOCKED items. Do not claim release/production takeover from L4 evidence alone.

- [ ] **Step 5: Build deterministic installable ZIP twice and compare**

Read version from the Theme and use the existing builder:

```bash
VERSION=$(php -r '$s=file_get_contents("style.css"); preg_match("/^Version:\\s*(.+)$/mi", $s, $m); echo trim($m[1]);')
mkdir -p /tmp/aznet-law01-a /tmp/aznet-law01-b
python3 scripts/build-release-package.py --source . --output "/tmp/aznet-law01-a/aznet-theme-${VERSION}.zip" --version "$VERSION"
python3 scripts/build-release-package.py --source . --output "/tmp/aznet-law01-b/aznet-theme-${VERSION}.zip" --version "$VERSION"
cmp "/tmp/aznet-law01-a/aznet-theme-${VERSION}.zip" "/tmp/aznet-law01-b/aznet-theme-${VERSION}.zip"
sha256sum "/tmp/aznet-law01-a/aznet-theme-${VERSION}.zip"
```

Unzip and exact-compare packaged files using the same R6 release-candidate method already in the repo.

- [ ] **Step 6: Open a draft PR, do not merge main automatically**

PR body must include PASS/BLOCKED/UNKNOWN, exact SHA, test commands, workflow evidence, package SHA-256, rollback (`switch back to previous Theme package or set homepage_preset=off`), and explicitly state that Law Site Provisioning is not included.

- [ ] **Step 7: Produce a user-downloadable candidate ZIP**

Download/copy the exact verified ZIP into the active conversation sandbox only after the package bytes have been verified. Provide the sandbox link and SHA-256 to the user. This is a candidate update package, not a main/release/deploy claim unless the owner separately approves those gates.

---

## Self-Review Results

- **Spec coverage:** Composer Core, stable Content Map, Law 01, Classic Editor, one `the_content()` boundary, typed references, Control Center, fail-soft, dedupe, responsive/a11y, package candidate and provisioning separation are each mapped to tasks.
- **Ownership check:** no task creates RootProfile/ConvertFlow/Woo domain state or direct provider storage reads.
- **Migration check:** default `homepage_preset=off` preserves existing sites until explicit selection; schema v1 inputs normalize into schema v2 without destructive content mutation.
- **TDD check:** every production behavior task starts with a failing contract/browser test before implementation.
- **Release check:** package generation is allowed as evidence/candidate; main merge, release tag and deployment remain explicit owner gates.
