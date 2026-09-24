# Homepage Authoring System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a preset-isolated Homepage authoring system so Law 01 and Curtain 01 keep independent source mappings while administrators can edit WordPress-owned Homepage content from the AZnet Theme Control Center without creating a proprietary content store.

**Architecture:** Add a bounded preset registry and resolver in Theme code. Preset-scoped flat reference keys live in the existing normalized `aznet_theme_settings` Theme Mod, while Page/Post/Category/Media/`wp_block` content remains WordPress-owned. Existing generic Homepage keys remain compatibility fallbacks; migration is explicit and idempotent, preset switching is mutation-free, Quick Edit writes only the already-mapped WordPress object through public WordPress APIs.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, native WordPress Pages/Categories/Media/`wp_block`, existing PHP offline contracts, Playwright browser QA, existing `scripts/verify-v1-core.sh`.

**Spec:** `docs/superpowers/specs/2026-09-24-homepage-authoring-system-design.md`

## Global Constraints

- AZnet Theme is the PRESENTATION OWNER; WordPress owns Post/Page/Menu/Media/query/editorial lifecycle.
- Use only public WordPress APIs and public/versioned provider contracts; do not read plugin-private option/meta/CPT/table/class storage.
- Keep the single Theme Mod `aznet_theme_settings`; do not add a second settings store or proprietary Homepage datastore.
- Keep global Theme `schema_version = 3`; the new mapping model is additive and backward-compatible.
- Store only Theme presentation state and typed WordPress references; do not store copied headings, paragraphs, service descriptions, FAQ answers, biographies or image URLs.
- Preset switching must never create, rewrite, duplicate, delete, publish, reclassify or remap WordPress content.
- Legacy generic Homepage keys remain compatibility-only in this implementation and are not deleted.
- Hero continues to follow D-030: WordPress-owned Core-block `wp_block`, explicit draft-first initialization/migration, public fallback until publish.
- Missing/invalid sources fail soft; do not repair by slug/title/URL/Page-ID inference.
- WordPress minimum remains 6.9+; PHP minimum remains 8.1+.
- No bundler/build framework is introduced.
- Production deploy, release publication and takeover remain separate approval gates.

## Review Focus

1. **Legacy-only site with no scoped keys** — must render the same effective sources through generic compatibility fallbacks; Task 2 adds resolver coverage.
2. **Two presets resolving the same WordPress object** — must show a shared-source warning without silently cloning or remapping; Task 6 adds detection and browser/admin coverage.
3. **Malicious/stale Quick Edit target** — forged post/term/media IDs must be rejected by capability, nonce, preset-slot and mapped-object validation; Task 7 adds action-level contracts and runtime coverage.
4. **Preset switch during incomplete migration** — switching Law 01 → Curtain 01 → Law 01 must preserve both scoped maps and legacy data; Tasks 3 and 4 add mutation-free regression coverage.
5. **Published/draft Hero edge cases** — draft migrated Hero must not replace public legacy Hero, and published scoped Hero must not leak across presets; Task 9 extends D-030 contracts and L4 coverage.

---

## File Structure

### New focused modules

- `inc/theme/homepage-authoring.php` — preset registry, scoped key map, compatibility fallback map, effective source resolver and shared-reference comparison primitives.
- `inc/admin/homepage-migration.php` — explicit reference-only migration actions for Law 01 and Curtain 01.
- `inc/admin/homepage-authoring.php` — Quick Edit and explicit duplicate-source actions; no generic settings-save mutation.
- `assets/js/admin/homepage-authoring.js` — WordPress Media modal integration for bounded featured-image selection.
- `tests/offline/homepage-preset-isolation-contract.php` — scoped-key and compatibility resolver contract.
- `tests/offline/homepage-preset-migration-contract.php` — explicit, idempotent, non-destructive migration contract.
- `tests/offline/homepage-authoring-actions-contract.php` — nonce/capability/mapped-target/WordPress-public-API mutation contract.
- `tests/offline/homepage-editorial-source-contract.php` — Law 01 hard-coded editorial-copy regression.
- `tests/browser/homepage-authoring-l4.mjs` — Control Center authoring, isolation and warning browser QA.

### Existing modules to modify

- `docs/source/AZT-02-architecture.md`
- `docs/source/AZT-04-roadmap-qa-decisions.md`
- `docs/source/SOURCE_MANIFEST.md`
- `docs/source/AZT-EXEC-MAP.md`
- `inc/theme/bootstrap.php`
- `inc/theme/settings.php`
- `inc/theme/homepage-content-map.php`
- `inc/theme/homepage-composer.php`
- `inc/theme/provisioning-discovery.php`
- `inc/theme/provisioning-runner.php`
- `inc/theme/provisioning-readiness.php`
- `inc/admin/bootstrap.php`
- `inc/admin/homepage.php`
- `inc/admin/homepage-hero.php`
- `inc/admin/control-center.php`
- `assets/css/admin/control-center.css`
- Law 01 template parts under `template-parts/homepage/law-01/`
- Curtain 01 template parts that currently consume generic references
- existing Homepage/Curtain contracts and `scripts/verify-v1-core.sh`

---

### Task 1: Ratify D-038 before production implementation

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`

**Interfaces:**
- Consumes: approved design `docs/superpowers/specs/2026-09-24-homepage-authoring-system-design.md`.
- Produces: accepted D-038 architecture boundary and exact implementation next; later tasks must conform to it.

- [ ] **Step 1: Reconcile branch base with current canonical main**

Run:
```bash
git fetch origin main
git rev-parse origin/main
git merge-base --is-ancestor origin/main HEAD
```

Expected: branch contains current `origin/main`. If main advanced after `9b93fd76f7d3238734bec58e247bf98d61851618`, merge/rebase the branch before editing source docs and record the new exact base in the source checkpoint.

- [ ] **Step 2: Add the architecture rule to AZT-02**

Bump AZT-02 from v0.14 to v0.15 and add a section with this normative content:

```markdown
# Homepage Authoring System — preset isolation

