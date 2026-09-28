# Law 01 Completion Audit and Hardening Design

**Date:** 2026-09-28  
**Status:** Proposed for owner review  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Base:** `main@6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`  
**Theme metadata:** `1.3.54`  
**Pilot:** `https://lstamduchn.vn`

## 1. Intent

Complete the Law 01 presentation as a coherent, production-grade AZnet Theme sample, starting from the Homepage and then following its public navigation into the key inner surfaces.

This work is not a redesign from scratch. The accepted Law 01 Burgundy Homepage baseline, existing source mappings, WordPress-native content model, and prior PASS evidence are retained unless fresh evidence proves a regression or incompleteness.

Success means:

1. Law 01 is visually and structurally complete across the public journey, not only on the Homepage.
2. Theme-owned defects are reproduced and fixed at the shallowest valid layer.
3. WordPress-owned or provider-owned source gaps are reported as source gaps rather than hidden with heuristics or duplicated data.
4. Fresh QA claims are made only at layers actually reverified on the final candidate bytes.
5. No release, production deployment, provider takeover, or destructive site mutation is implied by implementation completion.

## 2. Source and state baseline

The execution baseline is the latest canonical repository state, not an older Law 01 branch or release.

Current facts to revalidate at execution start:

- canonical branch: `main`;
- current canonical commit at design time: `6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`;
- canonical Theme metadata: `1.3.54`;
- the reviewed Law 01 Homepage presentation already has retained final-owner-acceptance evidence;
- later Homepage backend/effective-surface work is canonical and must not be bypassed;
- open or historical Law 01 PRs such as #272 and #208 are evidence/reference only unless their exact changes are independently shown to be missing from current main;
- the pilot `lstamduchn.vn` was observed on Theme `1.3.53` during pre-design read-only inspection;
- fresh pilot browser QA is currently constrained by OneShield challenge behavior;
- WPVibe Connect on the pilot was observed inactive, so plugin-backed draft/file operations are not currently a valid execution path.

If any of those facts changes before implementation, the implementation plan must use the newer valid state.

## 3. Ownership boundary

The governing rule remains:

> SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

### Theme-owned

Law 01 may change only Theme-owned presentation concerns, including:

- Law 01 design tokens and surface-scoped CSS;
- Header/Footer presentation;
- Homepage composition and bounded presentation settings;
- generic Page/Post/archive/search presentation;
- Services/Team card presentation when driven by existing public WordPress sources;
- responsive behavior, keyboard/focus presentation and accessibility;
- surface-aware asset loading;
- fail-soft rendering and empty-state presentation where appropriate.

### WordPress-owned

The Theme must not fabricate or silently repair authoritative content by inference. WordPress remains owner of:

- Page/Post titles and authored content;
- excerpts and featured media;
- Page hierarchy;
- categories and native queries;
- menu items and menu locations;
- native contact/address content where WordPress is the configured source.

### Provider-owned

RootProfile, ConvertFlow, WooCommerce and other domain owners retain their semantics and state. Theme may consume only approved public/versioned/capability-detected contracts.

### Explicitly forbidden

This program must not:

- infer lawyer/member identity from slug, URL, author account, title fragments or Page IDs;
- invent missing lawyer names, roles, credentials, achievements or staff relationships;
- infer service semantics from slugs when authoritative Page content is missing;
- read provider private option/meta/table/CPT storage;
- create a parallel Team, identity, conversion or commerce store;
- copy old PR code wholesale over canonical main;
- treat fixture data as proof of a real provider integration;
- mutate production content merely to make a Theme presentation test pass.

## 4. Observed pilot source gaps

The following read-only observations are inputs to the audit, not Theme defects by default:

- two published Service child Pages were observed with empty titles;
- two Team child Pages were observed with empty titles;
- the mapped/available “Phân tích vụ án” category had zero published Posts;
- the mapped/available “Tin tức” category had zero published Posts;
- Homepage Analysis and Legal News are designed to fail soft when their mapped sources have no renderable Posts.

These gaps must be revalidated before execution. If unchanged, the Theme should continue to avoid fabrication. The completion report should distinguish:

- **Theme defect** — presentation/code is wrong for valid source data;
- **source gap** — authoritative WordPress/provider data is absent or incomplete;
- **blocked integration** — required public contract is unavailable;
- **QA unknown** — environment prevents a claim at a deeper layer.

## 5. Public journey in scope

The audit proceeds in user journey order.

### Phase A — Homepage first

Audit the effective Law 01 Homepage from top to bottom:

1. Header / topbar / primary navigation / mobile navigation
2. Hero
3. profile / trust presentation
4. Services
5. About
6. Team
7. Latest Posts
8. Topics
9. Analysis
10. Legal News
11. Process
12. FAQ
13. final Contact CTA
14. Footer

For each surface, compare three things:

- current canonical source path;
- actual effective WordPress/provider source availability;
- rendered presentation behavior.

Do not reopen a retained PASS section unless fresh evidence demonstrates a defect, regression, inconsistency, or incomplete authoring path.

### Phase B — Homepage-linked inner surfaces

After Homepage completion, follow the public links into:

- About;
- Services root;
- representative Service detail Pages;
- Team directory;
- representative Team/Profile surface when authoritative data exists;
- Contact;
- category/archive surfaces;
- representative legal article;
- search/no-results and 404 where navigation reaches generic Theme surfaces.

The goal is visual-system consistency and public journey completeness, not legal-domain logic.

## 6. Implementation method

Every production code change follows:

`RED -> intended failure -> minimal GREEN -> retained regression`

### A. Reproduce at the shallowest layer

Before changing production code:

