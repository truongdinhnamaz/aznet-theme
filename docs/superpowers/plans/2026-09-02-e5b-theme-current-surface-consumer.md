# E5-B Theme Current-Surface Consumer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a dormant AZnet Theme consumer + renderer dispatcher for the approved RootProfile `rootprofile/presentation/current-surface/v1` contract without claiming or changing any production route.

**Architecture:** RootProfile remains responsible for resolving the current request and eventually publishing a normalized current-surface context. AZnet Theme validates that context, converts the embedded existing Provider v1/v2 payload into the already-proven E2/E3 presentation models, and dispatches to existing Theme renderers. This plan does not register a `template_include`, `template_redirect`, `the_content`, slug detector, Page detector, or any other production takeover hook.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, server-rendered PHP, WordPress filter API, existing AZnet Theme E4 presentation modules; no bundler required.

**Spec:** `docs/superpowers/specs/2026-09-02-e5-rootprofile-current-surface-contract-design.md`

## Global Constraints

- Theme namespace remains `AZnet\\Theme`; integration namespace remains `AZnet\\Theme\\Integrations\\RootProfile`.
- Current-surface hook is exactly `rootprofile/presentation/current-surface/v1`.
- Current-surface contract is exactly `rootprofile.current_surface`, version `1`.
- Allowed surfaces are exactly `person_profile`, `organization_profile`, `contact`.
- Nested `person_profile` and `organization_profile` payloads must be existing RootProfile Provider v2 payloads: contract `rootprofile.presentation`, version `2`, matching resource.
- Nested `contact` payload must be an existing RootProfile Provider v1 payload: contract `rootprofile.presentation`, version `1`, resource `contact`.
- No RootProfile post IDs, Page IDs, user IDs, option/meta/table names, private classes, routing classes, slugs, query vars or storage details may be needed by Theme.
- Theme must not call `TruongDinhNam\\RootProfile\\Surfaces\\Router` or any RootProfile implementation class.
- Theme must not infer Contact/Profile by slug, title, content, Page ID, post type or URL pattern.
- Current-surface context availability is not takeover authorization.
- This plan must not register the new dispatcher on WordPress request lifecycle hooks.
- Existing RootProfile rendering remains the production path until E5-C LIVE UAT and a separately approved E5-D takeover slice.
- Existing E2 Contact and E3/E4 Profile rendering behavior must remain compatible.
- RootProfile absent/current-surface hook absent/malformed/throwing must fail soft.
- Internal Theme version remains `0.1.0-alpha.7`; this plan is not a release-version promotion.
- E5-B local PASS must not be extrapolated to E5-C runtime/browser/a11y or E5-D takeover.
- E5-A RootProfile current-surface publisher is a separate source-owned dependency and is not implemented in this Theme repository.

---

## File Structure

### Production files

- Modify `inc/integrations/rootprofile.php`
  - Add current-surface hook constants.
  - Add pure validation for an already-returned provider payload.
  - Add `current_surface_context(): ?array`.
  - Preserve existing v1/v2 provider consumer behavior.

- Modify `inc/theme/contact-surface.php`
  - Extract existing payload-to-model transformation into `contact_surface_model_from_payload(array $contact_payload, ?array $organization_payload = null): ?array`.
  - Make `contact_surface_model()` fetch providers and delegate to the new pure transformer.

- Modify `inc/theme/profile-surface.php`
  - Extract existing payload-to-model transformation into `profile_surface_model_from_payload(string $resource, array $payload): ?array`.
  - Make `profile_surface_model()` fetch Provider v2 and delegate to the new pure transformer.
  - Preserve the E4 `profile_surface_model_is_renderable()` guard unchanged in semantics.

- Create `inc/theme/rootprofile-current-surface.php`
  - Own only context-to-existing-renderer dispatch.
  - Add `render_current_rootprofile_surface(array $context): bool`.
  - Enqueue only the Contact or Profile surface CSS needed for the selected surface.
  - Do not register any route/request hook.

- Modify `inc/theme/bootstrap.php`
  - `require_once` the dispatcher module.
  - Do not add a new action/filter for production rendering.

### Source-only verification files

