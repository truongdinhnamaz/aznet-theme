# P5 Final Candidate Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Produce fresh, exact-canonical technical evidence for the AZnet Theme 1.1.0 final candidate package without creating a tag, GitHub Release, deployment, provider certification, or production-site mutation.

**Architecture:** Use a temporary GitHub Actions runner on an isolated P5 branch, but explicitly check out the canonical `main` merge SHA `f0b2d581b16a663e4b422234e6ac15568bcc7985` before every verification step. Reuse the existing v1 core verification, deterministic package builder, WordPress 6.9 support-floor setup, browser/a11y harnesses, and WordPress-native lifecycle semantics. Persist only plan/evidence/source-state documentation; remove the temporary runner before final review.

**Tech Stack:** GitHub Actions, PHP 8.1, WordPress 6.9, MySQL 8, WP-CLI, Python 3 deterministic ZIP builder, Node 22, Playwright 1.55.0, axe-core 4.10.2.

**Spec:** `docs/source/AZT-04-roadmap-qa-decisions.md` — P5 final-candidate technical verification/package gates; `docs/source/AZT-EXEC-MAP.md` — current Exact Next.

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Theme remains PRESENTATION OWNER; WordPress and providers retain their data/domain ownership.
- WordPress support floor: 6.9+; PHP support floor: 8.1+.
- Theme metadata remains `1.1.0` unless separately source-approved; this slice does not change metadata.
- No production PHP/CSS/JS behavior change is required or permitted unless a fresh P5 verification exposes a Theme-owned defect and that defect is handled as a separate RED→GREEN bugfix slice.
- No private provider API/storage reads, no provider-domain heuristics, no parallel domain state.
- No Git tag, GitHub Release, final production deployment, provider L5 certification, or destructive pilot mutation.
- Final-candidate verification must be fresh against canonical `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`; retained earlier PASS is supporting history only.

---

### Task 1: Exact canonical identity and reusable core verification

**Files:**
- Create temporarily: `.github/workflows/_tmp-p5-final-candidate.yml`
- Reuse: `scripts/verify-v1-core.sh`
- Reuse: `tests/browser/g-core-l4.mjs`

**Interfaces:**
- Consumes: canonical merge SHA `f0b2d581b16a663e4b422234e6ac15568bcc7985`.
- Produces: exact checkout SHA/tree, static/contract PASS, clean WordPress runtime/browser/a11y evidence.

- [ ] **Step 1: Add temporary push runner that checks out the exact canonical SHA**

The workflow must use `actions/checkout@v4` with `ref: f0b2d581b16a663e4b422234e6ac15568bcc7985`, `fetch-depth: 0`, then assert `git rev-parse HEAD` equals that SHA and record `HEAD^{tree}`.

- [ ] **Step 2: Run reusable v1 core verification**

Run:
```bash
bash scripts/verify-v1-core.sh
```
Expected: exit 0 with all static/contracts green on the exact canonical checkout.

- [ ] **Step 3: Run clean WordPress 6.9 zero-plugin runtime/browser gate**

Install WordPress 6.9 + MySQL 8, activate only the exact checked-out AZnet Theme, create native Page/Post/Category/Menu fixtures, start PHP runtime, run the existing `tests/browser/g-core-l4.mjs`, and reject Theme-caused `PHP Fatal error|Warning|Parse error|Uncaught` markers.

Expected: zero active third-party plugins, route smoke PASS, browser/a11y PASS, zero Theme-owned blocking failures.

### Task 2: Deterministic final package and exact byte hygiene

**Files:**
- Reuse: `scripts/build-release-package.py`
- Reuse contract semantics from: `.github/workflows/v1-release-candidate.yml`

**Interfaces:**
- Consumes: exact canonical source tree from Task 1.
- Produces: two byte-identical builds, final `aznet-theme-1.1.0.zip`, SHA-256, manifest, exact source/package comparison, packaged PHP lint count.

- [ ] **Step 1: Cross-check metadata**

Read `Version:` from `style.css` and `AZNET_THEME_VERSION` from `functions.php`; require exact equality and exact value `1.1.0`.

- [ ] **Step 2: Build twice and compare bytes**

Run twice:
```bash
python3 scripts/build-release-package.py --source . --output <candidate-path> --version 1.1.0
```
Then run `cmp` on the two ZIPs.
Expected: byte-identical output.

- [ ] **Step 3: Verify ZIP hygiene and exact packaged bytes**

