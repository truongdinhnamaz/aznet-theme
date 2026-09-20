# Law 01 Services Hub Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade the existing portable Services Page Kit into the approved seven-band service hub while preserving WordPress content ownership and future ConvertFlow Service Journey ownership.

**Architecture:** Keep the existing `aznet-theme/page-services` pattern as the authored-content carrier. Extend only its block structure plus token-driven selectors in `assets/css/components/page.css`; do not add route detection, storage, provider reads, or service resolver logic.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, native block patterns, semantic CSS tokens, existing Y2 PHP contract and browser workflow.

**Spec:** `docs/superpowers/specs/2026-09-20-law01-services-hub-design.md`

## Global Constraints

- AZnet Theme is presentation owner only.
- WordPress owns Page/Post content and query.
- ConvertFlow owns any future Service Journey semantics/resolver/state/conversion/analytics.
- No direct option/meta/table/private-provider reads.
- No slug/title/Page-ID authority heuristic.
- No hard-coded client facts or contact identities.
- Existing five Professional Page Kit role mapping must not change.
- Shared Page CSS must remain semantic-token driven.

## Review Focus

- Empty or sparsely authored service cards must remain structurally safe and editable.
- Long Vietnamese service names/descriptions must wrap without horizontal overflow.
- Query section with zero Posts must preserve a useful no-results state.
- Keyboard focus must remain visible on service links, CTA links and Query links.
- Theme switching must preserve authored Page content byte-for-byte.

---

### Task 1: Lock the approved Services contract

**Files:**
- Modify: `tests/offline/y2-professional-page-kits-contract.php`

**Interfaces:**
- Consumes: existing `patterns/page-services.php` and `assets/css/components/page.css`.
- Produces: explicit failing assertions for seven Services bands, six cards, native Query block and service-specific CSS selectors.

- [ ] **Step 1: Write the failing test**

Add assertions requiring:
`aznet-theme-page-kit__service-selection`,
`aznet-theme-page-kit__orientation`,
`aznet-theme-page-kit__process`,
`aznet-theme-page-kit__trust`,
`aznet-theme-page-kit__knowledge`,
`aznet-theme-page-kit__cta`,
six occurrences of `aznet-theme-page-kit__service-card`,
a `wp:query` marker,
and service selectors in `page.css`.

- [ ] **Step 2: Run test to verify RED**

Run: `php tests/offline/y2-professional-page-kits-contract.php`

Expected: FAIL because the current Services pattern lacks the approved service-hub section classes.

- [ ] **Step 3: Commit RED**

Commit message: `test: lock Law 01 services hub contract`

### Task 2: Implement the Services Page Kit structure

**Files:**
- Modify: `patterns/page-services.php`

**Interfaces:**
- Consumes: Task 1 assertions.
- Produces: seven-band portable block pattern with six replaceable cards and a native recent-Posts Query block.

- [ ] **Step 1: Replace the generic Services skeleton with the approved structure**

Use native Group/Columns/Buttons/Query blocks only. Keep every client-specific factual statement as an explicit `Thay bằng...` placeholder.

- [ ] **Step 2: Run the Y2 contract**

Run: `php tests/offline/y2-professional-page-kits-contract.php`

Expected: still FAIL only on missing service-specific CSS selectors.

- [ ] **Step 3: Commit pattern implementation**

Commit message: `feat: add approved services hub pattern`

### Task 3: Add token-driven Services presentation

**Files:**
- Modify: `assets/css/components/page.css`

**Interfaces:**
- Consumes: semantic classes from Task 2.
- Produces: scannable 3 x 2 service selection, orientation band, four-step process, trust band, knowledge cards and final CTA with responsive reflow.

- [ ] **Step 1: Add Services-scoped CSS**

All new rules must be rooted under `.aznet-theme-page-kit--services` and use existing `--aznet-theme-*` variables only.

- [ ] **Step 2: Run GREEN contract**

Run: `php tests/offline/y2-professional-page-kits-contract.php`

Expected: PASS.

- [ ] **Step 3: Run retained scope verification**

Run the existing Y2 workflow on the exact branch head.

Expected: offline ownership/portability PASS, WordPress 6.9 runtime PASS, desktop/mobile browser+a11y PASS, Classic Editor persistence PASS and Twenty Twenty-Five theme-switch byte preservation PASS.

- [ ] **Step 4: Commit implementation**

Commit message: `feat: style approved services hub`

### Task 4: Review and handoff

**Files:**
- No production mutation required.

**Interfaces:**
- Consumes: exact branch head and CI evidence.
- Produces: draft PR ready for design review; no merge or client publication.

- [ ] **Step 1: Inspect exact diff for ownership drift and unsupported claims**
- [ ] **Step 2: Record RED→GREEN evidence in PR body**
- [ ] **Step 3: Keep PR draft; stop before merge/live publication gate**