- Create `tests/offline/e5-current-surface-consumer-contract.php`
  - Verify hook availability/fail-soft and exact current-surface contract validation.

- Create `tests/offline/e5-current-surface-model-contract.php`
  - Verify explicit provider-payload → E2/E3 presentation-model conversion and provider-path regression.

- Create `tests/offline/e5-current-surface-dispatcher-contract.php`
  - Verify correct renderer/template/asset dispatch for three surfaces and no output for malformed input.

- Create `tests/offline/e5-no-takeover-static-contract.php`
  - Verify the E5-B production delta contains no route/takeover/storage/private-class heuristics.

- Create `scripts/verify-e5b.sh`
  - Run the four offline contracts and PHP lint for all production PHP files.

The source-only `tests/` and `scripts/` files are repository verification assets and must remain excluded from distributable Theme ZIPs, consistent with prior E2-E4 package evidence.

---

### Task 1: Current-surface consumer contract

**Files:**
- Modify: `inc/integrations/rootprofile.php:14-143`
- Create: `tests/offline/e5-current-surface-consumer-contract.php`

**Interfaces:**
- Consumes: WordPress `has_filter()` and `apply_filters()` only.
- Produces:
  - `CURRENT_SURFACE_HOOK = 'rootprofile/presentation/current-surface/v1'`
  - `CURRENT_SURFACE_CONTRACT = 'rootprofile.current_surface'`
  - `CURRENT_SURFACE_VERSION = 1`
  - `current_surface_available(): bool`
  - `validated_provider_payload(array $candidate, string $resource, int $version): ?array`
  - `current_surface_context(): ?array`

- [ ] **Step 1: Write the failing consumer contract test**

Create `tests/offline/e5-current-surface-consumer-contract.php` with a minimal WordPress filter stub and assertions covering absent hook, throwing hook, wrong current-surface contract/version/surface, wrong nested provider version/resource, and valid Person/Organization/Contact contexts.

The core fixture shape must be:

```php
$person = [
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'person_profile',
    'presentation' => [
        'contract' => 'rootprofile.presentation',
        'version' => 2,
        'resource' => 'person_profile',
        'entity' => [
            'uuid' => '123e4567-e89b-42d3-a456-426614174000',
            'display_name' => 'Nguyen Van A',
            'profile_url' => 'https://example.test/ho-so/nguyen-van-a/',
        ],
        'sections' => [],
    ],
];
```

Use equivalent fixtures for `organization_profile` (Provider v2) and `contact` (Provider v1). Assertions must require returned arrays to preserve the exact `surface` and embedded `presentation` payload.

- [ ] **Step 2: Run the consumer contract and verify RED**

Run:

```bash
php tests/offline/e5-current-surface-consumer-contract.php
```

Expected before implementation: non-zero exit because `current_surface_context()` and/or current-surface constants/functions do not exist.

- [ ] **Step 3: Implement current-surface validation minimally**

In `inc/integrations/rootprofile.php`, add after the existing provider constants/functions:

```php
const CURRENT_SURFACE_HOOK = 'rootprofile/presentation/current-surface/v1';
const CURRENT_SURFACE_CONTRACT = 'rootprofile.current_surface';
const CURRENT_SURFACE_VERSION = 1;

function current_surface_available(): bool {
    if ( ! function_exists( 'has_filter' ) ) {
        return false;
    }

    return false !== has_filter( CURRENT_SURFACE_HOOK );
}

function validated_provider_payload( array $candidate, string $resource, int $version ): ?array {
    if ( ( $candidate['contract'] ?? null ) !== PROVIDER_CONTRACT ) {
        return null;
    }

    if ( (int) ( $candidate['version'] ?? 0 ) !== $version ) {
        return null;
    }

    if ( ( $candidate['resource'] ?? null ) !== $resource ) {
        return null;
    }

    return $candidate;
}

function current_surface_context(): ?array {
    if ( ! current_surface_available() || ! function_exists( 'apply_filters' ) ) {
        return null;
    }

    try {
        $candidate = apply_filters( CURRENT_SURFACE_HOOK, null );
    } catch ( \Throwable ) {
        return null;
    }

    if ( ! is_array( $candidate ) ) {
        return null;
    }

    if ( ( $candidate['contract'] ?? null ) !== CURRENT_SURFACE_CONTRACT
        || (int) ( $candidate['version'] ?? 0 ) !== CURRENT_SURFACE_VERSION ) {
        return null;
    }

    $surface = (string) ( $candidate['surface'] ?? '' );
    $presentation = $candidate['presentation'] ?? null;
    if ( ! is_array( $presentation ) ) {
        return null;
    }

    $expected_version = 'contact' === $surface ? PROVIDER_VERSION : PROFILE_PROVIDER_VERSION;
    if ( ! in_array( $surface, [ 'person_profile', 'organization_profile', 'contact' ], true ) ) {
        return null;
    }

    if ( null === validated_provider_payload( $presentation, $surface, $expected_version ) ) {
        return null;
    }

    return [
        'surface' => $surface,
        'presentation' => $presentation,
    ];
}
```

