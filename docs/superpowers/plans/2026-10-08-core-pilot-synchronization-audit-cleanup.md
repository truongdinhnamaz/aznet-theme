# Core/Pilot Synchronization Audit & Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make AZnet Theme Core/preset boundaries consistent with the current GitHub source of truth, isolate legacy pilot-specific compatibility, and prove shared presentation changes against Law 01, Rèm 01, Industrial 01 plus the synthetic fourth-template fixture.

**Architecture:** Follow AZT-02 D-042/D-043/D-048 as canonical. Generic Core must not contain site-specific Page IDs, Rèm Quốc Anh naming, or industry branching; legacy pilot compatibility may remain only in an explicitly isolated preset/compatibility asset while migration is incomplete. WordPress/provider data ownership remains unchanged.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, hybrid PHP theme + theme.json, CSS, GitHub Actions, offline PHP contracts, Playwright/axe where existing workflows provide L4 coverage.

**Spec:** `docs/source/AZT-02-architecture.md`, `docs/source/AZT-04-roadmap-qa-decisions.md`, `docs/source/AZT-05-product-constitution.md`.

## Global Constraints

- Canonical implementation/state evidence comes from current GitHub `main`; source documents under `docs/source/` remain authoritative for the topics they own.
- SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.
- Generic Core must not branch on Law 01, Rèm 01, Industrial 01, slug/title/URL/Page ID, or provider-private storage.
- D-042: shared-Core/shared-presentation changes require peer-pilot regression for Law 01, Rèm 01 and Industrial 01.
- D-043: synthetic fourth-template participation remains mandatory for extension-model closure.
- Production pilot rollout is separate from repository verification and requires backup/rollback plus fresh per-site runtime/browser evidence.
- Preserve current public presentation unless a separately scoped presentation slice explicitly changes it.

## Review Focus

- Generic Core accidentally retaining `.page-id-*` or `rqa-*` selectors.
- Moving legacy CSS without preserving Curtain 01 asset loading on non-Homepage Pages.
- Shared Page/full-width changes regressing Law 01 or Industrial 01.
- Legacy compatibility becoming a permanent semantic owner instead of an isolated migration shim.
- Version equality being mistaken for package-byte identity across pilots.

---

### Task 1: Core hygiene RED contract and compatibility boundary

**Files:**
- Create: `tests/offline/pilot-presentation-core-hygiene-contract.php`
- Modify: `tests/offline/rqa-quote-page-balance-contract.php`
- Modify: `tests/offline/rqa-contact-page-presentation-contract.php`
- Modify: `scripts/verify-v1-core.sh`

**Interfaces:**
- Consumes: AZT-02 D-042/D-043 core/preset boundary.
- Produces: a failing contract that forbids pilot/site selectors from generic `page.css` and requires RQA legacy presentation to live outside generic Core.

- [x] **Step 1: Write the failing hygiene contract**
  Assert `assets/css/components/page.css` contains neither `.page-id-` nor `rqa-`. Assert the dedicated compatibility asset exists and is Curtain 01-scoped.

- [x] **Step 2: Update existing RQA contracts to target the compatibility asset**
  Preserve the current visual requirements while removing the expectation that RQA rules live in generic `page.css`.

- [x] **Step 3: Wire the contract into `scripts/verify-v1-core.sh`**
  Place it in the Y1/Page verification group before the retained RQA presentation contracts.

- [x] **Step 4: Run/observe RED**
  Expected: FAIL because current `page.css` still contains Page-ID/RQA selectors and the compatibility asset does not yet exist.

- [x] **Step 5: Commit RED evidence**
  Commit only tests/verification wiring.

### Task 2: Minimal GREEN — isolate legacy RQA presentation from generic Core

**Files:**
- Create: `assets/css/compatibility/curtain01-rqa-pages.css`
- Modify: `assets/css/components/page.css`
- Modify: `inc/theme/assets.php`
- Test: Task 1 contracts plus retained Curtain/Page contracts.

**Interfaces:**
- Consumes: Task 1 hygiene contract.
- Produces: generic `page.css` free of RQA/Page-ID selectors; explicit Curtain 01 compatibility asset loaded only when Curtain 01 is the active visual preset on native Page surfaces.

- [x] **Step 1: Move the existing RQA quote/contact CSS byte-for-byte into the compatibility asset**
  Do not redesign or reinterpret WordPress content.

