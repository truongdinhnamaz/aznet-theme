# Digital 01 Brand Light 2026 Forward-port Implementation Plan

> For agentic workers: use superpowers:executing-plans task-by-task; retain the already verified PR #495 source and avoid repeating unchanged QA.

**Goal:** Carry the approved opt-in Brand Light 2026 presentation from draft PR #495 to current AZnet Theme main as a reversible, unmerged candidate.

**Architecture:** Theme-only CSS and public WordPress Page opt-in; WordPress owns draft Page 43515, menu/logo/content. WooCommerce and ConvertFlow retain business domains. Site production remains unchanged.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, semantic Theme CSS, isolated WordPress/browser GitHub Actions; no new build stack.

**Spec:** docs/superpowers/specs/2026-10-26-digital01-brand-light-2026-forwardport-design.md

## Global constraints
- Work on work/digital01-brand-light-2026-forwardport-20261026 from main@ee765af2; do not overwrite PR #495 or main.
- Preserve WordPress front page ID 2 and published 1.3.96 site until separate explicit approval.
- No direct plugin storage, no domain/business copy; no Page-ID/slug detection; theme switching and plugin absence fail-soft.
- Reuse exact historical PR #495 assets/tests where source-equivalent, record fresh branch test evidence separately.

## Review focus
- Non-opted-in Page, posts, Woo and drafts lacking valid class must never enqueue branded CSS.
- Opted-in Page missing native logo must retain a legible text fallback.
- Navigation submenu children must not inherit the red top-level CTA rule; keyboard focus/dropdowns must remain usable.
- Mobile panel and Header at breakpoint boundaries must not overflow or hide actions.
- WP Rocket stale CSS must not be mistaken for a source-code defect; verify the served bytes separately at release.

## Task 1 — Preserve prior RED contract on new main
Files: tests/offline/digital01-brand2026-presentation-contract.php; .github/workflows/digital01-brand-2026.yml.
- [ ] Copy existing PR #495 contract/workflow to the isolated branch without changing assertions.
- [ ] Run CI with only test/workflow: expected RED because opt-in CSS and asset loader are absent on current main.
- [ ] Record exact RED run and reason; never report production PASS.

## Task 2 — Minimal GREEN forward-port
Files: assets/css/components/homepage-digital-01-brand-2026.css; inc/theme/assets.php; template-parts/header/utility-navigation.php.
- [ ] Copy CSS bytes from PR #495 unchanged unless fresh tests prove a failure.
- [ ] Add function digital01_brand2026_page_active(): bool and enqueue_digital01_brand2026_asset(?string $version = null): void using public WordPress/Page class; enqueue only on opt-in.
- [ ] Scope utility menu depth=2 solely to the opt-in Page, otherwise depth=1.
- [ ] Run fresh Core/contract/WordPress/browser regressions and record exact evidence; fix only reproduced causes.

## Task 3 — Candidate preview / review
Files: optional WPVibe draft Theme overlay, no production files.
- [ ] Keep Page 43515 in Draft and Page 2 live. Clone active Theme 1.3.96 into isolated WPVibe draft only if no pre-existing draft.
- [ ] Reproduce candidate styling in draft without mutating published Page content or global nav/logo, then acquire preview URL.
- [ ] Attempt authenticated combined Page 43515 + Theme preview; if unavailable report BLOCKED/UNKNOWN rather than claiming pixel parity.

## Task 4 — Recoverable checkpoint
Files: docs/evidence/... (new checkpoint), PR description.
- [ ] Record code head, CI pass/fail per layer, WordPress draft state, source SHA and rollback.
- [ ] Keep draft PR open; no main merge, release, WPVibe publish or front-page/menu/logo activation.
- [ ] Exact NEXT after technical PASS: owner preview approval and separate release/deployment authorization.