Then refactor existing `payload()` and `profile_payload()` only enough to call `validated_provider_payload()` after their hook call. Their accepted/rejected behavior must remain the same.

- [ ] **Step 4: Run consumer contract GREEN and retained syntax check**

Run:

```bash
php tests/offline/e5-current-surface-consumer-contract.php
php -l inc/integrations/rootprofile.php
```

Expected: contract prints one PASS line and exits `0`; PHP lint reports no syntax errors.

- [ ] **Step 5: Commit Task 1**

```bash
git add inc/integrations/rootprofile.php tests/offline/e5-current-surface-consumer-contract.php
git commit -m "feat: add dormant RootProfile current-surface consumer"
```

---

### Task 2: Payload-to-model adapters without provider re-resolution

**Files:**
- Modify: `inc/theme/contact-surface.php:17-110`
- Modify: `inc/theme/profile-surface.php:19-116`
- Create: `tests/offline/e5-current-surface-model-contract.php`

**Interfaces:**
- Consumes: already validated Provider v1/v2 arrays.
- Produces:
  - `contact_surface_model_from_payload(array $contact_payload, ?array $organization_payload = null): ?array`
  - `profile_surface_model_from_payload(string $resource, array $payload): ?array`
- Preserves:
  - `contact_surface_model(): ?array`
  - `profile_surface_model(string $resource, int $entity_id = 0): ?array`
  - `profile_surface_model_is_renderable(array $model): bool`

- [ ] **Step 1: Write RED model-adapter tests**

Create `tests/offline/e5-current-surface-model-contract.php` that defines `ABSPATH`, stubs the required provider functions in `AZnet\\Theme\\Integrations\\RootProfile`, loads both surface modules, and asserts:

```php
$profile_model = \AZnet\Theme\profile_surface_model_from_payload(
    'person_profile',
    $person_provider_v2_fixture
);
```

returns a model with:

```php
[
    'resource' => 'person_profile',
    'entity' => $person_provider_v2_fixture['entity'],
    'sections' => /* valid sections in original order */,
    'navigation' => /* only show_in_navigation=true sections */,
]
```

Also assert:

```php
$contact_model = \AZnet\Theme\contact_surface_model_from_payload(
    $contact_provider_v1_fixture,
    $organization_provider_v1_fixture
);
```

preserves UUID matching and rejects an Organization enrichment payload with a different UUID.

Finally assert existing provider-derived `profile_surface_model()` and `contact_surface_model()` still return equivalent models when their public provider stubs return the same fixtures.

- [ ] **Step 2: Run model contract RED**

Run:

```bash
php tests/offline/e5-current-surface-model-contract.php
```

Expected before implementation: non-zero exit because the two `*_from_payload()` functions do not exist.

- [ ] **Step 3: Extract Contact payload transformation**

Move the transformation currently inside `contact_surface_model()` into:

```php
function contact_surface_model_from_payload( array $contact_payload, ?array $organization_payload = null ): ?array
```

Rules:

- require non-empty Contact entity UUID + display name;
- treat missing Organization enrichment as `[]`;
- if Organization UUID is non-empty and differs from Contact UUID, return `null`;
- preserve existing detail-empty rejection;
- preserve existing fields and output shape exactly.