Homepage authoring uses one Theme-owned preset registry over WordPress-owned content.

- Each Homepage preset owns an independent set of typed source references.
- The same WordPress object may be shared only by explicit mapping; sharing is not implied by a generic global key.
- Theme settings store references/presentation only. Editorial copy/media remain WordPress-owned.
- Control Center Quick Edit is an authoring bridge over the mapped WordPress object and must validate capability, nonce, preset, slot, source type and exact mapped object before mutation.
- Preset switching is content-mutation free.
- Legacy generic Homepage keys are compatibility fallbacks only and are not deleted or silently migrated.
- Reference migration is explicit, idempotent and copies references only.
- Shared Page/wp_block sources may be separated only by explicit draft-first duplication; Category sharing is separated by selecting another Category rather than cloning taxonomy semantics.
- Missing/invalid sources fail soft with no title/slug/URL heuristic repair.
- D-030 Hero remains WordPress-owned, scoped per preset for new mappings and draft-first for migration.
```

- [ ] **Step 3: Record D-038 in AZT-04**

Bump AZT-04 from v0.91 to v0.92 and append:

```markdown
| **D-038** | **Homepage Authoring System uses preset-isolated typed references with WordPress-native editorial ownership. Control Center may provide bounded Quick Edit over the exact mapped WordPress object through public APIs, while preset switching remains mutation-free. Legacy generic mappings are compatibility-only; migration is explicit/reference-only/idempotent; shared source use is warned and source separation is explicit/draft-first. No proprietary Homepage content store or heuristic source repair is allowed.** | **Accepted — owner approved 24/09/2026** |
```

Add the QA gate: L1 static ownership + L2 isolation/migration/action contracts must pass before runtime/browser work; Law 01 authoring completeness requires zero unclassified hard-coded editorial copy in the audited template scope.

- [ ] **Step 4: Synchronize manifest and execution map**

Update `SOURCE_MANIFEST.md` to reference AZT-02 v0.15 and AZT-04 v0.92 and state that D-038 is accepted but production implementation is not yet claimed.

Update `AZT-EXEC-MAP.md` with one exact next:

```markdown
Exact Next: implement D-038 preset registry/resolver with RED -> GREEN isolation contracts; do not mutate production or delete legacy Homepage keys.
```

Do not change AZT-03 implementation provenance in this task.

- [ ] **Step 5: Verify source-only delta**

Run:
```bash
git diff --check
git diff -- docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/SOURCE_MANIFEST.md docs/source/AZT-EXEC-MAP.md
```

Expected: source-only change; no PHP/CSS/JS production files.

- [ ] **Step 6: Commit**

```bash
git add docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/SOURCE_MANIFEST.md docs/source/AZT-EXEC-MAP.md
git commit -m "docs: ratify preset-isolated homepage authoring"
```

---

### Task 2: Add the preset registry and compatibility resolver

**Files:**
- Create: `inc/theme/homepage-authoring.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/settings.php`
- Create: `tests/offline/homepage-preset-isolation-contract.php`
- Modify: `tests/offline/homepage-composer-settings-contract.php`
- Modify: `tests/offline/r3-header-settings-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Produces: `homepage_preset_registry(): array`
- Produces: `homepage_source_key(string $preset, string $slot): ?string`
- Produces: `homepage_source_value(string $preset, string $slot, ?array $settings = null): mixed`
- Produces: `homepage_source_descriptor(string $preset, string $slot): ?array`
- Compatibility precedence: scoped key -> legacy key -> neutral empty value.

- [ ] **Step 1: Write the failing isolation contract**

Create `tests/offline/homepage-preset-isolation-contract.php` with assertions for these exact scoped keys:

```php
$required = [
    'homepage_law01_hero_block',
    'homepage_law01_hero_variant',
    'homepage_law01_hero_page',
    'homepage_law01_services_page',
    'homepage_law01_about_page',
    'homepage_law01_team_page',
    'homepage_law01_knowledge_terms',
    'homepage_law01_case_analysis_term',
    'homepage_law01_legal_news_term',
    'homepage_law01_process_page',
    'homepage_law01_faq_page',
    'homepage_law01_contact_page',
    'homepage_curtain01_hero_block',
    'homepage_curtain01_proof_block',
    'homepage_curtain01_about_page',
    'homepage_curtain01_about_image',
    'homepage_curtain01_knowledge_terms',
    'homepage_curtain01_contact_page',
    'homepage_curtain01_process_page',
    'homepage_curtain01_projects_term',
];
foreach ($required as $key) {
    assert(array_key_exists($key, AZnet\Theme\settings_defaults()), "Missing scoped Homepage key: {$key}");
}
```

Also assert:

```php
assert(AZnet\Theme\homepage_source_key('law-01', 'about') === 'homepage_law01_about_page');
assert(AZnet\Theme\homepage_source_key('curtain-01', 'about') === 'homepage_curtain01_about_page');

$legacy = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_knowledge_terms' => [17, 19],
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $legacy) === 91);
assert(AZnet\Theme\homepage_source_value('curtain-01', 'about', $legacy) === 91);

$scoped = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_law01_about_page' => 101,
    'homepage_curtain01_about_page' => 202,
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $scoped) === 101);
assert(AZnet\Theme\homepage_source_value('curtain-01', 'about', $scoped) === 202);
```

- [ ] **Step 2: Run RED**

Run:
```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-isolation-contract.php
```

Expected: FAIL because the new module/functions/scoped settings do not exist.

- [ ] **Step 3: Implement the bounded registry**

Create `inc/theme/homepage-authoring.php` with an explicit registry. Use the existing flat settings style:

