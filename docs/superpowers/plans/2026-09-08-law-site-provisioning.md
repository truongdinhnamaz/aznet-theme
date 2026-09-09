# Law Site Provisioning Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an explicit, idempotent Law 01 setup wizard that can provision a new WordPress site or safely map an existing site, producing a coherent Homepage without making AZnet Theme the ongoing owner of WordPress content.

**Architecture:** Provisioning is a separate admin-only bootstrap subsystem layered on top of the approved Homepage Composer + Law 01 work. It performs read-only discovery, generates an explicit immutable change plan, applies only user-confirmed WordPress-native writes through public APIs, records bounded provenance, rolls back only the current failed run, and hands off to the existing Content Map/preset after success. Theme activation itself remains mutation-free.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + `theme.json`, Classic Editor for native Post/Page, existing AZnet Theme Control Center, plain PHP/CSS/JS only where required, WP-CLI + Playwright/axe in disposable CI, no bundler.

**Spec:** `docs/superpowers/specs/2026-09-08-law-site-provisioning-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Stacked implementation dependency: Homepage Composer + Law 01 PR #58, branch `work/homepage-composer-law01`, current head `e7f3117948e00efb89430aec14f758bcac8a7081` at plan creation.
- `main` remains `12a480311dcb59f3d7a152ef7e72ca927c2f632d` at plan creation; do not assume PR #58 has merged.
- Theme activation alone must create no Page, Post, Category, menu, Front Page assignment or Law 01 mapping.
- Provisioning starts only after an authorized user explicitly launches the wizard and confirms the Step 3 change plan.
- WordPress remains owner of Page/Post/Category/menu/publication data after provisioning.
- Existing-site data is never overwritten by title/slug similarity; reuse is explicit and typed.
- No title/slug/URL heuristic may become authoritative source selection.
- No XML/WXR blind demo importer.
- No Theme-owned legal-service CPT, FAQ store, Person store, query-builder store or parallel content master.
- RootProfile/ConvertFlow/Woo private storage and domain semantics remain outside the Theme.
- Starter structural Pages may publish; starter editorial Posts, if created, are Draft by default.
- Starter copy must contain no fake lawyer/person, address, phone, email, credential, metric, testimonial, case result, current legal-news claim or substantive legal advice.
- Provisioning is idempotent by bounded provenance + current explicit Content Map, not semantic title matching.
- v1 never auto-updates starter copy after successful provisioning.
- No public “Delete all demo content” or destructive reset action.
- Failed-run rollback may delete only objects demonstrably created by that failed run; reused/pre-existing objects are never rollback-deleted.
- Setup Ready and Launch Ready are distinct; provisioning success must not be presented as production-launch readiness.
- Classic Editor policy and the existing Homepage Composer/`the_content()` boundary remain unchanged.
- Feature work uses RED -> verify expected failure -> minimal GREEN -> retained regression.
- Main merge, release tag, GitHub Release, production deployment and takeover remain explicit owner approval gates.

---

## File Structure

Create or modify these focused units:

- `docs/source/AZT-02-architecture.md` — ratify bounded WordPress-native provisioning ownership and limits.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — add D-025 and provisioning QA/release gates.
- `docs/source/SOURCE_MANIFEST.md` — advance source versions/decision range after ratification.
- `docs/source/AZT-EXEC-MAP.md` — set exact next to Law Site Provisioning implementation.
- `inc/theme/provisioning-blueprints.php` — immutable `law01-v1` Page/Category/menu/starter-copy definitions.
- `inc/theme/provisioning-discovery.php` — read-only WordPress inventory and typed candidate lists.
- `inc/theme/provisioning-plan.php` — canonical validated change-plan model and state fingerprint.
- `inc/theme/provisioning-provenance.php` — bounded object markers and successful/failed run receipts.
- `inc/theme/provisioning-runner.php` — apply/verify/rollback orchestration using public WordPress APIs.
- `inc/theme/provisioning-readiness.php` — Setup Ready / warnings evaluation and handoff checklist.
- `inc/theme/bootstrap.php` — require provisioning runtime modules only; no activation mutation.
- `inc/admin/provisioning.php` — wizard routing, nonce/capability checks, step render/action handlers.
- `inc/admin/bootstrap.php` — require/register provisioning admin module.
- `inc/admin/control-center.php` — add the persistent “Thiết lập website nhanh” entry point/status.
- `assets/css/admin/control-center.css` — bounded wizard/readiness presentation additions.
- `tests/offline/provisioning-blueprint-contract.php` — blueprint shape, publication policy and forbidden-copy checks.
- `tests/offline/provisioning-activation-contract.php` — no auto-provision on activation.
- `tests/offline/provisioning-discovery-contract.php` — read-only typed discovery and no heuristic ownership.
- `tests/offline/provisioning-plan-contract.php` — explicit plan generation/fingerprint/no hidden writes.
- `tests/offline/provisioning-runner-contract.php` — authorization, public API boundaries, rollback model.
- `tests/offline/provisioning-idempotency-contract.php` — rerun/provenance/current-mapping precedence.
- `tests/offline/provisioning-readiness-contract.php` — Setup Ready vs Launch warning rules.
- `tests/browser/law-site-provisioning-l4.mjs` — wizard/browser/a11y and resulting Homepage checks.
- `.github/workflows/law-site-provisioning-browser.yml` — disposable WordPress L3-L4 matrix.
- `scripts/verify-v1-core.sh` — retain Composer/Law 01 and add provisioning offline contracts.
- `docs/evidence/LAW_SITE_PROVISIONING_L4.md` — fresh L0-L4 evidence after verification.

Do not add a generic multi-industry provisioning framework in v1. Internal interfaces may be blueprint-shaped, but only `law01-v1` is implemented.

---

### Task 1: Source/State Gate — Ratify Provisioning Before Production Code

**Files:**
- Modify: `docs/source/AZT-02-architecture.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`

**Interfaces:**
- Consumes: approved provisioning spec.
- Produces: accepted architecture authority for Tasks 2-8.

- [ ] **Step 1: Revalidate stacked base and canonical main**

Run:

```bash
git fetch origin main work/homepage-composer-law01 work/law-site-provisioning-design
git rev-parse origin/main
git rev-parse origin/work/homepage-composer-law01
git rev-parse origin/work/law-site-provisioning-design
```

Expected at plan creation:

```text
main = 12a480311dcb59f3d7a152ef7e72ca927c2f632d
work/homepage-composer-law01 = e7f3117948e00efb89430aec14f758bcac8a7081
```

If either moved, compare before rebasing. Do not reimplement any still-valid Homepage Composer/Law 01 PASS work.

- [ ] **Step 2: Ratify provisioning ownership in AZT-02**

Add a section whose normative content is:

```markdown
## Law Site Provisioning v1

