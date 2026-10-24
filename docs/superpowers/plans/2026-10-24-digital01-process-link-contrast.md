# Digital 01 Process Link Accessibility — Bounded Fix Plan

> **For agentic workers:** Implement this plan with TDD, on an isolated Theme branch. Never infer live-site PASS from offline/static evidence.

**Goal:** Prevent the Digital 01 Process section CTA anchor from rendering as browser-default blue on a dark navy background, without changing WordPress content or optional provider ownership.

**Authority / Spec:** AZT-05 Articles 3, 6, 9; AZT-01 (Theme presentation ownership); [Issue #493](https://github.com/truongdinhnamaz/aznet-theme/issues/493); [PR #494 read-only L4 node diagnostic](https://github.com/truongdinhnamaz/aznet-theme/pull/494). CSS source: `assets/css/components/homepage-digital-01.css`.

**Architecture:** Keep this correction inside the already-owned Digital 01 stylesheet. A selector must apply to the actual descendant `a`, not merely the `.aznet-digital01__text-link` paragraph wrapper. Preserve all existing semantic tokens, native WordPress content, editor behavior and asset scope.

**Options:** (1) Change global link color (rejected: unrelated surfaces); (2) edit WordPress Page HTML (rejected: wrong owner and fragile); (3) Theme CSS rule scoped to Process anchor (chosen: narrowest reversible ownership-safe fix).

**Global constraints:** WordPress 6.9+, PHP 8.1+, hybrid PHP/theme.json; no new build stack, no additional provider dependency, no user content/WordPress settings mutation, no public contract change, no automatic merge/tag/Release/deploy.

**Review focus:** other Digital 01 sections unchanged; hover/focus still visible; Gutenberg default-anchor precedence; browser cache/minified CSS can mask source changes; valid color contrast on `--d01-navy`.

### Task 1 — Process link contrast (one TDD slice)

**Files:**
- Add `tests/offline/digital01-process-link-contrast-contract.php`
- Modify `scripts/verify-v1-core.sh` to retain the test
- Modify `assets/css/components/homepage-digital-01.css` with only a Process-specific anchor foreground rule

- [ ] **RED:** Run the new contract on the exact old CSS and observe a failure explicitly stating the Process anchor has no scoped foreground.
- [ ] **GREEN:** Add the minimal scoped selector for `.aznet-digital01__process .aznet-digital01__text-link > a` with foreground that measures at least WCAG AA 4.5:1 against Theme's declared navy token.
- [ ] **L1/L2:** New test and retained core suite PASS, CSS syntax/source shape and ownership reviewed.
- [ ] **L3/L4 (as available):** Isolated WordPress/Chromium with real nested anchor, focus, mobile; production cache/actual site remains separate UNKNOWN until independently verified.
- [ ] **Checkpoint:** Commit bounded branch changes; keep a Draft PR, with exact SHA, tests, rollback = revert CSS/test commits, and one Next.

**Exit:** Candidate L1/L2 PASS; only claim browser PASS if a fresh corresponding browser run exists. Production accessibility remains OPEN until installed-site CSS/cache and live browser are verified; release/deployment require separate owner approval.