```php
function homepage_preset_registry(): array {
    return [
        'law-01' => [
            'hero'          => [ 'type' => 'wp_block',   'key' => 'homepage_law01_hero_block',        'legacy' => 'homepage_hero_block' ],
            'hero_variant'  => [ 'type' => 'variant',    'key' => 'homepage_law01_hero_variant',      'legacy' => 'homepage_hero_variant' ],
            'hero_page'     => [ 'type' => 'page',       'key' => 'homepage_law01_hero_page',         'legacy' => 'homepage_hero_page' ],
            'services'      => [ 'type' => 'page',       'key' => 'homepage_law01_services_page',     'legacy' => 'homepage_services_page', 'children' => true ],
            'about'         => [ 'type' => 'page',       'key' => 'homepage_law01_about_page',        'legacy' => 'homepage_about_page' ],
            'team'          => [ 'type' => 'page',       'key' => 'homepage_law01_team_page',         'legacy' => 'homepage_team_page', 'children' => true ],
            'knowledge'     => [ 'type' => 'categories', 'key' => 'homepage_law01_knowledge_terms',   'legacy' => 'homepage_knowledge_terms' ],
            'case_analysis' => [ 'type' => 'category',   'key' => 'homepage_law01_case_analysis_term','legacy' => 'homepage_case_analysis_term' ],
            'legal_news'    => [ 'type' => 'category',   'key' => 'homepage_law01_legal_news_term',   'legacy' => 'homepage_legal_news_term' ],
            'process'       => [ 'type' => 'page',       'key' => 'homepage_law01_process_page',      'legacy' => 'homepage_process_page' ],
            'faq'           => [ 'type' => 'page',       'key' => 'homepage_law01_faq_page',          'legacy' => 'homepage_faq_page' ],
            'contact'       => [ 'type' => 'page',       'key' => 'homepage_law01_contact_page',      'legacy' => 'homepage_contact_page' ],
        ],
        'curtain-01' => [
            'hero'          => [ 'type' => 'wp_block',   'key' => 'homepage_curtain01_hero_block',      'legacy' => 'homepage_hero_block' ],
            'proof'         => [ 'type' => 'wp_block',   'key' => 'homepage_curtain01_proof_block',     'legacy' => 'homepage_proof_block' ],
            'about'         => [ 'type' => 'page',       'key' => 'homepage_curtain01_about_page',      'legacy' => 'homepage_about_page' ],
            'about_image'   => [ 'type' => 'attachment', 'key' => 'homepage_curtain01_about_image',     'legacy' => 'homepage_about_image' ],
            'knowledge'     => [ 'type' => 'categories', 'key' => 'homepage_curtain01_knowledge_terms', 'legacy' => 'homepage_knowledge_terms' ],
            'contact'       => [ 'type' => 'page',       'key' => 'homepage_curtain01_contact_page',    'legacy' => 'homepage_contact_page' ],
            'process'       => [ 'type' => 'page',       'key' => 'homepage_curtain01_process_page',    'legacy' => 'homepage_curtain01_process_page' ],
            'projects'      => [ 'type' => 'category',   'key' => 'homepage_curtain01_projects_term',   'legacy' => 'homepage_curtain01_projects_term' ],
        ],
    ];
}
```

Implement scoped-first resolution without truthy-value bugs:

```php
function homepage_source_value( string $preset, string $slot, ?array $settings = null ): mixed {
    $settings = null === $settings ? settings() : $settings;
    $descriptor = homepage_source_descriptor( $preset, $slot );
    if ( null === $descriptor ) {
        return null;
    }
    $key = (string) $descriptor['key'];
    $legacy = (string) $descriptor['legacy'];
    $type = (string) $descriptor['type'];

    $has_scoped = array_key_exists( $key, $settings )
        && ( 'categories' === $type ? [] !== (array) $settings[$key] : 0 !== $settings[$key] && '' !== $settings[$key] );

    if ( $has_scoped ) {
        return $settings[$key];
    }
    return $settings[$legacy] ?? ( 'categories' === $type ? [] : ( 'variant' === $type ? 'split' : 0 ) );
}
```

- [ ] **Step 4: Add scoped keys to normalization**

Keep `schema_version => 3`. Add the keys listed in Step 1 with the same `$normalize_id`, `$normalize_ids` and Hero variant allow-list rules used by legacy settings. Do not remove generic keys.

- [ ] **Step 5: Wire the module**

Require `inc/theme/homepage-authoring.php` from `inc/theme/bootstrap.php` before Homepage Composer/template execution.

- [ ] **Step 6: Run GREEN and retained settings regressions**

Run:
```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-isolation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-composer-settings-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/r3-header-settings-contract.php
```

Expected: PASS.

- [ ] **Step 7: Add the new contract to V1 verification and commit**

Add the test invocation to `scripts/verify-v1-core.sh`, then:

```bash
git add inc/theme/homepage-authoring.php inc/theme/bootstrap.php inc/theme/settings.php tests/offline/homepage-preset-isolation-contract.php tests/offline/homepage-composer-settings-contract.php tests/offline/r3-header-settings-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add preset-isolated homepage source registry"
```

---

### Task 3: Refactor frontend and provisioning consumers to the resolver

**Files:**
- Modify: `template-parts/homepage/law-01/*.php`
- Modify: `template-parts/homepage/curtain-01/hero.php`
- Modify: `template-parts/homepage/curtain-01/proof-strip.php`
- Modify: `template-parts/homepage/curtain-01/about.php`
- Modify: `template-parts/homepage/curtain-01/knowledge.php`
- Modify: `template-parts/homepage/curtain-01/final-cta.php`
- Modify: `inc/theme/provisioning-discovery.php`
- Modify: `inc/theme/provisioning-runner.php`
- Modify: `inc/theme/provisioning-readiness.php`
- Modify: existing Homepage/Curtain offline contracts
- Create: `tests/offline/homepage-consumer-isolation-contract.php`

**Interfaces:**
- Consumes: `homepage_source_value($preset, $slot)`.
- Produces: no direct generic setting reads in active Law 01/Curtain 01 renderers except inside the compatibility resolver.