AZnet Theme may own an explicit, bounded setup workflow that creates ordinary WordPress-native starter objects through public WordPress APIs only after an authorized user confirms an explicit change plan.

This bootstrap capability does not transfer ongoing Page/Post/Category/Menu ownership to Theme.

Required constraints:
- Theme activation performs no content provisioning.
- Provisioning and Homepage preset switching are separate operations.
- Existing-site reuse is explicit and typed; title/slug/URL matching cannot silently establish semantic source ownership.
- Created objects become ordinary WordPress content and survive Theme switching.
- Idempotency uses bounded provisioning provenance plus the current explicit Content Map.
- Failed-run rollback may remove only objects created by that failed run and may restore only captured configuration/settings changed by the run.
- After a successful handoff there is no Theme-owned destructive “delete all demo content” action.
- Starter copy is non-authoritative and is not synchronized after successful provisioning.
```

Advance AZT-02 from v0.6 to v0.7.

- [ ] **Step 3: Record D-025 in AZT-04**

Add:

```markdown
| **D-025** | **Law Site Provisioning v1 is an explicit idempotent WordPress-native bootstrap workflow: activation is mutation-free; new/existing sites use a visible confirmed change plan; created content becomes WordPress-owned; failed-run rollback is current-run bounded; successful setup is not equivalent to Launch Ready.** | **Accepted** |
```

Add a bounded provisioning implementation slice with L1 static, L2 TDD, L3 empty/existing/rerun/rollback runtime, L4 wizard + resulting Homepage browser/a11y, then candidate-package evidence. Main merge/release/deploy remain owner-gated.

Advance AZT-04 from v0.29 to v0.30.

- [ ] **Step 4: Reconcile source manifest and execution map**

Update `SOURCE_MANIFEST.md` to AZT-02 v0.7, AZT-04 v0.30 and Accepted D-001..D-025. Update `AZT-EXEC-MAP.md` so exact next is Law Site Provisioning v1 rather than Homepage Composer implementation.

- [ ] **Step 5: Commit source gate**

```bash
git add docs/source/AZT-02-architecture.md docs/source/AZT-04-roadmap-qa-decisions.md docs/source/SOURCE_MANIFEST.md docs/source/AZT-EXEC-MAP.md
git commit -m "docs: ratify law site provisioning architecture"
```

Acceptance: this commit contains no production PHP/CSS/JS change.

---

### Task 2: Immutable `law01-v1` Blueprint + Safe Starter Copy

**Files:**
- Create: `tests/offline/provisioning-blueprint-contract.php`
- Create: `inc/theme/provisioning-blueprints.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Produces:
  - `provisioning_blueprint( string $key ): ?array`
  - `provisioning_blueprint_keys(): array`
