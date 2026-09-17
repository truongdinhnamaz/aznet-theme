# Provisioning Provenance-Compatible Rerun Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Law 01 Quick Setup reruns and `law01-v1-1` → `law01-v1-2` upgrades reuse compatible Theme-provisioned starter Posts/media and make Step 3 preview that reuse before confirmation.

**Architecture:** Add one bounded provenance compatibility helper that delegates to the existing exact provenance primitive. Route starter Post/media runner reuse and Step 3 preview through that same helper so preview and apply cannot drift. Keep Page/Category/Menu semantics, immutable plan structure, Content Map ownership, featured-image safeguards, rollback behavior, and provider boundaries unchanged.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, AZnet Theme hybrid PHP + `theme.json`, GitHub Actions retained offline/runtime/browser regression workflows.

**Spec:** `docs/superpowers/specs/2026-09-18-provisioning-provenance-compatible-rerun-design.md`

## Global Constraints

- Product scope is `truongdinhnamaz/aznet-theme` only.
- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.`
- Do not infer semantic ownership from slug, title, URL, filename, Page ID, or other heuristics.
- Do not read or write private provider/plugin storage.
- Successfully provisioned Posts/attachments remain ordinary WordPress-owned content.
- Existing user edits, featured images, Content Map references, and WordPress-native publication state remain authoritative.
- Production changes must follow RED → verified expected failure → minimal GREEN → regression.
- Do not merge until exact-head offline, runtime, browser, and retained regression gates are green.

---

### Task 1: Lock the compatibility contract in RED

**Files:**
- Modify: `tests/offline/provisioning-idempotency-contract.php`
- Modify: `tests/offline/provisioning-admin-a11y-contract.php`
- Test: `tests/offline/provisioning-idempotency-contract.php`
- Test: `tests/offline/provisioning-admin-a11y-contract.php`

**Interfaces:**
- Consumes: existing `AZnet\Theme\provisioning_find_owned_role( string $blueprint, string $object_type, string $role ): int`
- Produces expectation for: `AZnet\Theme\provisioning_find_compatible_owned_role( string $blueprint, string $object_type, string $role ): int`
- Produces expectation for admin helper: `AZnet\Theme\Admin\provisioning_preview_operation( array $operation, array $plan ): array`

- [ ] **Step 1: Add a failing compatibility-resolver contract**

Extend the idempotency contract so a Law 01 current blueprint lookup checks current key first and then accepted historical keys, while a non-Law blueprint checks exact key only. The contract must explicitly name the accepted lineage: `law01-v1-2`, `law01-v1-1`, `law01-v1`.

- [ ] **Step 2: Add a failing preview contract**

Keep the existing admin source-level marker checks and require the preview helper plus reuse markers for both starter Post and starter media. Do not assert heuristics or new mutation behavior.

- [ ] **Step 3: Run the focused offline contracts and verify RED**

Run:

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-idempotency-contract.php
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-admin-a11y-contract.php
```

Expected: FAIL because `provisioning_find_compatible_owned_role()` and/or `provisioning_preview_operation()` do not exist yet. Any syntax error or unrelated failure is not an accepted RED and must be fixed before production code.

- [ ] **Step 4: Commit the verified RED test-only state**

```bash
git add tests/offline/provisioning-idempotency-contract.php tests/offline/provisioning-admin-a11y-contract.php
git commit -m "test: define provenance-compatible rerun behavior"
```

---

### Task 2: Add the shared provenance compatibility resolver

**Files:**
- Modify: `inc/theme/provisioning-provenance.php`
- Test: `tests/offline/provisioning-idempotency-contract.php`

**Interfaces:**
- Consumes: `provisioning_find_owned_role( string $blueprint, string $object_type, string $role ): int`
- Produces: `provisioning_find_compatible_owned_role( string $blueprint, string $object_type, string $role ): int`

- [ ] **Step 1: Implement the minimal resolver**

Add a helper with these exact semantics:

```php
function provisioning_find_compatible_owned_role( string $blueprint, string $object_type, string $role ): int {
    if ( '' === $role || ! in_array( $blueprint, provisioning_blueprint_keys(), true ) ) { return 0; }
    $candidates = in_array( $blueprint, [ 'law01-v1', 'law01-v1-1', 'law01-v1-2' ], true )
        ? array_values( array_unique( [ $blueprint, 'law01-v1-2', 'law01-v1-1', 'law01-v1' ] ) )
        : [ $blueprint ];
    foreach ( $candidates as $candidate ) {
        $id = provisioning_find_owned_role( $candidate, $object_type, $role );
        if ( $id > 0 ) { return $id; }
    }
    return 0;
}
```

