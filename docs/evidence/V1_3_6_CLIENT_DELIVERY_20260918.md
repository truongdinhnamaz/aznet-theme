# AZnet Theme 1.3.6 — Client Delivery Candidate

**Date:** 18/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**PR:** #143  
**Base:** `main@a60cd71437375a564444c181780f5e38ec37067b`  
**Verified production head:** `7db436b316ef5a9f9264fa9dd71f6cfad4d7ad80`

## Purpose

Create the newest installable AZnet Theme package for client delivery after the approved Law 01 visual-parity sequence closed.

The release identity is promoted from `1.3.5` to `1.3.6` because the current client-delivery bytes include Theme-owned presentation changes merged after the earlier 1.3.5 Hero compatibility package. Shipping those later bytes under the older 1.3.5 identity would make package provenance ambiguous.

This is a patch release candidate. No product ownership, public integration contract or provider/domain semantics change.

## Ownership boundary

- Theme remains presentation owner.
- WordPress remains source owner for native Page/Post/query/navigation state.
- RootProfile authoritative Team membership remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- No private provider storage read or heuristic authoritative membership was added.
- Git tag, GitHub Release and production deployment are separate approval gates and are not claimed here.

## RED -> GREEN

RED head: `120cd35e7d245f6a7e564f544df76d987fec2ddd`.

The Y5 offline gate failed for the intended reason while production metadata still remained 1.3.5:

`Y5 promoted style.css must be exactly 1.3.6`

GREEN promotion changed only the two Theme version declarations:

- `style.css`: `Version: 1.3.6`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.6`

The existing Y5/X6 release guards were extended to require the exact 1.3.5 -> 1.3.6 promotion boundary.

## Verification

Exact verified production head `7db436b316ef5a9f9264fa9dd71f6cfad4d7ad80` completed **14/14 triggered workflows SUCCESS, 0 failure**:

- V1 Core Pull Request CI
- Release Version Consistency Gate
- Y5 Client Delivery Release
- X6 Cross-surface Release Closure
- D-027 Standalone Core Exact Package
- D-027 Retained Full Regression
- F Homepage Native Regression Package
- Law Site Provisioning Browser Quality
- Law Site Provisioning Candidate Package
- R1 Design System Browser Parity
- R6 Performance Baseline
- X3 Media Gallery Embed
- X4 Pagination Navigation
- G Core Lifecycle Update Switch Rollback

Y5 exact-package lifecycle independently built the package twice and proved byte identity, exact source/package identity, clean WordPress 6.9 install, zero active third-party plugins, runtime/browser smoke, theme switch-away to Twenty Twenty-Five and switch-back continuity.

## Installable package

File: `aznet-theme-1.3.6.zip`  
Production files: **145**  
Packaged PHP lint: **108/108 PASS**  
SHA-256: `25271cd4c68f0bc88e236f084acb73d7661a618d1ae1d737f0f2a5202307a84a`

GitHub Actions run: `35353852165`  
Promoted-package artifact ID: `10550649207`  
Artifact digest: `sha256:7efe3e575a46950e85abd703bad87e05daac32bd2346265fa2e148569de48cfc`

## Current gate

The package is technically verified for client installation/delivery at the tested scope. PR #143 canonical merge remains a separate approval gate.

No tag, GitHub Release or production deployment is authorized or claimed by this checkpoint.
