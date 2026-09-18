# AZnet Theme 1.3.5 — Law 01 Hero backward-compatibility closure

**Date:** 18/09/2026  
**Scope:** Theme-owned Law 01 Homepage presentation and backward compatibility only.  
**Owner boundary:** WordPress remains the source of native Page/Site data; AZnet Theme only composes presentation.

## Problem

Theme 1.3.4 introduced a dedicated `homepage_hero_page` source. Existing upgraded sites with no mapping could therefore lose the Law 01 Hero entirely.

## RED

A focused offline regression contract was added first and failed on the intended behavior: an unmapped dedicated Hero source did not preserve the legacy WordPress-native Hero presentation.

## GREEN

The Theme now prefers a valid dedicated Hero Page and otherwise falls back to the previous public WordPress-native presentation inputs already used before 1.3.4:

- Site Title / Front Page title for heading composition;
- Front Page excerpt;
- Site Tagline;
- Front Page featured image.

No duplicate domain store, private provider read or authoritative identity/routing heuristic was introduced. A valid dedicated Hero Page always takes precedence.

## Provenance and QA

- Final PR #138 tested head: `949e9dd97ce805eed43811ce633739ed1934c1c2`.
- Owner-approved merge: `main@a9a3578516971abd9a9223c1e8dac69da5657bea`.
- Final PR head: 25/25 observed workflows SUCCESS.
- Exact-main `V1 Exact Main Verification` run `35335680576`: SUCCESS.
- Exact-main `X6 Cross-surface Release Closure` run `35335680518`: SUCCESS.
- Theme metadata: `1.3.5`.
- Deterministic Y5 package: `aznet-theme-1.3.5.zip`, 145 files.
- Package SHA-256: `ec9cf5c35dd3de8c7cff5673c5e3f392abdc3894cd676738e746b354c70e1564`.
- Packaged PHP lint: 108 files PASS.
- Y5 promoted-package artifact ID `10542541092`, artifact digest `sha256:1a53183d0bd9fe3b66faaa612c5f0c66d82b81a0304ad51b809616569754dce9`.

## Release boundary

This is a technical/source checkpoint only. Published GitHub Release and verified production deployment remain historical `v1.3.0` unless separately approved and executed.