- Supported key in v1: `law01-v1` only.
- Blueprint object roles use stable machine keys, not titles, for provisioning identity.

- [ ] **Step 1: Write RED blueprint contract**

Test the returned blueprint shape exactly:

```php
$blueprint = \AZnet\Theme\provisioning_blueprint( 'law01-v1' );
assert( is_array( $blueprint ) );
assert( $blueprint['homepage_preset'] === 'law-01' );
assert( $blueprint['pages']['home']['status'] === 'publish' );
assert( $blueprint['pages']['services']['status'] === 'publish' );
assert( $blueprint['pages']['service_criminal']['parent_role'] === 'services' );
assert( $blueprint['categories']['knowledge']['parent_role'] === null );
assert( $blueprint['categories']['knowledge_civil']['parent_role'] === 'knowledge' );
assert( $blueprint['editorial_examples']['status'] === 'draft' );
```

Required Page roles:

```text
home
about
services
service_business
service_civil
service_criminal
service_real_estate
service_family
service_labor
team
process
faq
contact
```

Required Category roles:

```text
knowledge
knowledge_business
knowledge_civil
knowledge_criminal
knowledge_real_estate
knowledge_family
knowledge_labor
case_analysis
legal_news
```

The test must scan all starter copy and fail if it contains forbidden credential/identity placeholders such as:

```text
500+
98%
1000+
tỷ lệ thắng
hàng đầu
luật sư Nguyễn
địa chỉ mẫu
090
@example
Điều 123
```

Also reject an allow-list of known fake-testimonial/award phrases.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-blueprint-contract.php
```

Expected: FAIL because the blueprint module/functions do not exist.

- [ ] **Step 3: Implement the minimal immutable blueprint**

Use static PHP arrays returned by functions; do not persist blueprint definitions in WordPress options.

Example shape:

```php
function provisioning_blueprint( string $key ): ?array {
    if ( 'law01-v1' !== $key ) {
        return null;
    }

    return [
        'key'             => 'law01-v1',
        'homepage_preset' => 'law-01',
        'pages'           => [
            'home' => [
                'title'   => 'Trang chủ',
                'status'  => 'publish',
                'excerpt' => 'Chúng tôi hỗ trợ khách hàng tiếp cận vấn đề pháp lý theo hướng rõ ràng, thực tiễn và phù hợp với từng hoàn cảnh cụ thể.',
                'content' => '<p>Nội dung giới thiệu tổng quan có thể được chỉnh sửa trực tiếp trong WordPress.</p>',
                'parent_role' => null,
            ],
        ],
        'categories'       => [],
        'menu'             => [ 'location' => 'primary', 'label' => 'AZnet Primary' ],
        'editorial_examples' => [ 'status' => 'draft', 'items' => [] ],
    ];
}
```

Populate all approved structural roles with neutral starter copy. Process starter body must contain the four approved generic steps. FAQ starter body may use accessible `<details><summary>` HTML with generic intake/process questions.

- [ ] **Step 4: Verify GREEN + retained Homepage contracts**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-blueprint-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/homepage-law-01-contract.php
bash scripts/verify-v1-core.sh
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/provisioning-blueprints.php inc/theme/bootstrap.php tests/offline/provisioning-blueprint-contract.php scripts/verify-v1-core.sh
git commit -m "feat: add law01 provisioning blueprint"
```

---

### Task 3: Activation Invitation + Read-Only Discovery

**Files:**
- Create: `tests/offline/provisioning-activation-contract.php`
- Create: `tests/offline/provisioning-discovery-contract.php`
- Create: `inc/theme/provisioning-discovery.php`
- Create: `inc/admin/provisioning.php`
- Modify: `inc/admin/bootstrap.php`
- Modify: `inc/admin/control-center.php`
- Modify: `inc/theme/bootstrap.php`

**Interfaces:**
- Produces:
  - `provisioning_discovery(): array`
  - `provisioning_page_candidates(): array`
  - `provisioning_category_candidates(): array`
  - `render_provisioning_invitation(): void`
  - admin route/tab key: `provisioning`
