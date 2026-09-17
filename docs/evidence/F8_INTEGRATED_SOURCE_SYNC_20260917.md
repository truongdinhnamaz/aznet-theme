# F8 Integrated Source Sync — 17/09/2026

## Scope

Docs/state-only closure for AZnet Theme after the external ConvertFlow Homepage landmark compatibility fix became canonical. No production Theme code changes.

## Canonical provider evidence

- ConvertFlow PR #31 merged to `truongdinhnamaz/convert-flow` `main@64cf2e66d41e6619e2bbcf53c12d8831e2852442` from exact verified head `7cad02091ce12ef0c146fd6e0837a653169ca753`.
- Compare from verified head to merge commit reports no changed files; canonical merge tree preserves the exact verified candidate bytes.
- F8 workflow `35231522317`: SUCCESS.
- Retained C1 workflow `35231522361`: SUCCESS.
- Artifact `10501317335`, digest `sha256:a353d11b456e31974be4b5362005a1e02ddad2a5770e2f2c62efd4bea87fbdda`.
- Exact environment: WordPress 6.9, PHP 8.1, AZnet Theme `252554961bc81d22427363aee0048365fb4e116b`, ConvertFlow Core P5.242 + Pro P5.164.
- L3: exactly one document-level `<main>`, owned by AZnet Theme; ConvertFlow Homepage Journey remains inside the Theme shell with a neutral root.
- L4: desktop/tablet/mobile/narrow, keyboard/focus, console and Axe matrix 4/4 PASS.

## Source change

- AZT-04 v0.57 records F8 integrated compatibility PASS and retains v1.3 core release/deployment state unchanged.
- AZT-EXEC-MAP v0.49 records the same exact compatibility evidence as derived execution state.
- Historical P5.239 nested-main evidence remains provenance; it is not rewritten as if it had passed.

## Boundary

This checkpoint closes only the historical F8 nested-main compatibility blocker at the exact verified L3-L4 scope. It does not infer F9/provider L5 certification, ConvertFlow release/deployment, or any change in product/domain ownership.

## Next

Any F9/provider L5 certification remains a separately approved slice.