- [ ] **Step 1: Write the failing consumer contract**

Assert that every active preset template uses the resolver and no longer contains direct reads such as:

```php
$forbidden = [
    "setting( 'homepage_about_page'",
    "setting( 'homepage_contact_page'",
    "setting( 'homepage_knowledge_terms'",
    "setting( 'homepage_hero_block'",
];
```

For Law 01 additionally reject direct `homepage_services_page`, `homepage_team_page`, `homepage_process_page`, `homepage_faq_page`, `homepage_case_analysis_term`, `homepage_legal_news_term`.

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-consumer-isolation-contract.php
```

Expected: FAIL on existing direct setting reads.

- [ ] **Step 3: Replace renderer reads with effective preset sources**

Use patterns such as:

```php
$parent = homepage_page_reference( (int) homepage_source_value( 'law-01', 'services' ) );
$terms = homepage_category_references( (array) homepage_source_value( 'law-01', 'knowledge' ) );
$page = homepage_page_reference( (int) homepage_source_value( 'curtain-01', 'about' ) );
$hero_block = homepage_block_reference( (int) homepage_source_value( 'curtain-01', 'hero' ) );
```

Law 01 Hero resolves the scoped synced block first, then scoped/legacy Hero Page via the resolver, while retaining the oldest Site/Front Page fallback.

- [ ] **Step 4: Move provisioning to Law 01 scoped keys for new writes**

In `provisioning-runner.php`, change the Page map to:

```php
$page_map = [
    'hero'     => 'homepage_law01_hero_page',
    'services' => 'homepage_law01_services_page',
    'about'    => 'homepage_law01_about_page',
    'team'     => 'homepage_law01_team_page',
    'process'  => 'homepage_law01_process_page',
    'faq'      => 'homepage_law01_faq_page',
    'contact'  => 'homepage_law01_contact_page',
];
```

Write Law 01 category mappings to `homepage_law01_knowledge_terms`, `homepage_law01_case_analysis_term`, `homepage_law01_legal_news_term`.

Do not overwrite generic legacy keys during new provisioning.

- [ ] **Step 5: Make discovery/readiness use effective Law 01 sources**

Replace direct generic lookups with `homepage_source_value('law-01', ...)` so an unmigrated legacy site remains READY while a migrated site uses scoped references.

- [ ] **Step 6: Run regression set**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-consumer-isolation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-hero-backward-compat-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/curtain01-c3-homepage-composition-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/curtain01-proof-strip-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/curtain01-project-showcase-contract.php
```

Expected: PASS and no rendering key leakage between presets.

- [ ] **Step 7: Commit**

```bash
git add template-parts/homepage inc/theme/provisioning-discovery.php inc/theme/provisioning-runner.php inc/theme/provisioning-readiness.php tests/offline scripts/verify-v1-core.sh
git commit -m "refactor: resolve homepage sources by preset"
```

---

### Task 4: Add explicit reference-only preset migration

**Files:**
- Create: `inc/admin/homepage-migration.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/homepage.php`
- Create: `tests/offline/homepage-preset-migration-contract.php`

**Interfaces:**
- Produces: `homepage_preset_migration_map(string $preset): array`
- Produces: `migrate_homepage_preset_references(string $preset, array $settings): array`
- Produces admin action: `admin_post_aznet_theme_migrate_homepage_preset`.

- [ ] **Step 1: Write RED migration tests**

Test Law 01:

```php
$before = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_services_page' => 81,
    'homepage_knowledge_terms' => [17, 19],
    'homepage_curtain01_about_page' => 300,
]);
$after = AZnet\Theme\Admin\migrate_homepage_preset_references('law-01', $before);
assert($after['homepage_law01_about_page'] === 91);
assert($after['homepage_law01_services_page'] === 81);
assert($after['homepage_law01_knowledge_terms'] === [17, 19]);
assert($after['homepage_curtain01_about_page'] === 300);
assert($after['homepage_about_page'] === 91);
```

Run migration twice and assert the second result equals the first.

Test that an existing scoped value is never overwritten:

```php
$settings['homepage_law01_about_page'] = 123;
$settings['homepage_about_page'] = 91;
assert(AZnet\Theme\Admin\migrate_homepage_preset_references('law-01', $settings)['homepage_law01_about_page'] === 123);
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-migration-contract.php
```

Expected: FAIL because migration functions do not exist.

- [ ] **Step 3: Implement pure reference migration**

Use the registry descriptors and copy only normalized reference/variant values when the scoped target is empty. No `wp_insert_post`, `wp_update_post`, `wp_update_term`, `set_post_thumbnail`, `wp_delete_post` or provider mutation belongs in this function.

- [ ] **Step 4: Add the protected admin action**

```php
function handle_homepage_preset_migration(): void {
    require_settings_capability();
    check_admin_referer( 'aznet_theme_migrate_homepage_preset' );
    $preset = isset( $_POST['homepage_preset_scope'] ) ? sanitize_key( wp_unslash( $_POST['homepage_preset_scope'] ) ) : '';
    if ( ! in_array( $preset, [ 'law-01', 'curtain-01' ], true ) ) {
        wp_die( esc_html__( 'Mẫu trang chủ không hợp lệ.', 'aznet-theme' ) );
    }
    $migrated = migrate_homepage_preset_references( $preset, settings() );
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $migrated ) );
    wp_safe_redirect( add_query_arg( [ 'page' => 'aznet-theme', 'section' => 'homepage', 'homepage_migrated' => $preset ], admin_url( 'admin.php' ) ) );
    exit;
}
```

- [ ] **Step 5: Expose one explicit migration button per active preset**

The button copy must state that it copies mappings only and does not copy/edit WordPress content.