- Discovery is read-only and returns IDs/types/status/counts; it does not assign semantic roles.

- [ ] **Step 1: Write RED activation contract**

Scan activation/bootstrap paths and assert provisioning code does not register an activation callback that calls any write primitive such as:

```text
wp_insert_post
wp_update_post
wp_insert_term
wp_update_term
wp_create_nav_menu
wp_update_nav_menu_item
update_option
set_theme_mod
```

The contract may allow a dismissible admin notice preference only if dismissal is stored through the existing admin settings mechanism and does not provision content.

- [ ] **Step 2: Write RED discovery contract**

Stub WordPress reads and assert:

```php
$state = \AZnet\Theme\provisioning_discovery();
assert( $state['page_count'] === 3 );
assert( $state['category_count'] === 4 );
assert( $state['post_count'] === 12 );
assert( $state['front_page_id'] === 44 );
assert( $state['homepage_preset'] === 'off' );
assert( isset( $state['homepage_slots'] ) );
```

Page candidates must contain only Page IDs/titles/status for selection. Category candidates must contain only Category term IDs/names. Production discovery code must not call:

```php
get_page_by_title(
get_page_by_path(
get_term_by( 'slug'
url_to_postid(
```

and must not scan post bodies/meta for semantic matching.

- [ ] **Step 3: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-activation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-discovery-contract.php
```

Expected: missing module/functions/UI route.

- [ ] **Step 4: Implement minimal invitation + discovery**

Use public reads only:

```php
function provisioning_page_candidates(): array {
    $pages = get_pages( [ 'post_status' => [ 'publish', 'draft', 'private', 'pending' ], 'sort_column' => 'post_title' ] );
    return array_map(
        static fn( WP_Post $page ): array => [
            'id'     => (int) $page->ID,
            'title'  => (string) $page->post_title,
            'status' => (string) $page->post_status,
        ],
        $pages
    );
}
```

Add a `Thiết lập website nhanh` entry in the existing admin area. The invitation only navigates to Step 1; it does not apply a blueprint.

- [ ] **Step 5: Verify GREEN + Classic Editor regression**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-activation-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-discovery-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/editor-policy-static-contract.php
```

Use the repository's exact Classic Editor contract filename if it differs; retain the guarantee for native Post/Page.

- [ ] **Step 6: Commit**

```bash
git add inc/theme/provisioning-discovery.php inc/admin/provisioning.php inc/admin/bootstrap.php inc/admin/control-center.php inc/theme/bootstrap.php tests/offline/provisioning-activation-contract.php tests/offline/provisioning-discovery-contract.php
git commit -m "feat: add provisioning discovery and setup invitation"
```

---

### Task 4: Explicit Change Plan + Wizard Steps 1-3

**Files:**
- Create: `tests/offline/provisioning-plan-contract.php`
- Create: `inc/theme/provisioning-plan.php`
- Modify: `inc/admin/provisioning.php`
- Modify: `assets/css/admin/control-center.css`

**Interfaces:**
- Produces:
  - `provisioning_build_plan( string $blueprint_key, array $selections, array $discovery ): array`
  - `provisioning_plan_fingerprint( array $plan ): string`
  - `provisioning_validate_plan( array $plan ): array`
- Selection action values: `reuse|create|skip`.
- Required roles cannot use `skip` unless a valid existing Content Map reference already satisfies the role.

- [ ] **Step 1: Write RED plan contract**

Given selections:

```php
$selections = [
    'pages' => [
        'home'     => [ 'action' => 'create', 'object_id' => 0 ],
        'about'    => [ 'action' => 'reuse', 'object_id' => 42 ],
        'services' => [ 'action' => 'create', 'object_id' => 0 ],
        'contact'  => [ 'action' => 'reuse', 'object_id' => 51 ],
    ],
    'categories' => [
        'case_analysis' => [ 'action' => 'create', 'object_id' => 0 ],
        'legal_news'    => [ 'action' => 'skip', 'object_id' => 0 ],
    ],
    'menu' => [ 'action' => 'create', 'menu_id' => 0 ],
    'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
];
```

Assert the plan emits ordered operations such as:

```text
reuse_page about #42
create_page home
create_page services
create_term case_analysis
create_menu primary
set_front_page home
map_homepage_sources
set_homepage_preset law-01
```

Each operation must have a stable operation ID, type, target role and human-readable effect string for Step 3.