Do not duplicate meta-query logic and do not add title/slug/URL fallback.

- [ ] **Step 2: Run the compatibility offline contract**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-idempotency-contract.php
```

Expected: PASS for lineage/exact-key behavior.

- [ ] **Step 3: Run PHP lint for the changed production file**

```bash
php -l inc/theme/provisioning-provenance.php
```

Expected: no syntax errors.

- [ ] **Step 4: Commit the resolver**

```bash
git add inc/theme/provisioning-provenance.php tests/offline/provisioning-idempotency-contract.php
git commit -m "fix: add compatible Law provisioning provenance lookup"
```

---

### Task 3: Route starter Post/media apply paths through the shared resolver

**Files:**
- Modify: `inc/theme/provisioning-runner.php`
- Modify: `inc/theme/provisioning-media.php`
- Modify: `tests/runtime/law-site-provisioning-v1-1.php`

**Interfaces:**
- Consumes: `provisioning_find_compatible_owned_role(...)`
- Preserves: existing receipt shape `['created' => ..., 'reused' => ...]`
- Preserves: existing `provisioning_import_media( string $media_role, string $blueprint, string $run_id ): int`

- [ ] **Step 1: Extend runtime RED coverage for upgrade/rerun**

Add a scenario that provisions starter Post/media under `law01-v1-1`, then applies a `law01-v1-2` plan for the same starter roles. Assert that:

```php
$post_count_after === $post_count_before;
$attachment_count_after === $attachment_count_before;
```

and that the second receipt records the starter Post/media as reused rather than created.

- [ ] **Step 2: Run the focused runtime test and verify RED**

Run the repository's existing Law provisioning runtime command/workflow entrypoint for `tests/runtime/law-site-provisioning-v1-1.php`.

Expected: FAIL because exact-current-blueprint lookups miss historical provenance.

- [ ] **Step 3: Replace exact starter Post lookup in the runner**

In the `create_post` branch, change only the ownership lookup from exact-key to `provisioning_find_compatible_owned_role(...)`. Keep existing `WP_Post` type validation, user-edit preservation, post creation, provenance marking, and receipt bookkeeping unchanged.

- [ ] **Step 4: Replace exact starter media lookup in the runner**

In the `import_media` branch, resolve the provenance role exactly as today, but use `provisioning_find_compatible_owned_role(...)` for the pre-import lookup used to classify created versus reused.

- [ ] **Step 5: Replace exact starter media lookup inside `provisioning_import_media()`**

Use the same compatible resolver before sideloading. Continue validating that a returned object is an attachment before reuse; otherwise fall back to the current safe import path.

- [ ] **Step 6: Re-run the focused runtime test**

Expected: PASS; rerun/upgrade does not increase starter Post/media counts and receipt records reuse.

- [ ] **Step 7: Run PHP lint for both changed production files**

```bash
php -l inc/theme/provisioning-runner.php
php -l inc/theme/provisioning-media.php
```

Expected: no syntax errors.

- [ ] **Step 8: Commit the apply-path fix**

```bash
git add inc/theme/provisioning-runner.php inc/theme/provisioning-media.php tests/runtime/law-site-provisioning-v1-1.php
git commit -m "fix: reuse compatible Law starter content across upgrades"
```

---

### Task 4: Make Step 3 preview match apply behavior

**Files:**
- Modify: `inc/admin/provisioning.php`
- Modify: `tests/offline/provisioning-admin-a11y-contract.php`
- Modify: `tests/browser/law-site-provisioning-v1-2-l4.mjs`

**Interfaces:**
- Consumes: `AZnet\Theme\provisioning_find_compatible_owned_role(...)`
- Produces: `AZnet\Theme\Admin\provisioning_preview_operation( array $operation, array $plan ): array`
- Return shape: the same operation array with display-only `type`/`effect` adjusted when compatible provenance proves reuse; no mutation.

- [ ] **Step 1: Define the preview helper minimally**

For `create_post` operations whose role starts with `starter_post:`:

```php
$id = provisioning_find_compatible_owned_role( $blueprint, 'post', $role );
```

If `$id > 0`, return a display operation with:

```php
[
    'type' => 'reuse_post',
    'effect' => 'REUSE starter Post #' . $id . ' → ' . $editorial_role,
]
```

merged into the original operation for display only.

For `import_media` operations, derive the same provenance role used by the runner and, if an owned attachment exists, return:

```php
[
    'type' => 'reuse_attachment',
    'effect' => 'REUSE starter media #' . $id . ' → ' . $media_role,
]
```

Otherwise return the original operation unchanged.

- [ ] **Step 2: Extend `provisioning_summary_group()`**

Treat `reuse_post` and `reuse_attachment` as `existing`. Do not change grouping for real plan operation types.

- [ ] **Step 3: Apply preview transformation before grouping**

Inside `provisioning_render_plan_summary()`, transform each operation with `provisioning_preview_operation()` before calling `provisioning_summary_group()` and rendering the effect string.

- [ ] **Step 4: Re-run the admin offline contract**

```bash
php -d zend.assertions=1 -d assert.exception=1 tests/offline/provisioning-admin-a11y-contract.php
```

Expected: PASS.

- [ ] **Step 5: Add browser regression for previously provisioned Step 3**

Extend the Law v1.2 browser scenario to reach Step 3 after a prior provisioned fixture and assert that the preview contains both:

```text
REUSE starter Post #
REUSE starter media #
```

under the visible `Sử dụng nội dung hiện có` section, and does not list those already-owned roles under `Sẽ tạo mới` / `Sẽ import media`.

- [ ] **Step 6: Run the Law provisioning browser workflow**

Expected: PASS on WordPress 6.9 with no console/page errors and unchanged accessibility gate.

- [ ] **Step 7: Commit the preview fix**

```bash
git add inc/admin/provisioning.php tests/offline/provisioning-admin-a11y-contract.php tests/browser/law-site-provisioning-v1-2-l4.mjs
git commit -m "fix: show provenance reuse in provisioning preview"
```

---

### Task 5: Preserve user edits and featured-image safety across compatible reuse

**Files:**
- Modify: `tests/runtime/law-site-provisioning-v1-1.php`
- Modify only if a regression exposes a defect: `inc/theme/provisioning-runner.php`

**Interfaces:**
- Consumes existing user-edit/featured-image safeguards.
- Produces regression evidence only unless production behavior is actually broken.

- [ ] **Step 1: Extend the runtime scenario**

Before the upgrade/rerun, modify one Theme-provisioned starter Post body and assign a non-starter featured image to the Front Page or relevant starter Post using the existing fixture utilities.

- [ ] **Step 2: Apply the v1.2 rerun**

Assert after apply that the edited body is byte-for-byte unchanged and the pre-existing featured image ID is unchanged.

- [ ] **Step 3: Run the focused runtime matrix**

Expected: PASS without production changes. If it fails, reproduce at the shallowest layer and add a minimal regression-proven fix; do not widen scope.

- [ ] **Step 4: Commit the regression coverage**

```bash
git add tests/runtime/law-site-provisioning-v1-1.php
git commit -m "test: protect edits during provenance-compatible reruns"
```

---

### Task 6: Run retained verification and close the PR gate

**Files:**
- No production file changes expected.
- Update PR description/evidence only if needed.

**Interfaces:**
- Consumes exact candidate SHA produced by Tasks 1-5.
- Produces L1/L2/L3/L4 and retained regression evidence.

- [ ] **Step 1: Run the reusable core verification**

```bash
bash scripts/verify-v1-core.sh
```

Expected: PASS.

- [ ] **Step 2: Run all provisioning offline contracts**

```bash
for t in tests/offline/provisioning-*.php; do php -d zend.assertions=1 -d assert.exception=1 "$t"; done
```

Expected: PASS.

- [ ] **Step 3: Push exact candidate and wait for GitHub Actions**

Required gates include at minimum:

- V1 Core Pull Request CI
- Law Site Provisioning Browser Quality
- Law Site Provisioning Candidate Package
- D-027 Retained Full Regression
- F Homepage Native Regression Package
- X3 Media Gallery Embed
- X4 Pagination Navigation

Any additional repository-required checks triggered by the exact head must also pass.

- [ ] **Step 4: Verify the exact head did not move**

Confirm PR #110 head SHA matches the SHA that received green CI and the base remains the intended `main` head lineage.

- [ ] **Step 5: Mark PR ready only after exact-head green evidence**

Do not merge while any relevant workflow is queued, in progress, cancelled, or failed.

- [ ] **Step 6: Merge only under the existing user approval gate**

Use normal merge (not force/override), then verify the resulting `main` merge commit. Do not deploy.

---

## Self-Review Result

- Spec coverage: shared resolver, runner/media reuse, Step 3 preview, exact rerun, v1.1→v1.2 upgrade, no heuristics, user-edit preservation, featured-image preservation, offline/runtime/browser/retained gates are all mapped to tasks.
- Placeholder scan: no TODO/TBD or unspecified implementation step remains.
- Type consistency: the shared helper signature and preview helper signature are consistent across all tasks.