1. identify the exact rendered defect;
2. locate the smallest Theme-owned component that causes it;
3. add or extend the narrowest stable contract that fails for that reason;
4. only then implement the minimal fix.

Examples:

- spacing/contrast/card hierarchy -> static/component contract plus browser assertion;
- wrong source path -> source/contract test;
- mobile overflow -> browser regression at the failing viewport;
- missing keyboard state -> focused browser/a11y assertion.

### B. Preserve the effective Homepage model

Current canonical Homepage composition is driven through the shared effective surface map. New Law 01 fixes must consume that model rather than create another parallel source resolver.

If a missing presentation feature appears to require a new authoritative field, provider capability or ownership transfer, stop that slice as a hard gate instead of creating a Theme-local substitute.

### C. Surface-aware assets

Law 01-specific CSS/JS must remain scoped to Law 01 surfaces. Do not add a new global bundle or load unrelated ecosystem assets site-wide.

## 7. Visual direction

Retain the accepted Law 01 character rather than introducing a new visual language:

- Burgundy / gold professional legal identity;
- warm light surfaces;
- strong editorial hierarchy;
- restrained, formal interaction;
- existing self-hosted typography baseline;
- consistent Header/Footer and inner-page color system;
- dense enough for professional information but not dashboard-like;
- no decorative effects that reduce readability or make the site feel like a generic template.

Visual refinement should focus on consistency, rhythm, hierarchy, responsive behavior and source-backed completeness.

## 8. QA model

Claims are layer-specific.

### L0 — Source/state

Required before coding:

- canonical main/commit/version;
- relevant accepted source decisions;
- current open PR divergence;
- pilot active Theme version;
- source availability and known external blockers.

### L1 — Static

For changed files:

- PHP lint;
- naming/namespace rules;
- ownership/private-storage scans;
- surface-aware asset checks;
- no forbidden heuristic source resolution.

### L2 — Contract/TDD

Every feature/bugfix change must have a RED-first contract and GREEN regression.

Homepage source-map/parity contracts remain required where touched.

### L3 — WordPress runtime

Required before claiming runtime PASS:

- real WordPress with supported minimum floor where applicable;
- Homepage renders without Theme-caused fatal/warning/uncaught errors;
- source-backed sections appear or fail soft as designed;
- inner surfaces preserve native WordPress behavior.

### L4 — Browser/visual/a11y

Required before claiming visual completion:

- desktop, tablet, mobile and narrow-mobile widths;
- no horizontal overflow;
- keyboard navigation and visible focus;
- heading/landmark/label sanity;
- no serious/critical accessibility regression;
- no Theme-caused console/page errors;
- Header/mobile menu and long-card/title stress;
- visual continuity from Homepage into inner surfaces.

If OneShield or another environment barrier prevents exact pilot browser proof, record L4 as UNKNOWN/BLOCKED for the pilot rather than inferring PASS from fixtures.

### L5 — Integration

Only claim provider integration where a real public/versioned contract is exercised. RootProfile Team/member authoritative discovery remains blocked unless the owner contract exists and is proven.

### L6 — Completion/release

This program may prepare a candidate and evidence, but publication/deployment remains a separate explicit gate.

## 9. Pilot strategy

The pilot is used as a real-stack validation target, not as a source of truth for Theme code.

Safe sequence:

1. inspect live state read-only;
2. verify whether an exact-current draft/staging path is available;
3. if available, sync candidate safely to an isolated draft/staging surface;
4. run L3/L4 there;
5. do not publish from this program without a separate explicit production approval and fresh backup/rollback preflight.

If WPVibe remains unavailable on the pilot:

- continue repository-owned L0-L2 work;
- use repository runtime/browser fixtures for Theme defects;
- keep pilot L3/L4 explicitly UNKNOWN;
- do not patch live files through ad hoc host mechanisms merely to obtain a green result.

## 10. Branch and provenance strategy

Implementation starts from the latest canonical main at execution time.

This design is recorded on:

`work/law01-completion-audit-20260928`

Production implementation should use bounded work/feature branches or a recoverable branch chain. Each independent defect/fix should produce:

- one bounded RED/GREEN checkpoint;
- fresh regression evidence;
- a clear rollback point;
- no unrelated refactor.

Historical/open Law 01 PRs may be consulted for provenance, but current main is authoritative.

## 11. Exit criteria

### Homepage completion exit

Homepage is complete only when:

- every effective surface has been reviewed;
- no known Theme-owned Homepage defect remains open;
- source gaps are explicitly classified instead of masked;
- current canonical code passes relevant L1/L2;
- L3/L4 are either freshly PASS or explicitly BLOCKED/UNKNOWN with cause;
- no retained PASS has been invalidated.

### Law 01 sample completion exit

The Law 01 sample is complete when:

- Homepage completion exit is met;
- key linked inner surfaces are reviewed and any Theme-owned regressions are fixed;
- responsive/a11y behavior is consistent across the journey;
- source/provider gaps are documented separately;
- final candidate provenance is recorded;
- one exact next action remains: merge/review, release, deploy, or external dependency resolution.

## 12. Rollback and recovery

- no destructive production mutation is part of implementation;
- each code slice is isolated in Git and reversible by commit/branch rollback;
- pilot draft/staging work must preserve the existing live Theme;
- production publication requires a separate backup and explicit owner approval;
- if a deeper QA layer exposes a defect, return to the shallowest reproducible layer and add a regression before fixing.

## 13. Initial exact next after approval

After this design is approved, write the implementation plan with **Homepage Phase A as the first executable milestone**.

The first implementation slice will revalidate canonical state and build a concrete Homepage surface audit matrix from the current shared effective surface map. It will not edit production code until that matrix identifies a specific Theme-owned defect and a RED test reproduces it.
