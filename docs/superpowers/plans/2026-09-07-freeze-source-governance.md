# Source Governance Freeze Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a stable repository source-governance rule so accepted source documents guide implementation without being rewritten for ordinary code progress.

**Architecture:** Add one repository governance source that owns only source-lifecycle policy. Keep architecture, ownership, contracts, roadmap semantics and live implementation evidence in their existing owners; do not create a second domain or implementation truth store.

**Tech Stack:** GitHub Markdown documentation only. No PHP/CSS/template/runtime changes.

**Spec:** AZT-00 governance rules plus owner direction on 2026-09-07: source is a fixed implementation baseline; live code state belongs to GitHub evidence rather than repeated source-document revisions.

## Global Constraints

- AZnet Theme remains presentation owner only.
- AZT-01/02/03/04 remain authoritative by subject; this change does not redefine their architecture/domain decisions.
- Source documents are not implementation progress ledgers.
- GitHub `main`, branches, PRs, CI runs and evidence/checkpoints carry live implementation state.
- No source revision is created solely because code, tests, commits, PRs, package SHA or a milestone slice progressed.
- Source changes require an explicit hard gate when the source-owned architecture, ownership, public contract, roadmap/gate or governance decision itself materially changes.
- No production code changes in this slice.

---

### Task 1: Add stable repository source-governance policy

**Files:**
- Create: `docs/SOURCE_GOVERNANCE.md`

**Interfaces:**
- Consumes: existing AZT source ownership model and repository evidence practice.
- Produces: the repository rule for source freeze vs live implementation evidence.

- [ ] State that accepted source is frozen-by-default and guides code.
- [ ] State that GitHub evidence is the live implementation state.
- [ ] Clarify AZT-03 baseline/provenance is not a continuously rewritten commit ledger after freeze.
- [ ] Define the only conditions that may reopen source: material source-owned decision change + explicit owner approval.
- [ ] Explicitly prohibit generating or handing off revised source files merely for implementation progress.

### Task 2: Make the governance discoverable

**Files:**
- Modify: `README.md`

**Interfaces:**
- Consumes: `docs/SOURCE_GOVERNANCE.md`.
- Produces: a stable pointer from repository entry documentation.

- [ ] Add a short Source governance section linking to `docs/SOURCE_GOVERNANCE.md`.
- [ ] Keep README descriptive, not a competing source of architecture/governance truth.

### Task 3: Verify and open review gate

**Files:**
- Verify branch diff only.

**Interfaces:**
- Produces: reviewable documentation-only PR.

- [ ] Confirm no PHP/CSS/template/theme.json/integration production path changed.
- [ ] Confirm README points to the governance source.
- [ ] Confirm governance text separates frozen source from live GitHub evidence and contains the hard-gate exception.
- [ ] Open PR to `main`; do not merge without explicit owner approval.