- [ ] **Step 6: Run GREEN and verify no mutation APIs in the pure migration path**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-migration-contract.php
grep -nE 'wp_insert_post|wp_update_post|wp_update_term|set_post_thumbnail|wp_delete_post' inc/admin/homepage-migration.php
```

Expected: contract PASS; grep only matches no lines inside the pure migration implementation.

- [ ] **Step 7: Commit**

```bash
git add inc/admin/homepage-migration.php inc/admin/bootstrap.php inc/admin/homepage.php tests/offline/homepage-preset-migration-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add explicit homepage reference migration"
```

---

### Task 5: Rebuild the Homepage Control Center as a preset-aware authoring console

**Files:**
- Modify: `inc/admin/homepage.php`
- Modify: `inc/admin/control-center.php`
- Modify: `assets/css/admin/control-center.css`
- Modify: `tests/offline/homepage-control-center-contract.php`
- Create: `tests/browser/homepage-authoring-l4.mjs`

**Interfaces:**
- Consumes: preset registry/effective source resolver.
- Produces: section cards with source/status/edit/change-source controls for the active preset.

- [ ] **Step 1: Extend the static Control Center contract**

Require these rendered labels/classes:

```php
foreach ([
    'Mẫu đang chỉnh',
    'Sửa nhanh',
    'Chỉnh đầy đủ',
    'Đổi nguồn',
    'Nguồn & chẩn đoán',
    'aznet-theme-homepage-section-card',
    'aznet-theme-homepage-section-card__status',
] as $needle) {
    assert(str_contains($source . $css, $needle), "Missing authoring-console contract: {$needle}");
}
```

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-control-center-contract.php
```

Expected: FAIL on missing authoring-console UI.

- [ ] **Step 3: Add preset-aware section definitions**

Render Law 01 cards in this order:

```php
[ 'hero', 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact' ]
```

Render Curtain 01 cards in this order:

```php
[ 'hero', 'proof', 'about', 'process', 'projects', 'knowledge', 'contact' ]
```

Use registry source types to choose Page/Category/`wp_block` selectors. Do not expose free-form numeric IDs.

- [ ] **Step 4: Keep technical diagnostics under an advanced panel**

Normal cards show human labels and source titles. A collapsed/secondary `Nguồn & chẩn đoán` panel may show status and IDs for support purposes.

- [ ] **Step 5: Add browser assertions**

In `tests/browser/homepage-authoring-l4.mjs`, assert:

```js
await expect(page.getByText('Mẫu đang chỉnh: Luật 01')).toBeVisible();
await expect(page.locator('.aznet-theme-homepage-section-card')).toHaveCount(10);
await expect(page.getByRole('button', { name: 'Sửa nhanh' }).first()).toBeVisible();
await expect(page.getByText('Nguồn & chẩn đoán')).toBeVisible();
```

Also test the Curtain 01 preset fixture and confirm the Law 01 source form controls are not used as Curtain 01 target names.

- [ ] **Step 6: Run static/browser-local contract set**

Run the PHP contract now; the full Playwright test runs in Task 10 after the WordPress runtime fixture includes the new actions.

- [ ] **Step 7: Commit**

```bash
git add inc/admin/homepage.php inc/admin/control-center.php assets/css/admin/control-center.css tests/offline/homepage-control-center-contract.php tests/browser/homepage-authoring-l4.mjs
git commit -m "feat: make homepage control center preset aware"
```

---

### Task 6: Detect shared sources without auto-cloning

**Files:**
- Modify: `inc/theme/homepage-authoring.php`
- Modify: `inc/admin/homepage.php`
- Modify: `tests/offline/homepage-preset-isolation-contract.php`
- Modify: `tests/browser/homepage-authoring-l4.mjs`

**Interfaces:**
- Produces: `homepage_shared_source_uses(string $preset, string $slot, ?array $settings = null): array`.

- [ ] **Step 1: Add failing shared-source tests**

Example:

```php
$settings = AZnet\Theme\normalize_settings([
    'homepage_law01_about_page' => 91,
    'homepage_curtain01_about_page' => 91,
]);
$uses = AZnet\Theme\homepage_shared_source_uses('law-01', 'about', $settings);
assert(count($uses) === 1);
assert($uses[0]['preset'] === 'curtain-01');
assert($uses[0]['slot'] === 'about');
```

Also test distinct IDs produce `[]`.

- [ ] **Step 2: Run RED**

Expected: missing function.

- [ ] **Step 3: Implement exact typed comparison**

Compare only descriptors with compatible types and non-empty effective values. For arrays of Categories, compare individual term IDs and report overlap; do not treat array order differences as a different source.

- [ ] **Step 4: Render the warning**

When shared:

```text
Nguồn này hiện cũng được mẫu Rèm 01 sử dụng. Sửa nội dung nguồn sẽ ảnh hưởng cả hai mẫu.
```

Show:
- `Giữ dùng chung` as explanatory state, not a mutation button;
- `Tạo nguồn riêng cho mẫu này` only for Page/`wp_block` source types;
- Category-backed slots show `Đổi nguồn` instead of duplicate.

- [ ] **Step 5: Extend browser test**

Map Law 01 About and Curtain 01 About to the same fixture Page and assert the warning appears. Remap Curtain 01 to a different Page and assert the warning disappears.

- [ ] **Step 6: Run GREEN and commit**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-preset-isolation-contract.php
git add inc/theme/homepage-authoring.php inc/admin/homepage.php tests/offline/homepage-preset-isolation-contract.php tests/browser/homepage-authoring-l4.mjs
git commit -m "feat: warn when homepage presets share sources"
```

---

### Task 7: Add bounded Quick Edit over mapped WordPress objects

**Files:**
- Create: `inc/admin/homepage-authoring.php`
- Create: `assets/js/admin/homepage-authoring.js`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/homepage.php`
- Modify: `assets/css/admin/control-center.css`
- Create: `tests/offline/homepage-authoring-actions-contract.php`
- Modify: `tests/browser/homepage-authoring-l4.mjs`