Then reduce `contact_surface_model()` to:

```php
function contact_surface_model(): ?array {
    if ( ! \AZnet\Theme\Integrations\RootProfile\provider_available() ) {
        return null;
    }

    $contact_payload = \AZnet\Theme\Integrations\RootProfile\contact();
    if ( ! is_array( $contact_payload ) ) {
        return null;
    }

    $organization_payload = \AZnet\Theme\Integrations\RootProfile\organization();

    return contact_surface_model_from_payload(
        $contact_payload,
        is_array( $organization_payload ) ? $organization_payload : null
    );
}
```

- [ ] **Step 4: Extract Profile payload transformation**

Move the transformation currently starting from `$entity = ...` through `return $model;` into:

```php
function profile_surface_model_from_payload( string $resource, array $payload ): ?array
```

The new function must:

- allow only `person_profile` and `organization_profile`;
- require entity `uuid`, `display_name`, `profile_url`;
- preserve provider-resolved section order;
- preserve `key`, `label`, `anchor`, `show_in_navigation`, `data`, optional `section_type`, optional `origin`;
- derive navigation only from provider `show_in_navigation`;
- preserve Person `organization_context` behavior;
- never infer a section registry.

Then make `profile_surface_model()` fetch Provider v2 exactly as it does now and delegate to `profile_surface_model_from_payload()`.

- [ ] **Step 5: Run model contract and lints GREEN**

Run:

```bash
php tests/offline/e5-current-surface-model-contract.php
php -l inc/theme/contact-surface.php
php -l inc/theme/profile-surface.php
```

Expected: contract PASS; both files lint clean.

- [ ] **Step 6: Commit Task 2**

```bash
git add inc/theme/contact-surface.php inc/theme/profile-surface.php tests/offline/e5-current-surface-model-contract.php
git commit -m "refactor: accept validated RootProfile surface payloads"
```

---

### Task 3: Dormant current-surface dispatcher

**Files:**
- Create: `inc/theme/rootprofile-current-surface.php`
- Modify: `inc/theme/bootstrap.php:14-22`
- Create: `tests/offline/e5-current-surface-dispatcher-contract.php`

**Interfaces:**
- Consumes: validated `current_surface_context()` output:

```php
array{
    surface: 'person_profile'|'organization_profile'|'contact',
    presentation: array<string,mixed>
}
```

- Produces:
  - `render_current_rootprofile_surface(array $context): bool`
- Must not produce any WordPress route hook registration.

- [ ] **Step 1: Write RED dispatcher contract**

Create `tests/offline/e5-current-surface-dispatcher-contract.php` with stubs for:

```php
get_template_part()
wp_enqueue_style()
get_theme_file_uri()
```

Load the RootProfile integration module, Contact module, Profile module, then the new dispatcher path. The test must assert:

- valid Person context → Profile CSS enqueued once, Profile template called once, returns `true`;
- valid Organization context → Profile CSS enqueued once, Profile template called once, returns `true`;
- valid Contact context → Contact CSS enqueued once, Contact template called once, returns `true`;
- malformed/unsupported context → no template, no surface CSS, returns `false`;
- no slug/query/Page resolver function is required by the dispatcher.

- [ ] **Step 2: Run dispatcher contract RED**

Run:

```bash
php tests/offline/e5-current-surface-dispatcher-contract.php
```

Expected before implementation: non-zero exit because `inc/theme/rootprofile-current-surface.php` and `render_current_rootprofile_surface()` do not exist.

- [ ] **Step 3: Implement the dispatcher**

Create `inc/theme/rootprofile-current-surface.php` with this responsibility only:

