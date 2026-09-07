# AZnet Theme v1.0 Core Completion Design

## Status

Approved design, implementation not started. This document is an implementation design derived from the authoritative AZnet Theme source set; it is not itself a source owner.

## Authoritative sources

Current governing source set for this design:

- AZT-05 Product Constitution v1.0 — product independence, quality floor, release constitution and amendment rule.
- AZT-01 Product Charter and Ownership v0.3 — product scope, non-goals, ownership and core v1.0 Definition of Done.
- AZT-02 Theme Architecture and Integration Contracts v0.4 — public/fail-soft provider boundaries and dependency policy.
- AZT-03 Current Baseline and Code Provenance v0.16 — canonical implementation state and provenance.
- AZT-04 Roadmap, QA and Decision Log v0.20 — D-016 and current release gates.
- AZT-EXEC-MAP-01 v0.12 — derived execution order; exact current next is G0.
- ECOSYSTEM-ARCH-01 v1.0 — cross-product domain ownership and source-of-truth rules.

If this design conflicts with an authoritative source, the source governs.

## Goal

Finish AZnet Theme as a production-quality, independent WordPress theme and promote a verified v1.0 release candidate without allowing optional RootProfile, ConvertFlow, WooCommerce or other provider-specific blockers to redefine core Theme readiness.

## Product rule

AZnet Theme must be useful and complete on clean WordPress. Optional integrations are capability/certification tracks. They may improve the product, but they are not hard dependencies for activation, rendering, update, rollback or core v1.0 release unless a future source amendment explicitly makes one mandatory.

## Architecture

The Theme remains a hybrid PHP theme plus `theme.json` on WordPress 6.9+ and PHP 8.1+. It owns design tokens, site shell, Header/Footer, generic templates, native Homepage fallback, responsive/accessibility presentation and Theme-owned asset loading. WordPress and external domain owners retain their authoritative state and semantics.

External providers are consumed only through public/versioned/capability-detected contracts. Provider absence, error or unsupported versions must fail soft. A provider-specific defect is recorded as BLOCKED/UNKNOWN with owner and evidence; the Theme must not self-unblock by reading private storage, copying provider code, guessing authoritative routing or duplicating domain logic.

## Current implementation state

Canonical `main` is `815ffcd43653930d1f302a055961ac24cf5ae843`.

Retain without reopening unless invalidated:

- Milestone 0 — PASS.
- Theme Foundation — PASS.
- Semantic Design Tokens — PASS.
- Header/Footer v1.5 — PASS.
- Generic Templates D0-D7 — PASS through L6.
- RootProfile E0-E4 and E5-B — retain PASS at already-proven layers; E5-C remains external BLOCKED, E5-D remains approval-gated.
- Woo W0-W7 — retain PASS; PR #24 is merged and carries W8/W9 delivery code/evidence basis.
- Theme-native Homepage shell — merged via PR #30 with fresh L1-L4 and retained regression/package evidence.
- ConvertFlow F6/F7 actual-package integration — evidence PASS; F8 integrated compatibility remains BLOCKED by provider-emitted nested document-level `<main>` and is not a core-v1.0 blocker under D-016.
- Control Center U — outside the v1.0 critical path.

## v1.0 critical path

### G0 — Core scope freeze and release inventory

Resolve the canonical tree, current tests, release metadata and remaining Theme-owned blockers. Classify every remaining item as core release blocker, optional compatibility blocker, historical cleanup candidate or out of scope.

Exit: no ambiguity remains about what can block v1.0 and exact G1 work is bounded.

### G1 — Safe Theme-owned cleanup

Retire only presentation code/assets that are demonstrably superseded and have proven destination, regression protection and rollback. Leave UNKNOWN, mixed, domain-owned or provider-dependent candidates untouched.

Exit: no safe Theme-owned retirement remains; no ownership drift.

### G2 — Core static and contract regression

Run PHP lint, naming/prefix, forbidden storage/private-access, escaping/security, asset-scope and retained contract suites. Any defect must be reproduced as RED before minimal GREEN and regression.

Exit: all core L1-L2 gates PASS on the canonical candidate.

### G3 — WordPress-clean runtime/browser/a11y

Verify the Theme on WordPress 6.9+ / PHP 8.1+ with optional ecosystem plugins absent. Cover Home, native front page, Page, Post, Archive, Search, 404, Header/Footer across desktop/tablet/mobile, keyboard/focus, landmark integrity, console/error markers and accessibility checks.

Exit: clean WordPress L3-L4 PASS.

### G4 — Fail-soft and optional-provider absence integration

Verify that optional providers can be absent/disabled without fatal or ownership leakage. Available provider smoke may be run where authorized, but provider-specific certification is not required for core closure.

Exit: Theme-owned fail-soft boundary PASS; any external compatibility blocker remains explicitly non-core.

### G5 — v1.0 release candidate package

Promote version metadata consistently only after G0-G4 PASS. Build deterministic package bytes, record SHA-256, unzip and exact-byte reverify, and validate packaged PHP.

Exit: reproducible installable v1.0 candidate artifact PASS.

### G6 — Upgrade, theme-switch and rollback

Verify upgrade from the prior alpha while retaining Theme-owned settings and native WordPress content; switch away/back; verify rollback package/path.

Exit: upgrade/theme-switch/rollback PASS.

### G7 — Source and provenance closure

Refresh source-owned current state only where facts changed, record candidate hashes/evidence and ensure no source/current-state contradiction.

Exit: AZT-03/AZT-04 and evidence are current; rollback/continuation is explicit.

### G8 — Owner release gate

Only after fresh candidate evidence: owner decides merge/tag/release/deploy. No automatic production deployment.

## Non-goals for v1.0 closure

- Do not add Control Center or new convenience features to the release critical path.
- Do not redesign already-PASS surfaces without concrete invalidation.
- Do not implement RootProfile or ConvertFlow source inside `aznet-theme`.
- Do not convert optional provider certification failures into Theme architecture workarounds.
- Do not add a bundler/build framework unless a concrete release need appears and source governance approves it.

## Testing discipline

Every production defect or behavior change follows RED -> minimal GREEN -> regression. Evidence layers remain distinct: L1/L2 do not prove L3/L4/L5/L6. Runtime/browser failures are reduced to the shallowest stable reproduction before fixes are made.

## Rollback

Every implementation slice is committed independently on an isolated work/feature branch. G1 deletions require a known pre-slice commit and provenance evidence. Release candidate work preserves the prior verified alpha package and candidate SHA as rollback references until v1.0 promotion is approved.

## Exact next

G0 — revalidate canonical `main@815ffcd43653930d1f302a055961ac24cf5ae843`, freeze core-v1.0 scope, and inventory the remaining Theme-owned cleanup/release blockers. No production code is authorized until the written source/spec review gate is approved.