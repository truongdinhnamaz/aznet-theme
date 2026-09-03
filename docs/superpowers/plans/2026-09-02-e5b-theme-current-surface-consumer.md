# E5-B Theme Current-Surface Consumer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a dormant AZnet Theme consumer + renderer dispatcher for the approved RootProfile `rootprofile/presentation/current-surface/v1` contract without claiming or changing any production route.

**Architecture:** RootProfile remains responsible for resolving the current request and eventually publishing a normalized current-surface context. AZnet Theme validates that context, converts the embedded existing Provider v1/v2 payload into the already-proven E2/E3 presentation models, and dispatches to existing Theme renderers. This plan does not register a `template_include`, `template_redirect`, `the_content`, slug detector, Page detector, or any other production takeover hook.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, server-rendered PHP, WordPress filter API, existing AZnet Theme E4 presentation modules; no bundler required.

**Spec:** `docs/superpowers/specs/2026-09-02-e5-rootprofile-current-surface-contract-design.md`

## Global Constraints

- Theme namespace remains `AZnet\Theme`; integration namespace remains `AZnet\Theme\Integrations\RootProfile`.
- Current-surface hook is exactly `rootprofile/presentation/current-surface/v1`.
- Current-surface contract is exactly `rootprofile.current_surface`, version `1`.
- Allowed surfaces are exactly `person_profile`, `organization_profile`, `contact`.
- Nested `person_profile` and `organization_profile` payloads must be existing RootProfile Provider v2 payloads: contract `rootprofile.presentation`, version `2`, matching resource.
- Nested `contact` payload must be an existing RootProfile Provider v1 payload: contract `rootprofile.presentation`, version `1`, resource `contact`.
- No RootProfile post IDs, Page IDs, user IDs, option/meta/table names, private classes, routing classes, slugs, query vars or storage details may be needed by Theme.
- Theme must not call `TruongDinhNam\RootProfile\Surfaces\Router` or any RootProfile implementation class.
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
  - Make `profile_surface_model()` fetch Provider v2 and delegate to `profile_surface_model_from_payload()`.
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

- [x] **Step 1: Write the failing consumer contract test**
- [x] **Step 2: Run the consumer contract and verify RED**
- [x] **Step 3: Implement current-surface validation minimally**
- [x] **Step 4: Run consumer contract GREEN and retained syntax check**
- [x] **Step 5: Commit Task 1**

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

- [x] **Step 1: Write RED model-adapter tests**
- [x] **Step 2: Run model contract RED**
- [x] **Step 3: Extract Contact payload transformation**
- [x] **Step 4: Extract Profile payload transformation**
- [x] **Step 5: Run model contract and lints GREEN**
- [x] **Step 6: Commit Task 2**

---

### Task 3: Dormant current-surface dispatcher

**Files:**
- Create: `inc/theme/rootprofile-current-surface.php`
- Modify: `inc/theme/bootstrap.php:14-22`
- Create: `tests/offline/e5-current-surface-dispatcher-contract.php`

**Interfaces:**
- Consumes: validated `current_surface_context()` output.
- Produces: `render_current_rootprofile_surface(array $context): bool`.
- Must not produce any WordPress route hook registration.

- [x] **Step 1: Write RED dispatcher contract**
- [x] **Step 2: Run dispatcher contract RED**
- [x] **Step 3: Implement the dispatcher**
- [x] **Step 4: Load the dispatcher without registering it**
- [x] **Step 5: Run dispatcher test and lints GREEN**
- [x] **Step 6: Commit Task 3**

---

### Task 4: Ownership / no-takeover regression gate

**Files:**
- Create: `tests/offline/e5-no-takeover-static-contract.php`
- Create: `scripts/verify-e5b.sh`

**Interfaces:**
- Consumes: production tree after Tasks 1-3.
- Produces: repeatable local L0-L2 E5-B verification command.

- [x] **Step 1: Write static ownership contract**

