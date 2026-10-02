# AZnet Theme 1.3.57 Core Technical Completion Candidate — 02/10/2026

## Scope

Repository-native Core completion only. This evidence does not publish a Git tag/GitHub Release and does not deploy any production pilot.

## Canonical production bytes

- Last production-byte-changing canonical checkpoint: `main@eada15dcc8962c364cb6e83697bdebac51c15856` (PR #359 Team Directory management).
- Theme metadata: `1.3.57`.
- Fresh exact-main V1 run: `36964065495` — SUCCESS.
- Fresh exact-main X6 run: `36964065510` — SUCCESS.
- X6 browser matrix: 32/32 PASS.
- X6 WordPress runtime: WordPress 6.9, zero-plugin package path, runtime/asset boundaries PASS.
- Exact package lifecycle: install/activate, switch to Twenty Twenty-Five, switch back, WordPress content + Theme Mod continuity PASS.
- Deterministic package: `aznet-theme-1.3.57.zip`.
- Production files: 215.
- Package SHA-256: `f4f0599b51f58671a0f0e0e6026f03035fbc90d1448fefe0d7194c4e7b0e5333`.
- X6 evidence artifact: `11209335729`.
- X6 artifact digest: `sha256:f5c79733956cca0afa62f75fd05d810c62c1d9a54d91184cac49cbcf6a84f68b`.

## Post-checkpoint byte identity

PR #365 changed only `tests/browser/r5-control-center-l4.mjs`. Its final head `cd1b1de902c2d50d339d3138b7d2b42975ee0b1a` completed 7/7 checks SUCCESS and rebuilt the deterministic candidate package with the same 215 production files and the same SHA-256 `f4f0599b51f58671a0f0e0e6026f03035fbc90d1448fefe0d7194c4e7b0e5333`.

PR #366 changed only `docs/source/AZT-04-roadmap-qa-decisions.md` and `docs/source/AZT-EXEC-MAP.md`. Therefore canonical `main@51e15aa706d1e9a04bc06dcde5d362e3075dbc72` retains the exact verified production bytes.

## Release state

- Latest public GitHub Release: `v1.3.24`.
- Release ID: `392498063`.
- Published: 20/09/2026.
- Public asset: `aznet-theme-1.3.24.zip`.
- Public asset SHA-256: `d38c3a172026dd15f58a0dbd237a455a6de12d5fdb7d8293258a27d6567ca645`.
- `v1.3.57` is **not published** by this checkpoint.

## Disposition

**PASS — Core technical completion / L6 repository release readiness.**

Under AZT-05 plus D-033/D-044, RootProfile Team, provider L5, inaccessible Rèm 01, Curtain 01 authenticated-pilot parity and the external Template Distribution Service are separate optional/pilot tracks and do not block Core technical completion.

**HARD GATE — publication:** creating/publishing the `v1.3.57` tag/GitHub Release requires explicit product-owner approval.

**SEPARATE HARD GATE — production deployment:** any pilot install/update remains independently preflighted, rollback-safe and explicitly approved.