**Interfaces:**
- Produces admin action: `admin_post_aznet_theme_quick_edit_homepage_source`.
- Consumes exact preset/slot from registry and exact effective mapped source.
- Page Quick Edit fields: `post_title`, `post_excerpt`, optional featured-image attachment ID.
- Category Quick Edit fields: `name`, `description`.
- Hero `wp_block`: Quick Edit button opens native editor; no block-markup mutation in this task.

- [ ] **Step 1: Write the failing action contract**

Require:

```php
foreach ([
    'current_user_can(',
    'check_admin_referer(',
    'homepage_source_descriptor(',
    'homepage_source_value(',
    'wp_update_post(',
    'wp_update_term(',
    'set_post_thumbnail(',
] as $needle) {
    assert(str_contains($action, $needle), "Missing Quick Edit safety/API contract: {$needle}");
}
```

Reject:
- any direct update of `aznet_theme_settings` from the Quick Edit action;
- any slug/title/URL lookup to discover a target;
- any external provider mutation.

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-authoring-actions-contract.php
```

- [ ] **Step 3: Implement exact mapped-target validation**

Before mutation:

```php
$descriptor = homepage_source_descriptor( $preset, $slot );
$mapped = homepage_source_value( $preset, $slot );
if ( null === $descriptor || (int) $mapped !== $source_id ) {
    wp_die( esc_html__( 'Nguồn chỉnh sửa không còn khớp với cấu hình trang chủ.', 'aznet-theme' ) );
}
```

For Page fields, require the target to be an existing Page and `current_user_can('edit_post', $source_id)`.

For Category fields, require an existing Category and `current_user_can('manage_categories')`.

For featured image, accept only `0` or an attachment for which `wp_attachment_is_image($image_id)` is true.

- [ ] **Step 4: Implement Page mutation with public APIs**

```php
$result = wp_update_post([
    'ID'           => $source_id,
    'post_title'   => sanitize_text_field($title),
    'post_excerpt' => sanitize_textarea_field($excerpt),
], true);
if ( is_wp_error($result) ) {
    wp_die( esc_html($result->get_error_message()) );
}
if ( 0 === $image_id ) {
    delete_post_thumbnail($source_id);
} else {
    set_post_thumbnail($source_id, $image_id);
}
```

Do not Quick Edit `post_content`; full content remains in the native editor.

- [ ] **Step 5: Implement Category mutation**

```php
$result = wp_update_term($term_id, 'category', [
    'name'        => sanitize_text_field($name),
    'description' => sanitize_textarea_field($description),
]);
if ( is_wp_error($result) ) {
    wp_die( esc_html($result->get_error_message()) );
}
```

- [ ] **Step 6: Add Media modal support**

Enqueue `wp_enqueue_media()` and `assets/js/admin/homepage-authoring.js` only on the AZnet Theme Control Center. The JS stores the chosen attachment ID in the Quick Edit form and updates a bounded thumbnail preview.

- [ ] **Step 7: Add browser tests for happy path and stale-target rejection**

Fixture:
1. open Law 01 About Quick Edit;
2. change title/excerpt;
3. save;
4. verify WordPress Page changed;
5. verify `aznet_theme_settings` contains only the same reference, not copied title/excerpt;
6. submit a forged source ID and verify the action is rejected.

- [ ] **Step 8: Run GREEN and commit**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-authoring-actions-contract.php
php -l inc/admin/homepage-authoring.php
git add inc/admin/homepage-authoring.php assets/js/admin/homepage-authoring.js inc/admin/bootstrap.php inc/admin/homepage.php assets/css/admin/control-center.css tests/offline/homepage-authoring-actions-contract.php tests/browser/homepage-authoring-l4.mjs scripts/verify-v1-core.sh
git commit -m "feat: add homepage quick edit bridge"
```

---

### Task 8: Add explicit draft-first source duplication

**Files:**
- Modify: `inc/admin/homepage-authoring.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/homepage.php`
- Modify: `tests/offline/homepage-authoring-actions-contract.php`
- Modify: `tests/browser/homepage-authoring-l4.mjs`

**Interfaces:**
- Produces admin action: `admin_post_aznet_theme_duplicate_homepage_source`.
- Supports Page and `wp_block`.
- For Page-backed registry entries with `children => true`, duplicates the parent plus direct child Pages as drafts and reparents duplicates under the new draft parent.
- Reuses featured-image attachment references; it does not duplicate Media files.

- [ ] **Step 1: Add RED duplication contract**

Require:
- `wp_insert_post(`;
- created status `draft`;
- `set_post_thumbnail(` only to reuse existing Media reference;
- exact preset/slot/source validation;
- scoped mapping update only;
- no generic legacy mapping overwrite.

- [ ] **Step 2: Implement single-source Page/wp_block duplication**

Use:

```php
$new_id = wp_insert_post([
    'post_type'    => $source->post_type,
    'post_status'  => 'draft',
    'post_title'   => $source->post_title . ' — ' . $preset_label,
    'post_excerpt' => $source->post_excerpt,
    'post_content' => $source->post_content,
    'post_parent'  => 'page' === $source->post_type ? (int) $source->post_parent : 0,
], true);
```

If the source Page has a featured image, set the same attachment ID on the draft duplicate.

- [ ] **Step 3: Duplicate direct children for collection-backed Page slots**

For `services` and `team`, read direct children by exact parent ID, create each duplicate as draft, and set `post_parent` to the new draft parent. Do not publish any duplicate.

- [ ] **Step 4: Remap only the requesting preset**

Resolve the descriptor's scoped key and write only that key through `normalize_settings` + `set_theme_mod`. The original source and the other preset mapping remain unchanged.

- [ ] **Step 5: Category rule**

Do not offer duplication for `category` or `categories` descriptors. The UI must direct the administrator to `Đổi nguồn` because cloning taxonomy names without post membership would create misleading semantics.

- [ ] **Step 6: Browser regression**