```php
<?php
/**
 * Dormant RootProfile current-surface presentation dispatcher.
 *
 * This module does not claim WordPress routes. A future separately gated E5-D
 * slice may invoke it from an approved takeover boundary after LIVE UAT.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function render_current_rootprofile_surface( array $context ): bool {
    $surface = (string) ( $context['surface'] ?? '' );
    $presentation = $context['presentation'] ?? null;
    if ( ! is_array( $presentation ) ) {
        return false;
    }

    if ( in_array( $surface, [ 'person_profile', 'organization_profile' ], true ) ) {
        $model = profile_surface_model_from_payload( $surface, $presentation );
        if ( ! is_array( $model ) || ! profile_surface_model_is_renderable( $model ) ) {
            return false;
        }
        enqueue_profile_surface_assets();
        render_profile_surface( $model, $surface );
        return true;
    }

    if ( 'contact' === $surface ) {
        $organization_payload = \AZnet\Theme\Integrations\RootProfile\organization();
        $model = contact_surface_model_from_payload(
            $presentation,
            is_array( $organization_payload ) ? $organization_payload : null
        );
        if ( ! is_array( $model ) ) {
            return false;
        }
        enqueue_contact_surface_assets();
        render_contact_surface( $model );
        return true;
    }

    return false;
}
```

- [ ] **Step 4: Load the dispatcher without registering it**

In `inc/theme/bootstrap.php`, add exactly one require after the Contact/Profile modules:

```php
require_once __DIR__ . '/rootprofile-current-surface.php';
```

Do not add an `add_action()` or `add_filter()` for this dispatcher.

- [ ] **Step 5: Run dispatcher test and lints GREEN**

Run:

```bash
php tests/offline/e5-current-surface-dispatcher-contract.php
php -l inc/theme/rootprofile-current-surface.php
php -l inc/theme/bootstrap.php
```

Expected: PASS; syntax clean.

- [ ] **Step 6: Commit Task 3**

```bash
git add inc/theme/rootprofile-current-surface.php inc/theme/bootstrap.php tests/offline/e5-current-surface-dispatcher-contract.php
git commit -m "feat: add dormant RootProfile surface dispatcher"
```

---

### Task 4: Ownership / no-takeover regression gate

**Files:**
- Create: `tests/offline/e5-no-takeover-static-contract.php`
- Create: `scripts/verify-e5b.sh`

**Interfaces:**
- Consumes: production tree after Tasks 1-3.
- Produces: repeatable local L0-L2 E5-B verification command.

- [ ] **Step 1: Write static ownership contract**

Create `tests/offline/e5-no-takeover-static-contract.php` that reads the five E5-B production files and fails if the E5-B delta contains any of these strings:

```text
TruongDinhNam\\RootProfile\\
rootprofile_person
rootprofile_organization
get_query_var(
is_page(
get_queried_object_id(
template_include
template_redirect
the_content
_rootprofile_
```

The test must allow WordPress `add_filter`/`apply_filters` only inside `inc/integrations/rootprofile.php` for public contract consumption. It must separately assert `inc/theme/rootprofile-current-surface.php` contains no `add_action(` and no `add_filter(`.

Also assert `inc/theme/bootstrap.php` has exactly the existing setup/global asset hooks and does not register `render_current_rootprofile_surface()`.

- [ ] **Step 2: Run static contract**

Run:

```bash
php tests/offline/e5-no-takeover-static-contract.php
```

Expected after Tasks 1-3: PASS.

- [ ] **Step 3: Create the bounded verification script**

Create executable `scripts/verify-e5b.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/e5-current-surface-consumer-contract.php
php tests/offline/e5-current-surface-model-contract.php
php tests/offline/e5-current-surface-dispatcher-contract.php
php tests/offline/e5-no-takeover-static-contract.php

PHP_COUNT=0
while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
    PHP_COUNT=$((PHP_COUNT + 1))
done < <(find . -type f -name '*.php' \
    -not -path './tests/*' \
    -not -path './vendor/*' \
    -print0)

echo "PASS: E5-B offline contracts"
echo "PASS: production PHP lint ${PHP_COUNT}/${PHP_COUNT}"
```

Mark executable:

```bash
chmod +x scripts/verify-e5b.sh
```

- [ ] **Step 4: Run the full E5-B verification command**

Run:

```bash
./scripts/verify-e5b.sh
```

Expected: four contract PASS lines, final `PASS: E5-B offline contracts`, and production PHP lint count with no failures.

- [ ] **Step 5: Verify source/package boundary**

Run:

```bash
find . -type f | sort
```

Confirm production Theme files remain under the normal Theme root and `tests/` + `scripts/` are source-only. Do not create a distributable ZIP in E5-B; package/release remains E7 responsibility.

