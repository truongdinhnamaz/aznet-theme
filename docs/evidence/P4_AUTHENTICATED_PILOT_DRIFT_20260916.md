# P4 Authenticated Pilot QA — access restored, pilot Theme bits drift detected

**Date:** 2026-09-16  
**Pilot:** `https://tamduchanoi.aznet.vn`  
**QA branch:** `work/p4-authenticated-pilot-qa`  
**Canonical base:** `main@61ee9bcbe6c1193d504759da516b33545d7e5944`  
**Scope:** read-only authenticated WordPress Admin / AZnet Theme Control Center / System Health

## PASS

- Repository Actions secrets were supplied by the owner and remained masked in GitHub Actions logs.
- GitHub-hosted Chromium successfully authenticated to the real pilot WordPress Admin. No credential value, cookie or browser storage state is written to the evidence artifact.
- AZnet Theme Control Center is reachable at `wp-admin/admin.php?page=aznet-theme`; the page heading is `AZnet Theme` and exactly one AZnet Theme admin menu entry is present.
- WordPress/PHP support floor is satisfied on the observed pilot: WordPress `7.1`, PHP `8.4.25`.
- Read-only evidence collection caused no Theme settings save/reset/import, provisioning action, plugin/theme update, publish, delete, release or deployment action.

## RED -> diagnostic regression

1. Initial RED run `35047029137` failed as intended because the authenticated workflow/harness did not yet exist.
2. First implemented run `35047132708` exposed an over-specific static contract marker before runtime; root cause was the test requiring literal `section=system-health` while the harness correctly generated the section URL through its route helper. Only the test contract was corrected.
3. Live authenticated run `35047180666` then reached the real pilot and showed that the active System Health payload did not match the D-027 canonical shape. Artifact `10427890575`, digest `sha256:d13bdc1fabd0ac05ef8be560da36475be829a5aff937838be6a9517b5b0eb860`.
4. A focused diagnostic contract was added to classify legacy-vs-current report shape explicitly. Focused RED run `35047390215` failed because the harness did not yet expose that diagnosis.
5. Diagnostic run `35047440842` passed the static contract and authenticated successfully, then failed with exactly one blocker: `PILOT_THEME_BITS_DRIFT`. Artifact `10427543008`, digest `sha256:b223bd0e8c7fb7055c18100d4399cd2805c5a8dac4e9d45acfecca709a350fec`.

## Observed authenticated pilot state

The fresh Support Snapshot reports:

```text
product = aznet-theme
environment.wordpress = 7.1
environment.php = 8.4.25
theme.name = AZnet Theme
theme.version = 1.1.1
report_shape = legacy_pre_d027
configuration.visual_preset = editorial
configuration.header_preset = standard
configuration.woo_catalog_preset = grid
configuration.logo = false
configuration.primary_menu = true
capabilities.woocommerce = false
capabilities.rootprofile_v1 = true
capabilities.rootprofile_v2 = true
capabilities.rootprofile_current_surface = false
capabilities.convertflow = unknown
```

The live page therefore exposes the pre-D-027 `configuration` / `capabilities` System Health schema rather than the canonical D-027 `standalone_core` / `optional_integrations` schema.

## Provenance classification

This is not evidence that canonical Theme code regressed. The repository contains a historical Law Site Provisioning candidate at `9a39977a6888f1fc656dd64f202e47dbd441ac3c` whose Theme candidate metadata is `1.1.1` and whose `system_health_report()` uses the same `configuration` / `capabilities` shape observed on the pilot.

Canonical source later rejected `1.1.1` as the integration/release metadata baseline and restored `1.1.0`. D-027 then changed current System Health to explicit `standalone_core` and `optional_integrations` groups.

The exact D-027 package run `35042040620` produced installable `aznet-theme-1.1.0-d027-candidate.zip` with SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`. Direct Git comparison from D-027 final head `4b38f792d7de575bc08733748dac0399372e18c2` to canonical `main@61ee9bcbe6c1193d504759da516b33545d7e5944` changes only workflows/tests/docs, so no packaged production Theme file has changed since that candidate.

## BLOCKED

Authenticated access is no longer the blocker. The remaining gate is **pilot Theme bits drift**: the live pilot must run the canonical D-027-capable Theme bytes before the authenticated P4 assertions can be evaluated fairly.

No automatic pilot update is performed by this QA branch. Replacing/updating the active Theme on the pilot is a site mutation and remains owner-gated.

## NEXT

Owner installs/replaces the active pilot Theme with the exact verified canonical D-027 package, preserving existing WordPress-owned content/settings. Then rerun the same read-only authenticated QA without changing the harness. If the drift blocker clears, execute the remaining System Health/admin visual/a11y checks and reconcile P4 state. P5 remains separately gated.
