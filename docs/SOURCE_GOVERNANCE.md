# AZnet Theme Source Governance

**Status:** ACCEPTED / FROZEN-BY-DEFAULT  
**Repository:** `truongdinhnamaz/aznet-theme`

## 1. Core rule

**SOURCE LIVES IN GITHUB. GITHUB ALSO RECORDS IMPLEMENTATION EVIDENCE. DO NOT CONFUSE THE TWO.**

Canonical AZnet Theme product source is maintained as reviewable Markdown under `docs/source/` on canonical `main`. Git history provides source provenance and review history. Implementation facts continue to live in code history, PRs, Actions, packages and `docs/evidence/*`.

The normal execution loop is:

`read canonical source -> resolve latest valid GitHub implementation state -> code -> test/verify -> commit/PR/evidence -> continue`

Do not rewrite source merely because implementation progressed.

## 2. Canonical source set

- `docs/source/AZT-05-product-constitution.md` — highest internal authority for product invariants and core release constitution.
- `docs/source/AZT-00-governance.md` — source priority, change control and documentation governance.
- `docs/source/AZT-01-product-charter.md` — product scope, ownership, goals and non-goals.
- `docs/source/AZT-02-architecture.md` — architecture, dependency policy and public integration contracts.
- `docs/source/AZT-03-baseline-provenance.md` — baseline/provenance authority.
- `docs/source/AZT-04-roadmap-qa-decisions.md` — roadmap, QA gates and accepted decisions.
- `docs/source/AZT-EXEC-MAP.md` — derived execution map; never overrides the authoritative AZT files.

Cross-product and external-domain semantics remain governed by their own source owners, including `ECOSYSTEM-ARCH-01` where applicable.

## 3. Authority vs evidence

Being in the same repository does not give every file equal authority.

**Source:** product rules, ownership, architecture, contracts, roadmap/release governance.  
**Evidence:** commits, PRs, CI runs, screenshots, package hashes, runtime findings, debug notes and `docs/evidence/*`.

Evidence may prove or invalidate implementation. It does not silently amend source.

## 4. Frozen-by-default

The following do not require a source version change by themselves:

- feature/bugfix commits;
- branch or PR state changes;
- RED/GREEN tests;
- runtime/browser/integration runs;
- package rebuilds, hashes or file counts;
- a slice moving ACTIVE -> PASS when the governing rule did not change.

When the rule did not change, record evidence and continue implementation.

## 5. Reopen source only at a true source gate

Source is amended when its owned content materially changes, for example:

- product nature, independence or core release constitution;
- product/domain/presentation ownership;
- architecture or dependency policy;
- public/versioned integration contracts;
- support floors or naming family;
- roadmap/gate semantics or release governance.

Before production code implements a new rule, amend the owning source file first and obtain the required owner approval.

## 6. Constitution-before-code

Any change that can alter the nature of AZnet Theme, product independence, ownership invariants or v1.x release constitution must first be consistent with `AZT-05-product-constitution.md`.

If the Constitution itself must change, amend AZT-05 and all directly affected owner documents before production implementation.

## 7. Source changes use Git workflow

1. Work on a source/docs branch.
2. Change only the source owner documents whose rules actually changed.
3. Review the diff and source impact.
4. Open a PR.
5. Merge only after the required owner approval.
6. Production code governed by the new rule starts only after the source gate is accepted.

## 8. DOCX/PDF status

DOCX/PDF versions are derived exports for reading, archival or handoff. They are not authoritative once the corresponding source is maintained in `docs/source/` on canonical `main`.

Do not edit an export and treat it as a source change. Apply the change to GitHub Markdown source, review/merge it, then regenerate exports only when useful.

## 9. Live implementation state

Resolve the latest valid implementation state from GitHub in this order:

1. canonical `main` head;
2. relevant work/feature branch and PR state;
3. fresh CI/runtime/browser/integration evidence;
4. release/package artifacts and hashes when byte identity matters;
5. repository evidence/checkpoint files.

An older source checkpoint must not override fresher implementation evidence when the source-owned rule did not change.

## 10. Implementation discipline

- Theme owns presentation; external domain owners keep authoritative data/state.
- Integrations use public/versioned contracts only.
- Provider absence/error/version mismatch fails soft/safe.
- No private storage reads, domain-logic cloning or parallel truth stores.
- No authoritative identity/routing inference from slug/title/Page ID/URL heuristics.
- QA layers remain distinct; claim only the layer freshly evidenced.
- Production feature/bugfix behavior uses RED -> minimal GREEN -> regression where applicable.
- Main merge, release/deploy, takeover and destructive retirement remain approval-gated where source says so.

## 11. Default behavior

**Keep source stable; move implementation forward. When a true product rule changes, change the canonical GitHub source first.**