The fingerprint must change if selected object IDs, current Front Page/menu assignment, or relevant Content Map state changes.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-plan-contract.php
```

Expected: plan module/functions missing.

- [ ] **Step 3: Implement canonical plan representation**

Use a pure array model with no writes:

```php
[
    'schema_version' => 1,
    'blueprint'      => 'law01-v1',
    'generated_at'   => 0,
    'state_fingerprint' => 'sha256:...',
    'operations'     => [
        [
            'id'       => 'page:about:reuse',
            'type'     => 'reuse_page',
            'role'     => 'about',
            'object_id'=> 42,
            'effect'   => 'Reuse Page #42 as About source',
        ],
    ],
]
```

Do not include arbitrary callable names or SQL in plan data.

- [ ] **Step 4: Implement Wizard Steps 1-3**

Step 1 renders discovery. Step 2 renders typed select/action controls. Step 3 renders the exact operation list and stores the confirmed plan in a signed/nonce-protected server-side or request-safe representation that can be revalidated before apply.

State-changing form submission requires:

```php
current_user_can( 'manage_options' )
check_admin_referer( 'aznet_theme_provisioning' )
```

Use strict allow-lists for `blueprint`, `action`, role keys and object IDs.

- [ ] **Step 5: Verify GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-plan-contract.php
php -l inc/theme/provisioning-plan.php
php -l inc/admin/provisioning.php
```

- [ ] **Step 6: Commit**

```bash
git add inc/theme/provisioning-plan.php inc/admin/provisioning.php assets/css/admin/control-center.css tests/offline/provisioning-plan-contract.php
git commit -m "feat: add provisioning change plan wizard"
```

---

### Task 5: Provenance + Transaction Runner + Failed-Run Rollback

**Files:**
- Create: `tests/offline/provisioning-runner-contract.php`
- Create: `inc/theme/provisioning-provenance.php`
- Create: `inc/theme/provisioning-runner.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/admin/provisioning.php`

**Interfaces:**
- Produces:
  - `provisioning_apply_plan( array $plan ): array`
  - `provisioning_rollback_failed_run( array $receipt ): array`
  - `provisioning_mark_post( int $post_id, string $blueprint, string $role, string $run_id ): bool`
  - `provisioning_mark_term( int $term_id, string $blueprint, string $role, string $run_id ): bool`
  - `provisioning_find_owned_role( string $blueprint, string $object_type, string $role ): int`
- Result shape:

```php
[
    'ok'      => true,
    'run_id'  => 'opaque-id',
    'created' => [ [ 'type' => 'page', 'id' => 123, 'role' => 'about' ] ],
    'reused'  => [ [ 'type' => 'page', 'id' => 42, 'role' => 'contact' ] ],
    'errors'  => [],
]
```

- [ ] **Step 1: Write RED runner contract with injectable WordPress stubs**

Test an injected failure after two creates:

```php
$result = \AZnet\Theme\provisioning_apply_plan( $plan );
assert( $result['ok'] === false );
assert( $fake_db->existing_page_42_still_exists === true );
assert( $fake_db->created_page_ids === [] );
assert( $fake_db->front_page_id === $pre_run_front_page_id );
assert( $fake_db->theme_settings === $pre_run_theme_settings );
```

The contract must fail if production rollback can delete an object not listed in the current run's `created` receipt.

Also scan runner/provenance code and reject direct SQL or provider-private storage access.

- [ ] **Step 2: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-runner-contract.php
```

Expected: runner/provenance functions missing.

- [ ] **Step 3: Implement bounded provenance**

For Pages, use public post-meta APIs with private internal keys:

```text
_aznet_theme_provisioned_by
_aznet_theme_provisioning_role
_aznet_theme_provisioning_run
```

For Category terms use corresponding public term-meta APIs. Provenance may identify only objects created by this subsystem; it must not establish current Homepage mapping authority.

- [ ] **Step 4: Implement transaction receipt and runner**

Before writes capture only state the plan is allowed to change:

```php
$receipt = [
    'run_id' => $run_id,
    'pre' => [
        'homepage_settings' => settings(),
        'show_on_front'     => get_option( 'show_on_front' ),
        'page_on_front'     => (int) get_option( 'page_on_front' ),
        'nav_locations'     => get_theme_mod( 'nav_menu_locations', [] ),
    ],
    'created' => [],
    'reused'  => [],
];
```

Before executing, re-run discovery and require the current state fingerprint to equal the confirmed plan fingerprint. If not equal, return a stale-plan error before writes.

Create/update only through public APIs such as `wp_insert_post`, `wp_insert_term`, `wp_create_nav_menu`, `wp_update_nav_menu_item`, `update_option`, and the existing normalized Theme settings path.

- [ ] **Step 5: Implement failed-run rollback**

Rollback order:

1. restore Homepage Theme settings captured in `pre`;
2. restore `show_on_front` and `page_on_front` if this run changed them;
3. restore menu-location assignment if this run changed it;
4. delete only IDs present in `receipt['created']`, in reverse dependency order;
5. never delete `reused` IDs.

If rollback itself has an error, return it explicitly; do not claim clean rollback.

- [ ] **Step 6: Verify GREEN**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-runner-contract.php
php -l inc/theme/provisioning-provenance.php
php -l inc/theme/provisioning-runner.php
```

