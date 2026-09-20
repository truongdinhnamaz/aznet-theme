# AZnet Theme v1.3.21 About Page technical closure — 20/09/2026

## Scope

This checkpoint closes the owner-approved **About / Giới thiệu icon-led presentation** and the associated AZnet Theme `1.3.21` technical promotion.

The slice remains presentation-only:

- WordPress continues to own native Page content, links and stored content bytes.
- AZnet Theme owns the scoped visual presentation under `.aznet-theme-page-kit--about`.
- No RootProfile/private storage reads, authoritative Team/Profile inference, provider-domain takeover, route heuristic, or parallel content store was introduced.
- Production deployment to `lstamduchn.vn` is not part of this technical closure.

## About presentation closure

PR #196, **About Page: refined icon-led presentation**, was rebuilt from then-current canonical main rather than promoting the stale draft About branch.

TDD evidence:

- RED commit: `4b7435b6daaed58fc4d1b68f877000aadf92e637`.
- RED Y2 offline contract failed for the intended missing About presentation marker.
- GREEN commit: `9e2b8989bc9d6e382495791bba4379b685494c4f`.
- Exact GREEN head completed 15/15 triggered workflows SUCCESS.
- Owner approval was recorded before merge.
- PR #196 merged to canonical `main@aab636d3fe1c21be924ba62ea30b7a910cf2e019`.

The production presentation delta adds restrained decorative line-icon treatment and hierarchy for the About Page Kit while retaining Theme tokens and WordPress-owned authored content.

## v1.3.21 RED -> GREEN promotion

Release branch: `release/v1.3.21`.

RED head:

- `d7d91627d325dc76433c9e693140fcd37192ee17`.
- Y5 run `35517794896`, offline job `106096559299`, failed for the intended reason:
  `Y5 promoted style.css must be exactly 1.3.21`.

Minimal GREEN release delta:

- `style.css` Theme metadata -> `1.3.21`.
- `AZNET_THEME_VERSION` -> `1.3.21`.
- Y5 package/runtime/workflow targets -> `1.3.21`.
- X6 gained the explicit `1.3.20 -> 1.3.21` metadata-only promotion boundary.
- Retained release contracts require the new version boundary.

Exact GREEN head:

- `f7107f301485839d361fd00e9cb11c0837074434`.
- 14/14 triggered pull-request workflows completed SUCCESS.
- Y5 Client Delivery Release: `35517851679` SUCCESS.
- X6 Cross-surface Release Closure: `35517851704` SUCCESS.
- V1 Core Pull Request CI: `35517851644` SUCCESS.
- Release Version Consistency Gate, D-027 retained/exact-package, R1, R6, lifecycle, Homepage regression, X3/X4 and Law Site Provisioning gates all completed SUCCESS.

PR #197 then merged to canonical:

- `main@998a8dcf342b09824b197059524c50c013dba828`.

## Exact-main verification

Fresh exact-main verification on `998a8dcf342b09824b197059524c50c013dba828`:

- V1 Exact Main Verification run `35518082825`: SUCCESS.
- X6 Cross-surface Release Closure run `35518082833`: SUCCESS.
- X6 integrated browser/axe matrix: **32/32 PASS**.
- X6 ownership check: `PASS: X6 promoted 1.3.21 ownership boundary`.

Deterministic exact package:

- File: `aznet-theme-1.3.21.zip`.
- Production package files: **158**.
- Packaged PHP files linted: **113**, PASS.
- Independent package builds A/B were byte-identical.
- SHA-256: `da695f2b33aafc38172a9a412a3b1afd16263db3f03d305be4304eba877e09e2`.
- Exact-main X6 evidence artifact: `10607616743`.
- Artifact digest: `sha256:beacf4a2d8bc6abf0c0b1f8453822c3aa3a3fcee99e7ca4aad4611b25917e655`.

The earlier exact-GREEN Y5 promoted-package artifact is retained as corroborating evidence:

- Artifact `10607861001`.
- Artifact digest `sha256:6f3b8f92de8d7073998b2916a3306c42dbfa80153f62eaef63e718091ec46b98`.

## Verification-path decision

The owner explicitly directed that AZnet Theme should **not depend on WPVibe** for this technical/release path.

For this checkpoint:

- repository-native GitHub static, contract, WordPress runtime, browser/a11y, exact-package and lifecycle evidence are sufficient for the claimed technical PASS;
- WPVibe preview/readback is not a prerequisite for this repository technical closure;
- this does **not** waive production deployment verification: any future deployment still requires a safe authenticated target-site path, fresh preflight and rollback evidence by an available approved mechanism.

## Current boundary

**PASS:** About presentation + v1.3.21 canonical technical closure through exact-main L1-L4/L6 verification.

**NOT CLAIMED:** Git tag/GitHub Release publication for `v1.3.21`.

**NOT CLAIMED:** production deployment of `1.3.21` to `lstamduchn.vn`.

The current published GitHub Release remains `v1.3.20` until a separate publication action is completed.
