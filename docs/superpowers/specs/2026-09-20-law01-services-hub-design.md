# Law 01 Services Hub — Approved Design Packet

**Status:** Approved for implementation
**Date:** 20/09/2026
**Base:** `main@ddf7cfe4b3e5f928bf431af77add3a561bc54ebe`
**Target product:** AZnet Theme only
**Target surface:** native WordPress Services / Dịch vụ Page presentation

## Goal

Turn the generic Services Page Kit into a clear service hub that helps visitors identify the legal area relevant to them, understand the engagement flow, continue reading useful content, and reach a consultation CTA without moving service facts, identity, routing, conversion state, or provider semantics into the Theme.

## Ownership

AZnet Theme owns:
- visual composition, hierarchy, spacing, responsive behavior and accessibility presentation;
- reusable Page Kit section classes;
- token-driven card/grid/process/trust/CTA presentation.

WordPress owns:
- Page title, excerpt, body, links and editorial lifecycle;
- authored service names, descriptions and destination links;
- native Post query used for the knowledge section.

ConvertFlow remains owner of any future Service Journey semantics, resolver/state, validation, conversion behavior or analytics. This slice is presentation-only and does not implement a Service Journey.

## Approved composition

1. **Opening / service orientation**
   - compact intro;
   - eyebrow + clear question/value statement + supporting copy;
   - no oversized marketing hero and no fabricated factual claims.

2. **Service selection**
   - six replaceable service cards;
   - desktop intent: 3 x 2;
   - each card can carry service name, short problem-oriented description and destination link;
   - card content remains WordPress-authored.

3. **Unsure where to start**
   - a distinct orientation band for visitors who cannot classify their issue;
   - concise explanation + authored contact CTA;
   - no Theme-side legal triage or resolver.

4. **Working process**
   - four presentation steps: receive, review, propose, accompany;
   - labels are editorial placeholders and must be replaced/verified by the client.

5. **Trust / practice principles**
   - restrained presentation for clear communication, practical options, confidentiality and responsible accompaniment;
   - Theme ships placeholders only and does not assert client facts.

6. **Knowledge / continue reading**
   - WordPress-native Query block for recent Posts;
   - no custom search/recommendation engine and no domain inference.

7. **Final CTA**
   - decisive closing band;
   - destination and wording remain authored WordPress content.

## Presentation intent

- visually belongs to the Law 01 inner-page family while remaining token-driven;
- burgundy/gold client styling is not hard-coded into the generic Page Kit;
- generous white space, restrained borders/shadows;
- service cards are easier to scan than Homepage teaser cards;
- desktop 3 x 2 service grid, clean tablet reflow, one-column mobile;
- clear keyboard focus and no motion dependency.

## Data / claim discipline

The pattern may contain replaceable editorial placeholders only. It must not ship fixed:
- law-firm names or people;
- addresses, phone numbers or email identities;
- success rates, client counts, years, certifications or guarantees;
- authoritative service routing or legal triage results.

## Non-goals

- no RootProfile implementation/source change;
- no ConvertFlow Service Journey implementation;
- no slug/title/Page-ID authority heuristic;
- no custom service data store;
- no direct plugin storage/private API access;
- no live client Page mutation, production deployment or merge in this slice.

## Acceptance

- Services pattern exposes explicit semantic classes for all seven approved bands;
- six service card placeholders are present and clearly replaceable;
- recent-content section uses a WordPress-native Query block;
- shared Page CSS is semantic-token only and includes responsive service-grid behavior;
- Professional Page Kit registry remains unchanged;
- Y2 ownership/portability contract is RED before implementation, then GREEN;
- deeper runtime/browser/client-site publication remains a separate gate.