- [ ] **Step 7: Commit**

```bash
git add inc/theme/provisioning-provenance.php inc/theme/provisioning-runner.php inc/theme/bootstrap.php inc/admin/provisioning.php tests/offline/provisioning-runner-contract.php
git commit -m "feat: add bounded provisioning transaction runner"
```

---

### Task 6: Idempotency, Existing-Site Protection, Mapping + Readiness Handoff

**Files:**
- Create: `tests/offline/provisioning-idempotency-contract.php`
- Create: `tests/offline/provisioning-readiness-contract.php`
- Create: `inc/theme/provisioning-readiness.php`
- Modify: `inc/theme/provisioning-plan.php`
- Modify: `inc/theme/provisioning-runner.php`
- Modify: `inc/admin/provisioning.php`
- Modify: `assets/css/admin/control-center.css`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Produces:
  - `provisioning_readiness(): array`
  - `provisioning_launch_warnings(): array`
- Setup status enum: `READY|READY_WITH_WARNINGS|INCOMPLETE`.

- [ ] **Step 1: Write RED idempotency contract**

Scenario A — successful rerun:

```php
$first = provisioning_apply_plan( $new_site_plan );
$second_plan = provisioning_build_plan( 'law01-v1', $same_selections, provisioning_discovery() );
$second = provisioning_apply_plan( $second_plan );
assert( $second['ok'] === true );
assert( count( $second['created'] ) === 0 );
```

Scenario B — user edits a provisioned About Page after success:

```php
wp_update_post( [ 'ID' => $about_id, 'post_content' => 'User-authored content' ] );
provisioning_apply_plan( $rerun_plan );
assert( get_post( $about_id )->post_content === 'User-authored content' );
```

Scenario C — explicit current Content Map remaps `about` to unmarked Page #88. A rerun must keep #88 as the presentation source and must not silently revert to old provisioned About #42.

Scenario D — an unmarked existing Page happens to have title `Giới thiệu`; it must not be auto-owned/reused without an explicit user selection.

- [ ] **Step 2: Write RED readiness contract**

Assert:

```php
$status = \AZnet\Theme\provisioning_readiness();
assert( in_array( $status['status'], [ 'READY', 'READY_WITH_WARNINGS', 'INCOMPLETE' ], true ) );
```

Setup Ready requires:

```text
valid static Front Page
homepage_preset = law-01
valid Services source
valid About source
valid Contact Page source
valid/assigned Primary Menu when selected by setup plan
Homepage Composer render path available
```

Missing Team/Knowledge/Analysis/News/Process/FAQ produces warnings/fail-soft, not Setup failure.

Launch warnings must include missing real brand/contact/image review items where detectable, and the UI text must say:

```text
Thiết lập hoàn tất — Website đã sẵn sàng để chỉnh nội dung.
```

It must not say the site is ready to launch solely because provisioning succeeded.

- [ ] **Step 3: Verify RED**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-idempotency-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-readiness-contract.php
```

- [ ] **Step 4: Implement idempotent operation resolution**

Before creating a role, use bounded provenance for that exact blueprint/object-type/role. If a current explicit Homepage mapping already points to a valid different object, current mapping wins for presentation-source roles.

Never update starter body/excerpt after successful creation in v1.

Menu rerun rules:

- do not duplicate a provisioning-owned menu item already linked to the same object;
- do not rewrite unrelated existing menu items;
- if the user explicitly chose an existing menu, only add operations shown in the confirmed plan.

- [ ] **Step 5: Implement final mapping and readiness**

After object creation/reuse succeeds, resolve role IDs and save Homepage Content Map using the existing normalized settings path, then set `homepage_preset=law-01` only if the plan contains that operation.

Final wizard actions:

```text
Xem trang chủ
Chỉnh nội dung cần thiết
Quản lý Trang chủ
```

The second action opens a checklist of starter Pages and launch warnings; the third opens Control Center → Trang chủ.

- [ ] **Step 6: Verify GREEN + full offline chain**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-idempotency-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-readiness-contract.php
bash scripts/verify-v1-core.sh
```

