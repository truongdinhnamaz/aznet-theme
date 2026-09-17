# Provisioning Provenance-Compatible Rerun Design

## Context

Quick Setup Step 3 currently renders selected starter Posts and starter media as `CREATE` / `IMPORT` operations even on a previously provisioned Law 01 site. The apply runner is intended to be idempotent by bounded provisioning provenance, but the current lookup paths are inconsistent across Law 01 blueprint generations: recommendation logic already checks compatible historical Law 01 provenance, while runner/media reuse paths primarily resolve against the exact current blueprint key.

This creates two related problems:

1. **Preview accuracy:** Step 3 can describe a rerun as creating/importing starter objects even when compatible Theme-owned starter objects already exist and will be reused.
2. **Upgrade/rerun idempotency risk:** a site provisioned under an earlier Law 01 blueprint can be rerun under a later Law 01 blueprint without a single shared compatibility resolver, allowing duplicate starter Posts or attachments if the exact-key lookup misses historical provenance.

## Goal

Make Law 01 reruns and blueprint upgrades provenance-compatible and visibly idempotent: previously provisioned starter Posts/media must be reused across the accepted Law 01 blueprint lineage, and Step 3 must describe that reuse before confirmation.

## Scope

### In scope

- Law 01 blueprint lineage only: `law01-v1`, `law01-v1-1`, `law01-v1-2`.
- Starter Post provenance lookup.
- Starter media/attachment provenance lookup.
- Step 3 preview grouping/effect text for starter Post/media reuse.
- Regression coverage for exact rerun and earlier-blueprint → later-blueprint upgrade paths.

### Out of scope

- No slug/title/URL/fuzzy matching.
- No changes to authoritative WordPress content ownership.
- No rewriting of existing Post/Page content, featured images, categories, or user edits.
- No provider/private storage reads.
- No changes to RootProfile, ConvertFlow, WooCommerce, Rank Math, Yoast, or other product/domain logic.
- No destructive cleanup of previously created ordinary WordPress content.

## Ownership and architecture boundary

Theme remains the owner of the bounded provisioning workflow and preview presentation only. Successfully created Posts and attachments remain ordinary WordPress-owned objects. Provenance is used only to recognize objects previously created by this Theme provisioning family; it does not make Theme the ongoing content owner.

The implementation must continue to satisfy:

- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.`
- explicit confirmation before mutation;
- fail-safe behavior on missing/invalid objects;
- no heuristic semantic inference;
- rollback limited to the current failed run;
- user edits preserved after successful provisioning.

## Design

### 1. Shared Law 01 provenance compatibility resolver

Add one Theme helper in the provisioning provenance/recommendation boundary that defines the accepted Law 01 compatibility order for reuse.

Conceptual interface:

```php
function provisioning_find_compatible_owned_role(
    string $blueprint,
    string $object_type,
    string $role
): int;
```

Behavior:

- For non-Law blueprints, resolve only the exact blueprint key.
- For a Law 01 blueprint, search the current blueprint first, then compatible Law 01 historical keys.
- The accepted lineage is bounded to `law01-v1-2`, `law01-v1-1`, `law01-v1`.
- Return the first valid owned object ID or `0`.
- Never match by title, slug, URL, filename, or other heuristic.

The helper must call the existing exact provenance primitive rather than duplicating meta-query logic.

### 2. Runner and media reuse use the same resolver

Replace exact-key starter reuse checks with the shared compatibility resolver for:

- `create_post` starter Post operations;
- `import_media` starter attachment operations;
- `provisioning_import_media()` pre-import reuse check.

Page/Category/Menu behavior is intentionally unchanged unless already routed through existing recommendation/current mapping logic; this slice is specifically about starter Post/media duplication risk.

If a compatible historical object is found, the runner records it in `receipt['reused']` and does not create/import a duplicate.

### 3. Step 3 preview reflects actual idempotent behavior

Before grouping/rendering each operation, the admin preview resolves whether a selected starter Post/media operation is already satisfied by compatible provenance.

Expected presentation:

- Existing starter Post: show under **Sử dụng nội dung hiện có** with text such as `REUSE starter Post #123 → knowledge_business`.
- Existing starter media: show under **Sử dụng nội dung hiện có** with text such as `REUSE starter media #456 → hero`.
- No compatible owned object: keep current `CREATE starter Post ...` / `IMPORT starter media ...` wording and grouping.
- Configuration operations remain unchanged.

Preview must not mutate data and must use the same compatibility resolver as the runner, so displayed intent and apply behavior cannot drift.

### 4. Featured image safety remains unchanged

Existing featured images remain authoritative. Reusing a starter attachment does not imply replacing a user-set featured image. Existing `assign_featured_media` safeguards remain in force.

## Data flow

1. Step 1 discovery detects prior provisioning state.
2. Step 2 recommendations/selections remain proposal-only.
3. Step 3 builds an immutable plan as today.
4. Preview transforms only the display interpretation of starter Post/media operations using compatible provenance lookup.
5. On confirmation, runner executes the immutable plan.
6. For starter Post/media operations, runner performs the same compatible provenance lookup and reuses an existing owned object when present.
7. Only when no compatible object exists does WordPress public API creation/import run.

## Error handling and fail-safe behavior

- Invalid blueprint, object type, or empty role returns no compatible object.
- If a provenance lookup points to an object that no longer exists or is of the wrong WordPress object type, treat it as missing and continue with the plan's normal safe create/import path.
- Preview lookup failures must not mutate state; if lookup cannot establish safe reuse, display the original create/import effect.
- Existing stale-plan fingerprint validation remains unchanged.

## Testing strategy

Production changes require RED → GREEN.

### L1/L2 offline contracts

Add/extend contracts to prove:

- shared compatibility resolver recognizes historical Law 01 provenance;
- non-Law blueprints remain exact-key only;
- rerun preview contains `REUSE starter Post #` and `REUSE starter media #` when compatible objects exist;
- preview still shows CREATE/IMPORT when no compatible provenance exists;
- no heuristic matching is introduced.

### L3 runtime

Extend retained Law provisioning runtime coverage to prove:

- provision v1.1 starter Post/media;
- rerun/upgrade through v1.2;
- object counts do not increase for already-owned starter roles;
- receipt records reuse;
- existing user-edited content and featured image remain unchanged.

### L4 browser/admin

Exercise Step 3 on a previously provisioned fixture and verify the preview groups compatible starter objects under `Sử dụng nội dung hiện có` rather than `Sẽ tạo mới` / `Sẽ import media`.

## Acceptance criteria

The slice is PASS only when all are true:

1. Exact rerun does not duplicate starter Posts or media.
2. `law01-v1-1` → `law01-v1-2` rerun does not duplicate compatible starter Posts or media.
3. Step 3 preview describes reuse before confirmation when compatible provenance exists.
4. No title/slug/URL heuristic or private provider storage is introduced.
5. Existing user edits and featured images are preserved.
6. Relevant offline, runtime, browser, and retained regression workflows pass on the exact candidate SHA.

## Rollback

The code change is isolated to Theme provisioning lookup/preview behavior and can be reverted as one bounded PR. Runtime failed-run rollback semantics remain unchanged; no migration or destructive data rewrite is required.
