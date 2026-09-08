# R6 ConvertFlow Projection Asset Gate — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 3  
**Owner boundary:** Theme owns its compatibility projection CSS; ConvertFlow owns any public/versioned capability that would authorize conditional provider-aware loading.

## 1. Result

**BLOCKED_EXTERNAL_CONTRACT for provider-aware optimization.**

The measured R6 baseline proves that the Theme-owned bridge `assets/css/integrations/convertflow.css` is loaded on every tested clean and Woo route.

Observed per-page bridge cost:

```text
transfer: 2,820 bytes
decoded: 2,520 bytes
```

This is a real globally loaded Theme asset and therefore a legitimate optimization candidate. However, AZnet Theme does not currently have a documented public/versioned Theme-consumable ConvertFlow presence/surface capability that would let it determine safely when the bridge may be omitted.

## 2. Authoritative constraint

AZT-04 O-008 states that ConvertFlow projection asset gating is OPEN/BLOCKED unless such a public/versioned Theme-consumable capability exists and explicitly forbids private detection.

Retained R5 System Health evidence likewise reports ConvertFlow capability as `unknown` because no Theme-consumable public capability is established.

Therefore the absence of a public capability is not permission to infer provider state through plugin constants, namespaces, active-plugin lists, options, post meta, database reads, slugs, Page IDs or URL heuristics.

## 3. Safe Theme behavior retained

R6 retains the current safe bridge behavior:

- Theme enqueues `aznet-theme-convertflow-contract` itself;
- Theme does not enqueue or own ConvertFlow plugin assets;
- Theme does not read ConvertFlow private state;
- provider absence remains fail-soft;
- existing W8 exact-byte coexistence evidence remains valid unless invalidated by a later contract change.

No production PHP/CSS/JS change is justified by this Task 3 gate.

## 4. Regression guard

`tests/offline/r6-convertflow-asset-gate-contract.php` protects the current boundary by requiring:

- the Theme-owned bridge fallback to remain present while O-008 is blocked;
- no `ChoiceGuide\\`, `CHOICEGUIDE_`, private option/meta/database or active-plugin detection in Theme asset loading;
- authoritative source to continue recording O-008 as blocked until explicitly changed by the correct owner/source process.

## 5. Unblock condition

Task 3 may become implementable only after a documented public/versioned ConvertFlow contract provides a stable Theme-consumable capability sufficient to answer the loading question without private inference.

At that point R6 must add a new RED -> GREEN contract against that public capability and remeasure the exact same route matrix before claiming any reduction.

## 6. Release impact

This external contract gap does **not** block core v1.1 technical release readiness under the accepted core/provider separation. It blocks only the specific provider-aware optimization claim.

R6 may continue Theme-owned performance/release infrastructure work while accurately carrying this item as `BLOCKED_EXTERNAL_CONTRACT`.

## NEXT

Continue R6 Theme-owned work: preserve the measured asset-scope matrix, build reusable PR/exact-main CI and deterministic release-candidate packaging. Do not promote metadata to `1.1.0`, create a tag/GitHub Release or deploy without the explicit owner gate.