Require one top-level `aznet-theme/` directory; reject `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, and `aznet-preview.png`; compare every packaged file byte-for-byte against the exact canonical production file set.

Expected: file-set and byte comparison PASS.

- [ ] **Step 4: Lint every packaged PHP file**

Run `php -l` over all packaged PHP files and record the count.
Expected: zero lint failures and a non-zero file count.

### Task 3: Exact-package clean WordPress and lifecycle rollback reference

**Files:**
- Reuse: `tests/runtime/d027-standalone-core.php`
- Reuse: `tests/browser/d027-standalone-core.spec.js` where compatible with final exact-package fixtures.
- Reuse lifecycle semantics from: `.github/workflows/d027-standalone-core-package.yml` and `.github/workflows/g-core-lifecycle.yml`.

**Interfaces:**
- Consumes: exact final ZIP from Task 2.
- Produces: clean-install activation/render evidence, native-content continuity sentinels, theme-switch rollback reference, switch-back verification.

- [ ] **Step 1: Install exact ZIP on clean WordPress 6.9 with zero active plugins**

Use WP-CLI to install WordPress 6.9, install the exact candidate ZIP, activate `aznet-theme`, deactivate all plugins, and assert the active-plugin list is empty.

Expected: candidate active as `aznet-theme` version `1.1.0`.

- [ ] **Step 2: Create WordPress-owned continuity sentinels**

Create native Page/Post/Category/Menu fixtures and a sentinel post meta value. Record object IDs before theme switching.

Expected: all sentinels exist before lifecycle transition.

- [ ] **Step 3: Verify exact-package runtime/browser/a11y**

Start the package-backed runtime and execute the existing D-027/core browser matrix against package bytes, rejecting Theme-owned critical/serious axe, horizontal overflow, console/page errors, and PHP fatal/warning/parse/uncaught markers.

Expected: PASS at the exact-package L3/L4 boundary.

- [ ] **Step 4: Switch to a bundled WordPress default theme and verify WordPress-owned data survives**

Activate an installed `twenty*` theme, then verify the recorded Page/Post/Category/Menu/meta sentinels remain unchanged.

Expected: native data continuity PASS while AZnet Theme is inactive.

- [ ] **Step 5: Switch back to exact AZnet Theme package and reverify**

Reactivate `aznet-theme`, confirm the same sentinels and runtime output remain valid, and record the final active Theme/version.

Expected: rollback/switch-back reference PASS.

### Task 4: Preserve evidence and remove temporary runner

**Files:**
- Create: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`
- Delete before final diff: `.github/workflows/_tmp-p5-final-candidate.yml`

**Interfaces:**
- Consumes: workflow run IDs, job results, artifact IDs/digests, package SHA-256, exact canonical SHA/tree.
- Produces: durable audit evidence without retaining a one-off runner.

- [ ] **Step 1: Record fresh evidence**

Document canonical SHA/tree, Theme version, workflow run, candidate ZIP SHA-256, artifact digest, deterministic double-build result, source/package exact compare, lint, clean WordPress, zero-plugin, exact-package browser/a11y, and lifecycle rollback/switch-back results.

- [ ] **Step 2: Remove temporary workflow**

Delete `.github/workflows/_tmp-p5-final-candidate.yml` and compare final branch to canonical main.
Expected: final diff contains only plan/evidence/source documentation; no production PHP/CSS/JS and no temporary workflow.

### Task 5: Reconcile source state to technical-candidate gate

**Files:**
- Modify: `docs/source/AZT-03-baseline-provenance.md`
- Modify: `docs/source/AZT-04-roadmap-qa-decisions.md`
- Modify: `docs/source/AZT-EXEC-MAP.md`
- Modify: `docs/source/SOURCE_MANIFEST.md`

**Interfaces:**
- Consumes: successful fresh P5 technical evidence.
- Produces: authoritative state that separates technical candidate readiness from publication/deployment approval.

- [ ] **Step 1: Record P5 technical verification result**

If all technical gates PASS, set P5 technical state to `TECHNICAL CANDIDATE PASS / PUBLICATION GATED` (or equivalent existing source terminology) and record the exact package SHA/artifact provenance.

- [ ] **Step 2: Preserve hard gates**

Explicitly retain separate owner gates for Git tag, GitHub Release, and final production deployment. Do not infer provider L5 certification.

- [ ] **Step 3: Open a docs/evidence-only PR**

Require final compare against `main` to show no production Theme bytes and no one-off workflow. Run the normal docs-triggered retained checks and stop at merge approval.

## Self-review

- Spec coverage: exact-main, deterministic double-build, exact unzip/source compare, packaged PHP lint, clean WordPress 6.9 activation, zero-plugin Core, exact-package browser/a11y, rollback/theme-switch reference, artifact provenance, and publication hard gates are all represented.
- Placeholder scan: no TBD/TODO/implement-later steps.
- Ownership: Theme presentation boundary is unchanged; WordPress owns fixtures/data/lifecycle; providers remain optional and external.
- Failure rule: any technical failure must be reduced to the shallowest reproducible layer before production behavior is changed; do not paper over a failure with documentation or fixtures.
