# GitHub Canonical Source Migration — 2026-09-07

## Result

AZnet Theme product source has been normalized into reviewable Markdown under `docs/source/` on branch `docs/v1-constitution-source-20260907`.

## Source set

- AZT-05 Product Constitution v1.0
- AZT-00 Governance v0.4
- AZT-01 Product Charter v0.3
- AZT-02 Architecture v0.4
- AZT-03 Baseline/Provenance v0.17
- AZT-04 Roadmap/QA/Decisions v0.21
- AZT-EXEC-MAP v0.13
- Source README + manifest

## Governance change

Once this source PR is merged, canonical source medium becomes Markdown in `docs/source/` on `main`. DOCX/PDF are derived exports only. GitHub implementation evidence remains evidence and does not silently amend product source.

## Production boundary

This migration changes documentation/source governance only. No Theme production PHP/CSS/JS/template/theme.json behavior is changed.

## Implementation gate

Production work governed by the new constitution waits for source acceptance. Exact implementation next after source acceptance is G0: freeze core-v1.0 scope and inventory the remaining Theme-owned cleanup/release blockers.