Shared Law 01/Curtain About:
- click `Tạo nguồn riêng cho mẫu này` for Law 01;
- assert new Page status is draft;
- assert Law 01 scoped About ID changed;
- assert Curtain 01 scoped About ID remains original;
- assert original Page content/status unchanged.

- [ ] **Step 7: Run tests and commit**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-authoring-actions-contract.php
git add inc/admin/homepage-authoring.php inc/admin/bootstrap.php inc/admin/homepage.php tests/offline/homepage-authoring-actions-contract.php tests/browser/homepage-authoring-l4.mjs
git commit -m "feat: separate shared homepage sources safely"
```

---

### Task 9: Complete Law 01 editorial-source cleanup and scoped Hero migration

**Files:**
- Modify: `template-parts/homepage/law-01/services.php`
- Modify: `template-parts/homepage/law-01/profile.php`
- Modify: `template-parts/homepage/law-01/latest.php`
- Modify: `template-parts/homepage/law-01/topics.php`
- Modify: `template-parts/homepage/law-01/analysis.php`
- Modify: `template-parts/homepage/law-01/news.php`
- Modify: `template-parts/homepage/law-01/process.php`
- Modify: `template-parts/homepage/law-01/faq.php`
- Modify: `template-parts/homepage/law-01/final-cta.php`
- Modify: `template-parts/homepage/law-01/hero.php`
- Modify: `inc/admin/homepage-hero.php`
- Modify: `inc/admin/homepage.php`
- Create: `tests/offline/homepage-editorial-source-contract.php`
- Modify: `tests/offline/homepage-hero-library-contract.php`
- Modify: `tests/offline/homepage-law-01-hero-backward-compat-contract.php`

**Interfaces:**
- Law 01 site-specific headings/body come from mapped Page/Category/Posts Page/Hero sources.
- Generic UI labels remain translatable Theme microcopy.
- Hero actions write the active preset's scoped Hero reference/variant; generic Hero keys become compatibility-only.

- [ ] **Step 1: Write the hard-coded editorial RED contract**

Fail if Law 01 template parts still contain these site-specific strings:

```php
$forbidden = [
    'Dịch vụ nổi bật',
    'Các dịch vụ pháp lý dành cho bạn',
    'Giới thiệu về văn phòng',
    'Đội ngũ luật sư',
    'Kiến thức pháp luật',
    'Bài viết mới nhất',
    'Chủ đề pháp luật',
    'Tìm hiểu theo lĩnh vực',
    'Bạn đang cần hỗ trợ về một vấn đề pháp lý?',
    'Trao đổi với đội ngũ để xác định hướng xử lý phù hợp cho trường hợp của bạn.',
];
```

Do not forbid generic UI microcopy such as `Xem chi tiết`, `Đọc thêm`, `Đổi nguồn`, Previous/Next or accessibility labels.

- [ ] **Step 2: Run RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-editorial-source-contract.php
```

Expected: FAIL on current Law 01 templates.

- [ ] **Step 3: Replace site-specific headings with authoritative source fields**

Use these rules:

- Services H2 = mapped Services Page title; intro = Page excerpt. Optional eyebrow becomes generic UI `Dịch vụ`.
- About H2 = mapped About Page title; excerpt/image remain Page-owned. Optional eyebrow = generic UI `Giới thiệu`.
- Team H2 = mapped Team Page title; optional eyebrow = generic UI `Đội ngũ`.
- Latest H2 = published Posts Page title when configured; otherwise generic UI `Bài viết`. Optional eyebrow = generic UI `Mới cập nhật`.
- Topics H2 = generic UI `Chủ đề`; actual topic names remain Category-owned.
- Analysis H2 = mapped Category name; optional eyebrow = generic UI `Phân tích`.
- News H2 = mapped Category name; optional eyebrow = generic UI `Cập nhật`.
- Process H2 = mapped Process Page title; optional eyebrow = generic UI `Quy trình`.
- FAQ H2 = mapped FAQ Page title; optional eyebrow = generic UI `Hỏi đáp`.
- Final CTA H2 = mapped Contact Page title; body = mapped Contact Page excerpt; button `Liên hệ tư vấn` remains generic action microcopy.

This keeps all site-specific editorial copy in WordPress and avoids adding Theme text settings.

- [ ] **Step 4: Keep Curtain legacy editorial settings compatibility-only**

Do not add new UI that writes `homepage_about_kicker`, `homepage_about_heading` or `homepage_about_quote`. Mark them in code comments/tests as legacy Curtain compatibility only. New Curtain authoring should use Page title/excerpt/featured image and the scoped `homepage_curtain01_about_image` reference where a separate presentation image is still needed.

- [ ] **Step 5: Make Hero Library preset-scoped**

Pass `homepage_preset_scope` through the Hero Library form.

For Law 01:
- reference key = `homepage_law01_hero_block`;
- variant key = `homepage_law01_hero_variant`;
- legacy fallback = generic `homepage_hero_block` / `homepage_hero_page`.

For Curtain 01:
- reference key = `homepage_curtain01_hero_block`;
- generic Hero remains fallback only.

Changing a Law 01 Hero must not change Curtain 01 Hero mapping.

- [ ] **Step 6: Add explicit legacy-Hero migration action**

When Law 01 has a legacy Hero Page and no scoped Hero block, expose `Nâng cấp Hero hiện tại`.

Create a draft Core-block `wp_block` from the exact legacy Page fields:

```php
$title = get_the_title($legacy_page);
$excerpt = trim((string) $legacy_page->post_excerpt);
$image_id = (int) get_post_thumbnail_id($legacy_page);

$content = '<!-- wp:group {"className":"aznet-theme-homepage-hero-content"} -->'
    . '<div class="wp-block-group aznet-theme-homepage-hero-content">'
    . '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">' . esc_html($title) . '</h1><!-- /wp:heading -->'
    . ('' !== $excerpt ? '<!-- wp:paragraph --><p>' . esc_html($excerpt) . '</p><!-- /wp:paragraph -->' : '')
    . '</div><!-- /wp:group -->';
```

