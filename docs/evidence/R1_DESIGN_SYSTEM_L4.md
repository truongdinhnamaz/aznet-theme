# R1 Design System 2.0 — L1-L4 Closure Evidence

**Date:** 07/09/2026  
**Scope:** AZnet Theme R1 — Design System 2.0  
**Canonical R1 base:** `main@fdb227b20d569bc3bcbf81ea570f94c3a7723e87`  
**Verified functional/test head:** `db63c13fc376199a39722f1d9570e3210ece9342`

## 1. Ownership and architecture boundary

R1 changes Theme-owned presentation only:

- one normalized/versioned Theme Mod schema: `aznet_theme_settings`;
- semantic Design System tokens and compatibility aliases;
- curated `theme.json` editor mapping;
- three visual outcomes: Default, Editorial and Commerce;
- WordPress-native editor-style parity;
- presentation-only body class and selected preset stylesheet loading.

R1 does **not** add a page builder, proprietary content store, FSE/block-template takeover, Woo commerce state, RootProfile/ConvertFlow domain state or a new public provider contract.

ConvertFlow projection asset optimization is intentionally not changed in R1. Its future conditional loading remains R6/public-contract-gated; no private provider detection is introduced here.

## 2. Settings contract — RED -> GREEN

The R1 settings contract was created before `inc/theme/settings.php` existed and failed for the intended missing-production-path reason. The corrected harness was also checked against a deliberately broken implementation before the production implementation was accepted.

Final contract proves:

- schema version `1`;
- allowed `visual_preset`: `default|editorial|commerce`;
- unknown keys are dropped;
- invalid values fall back safely;
- reads go through the single Theme-owned normalized settings surface.

## 3. Semantic token contract — RED -> GREEN

R1 expands semantic tokens while retaining the v1.0 public aliases. The reduced-motion requirement was added as a failing contract first.

Recorded RED:

```text
FAIL: reduced-motion token contract missing @media (prefers-reduced-motion: reduce)
```

Minimal GREEN sets `--aznet-theme-motion-fast` and `--aznet-theme-motion-base` to `0ms` under `prefers-reduced-motion: reduce` for both frontend root and editor styles wrapper.

## 4. WordPress 6.9 visual-preset feasibility

**Selected mechanism:** `THEME_VISUAL_PRESET_MAPPING`

Fresh exact-functional-head feasibility workflow:

- Workflow: `R1 Visual Preset Feasibility`
- Run: `34131587061`
- Job: `101772742888`
- Head: `db63c13fc376199a39722f1d9570e3210ece9342`
- Result: `SUCCESS`
- Artifact: `r1-visual-preset-feasibility`
- Artifact ID: `10022310676`
- Artifact ZIP SHA-256: `12ff94844400061d5a2679c6048a6eaddcdf76f8db00462311fd406aa9326ab2`

Observed WordPress 6.9 probe:

```text
IS_BLOCK_THEME=0
THEME_OBJECT_IS_BLOCK=0
HAS_THEME_JSON=1
PROBE_VARIATION_DISCOVERED=1
VARIATION_COUNT=1
```

Interpretation: WordPress 6.9 discovers theme style-variation JSON while AZnet Theme remains a classic/hybrid PHP theme, but R1 does not adopt the block-theme Site Editor variation-selection model or change template ownership. The bounded Theme-owned semantic preset mapping is therefore retained.

## 5. L1-L2 static / contract verification

Fresh exact-functional-head workflow:

- Workflow: `R1 Design System Static Contracts`
- Run: `34131587109`
- Job: `101772742864`
- Head: `db63c13fc376199a39722f1d9570e3210ece9342`
- Result: `SUCCESS`
- PHP: `8.1.34`

R1 contracts PASS:

- Theme settings contract;
- semantic token contract, including reduced motion;
- visual-preset mechanism evidence contract;
- visual-preset presentation contract.

Retained G3/core regression also PASS on the same R1 bytes:

- production PHP lint `31/31`;
- E5 retained current-surface/no-takeover contracts;
- Woo W1-W6 ownership/asset contracts;
- W8 ConvertFlow public theme-token bridge;
- Homepage F1/F2/F3/F5 contracts;
- G3 static/security/package hygiene.

## 6. L3 runtime and retained browser regressions

Fresh workflows on the same functional/test head `db63c13fc376199a39722f1d9570e3210ece9342` completed successfully:

| Workflow | Run | Result |
| --- | ---: | --- |
| G Core WordPress Clean Runtime | `34131587044` | SUCCESS |
| G Core Lifecycle Update Switch Rollback | `34131587283` | SUCCESS |
| G Core Browser A11y Performance | `34131587160` | SUCCESS |
| F Homepage Native Runtime Browser | `34131587068` | SUCCESS |
| F Homepage Native Regression Package | `34131587059` | SUCCESS |
| G Core Release Candidate Package | `34131587046` | SUCCESS |
| G Core Static Release Gate | `34131587149` | SUCCESS |

These retained workflows prove that R1 did not invalidate the existing clean-WordPress/core package/runtime/browser/lifecycle paths they cover. They do not promote optional provider certification beyond its existing state.

## 7. R1 L4 editor/frontend parity matrix

Fresh exact-functional-head browser workflow:

- Workflow: `R1 Design System Browser Parity`
- Run: `34131587269`
- Job: `101772743975`
- Head: `db63c13fc376199a39722f1d9570e3210ece9342`
- Result: `SUCCESS`
- WordPress: `6.9`
- PHP: `8.1.34`
- MySQL: `8.0`
- Node: `22`
- Playwright: `1.55.0`
- axe-core Playwright: `4.10.2`

Matrix:

| Preset | 1440x1000 | 1024x900 | 390x844 | Native block editor |
| --- | --- | --- | --- | --- |
| Default | PASS | PASS | PASS | PASS — editor iframe |
| Editorial | PASS | PASS | PASS | PASS — editor iframe |
| Commerce | PASS | PASS | PASS | PASS — editor iframe |

Checks on the Theme-controlled fixture include:

- semantic computed-variable expectations per preset;
- frontend loads only the selected non-default preset stylesheet;
- Default loads no preset-specific stylesheet;
- editor/frontend computed semantic-variable parity;
- horizontal overflow `<= 1px`;
- core-block button reachable by keyboard;
- visible `:focus-visible` treatment;
- mobile reduced-motion media query active and both Theme motion tokens resolve to `0ms`;
- axe critical/serious violations = `0`;
- no unexpected console/page errors or failed non-document subresources;
- no Theme-caused PHP Fatal/Warning/Parse/Uncaught markers in the runtime log.

Browser evidence artifact:

- Artifact: `r1-design-system-browser-parity`
- Artifact ID: `10022337526`
- Size: `684241` bytes
- Artifact ZIP SHA-256: `c03555f25495ec678dab3d7198472e9ff6fc037dd8f436cd0c94740cdbc6dace`

## 8. Result

**R1 L1-L4 technical evidence: PASS on functional/test head `db63c13fc376199a39722f1d9570e3210ece9342`.**

This evidence document is written after the verified functional/test head. Before PR promotion/merge review, GitHub compare must prove that any later R1 head delta is documentation-only and that production/test bytes remain identical to the verified head. R1 merge to canonical `main` remains a separate explicit owner gate.