- [x] **Step 2: Add a surface-aware enqueue**
  Load the compatibility asset only when native Page assets render and `visual_preset() === 'curtain-01'`; depend on `aznet-theme-page`. No slug/title/Page-ID PHP detection.

- [x] **Step 3: Run Task 1 and retained Page/Curtain contracts**
  Expected: GREEN with no RQA/Page-ID selector remaining in generic `page.css`.

- [x] **Step 4: Commit minimal GREEN**
  Keep migration reversible as one bounded commit.

### Task 3: Shared Page/preset regression closure

**Files:**
- Modify only tests/workflow wiring if an uncovered regression is found.
- No production change unless a concrete failing regression requires the shallowest fix.

**Interfaces:**
- Consumes: Task 2 exact candidate.
- Produces: D-042/D-043 repository evidence for Law 01, Rèm 01, Industrial 01 and synthetic fourth-template participation.

- [ ] **Step 1: Run V1 Core CI and D-027 retained regression on exact head**
- [ ] **Step 2: Run Y1 Page Experience and D-027 L4**
- [x] **Step 3: Run/confirm `template-three-pilot-regression-contract.php` and `template-fourth-template-proof-contract.php`**
- [x] **Step 4: Verify no unexpected Core industry branch/selectors were introduced**
- [ ] **Step 5: Commit only if regression repair is required**

### Task 4: Pilot drift inventory and rollout decision

**Files:**
- Create: `docs/evidence/CORE_PILOT_SYNC_AUDIT_20261008.md`
- Update authoritative source only if an architecture/roadmap/current-baseline fact owned by that source truly changes.

**Interfaces:**
- Consumes: exact repository candidate plus fresh authenticated read-only pilot inventory.
- Produces: explicit per-pilot status: Theme version, preset, settings schema, known compatibility state, byte-identity status, runtime/browser status, and safe Next.

- [x] **Step 1: Record fresh Law 01 / Rèm 01 / Industrial 01 read-only inventory**
- [x] **Step 2: Distinguish version alignment from byte identity**
- [x] **Step 3: Identify which pilots require update versus only verification**
- [x] **Step 4: Preserve backup/rollback and per-site approval gates before production mutation**
- [x] **Step 5: Commit evidence**

### Task 5: Review and merge readiness

**Files:** none unless review finds an issue.

**Interfaces:**
- Consumes: Tasks 1–4.
- Produces: merge-ready branch or a precise BLOCKED state.

- [ ] **Step 1: Request whole-branch code review**
- [ ] **Step 2: Fix Critical/Important findings with RED→GREEN if production behavior changes**
- [ ] **Step 3: Freshly rerun required gates**
- [ ] **Step 4: Open/refresh PR with exact evidence**
- [ ] **Step 5: Stop at merge/deployment hard gate unless prior owner approval clearly covers that exact action**


## Execution ledger

- RED evidence: PR #429 V1 Core `static-contracts` failed on `FAIL: generic page.css still contains pilot-specific selector .page-id-` at RED head `70c09bd9252675eef88a347b8c86737bd017259a`.
- GREEN implementation candidate: `page.css` pilot block moved to `assets/css/compatibility/curtain01-rqa-pages.css`; scoped enqueue added in `inc/theme/assets.php`. Fresh GREEN verification is pending on this exact branch head.

- Task 2 Ruling: the initially planned separate `curtain01-rqa-pages.css` + pilot-named PHP enqueue left site-specific knowledge in generic `inc/theme/assets.php`. Final GREEN instead places the legacy authored-marker rules inside the existing Curtain 01 preset stylesheet and removes the pilot-specific Core enqueue. Cost if wrong: the Curtain preset CSS carries the legacy compatibility payload on all Curtain routes until a separately approved WordPress-content marker migration retires it.
- Task 2 final GREEN: exact production-code head `630e33d3f30f97747d002175b14d3d9f08e04585`; temporary verification run `37703678012`, job `113072869447` SUCCESS with reusable V1, RQA retained contracts, D-043 C7 and C8 PASS.
- Task 4 evidence: `docs/evidence/CORE_PILOT_SYNC_AUDIT_20261008.md`; AZT-03 v0.85 adds only the current-state reconciliation owned by baseline/provenance.