The static gate scans the five E5-B production files for these forbidden ownership/takeover tokens:

```text
TruongDinhNam\RootProfile\
rootprofile_person
rootprofile_organization
get_query_var(
is_page(
get_queried_object_id(
template_include
template_redirect
the_content
```

RootProfile storage-key detection is intentionally **literal-aware** rather than a raw `_rootprofile_` substring ban. The raw substring would falsely reject the approved Theme function name `render_current_rootprofile_surface()`. The gate therefore rejects quoted storage-key literals matching `_rootprofile_*` while allowing the approved presentation function name.

The test allows WordPress `add_filter`/`apply_filters` only inside `inc/integrations/rootprofile.php` for public contract consumption. It separately asserts `inc/theme/rootprofile-current-surface.php` contains no `add_action(` and no `add_filter(`, and that `inc/theme/bootstrap.php` retains only the two existing setup/global-asset actions and never registers/invokes `render_current_rootprofile_surface()`.

- [x] **Step 2: Run static contract**

Observed initial plan-level false positive:

```text
FAIL: forbidden ownership/takeover token _rootprofile_ found in inc/theme/rootprofile-current-surface.php
```

Root-cause evidence showed the only `_rootprofile_` occurrence was the approved function name, with zero quoted `_rootprofile_*` storage-key literals. The corrected literal-aware gate then passed.

- [x] **Step 3: Create the bounded verification script**

`scripts/verify-e5b.sh` runs the four E5-B offline contracts and PHP lint for all production PHP files, excluding `tests/` and `vendor/`.

- [x] **Step 4: Run the full E5-B verification command**

Observed:

```text
PASS: E5 current-surface consumer contract
PASS: E5 current-surface payload-to-model adapters
PASS: E5 dormant current-surface dispatcher
PASS: E5-B ownership / no-takeover static contract
PASS: E5-B offline contracts
PASS: production PHP lint 22/22
```

- [x] **Step 5: Verify source/package boundary**

Production Theme files remain under the normal Theme root; `tests/` + `scripts/` are source-only verification assets. No distributable ZIP is created in E5-B; package/release remains E7 responsibility.

- [x] **Step 6: Commit Task 4**

Task 4 verification assets are committed on `work/e5b-current-surface-consumer`. The verifier is stored executable (`100755`).

---

### Task 5: E5-B checkpoint evidence and handoff to E5-A/E5-C

**Files:**
- Create: `docs/evidence/E5B_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes: Task 1-4 verification evidence.
- Produces: durable E5-B recovery checkpoint and exact next action.

- [ ] **Step 1: Record branch/baseline and verified scope**
- [ ] **Step 2: Record PASS by evidence layer without extrapolation**
- [ ] **Step 3: Record blocked dependency and exact next**
- [ ] **Step 4: Commit Task 5 evidence checkpoint**

Task 5 must state explicitly:

- E5-B Theme consumer/adapter/dispatcher/no-takeover verification is PASS local L0-L2 only;
- E5-A RootProfile `current-surface/v1` publisher remains source-owned and is not implemented in the Theme repo;
- E5-C real WordPress runtime/browser/a11y remains UNKNOWN until E5-A exists in a canonical RootProfile source and a database-backed runtime is available;
- E5-D production takeover remains LOCKED and requires separate approval/gate;
- no E5-B result may be used to infer E5-C/E5-D or E6/E7 PASS.

---

## Execution order / recovery rule

Execute strictly:

`Task 1 -> Task 2 -> Task 3 -> Task 4 -> Task 5`

At every checkpoint:

- do not redo a PASS task unless its evidence is invalidated;
- do not infer PASS between L0-L2, runtime/browser/a11y, integration or release;
- keep production takeover dormant;
- preserve RootProfile ownership of request resolution and authoritative data;
- stop and reclassify if implementation requires a new public schema, RootProfile storage change, canonical-route change or presentation-owner vocabulary change.