- [ ] **Step 7: Commit**

```bash
git add inc/theme/provisioning-readiness.php inc/theme/provisioning-plan.php inc/theme/provisioning-runner.php inc/admin/provisioning.php assets/css/admin/control-center.css tests/offline/provisioning-idempotency-contract.php tests/offline/provisioning-readiness-contract.php scripts/verify-v1-core.sh
git commit -m "feat: make law site provisioning idempotent"
```

---

### Task 7: L3-L4 WordPress Runtime Matrix + Wizard Browser/A11y

**Files:**
- Create: `tests/browser/law-site-provisioning-l4.mjs`
- Create: `.github/workflows/law-site-provisioning-browser.yml`
- Create: `docs/evidence/LAW_SITE_PROVISIONING_L4.md`

**Interfaces:**
- Workflow provisions WordPress 6.9 / PHP 8.1 disposable environments.
- Test fixture may mutate its disposable WordPress database directly through WP-CLI to create preconditions; production Theme code may not use fixture shortcuts.

- [ ] **Step 1: Write browser test before workflow fixture is GREEN**

Admin wizard test must assert:

```text
Step 1 discovery is visible and read-only
Step 2 exposes Reuse/Create/Skip controls with typed selectors
Step 3 shows explicit operation rows before apply
Apply requires authenticated admin session
final page shows Setup Ready status and 3 handoff actions
keyboard focus is visible
aXe critical=0, serious=0 on wizard screens tested
no Theme-caused console/page errors
```

Resulting public Law 01 Homepage must retain the existing Law 01 L4 requirements at 1440, 1024, 390 and 320 widths.

- [ ] **Step 2: Implement runtime Scenario 1 — empty site**

Use WP-CLI to install WordPress 6.9, activate the exact candidate, then drive the wizard through browser or a test-only authenticated form helper using the same production endpoints/nonces.

Verify after setup:

```text
all required structural Pages exist
6 service child Pages have correct parent
starter Categories exist with correct parent relationships
structural Pages are publish
any editorial demo Posts are draft
Front Page is assigned
Primary Menu is assigned
Homepage Content Map references the created IDs
homepage_preset = law-01
public Homepage renders
```

- [ ] **Step 3: Implement Scenario 2 — existing site**

Fixture creates existing About/Contact Pages, Categories, a Front Page and an unrelated menu before opening wizard.

Choose explicit reuse for existing objects. Verify their title/slug/content/status are byte-for-byte unchanged after apply.

- [ ] **Step 4: Implement Scenario 3 — rerun after success**

Run discovery/plan/apply again and assert zero duplicate provisioning roles, zero duplicate provisioning-owned menu items, and stable current Content Map.

- [ ] **Step 5: Implement Scenario 4 — injected mid-run failure**

Use a test-only failure injection hook defined only when `AZNET_THEME_TEST_PROVISIONING_FAILURE` is enabled in the disposable environment. Fail after a known create operation and verify:

```text
current-run created objects removed
pre-existing/reused objects still exist
Front Page/menu/settings restored
wizard reports failure, not success
```

Production default must never enable the hook.

- [ ] **Step 6: Implement Scenario 5 — user edit protection**

After a successful run, edit a provisioned Page body through WP-CLI or WordPress admin, rerun setup, and assert the user body remains unchanged.

- [ ] **Step 7: Run workflow and preserve RED reasons until fixed**

Each initial failure must be attributed to an actual provisioning behavior or test-fixture problem. Do not weaken production ownership or overwrite rules to make CI green.

- [ ] **Step 8: Record fresh evidence**

`docs/evidence/LAW_SITE_PROVISIONING_L4.md` must include:

```text
branch/head SHA
base dependency SHA
WordPress/PHP versions
workflow run ID
scenario results
axe summary
Law 01 retained L4 result
rollback scenario result
idempotency scenario result
artifact IDs
explicit BLOCKED/UNKNOWN items
```

- [ ] **Step 9: Commit workflow/evidence**

```bash
git add tests/browser/law-site-provisioning-l4.mjs .github/workflows/law-site-provisioning-browser.yml docs/evidence/LAW_SITE_PROVISIONING_L4.md
git commit -m "test: verify law site provisioning runtime"
```

---

### Task 8: Full Regression + Deterministic Downloadable Candidate

