# E5-B Task 4 — Ownership / no-takeover regression gate

Date: 2026-09-03

## Scope

Task 4 only: add repeatable ownership/no-takeover verification for the dormant E5-B Theme consumer/adapter/dispatcher work. No production Theme behavior is changed in this task.

## Baseline

- Active branch: `work/e5b-current-surface-consumer`
- Task 3 evidence head before Task 4: `e63d10db3df3d78a72bc1532d5fa3ba6b7ffa338`
- E4 frozen artifact used for isolated reconstruction: `aznet-theme-0.1.0-alpha.7-e4-candidate.zip`
- E4 SHA-256: `5ee905ba2788fa99334c36da512e193c9e63d8d9b7225aa658164cdc2840e483`
- Task 1-3 production blobs were verified byte-identical to the active GitHub branch before Task 4 verification.

## Plan-level false positive and root cause

The initial static rule copied the plan's raw forbidden substring `_rootprofile_`. Running the new gate produced:

```text
FAIL: forbidden ownership/takeover token _rootprofile_ found in inc/theme/rootprofile-current-surface.php
```

Root-cause inspection showed exactly one `_rootprofile_` occurrence across the five E5-B production files:

```text
inc/theme/rootprofile-current-surface.php:function render_current_rootprofile_surface(...)
```

There were zero quoted `_rootprofile_*` storage-key literals. Therefore the raw substring rule conflicted with the already approved Theme API name and could not distinguish presentation code from RootProfile storage access.

The gate was corrected at the test layer only: it now rejects quoted RootProfile storage-key literals matching `_rootprofile_*`, while continuing to reject direct RootProfile private namespace/storage/routing/takeover tokens. No production code was changed to satisfy the gate.

## Verification assets

Added:

- `tests/offline/e5-no-takeover-static-contract.php`
- `scripts/verify-e5b.sh`

The verifier is stored executable (`100755`).

## Fresh local verification

Command:

```bash
./scripts/verify-e5b.sh
```

Observed:

```text
PASS: E5 current-surface consumer contract
PASS: E5 current-surface payload-to-model adapters
PASS: E5 dormant current-surface dispatcher
PASS: E5-B ownership / no-takeover static contract
PASS: E5-B offline contracts
PASS: production PHP lint 22/22
```

Source/package boundary inspection also confirmed `tests/` and `scripts/` are source-only verification assets; no E5-B distributable ZIP was produced.

## Ownership / no-takeover assertions

The Task 4 gate verifies the E5-B production files do not contain or require:

- RootProfile private namespace calls;
- RootProfile internal CPT/storage names `rootprofile_person` / `rootprofile_organization`;
- quoted `_rootprofile_*` storage key literals;
- `get_query_var()`, `is_page()`, or `get_queried_object_id()` route heuristics;
- `template_include`, `template_redirect`, or `the_content` takeover hooks;
- public provider filter API use outside the dedicated RootProfile integration consumer;
- `add_action()` / `add_filter()` in the dormant dispatcher;
- bootstrap registration/invocation of `render_current_rootprofile_surface()`.

## Repository bookkeeping

GitHub Contents API creates shell scripts as mode `100644`. The branch tree was therefore corrected with Git data API to keep `scripts/verify-e5b.sh` executable (`100755`). A temporary shortened plan-file update was also reverted at the tree level to the original detailed plan blob `5c5b0a3f5a287402a2b956f346f5206644ea603e`. These bookkeeping corrections do not change production Theme code or Task 4 verification semantics.

## Layer status

- E5-B Task 4 ownership/no-takeover gate: **PASS local L0-L2**.
- Full E5-B local closure evidence: not yet recorded; Task 5 is next.
- E5-A RootProfile current-surface publisher: **BLOCKED/UNKNOWN** until canonical RootProfile source is accessible for source-owned implementation.
- E5-C real WordPress runtime/browser/a11y: **UNKNOWN**.
- E5-D production takeover: **LOCKED** and not implemented.
- E6/E7 remain locked.

## Next

Task 5 — create `docs/evidence/E5B_LOCAL_VERIFICATION.md` consolidating Tasks 1-4 and hand off to the source-owned E5-A / runtime E5-C path without extrapolating PASS.
