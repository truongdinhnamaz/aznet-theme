# AZnet Theme Canonical Source

**Repository:** `truongdinhnamaz/aznet-theme`  
**Status:** authoritative product source set once merged to `main`  
**Rule:** source lives as reviewable text in GitHub; DOCX/PDF are derived export artifacts only.

## Authority

The canonical AZnet Theme source set is maintained in this directory so decisions, history, review and provenance live beside implementation without turning implementation evidence into product truth.

Authority by subject:

1. `AZT-05-product-constitution.md` — product constitution; highest internal authority for product invariants and core release constitution.
2. `AZT-00-governance.md` — source priority, change control and documentation governance.
3. `AZT-01-product-charter.md` — product scope, ownership, goals and non-goals.
4. `AZT-02-architecture.md` — architecture, dependency policy and public integration contracts.
5. `AZT-03-baseline-provenance.md` — baseline/provenance rules and registered provenance evidence.
6. `AZT-04-roadmap-qa-decisions.md` — roadmap, QA gates and accepted decisions/open questions.
7. `AZT-EXEC-MAP.md` — derived execution map; never overrides authoritative AZT sources.

Cross-product/domain authority remains outside this Theme source set. `ECOSYSTEM-ARCH-01` and the authoritative source of each external domain continue to govern their own semantics and cross-product ownership boundaries.

## GitHub as canonical source medium

- The Markdown files in `docs/source/` on canonical `main` are the authoritative AZnet Theme source documents.
- Git history is the version/provenance record. Each document also retains its human-readable source version in the document body.
- A source change is made by pull request, reviewed against the owning source document, then merged only after the required owner approval.
- DOCX/PDF exports may be generated for reading, archival or handoff, but they are derived artifacts. They must not silently supersede the Markdown source on `main`.
- GitHub implementation state (commits, PRs, Actions, package hashes, runtime evidence) remains evidence. It changes source only when a source-owned rule actually changes and the corresponding source PR is approved.

## Source change gate

Before production code implements a new or changed product rule:

1. Identify the authoritative source owner for that rule.
2. Amend the relevant file in `docs/source/`.
3. If the change affects product invariants/release constitution, amend `AZT-05` first or in the same source PR.
4. Record affected architecture/roadmap documents only when their owned content changes.
5. Obtain the required owner approval and merge the source PR.
6. Only then implement production behavior governed by the new rule.

This keeps the constitution and source of truth reviewable, diffable and inseparable from repository provenance.