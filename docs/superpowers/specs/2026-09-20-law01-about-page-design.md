# Law 01 About Page — Approved Design Packet

**Status:** Approved for implementation
**Date:** 20/09/2026
**Base:** `main@ddf7cfe4b3e5f928bf431af77add3a561bc54ebe`
**Target product:** AZnet Theme only
**Target surface:** native WordPress About / Giới thiệu Page presentation

## Goal

Raise the About / Giới thiệu Page from the generic Page Kit skeleton to an editorial, trust-oriented inner-page composition that visually belongs to the Law 01 client experience without moving client facts, identity, legal claims or profile semantics into the Theme.

## Ownership

AZnet Theme owns:
- visual composition;
- section rhythm, spacing and hierarchy;
- responsive behavior;
- accessibility presentation;
- presentation classes and reusable block-pattern structure.

WordPress owns:
- Page title, excerpt, body, featured media and links;
- authored About content;
- native Page lifecycle and editing.

RootProfile remains authoritative for any organization/person identity or Profile semantics exposed through its public contracts. This slice does not read RootProfile storage, infer identity or create a parallel organization/profile store.

## Approved composition

1. **About hero / opening**
   - compact editorial opening, not an oversized marketing hero;
   - eyebrow + clear value statement + short supporting copy;
   - optional real media when authored;
   - no fabricated organization facts.

2. **Origin story**
   - two-column editorial band;
   - narrative + optional supporting visual;
   - collapses cleanly to one column.

3. **Values / practice principles**
   - three or four concise items;
   - flatter treatment with restrained borders/shadows;
   - values must be client-authored facts, never Theme claims.

4. **Working approach / process**
   - clear step-oriented composition;
   - Theme supplies presentation only;
   - actual process text remains authored content.

5. **Core capabilities**
   - bounded overview that links onward to Services;
   - must not duplicate or take ownership of the Services domain/content model.

6. **Team teaser**
   - short organization/team introduction + CTA to Team/Expertise;
   - no synthetic people or membership inference.

7. **Trust / evidence band**
   - room for verified credentials, documents, references or evidence;
   - no invented metrics, success rates, years, client counts or certifications.

8. **Final CTA**
   - concise, visually decisive closing band;
   - authored destination and wording remain WordPress content.

## Presentation intent

- editorial rather than card-heavy;
- generous white space;
- alternating surface rhythm;
- restrained use of borders and shadows;
- typography hierarchy aligned with the current Theme design system;
- use semantic Theme tokens in shared Page CSS; no hard-coded client truth or provider semantics;
- responsive reflow at tablet/mobile;
- visible keyboard focus and reduced-motion compatibility.

## Data / claim discipline

The pattern may contain replaceable editorial placeholders only. It must not ship fixed:
- names or people;
- addresses;
- phone/email identities;
- years of experience;
- certifications;
- customer counts;
- success rates;
- guarantees;
- factual service claims.

## Non-goals

- no RootProfile implementation or source change;
- no route takeover or slug/title/Page-ID authority heuristic;
- no custom About data store;
- no page builder or proprietary serialized format;
- no production deployment in this slice;
- no automatic mutation of an existing client Page.

## Acceptance

- About pattern exposes explicit semantic section classes for the eight approved bands;
- placeholders remain clearly replaceable and claim-safe;
- shared Page presentation stays token-driven;
- existing five Professional Page Kit role mapping remains unchanged;
- no provider/private-storage access;
- Y2 retained ownership/portability contract stays GREEN after the new RED requirement is implemented;
- deeper WordPress/browser/client-site verification remains a separate L3/L4 gate.
