# Digital 01 Three Commercial Paths — Implementation Plan

> For agentic workers: REQUIRED SUB-SKILL: use superpowers:executing-plans task-by-task. Keep independent tests/red-green.

**Goal:** Display WordPress-owned three equal paths under the Brand Light Hero on draft Page 43515 while preserving native commerce and published site state.

**Architecture:** Gutenberg Group content stays in WordPress; existing Brand Light opt-in stylesheet gains a reusable presentation-only card/grid pattern; isolated WordPress fixture and browser tests verify structure.

**Tech stack:** WordPress 6.9+, PHP 8.1+, CSS only, Playwright/axe, WPVibe native content-edit.

**Spec:** docs/superpowers/specs/2026-10-26-digital01-three-commercial-paths-design.md

## Global constraints
- Canonical repo truongdinhnamaz/aznet-theme, isolated draft PR #516, exact pre-change head 28f965d5d85968b4a3e6f2da12a7f24fc968f672; base main ee765af2dc0af5f11d65bf779ac2f244b43475a3.
- Existing 41/41 success retained as historical exact-head evidence; new changes require fresh verification, not inherited PASS.
- Source owns data, Theme owns presentation. Never mutate Woo products/prices, ConvertFlow/RootProfile or published Front Page #2.
- No production deploy or main merge without separate approval.

## Task 1: RED three-path regression
**Files:** tests/offline/digital01-brand2026-presentation-contract.php, tests/runtime/digital01-brand2026-seed.php, tests/browser/digital01-brand2026-l4.mjs.
- [ ] Add offline scoped selector/3-card responsiveness assertions.
- [ ] Add three WordPress native Group path cards to disposable seed immediately below Hero.
- [ ] Add browser assertions: 3 cards with links, correct order, all equal columns on desktop, single column <=900px, no overlap/overflow and contrast/axe. Keep existing four service card assertions.
- [ ] Run dedicated workflow on test-only commit; require expected RED for missing new CSS selectors/rules.

## Task 2: Minimal GREEN CSS
**Files:** assets/css/components/homepage-digital-01-brand-2026.css only.
- [ ] Add scoped path-card, group and responsive CSS under opt-in Brand Light selector; no URL, provider storage, Woo state or business logic.
- [ ] Run dedicated workflow, inspect per-case JSON and screenshots; fix only reproduced failures with fresh RED regression.
- [ ] Confirm broader L1/L2 and representative Header/Woo regressions remain GREEN.

## Task 3: WordPress draft only
**Files:** WordPress-owned Page #43515 draft content, not the Theme repo.
- [ ] Confirm status draft, original post_content marker matches once, and live Front Page #2 unchanged.
- [ ] Insert one native Gutenberg section between Hero and Services using /wpvibe/v1/content/edit match-once without full-page overwrite.
- [ ] Read back Page #43515: exactly 3 cards, expected published URLs, ordered between Hero and Services, Gutenberg delimiters preserved, status draft. Record WordPress revision/recovery checkpoint.
- [ ] If match-once patch fails, do not replace whole page; re-inspect exact source.

## Task 4: Checkpoint
- [ ] Record final branch SHA, CI runs, WordPress readback, remaining authenticated browser QA, rollback and a single Next in PR #516.
- [ ] Keep PR draft and Page #43515 draft. Next: authenticated visual comparison and distinct release approval.