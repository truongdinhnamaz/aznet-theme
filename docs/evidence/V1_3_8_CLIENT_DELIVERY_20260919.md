# AZnet Theme 1.3.8 — Final Client Delivery Candidate

**Date:** 19/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Canonical base:** `main@9ad2faa9141f9aca93f2c6b2a0a37731143ccd44`  
**PR:** #149  
**Verified production head:** `5f3dcf00f0a619b58a966078b2cd969cb7fec45d`

## Purpose

Create the newest installable AZnet Theme package from canonical main after the owner-approved Law 01 screenshot-reference presentation closure.

The version is promoted from canonical `1.3.6` directly to **1.3.8**. Version `1.3.7` is intentionally not reused because an installable 1.3.7 artifact had already been generated from a superseded, unmerged intermediate candidate. Skipping that package identity avoids provenance ambiguity between different bytes carrying the same version.

No architecture, provider public contract or domain ownership changes are introduced.

## RED -> GREEN

RED head: `5f188f3fcca6fd32122a1799aff703d4837b89e9`.

The Y5 release contract failed for the intended reason while production metadata still declared 1.3.6:

`Y5 promoted style.css must be exactly 1.3.8`

Minimal GREEN promoted only the Theme version declarations:

- `style.css`: `Version: 1.3.8`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.8`

Y5/X6 release guards were extended to recognize only the approved canonical `1.3.6 -> 1.3.8` promotion boundary for this slice.

## Verification

Exact production head `5f3dcf00f0a619b58a966078b2cd969cb7fec45d` completed **14/14 triggered workflows SUCCESS, 0 failure**:

- Release Version Consistency Gate
- V1 Core Pull Request CI
- Y5 Client Delivery Release
- X6 Cross-surface Release Closure
- D-027 Standalone Core Exact Package
- D-027 Retained Full Regression
- F Homepage Native Regression Package
- Law Site Provisioning Browser Quality
- Law Site Provisioning Candidate Package
- R1 Design System Browser Parity
- R6 Performance Baseline
- G Core Lifecycle Update Switch Rollback
- X3 Media Gallery Embed
- X4 Pagination Navigation

Y5 exact-package lifecycle built the package twice and proved byte identity, exact source/package identity, packaged PHP lint, clean WordPress 6.9 install with zero active third-party plugins, browser/runtime smoke, and theme switch-away/switch-back continuity.

## Installable package

File: `aznet-theme-1.3.8.zip`  
Production files: **145**  
Packaged PHP lint: **108/108 PASS**  
SHA-256: `0aba4cf0e213150319c8288d62d06cb136481670cd8f53fd0edbfd9276a03238`

Y5 run: `35403751052`  
Promoted-package artifact ID: `10571900478`  
Artifact digest: `sha256:2f8b380f8ffa6e88d481d99a14ea1d248f86243ebd1c213bdccb49fda0fc9da4`

The direct WordPress-installable ZIP has been extracted from the GitHub Actions artifact wrapper and independently checked to contain `aznet-theme/style.css` declaring version 1.3.8.

## Ownership and gates

- Theme remains presentation owner.
- RootProfile authoritative Team integration remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- No private provider storage read or authoritative Team inference is introduced.
- Canonical merge of PR #149 remains an approval gate.
- Git tag, GitHub Release and production deployment remain separate approval gates.
- Published GitHub Release remains historical `v1.3.0` until separately approved.