**Files:**
- Modify: `docs/source/AZT-03-baseline-provenance.md` only after fresh evidence exists.
- Modify: `docs/source/SOURCE_MANIFEST.md` if AZT-03 version changes.
- Modify: PR metadata/evidence only; do not merge `main` automatically.

**Interfaces:**
- Produces an installable candidate ZIP containing Homepage Composer + Law 01 + Law Site Provisioning v1.
- Candidate version must be read from `style.css`; if the project governance requires a new version before distribution, advance it in a bounded release-candidate commit and verify all version constants match.

- [ ] **Step 1: Run the full reusable regression suite on exact candidate head**

```bash
bash scripts/verify-v1-core.sh
```

Then ensure all relevant GitHub workflows triggered on the candidate head complete successfully, including:

```text
V1 Core Pull Request CI
F Homepage Native contracts/runtime/package
G lifecycle update/switch/rollback
R1 Design System
R2 Pattern Library
R3 Header
R4 WooCommerce presentation
R5 Control Center
R6 Performance Baseline
Homepage Composer Law 01 Browser Quality
Law Site Provisioning Browser Quality
```

Do not claim a workflow that did not run on the exact candidate head.

- [ ] **Step 2: Update AZT-03 only from fresh evidence**

Record exact head SHA, QA layers actually passed, workflow IDs/artifacts, and explicit remaining UNKNOWN/BLOCKED items. Do not claim L5 provider certification or L6 release/deployment unless separately proven/approved.

- [ ] **Step 3: Build deterministic installable ZIP twice**

Use the existing release-package builder if present; otherwise use the repository's current deterministic candidate method already proven for Homepage Law 01.

Example with existing builder:

```bash
VERSION=$(php -r '$s=file_get_contents("style.css"); preg_match("/^Version:\\s*(.+)$/mi", $s, $m); echo trim($m[1]);')
mkdir -p /tmp/aznet-provision-a /tmp/aznet-provision-b
python3 scripts/build-release-package.py --source . --output "/tmp/aznet-provision-a/aznet-theme-${VERSION}.zip" --version "$VERSION"
python3 scripts/build-release-package.py --source . --output "/tmp/aznet-provision-b/aznet-theme-${VERSION}.zip" --version "$VERSION"
cmp "/tmp/aznet-provision-a/aznet-theme-${VERSION}.zip" "/tmp/aznet-provision-b/aznet-theme-${VERSION}.zip"
sha256sum "/tmp/aznet-provision-a/aznet-theme-${VERSION}.zip"
```

Unzip and compare packaged production files byte-for-byte against the exact verified candidate tree using the retained package gate.

- [ ] **Step 4: Open/update a draft stacked PR, do not merge automatically**

If PR #58 remains unmerged, open the provisioning PR against `work/homepage-composer-law01` so the diff contains provisioning only. If PR #58 has been owner-approved and merged by then, rebase safely and target `main`.

PR body must include:

```text
PASS / BLOCKED / UNKNOWN
exact base and head SHAs
D-025/source versions
empty-site scenario
existing-site no-overwrite scenario
rerun/idempotency scenario
failed-run rollback scenario
user-edit preservation scenario
browser/a11y evidence
candidate filename + SHA-256
rollback path
explicit note: no auto-provision on activation, no destructive demo reset
```

- [ ] **Step 5: Produce the user-downloadable candidate ZIP**

Only after the exact candidate bytes are verified, copy/download the inner installable ZIP into the active conversation sandbox and provide:

```text
file link
Theme version
SHA-256
verified head SHA
what is included
what is not implied (no main merge/release/deploy unless separately approved)
```

---

## Self-Review Results

- **Spec coverage:** activation invitation, new/existing-site support, 4-step wizard, explicit change plan, hybrid publication, neutral starter copy, provenance, idempotency, stale-plan detection, rollback, no destructive reset, Setup vs Launch readiness, handoff actions, L0-L4 QA and candidate packaging are each mapped to concrete tasks.
- **Ownership check:** no task creates RootProfile/ConvertFlow/Woo domain state or introduces a Theme-owned long-term content master.
- **Safety check:** existing objects are reuse-only unless a displayed/confirmed plan explicitly changes allowed WordPress configuration; rollback deletion is restricted to current-run creations.
- **TDD check:** every production subsystem begins with an expected RED contract before implementation.
- **Dependency check:** implementation remains stacked on Homepage Composer/Law 01 until PR #58 is owner-approved/merged; the plan does not silently merge or rebase across that gate.
- **Release check:** candidate package generation is allowed as verification output; main merge, tag, GitHub Release and deployment remain owner gates.
