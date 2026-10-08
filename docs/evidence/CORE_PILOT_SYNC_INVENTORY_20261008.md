# Core / Pilot Synchronization Leakage Inventory — 08/10/2026

**Scope:** S0 exact inventory before migration  
**Canonical source base:** `main@3226908cae6488e4e957e578cafbedeaba45f5a2`  
**Execution branch:** `work/core-pilot-sync-cleanup-20261008`

## Source priority

Current implementation/state is resolved from canonical GitHub first. Accepted AZT source documents in `docs/source/` remain authoritative for ownership/architecture. Historical evidence is not rewritten to manufacture current-state parity.

## Fresh production-code search

### Generic asset coupling

GitHub code search against canonical source found:

- `"page-id-" path:assets` -> exactly **1 production file**: `assets/css/components/page.css`.
- `"rqa-" path:assets` -> exactly **1 production file**: `assets/css/components/page.css`.
- `remquocanh.vn` in `assets/` or `inc/` -> **0 production matches**.
- `lstamduchn.vn` in `assets/` or `inc/` -> **0 production matches**.
- `minhnguyen.vn` in `assets/` or `inc/` -> **0 production matches**.

Within `page.css`, the exact pre-migration scan returned **142 lines** containing either a `.page-id-*` selector or `rqa-` identifier. The block begins around the RQA quote-page rules at Page ID 726 and continues through the RQA contact-page rules at Page ID 45.

**Classification:** migration debt. These are Theme-owned presentation rules, but their generic-Core location and Page-ID qualification violate the D-043 target extension boundary. They must move to Curtain 01 scoped presentation without changing WordPress content ownership.

### Template-ID references in `inc/theme`

Fresh search also shows existing `law-01`, `curtain-01` and `industrial-01` references in generic Theme modules such as Homepage composition, authoring, surface map, assets, settings and provisioning compatibility paths.

**Classification:** not automatically a defect. D-043 explicitly permits bounded compatibility code during migration while the target extension path remains manifest-driven. This slice will not broaden into unrelated rewrites. The retained D-043 three-pilot and synthetic fourth-template contracts are the gate: if extraction exposes a manifest-bypass or new generic industry branch, fix the shallowest violating path; otherwise preserve existing accepted compatibility behavior.

## Fresh pilot version checkpoint

Authenticated read-only inventory immediately before this design recorded:

- Law 01 — `lstamduchn.vn`: AZnet Theme `1.3.86`.
- Curtain 01 / Rèm 01 — `remquocanh.vn`: AZnet Theme `1.3.86`.
- Industrial 01 — `minhnguyen.vn`: AZnet Theme `1.3.79`.

Equal version numbers are not byte-identity proof. Exact production identity remains a later S5 reconciliation question.

## RED target

The new `tests/offline/core-preset-isolation-contract.php` must fail on this base because generic `page.css` still contains both Page-ID coupling and RQA-specific presentation identifiers.

No production code is changed in S0/S1.
