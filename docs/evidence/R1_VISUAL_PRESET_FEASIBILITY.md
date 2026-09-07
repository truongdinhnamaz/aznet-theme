# R1 Visual Preset Feasibility

**Status:** PASS  
Mechanism: `THEME_VISUAL_PRESET_MAPPING`

## Environment

- WordPress floor under probe: `6.9`
- WordPress observed: `6.9`
- PHP floor under probe: `8.1`
- PHP observed: `8.1.34`
- Theme: `AZnet Theme`
- Architecture constraint: hybrid PHP + `theme.json`; no block/FSE template takeover.

## Probe result

The WordPress 6.9 feasibility probe created a temporary `styles/r1-probe.json` only inside the CI-installed Theme and inspected the public WordPress theme JSON resolver behavior.

Observed values:

```text
IS_BLOCK_THEME=0
THEME_OBJECT_IS_BLOCK=0
HAS_THEME_JSON=1
PROBE_VARIATION_DISCOVERED=1
VARIATION_COUNT=1
```

WordPress 6.9 can discover a Theme style-variation JSON file while AZnet Theme remains a classic/hybrid PHP theme. Discovery alone does not provide the bounded Site Editor style-variation selection model required by the R1 product design without changing template/editor ownership. R1 therefore uses a Theme-owned semantic visual-preset mapping and does not convert the Theme to a block/FSE theme.

## Evidence

- Workflow: `R1 Visual Preset Feasibility`
- Run: `34129081929`
- Job: `101764598293`
- Tested R1 head: `fe168bcacb925bfd158fe3d082e9b45a9fe5b4f3`
- Result: `SUCCESS`
- Artifact: `r1-visual-preset-feasibility`
- Artifact ID: `10021325244`
- Artifact ZIP SHA-256: `142257b65d19c9b8e3dbe5a32339dc12d6272f05cecae144e26adf673b7d9f88`

## Decision boundary

This feasibility result selects only the presentation mechanism for R1. It does not authorize FSE takeover, a proprietary content schema, provider-domain state in Theme settings, or a new public integration contract.
