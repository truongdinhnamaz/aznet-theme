# G0 — Core v1.0 Scope Freeze / Blocker Inventory — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Execution branch: `work/v1-core-completion`  
Canonical source merge base: `main@b7443a96c8687493a0a4994aeca23b5e09a8770c`  
Pre-source production checkpoint: `815ffcd43653930d1f302a055961ac24cf5ae843`

## Goal

Freeze the finite Theme-owned critical path required by AZT-05 v1.0. This checkpoint does not add product scope and does not reopen already-PASS milestones without a concrete invalidation.

## L0 source/state reconciliation

Fresh compare `815ffcd43653930d1f302a055961ac24cf5ae843..b7443a96c8687493a0a4994aeca23b5e09a8770c` is 14 commits ahead, 0 behind. All changed paths are under `docs/**`; no PHP, CSS, JS, template, `theme.json`, asset, or other production Theme byte changed in PR #33.

Therefore PR #33 establishes the canonical GitHub source/constitution without invalidating the previously merged production Theme tree solely by byte change.

## Classification

### ALREADY_PASS / retained unless invalidated

- Milestone 0 — Source Freeze & Provenance.
- Theme Foundation — hybrid PHP + `theme.json`, WordPress-clean baseline.
- Semantic Design Tokens — `--aznet-theme-*` and `theme.json` mapping.
- Header/Footer — Theme-owned presentation at previously evidenced layers.
- Generic Templates — Page/Post/Archive/Search/404 through prior L6 closure.
- WooCommerce presentation shell — W code is on canonical `main`; Woo keeps commerce truth.
- Theme-native Homepage core — native front-page shell is on canonical `main` with prior L1-L4 evidence.

These remain closed unless a G gate reproduces a concrete invalidation.

### OPTIONAL_COMPAT — not core-v1.0 release blockers

- RootProfile E5-C — external current-surface publisher dependency remains BLOCKED.
- RootProfile E5-D — takeover remains approval-gated and is not part of core v1.0 closure.
- ConvertFlow F8 — integrated compatibility remains BLOCKED by provider-emitted nested document-level `<main>`.
- Provider/version-specific runtime certification beyond Theme-owned absence/failure safety.

No RootProfile source, ConvertFlow source, provider-private storage or authoritative routing workaround may be implemented in this Theme branch.

### DEFERRED / non-v1.0

- Control Center U0-U6.
- Page builder or page-builder-like capability.
- New convenience features not required by AZT-05 core product promise.
- New bundler/build framework absent an approved source decision and concrete release need.

### CORE_BLOCKER — finite remaining Theme-owned closure work

1. **G1** — cleanup inventory and canonical-destination/use-site review.
2. **G2** — bounded retirement only where deletion is proven safe; otherwise explicit no-op PASS.
3. **G3** — fresh static/security/package hygiene and retained L1-L2 contract regression.
4. **G4** — WordPress 6.9+/PHP 8.1+ clean runtime on core routes with optional ecosystem plugins off.
5. **G5** — browser/responsive/keyboard/a11y/performance and asset-scope verification on core routes.
6. **G6** — in-place update continuity, theme-switch safety and rollback verification.
7. **G7** — full retained regression plus deterministic package/SHA/unzip/exact-byte/activation closure.
8. **G8** — synchronized v1.0 release metadata and final evidence/source closure, stopping at owner release tag/deploy gate.

## Release metadata state

Current production metadata intentionally remains pre-release:

- `style.css`: `Version: 0.1.0-alpha.7`
- `functions.php`: `AZNET_THEME_VERSION = 0.1.0-alpha.7`

Version promotion is **not** authorized in G0 and remains deferred until the final verified candidate stage defined by G8.

## Scope guard

The following are explicitly rejected as core blockers:

- Control Center expansion;
- RootProfile source implementation;
- ConvertFlow source implementation;
- page builder work;
- external provider defects not caused by Theme-owned code.

## PASS

**G0 / L0 PASS.** The core-v1.0 critical path is finite, Theme-owned, and separated from optional provider certification. No production behavior changed in this slice.

## Rollback

This evidence-only slice can be reverted independently without changing production Theme bytes. Canonical source rollback reference remains `main@b7443a96c8687493a0a4994aeca23b5e09a8770c`.

## NEXT

**G1 — verify current cleanup candidates, canonical destinations and use-sites; delete nothing without destination + current use-site + regression + rollback evidence.**