If `$image_id > 0`, append a valid Core Image block using the current attachment URL/alt. Create the `wp_block` as `draft`, write only `homepage_law01_hero_block`, leave legacy Page untouched, and keep public fallback until publish.

- [ ] **Step 7: Run editorial and Hero regressions**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-editorial-source-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-hero-library-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-hero-backward-compat-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add template-parts/homepage/law-01 inc/admin/homepage-hero.php inc/admin/homepage.php tests/offline/homepage-editorial-source-contract.php tests/offline/homepage-hero-library-contract.php tests/offline/homepage-law-01-hero-backward-compat-contract.php tests/offline/homepage-law-01-contract.php scripts/verify-v1-core.sh
git commit -m "feat: complete law01 homepage authoring sources"
```

---

### Task 10: Runtime, browser, regression and evidence closure

**Files:**
- Modify: `.github/workflows/homepage-law-01-browser.yml`
- Modify or create: workflow for `tests/browser/homepage-authoring-l4.mjs`
- Modify: `scripts/verify-v1-core.sh`
- Create: `docs/evidence/HOMEPAGE_AUTHORING_SYSTEM_D038_20260924.md`
- Modify after PASS only: `docs/source/AZT-03-baseline-provenance.md`
- Modify after PASS only: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify after PASS only: `docs/source/SOURCE_MANIFEST.md`
- Modify after PASS only: `docs/source/AZT-EXEC-MAP.md`

**Interfaces:**
- Consumes all implementation tasks.
- Produces fresh L1/L2/L3/L4 evidence and a recoverable branch checkpoint.
- Does not deploy production or publish a release.

- [ ] **Step 1: Run the full static/contract verification**

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS on PHP 8.1 contract path, including all new D-038 tests.

- [ ] **Step 2: Run PHP lint on touched production PHP**

```bash
git diff --name-only origin/main...HEAD -- '*.php' | xargs -r -n1 php -l
```

Expected: no syntax errors.

- [ ] **Step 3: Add a disposable WordPress runtime fixture**

The fixture must create:
- Law 01 and Curtain 01 mapped Pages with intentionally different IDs/content;
- one shared About Page for the warning case;
- mapped Categories;
- a legacy Law 01 Hero Page;
- a published Curtain `wp_block` Hero;
- one Page-backed collection with child Pages.

It must save scoped mappings through `AZnet\Theme\normalize_settings()` and must not rely on slug/title inference.

- [ ] **Step 4: Run L3 mutation/isolation assertions**

Verify in WordPress:
1. legacy-only Law 01 resolves generic sources;
2. explicit Law migration copies refs only;
3. Quick Edit changes the mapped Page/Category and not Theme copy fields;
4. forged target is rejected;
5. source duplication creates drafts and remaps one preset only;
6. Law/Curtain preset switching preserves both maps;
7. Hero migration creates draft block and public Hero stays on legacy until publish;
8. invalid/deleted mapped source fails soft.

- [ ] **Step 5: Run L4 admin/frontend Playwright**

Run the new Homepage authoring test plus retained Law 01 and Curtain 01 tests. Minimum commands in the workflow:

```bash
node tests/browser/homepage-authoring-l4.mjs
node tests/browser/tamduc-homepage-final-l4.mjs
node tests/browser/curtain01-c5-pilot-preview.mjs
```

Expected:
- no browser/runtime errors;
- no blocking axe violations;
- no horizontal overflow at retained viewport matrix;
- Control Center keyboard/focus path usable;
- Law 01 and Curtain 01 render their own mapped sources.

- [ ] **Step 6: Run retained exact-package/core checks appropriate to the branch**

Use the existing V1/X6 repository workflow entry points. Do not claim GitHub Release publication or production deployment from branch-only results.

- [ ] **Step 7: Write evidence**

`docs/evidence/HOMEPAGE_AUTHORING_SYSTEM_D038_20260924.md` must record:
- exact branch/head;
- canonical base;
- source decision D-038;
- RED failure causes and GREEN commits;
- L1/L2 commands/results;
- L3 runtime fixture/result;
- L4 browser/a11y result;
- Law/Curtain isolation proof;
- legacy compatibility proof;
- Hero draft-first proof;
- remaining UNKNOWN/BLOCKED layers;
- rollback: revert branch/PR; legacy refs and WordPress content remain.

- [ ] **Step 8: Update provenance/source state only to the evidenced depth**

After fresh evidence:
- bump AZT-03 with exact head and tested layers;
- mark D-038 implementation PASS only for layers actually run in AZT-04;
- update SOURCE_MANIFEST and AZT-EXEC-MAP exact next.

Do not claim release/deployment unless those separate gates are later approved and executed.

- [ ] **Step 9: Commit the verification checkpoint**

```bash
git add .github/workflows scripts/verify-v1-core.sh tests docs/evidence docs/source
git commit -m "test: close homepage authoring system verification"
```

- [ ] **Step 10: Open a review PR; do not merge**

Create a PR from the work branch to `main` with:
- spec link;
- D-038 source decision;
- task/commit breakdown;
- test/evidence summary;
- explicit statement that production deploy and release publication are not included.

Owner merge remains a separate gate.

---

## Rollback / Recovery

- Every task ends in a bounded commit; revert the last task without discarding previous PASS slices.
- Generic Homepage keys are retained throughout this plan, so legacy resolver fallback remains available.
- Quick Edit mutates only the explicit WordPress object the administrator selected and can use WordPress revisions/editorial recovery where available.
- Explicit duplicate actions create drafts; rollback can remap the preset to the prior source without deleting the draft.
- Hero migration creates a draft `wp_block`; returning it to draft or clearing the scoped reference restores the legacy Hero fallback while preserving authored content.
- No production-site mutation is part of this plan until a later owner-approved deployment operation.