- [ ] **Step 6: Commit Task 4**

```bash
git add tests/offline/e5-no-takeover-static-contract.php scripts/verify-e5b.sh
git commit -m "test: gate E5-B ownership and no takeover"
```

---

### Task 5: E5-B checkpoint evidence and handoff to E5-A/E5-C

**Files:**
- Create: `docs/evidence/E5B_LOCAL_VERIFICATION.md`
- Modify: `docs/superpowers/plans/2026-09-02-e5b-theme-current-surface-consumer.md` only to check completed boxes during execution.

**Interfaces:**
- Consumes: fresh output from `./scripts/verify-e5b.sh`, Git commit SHA and diff.
- Produces: recoverable E5-B evidence without claiming E5 globally PASS.

- [ ] **Step 1: Run fresh verification**

Run:

```bash
./scripts/verify-e5b.sh
```

Do not reuse a prior run for closure evidence.

- [ ] **Step 2: Record exact source state**

Run:

```bash
git status --short
git rev-parse HEAD
git diff main...HEAD --name-status
```

Record exact branch, commit and changed production/source-only paths.

- [ ] **Step 3: Create `docs/evidence/E5B_LOCAL_VERIFICATION.md`**

The evidence must explicitly state:

```text
E5-B STATUS: PASS local L0-L2 only, if and only if all fresh checks pass.
PRODUCTION TAKEOVER: NOT ENABLED.
E5-A ROOTPROFILE PUBLISHER: BLOCKED/UNKNOWN until RootProfile canonical repository is accessible and its source-owned contract is implemented/tested.
E5-C WORDPRESS RUNTIME/BROWSER/A11Y: NOT RUN / UNKNOWN.
E5-D TAKEOVER: LOCKED.
```

Include the exact verification command output, branch/commit SHA, list of changed files, and the next action.

- [ ] **Step 4: Commit checkpoint evidence**

```bash
git add docs/evidence/E5B_LOCAL_VERIFICATION.md docs/superpowers/plans/2026-09-02-e5b-theme-current-surface-consumer.md
git commit -m "docs: record E5-B local verification checkpoint"
```

- [ ] **Step 5: Open a PR against `main`**

PR title:

```text
feat: prepare dormant RootProfile current-surface rendering
```

PR body must state:

```text
Scope: E5-B local L0-L2 only.
No production takeover hook is registered.
E5-A RootProfile publisher remains a source-owned dependency.
E5-C runtime/browser/a11y and E5-D takeover are not claimed.
```

Do not merge the implementation PR until its fresh verification evidence is present.

---

## Dependency Handoff: RootProfile E5-A

This Theme plan deliberately does not implement RootProfile-owned code. Before E5-C can start, the canonical RootProfile repository must implement and verify the approved source-owned publisher:

```text
rootprofile/presentation/current-surface/v1
contract: rootprofile.current_surface
version: 1
```

The RootProfile implementation must resolve the current request using its existing `Router`, `SurfaceResolver`, explicit Contact mapping and provider v1/v2 internals, but expose only the normalized public context defined by the approved spec. It must not expose internal Person/Page IDs or force AZnet Theme production takeover.

At the time this plan was written, the linked GitHub connector had no accessible RootProfile repository, so E5-A cannot truthfully be marked implemented or PASS from this Theme repository.

## Self-Review Results

- **Spec coverage:** Theme-side current-surface validation, nested provider validation, payload-to-model conversion, dormant dispatch, fail-soft behavior, asset scoping, no heuristic routing, no production takeover and recovery evidence are all mapped to Tasks 1-5.
- **Cross-product boundary:** RootProfile E5-A is explicitly separated and remains source-owned; no RootProfile implementation file is placed in this repository.
- **Placeholder scan:** no TBD/TODO/"implement later" step is used as an implementation instruction.
- **Type consistency:** `current_surface_context()` returns `surface` + `presentation`; `render_current_rootprofile_surface()` consumes that exact shape; model adapters consume the exact nested provider array.
- **Gate consistency:** E5-B can prove only local Theme compatibility. E5-A, E5-C and E5-D cannot be inferred from it.
