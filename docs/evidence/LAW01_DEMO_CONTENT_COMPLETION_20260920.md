# Law 01 Demo Content Completion — 20/09/2026

## Scope

Owner supplied the Tâm Đức homepage demo and explicitly froze already-approved surfaces. This slice therefore preserves Header, Hero, Trust, Services, Latest Posts and Footer and addresses only the still-missing demo Team category.

Logical base:
- PR #166 candidate: `fd84bdca1650f1b69a057a7a73527d4ef46e75ff`
- Theme metadata remains `1.3.14` in this stacked feature slice.

Exact verified production head:
- `c5fbbb87b9ab2d023f0b6de27f0cf33e845d7210`

## Demo comparison ruling

The supplied demo contains:
1. Header
2. Hero
3. Trust strip
4. Services
5. About + Team band
6. Latest Posts
7. Footer

The demo does **not** show Process, FAQ or a final CTA section. An exploratory branch delta briefly enabled those existing Law01 sections, but browser regression correctly proved that this exceeded the approved Burgundy demo closure. That delta was fully reverted before the verified production head.

Therefore this slice changes no composer section order and does not add any extra homepage section beyond the mapped Team teaser.

## Team source behavior

Source authority:
- existing Law01 design spec §9.6 defines the Team teaser source as the mapped WordPress Team Page;
- RootProfile authoritative organization-team membership is still blocked by issue #112.

Behavior on the verified head:
- if no mapped Team Page exists, About retains the inherited About-only fail-soft layout;
- if a mapped Team Page exists, the Team category remains visible in the approved 48/52 About+Team desktop band;
- Team title, excerpt and CTA remain WordPress Page-owned presentation inputs;
- member cards render only from legitimate mapped direct published child Pages;
- zero member Pages renders zero person cards;
- no WordPress user/author enumeration, slug/title/URL inference or private RootProfile read is introduced.

## TDD evidence

RED:
- `e26f6855919e307feb834fbeee01c69730b28e9b`
- Law Site Provisioning Candidate Package failed exactly on:
  `A mapped Team Page must retain the 48/52 About+Team reference band even when no legitimate member cards are available.`

GREEN:
- `c5fbbb87b9ab2d023f0b6de27f0cf33e845d7210`
- minimal production change ties the About-only modifier to absence of a mapped Team Page instead of absence of member cards.

Retained contract updates also require:
- Team shell is present for a mapped Team Page;
- member cards remain guarded by non-empty legitimate member input;
- accessibility label reflects mapped About/Team sections.

## Fresh exact-head verification

Exact production head `c5fbbb87b9ab2d023f0b6de27f0cf33e845d7210`:
- **25/25 GitHub checks SUCCESS**
- Candidate Package full reusable v1 Core regression: SUCCESS
- V1 Core static + WordPress 6.9 clean runtime: SUCCESS
- Homepage Composer Law01 L3/L4 browser + accessibility: SUCCESS
- Law Site Provisioning Browser Quality: SUCCESS
- D-027 Retained Full Regression: SUCCESS
- Y5 exact-package lifecycle/browser: SUCCESS
- X6 cross-surface release closure: SUCCESS
- R6 performance baseline: SUCCESS
- no completed check failure on the exact production head.

## Protected already-approved surfaces

Diff from the 1.3.14 logical base contains production change only in:
- `template-parts/homepage/law-01/profile.php`

No Header, Hero, Trust, Services, Latest, Footer, global CSS, composer order, navigation or contact source file is changed by the final slice.

## BLOCKED / intentionally omitted

The demo contains business facts that are not currently backed by an authoritative source:
- lawyer names, roles and portraits;
- `10+`, `500+`, `95%` metrics;
- social profile URLs;
- unsupported email/contact values.

These remain omitted rather than fabricated.

## Integration / release state

- No live-site mutation is performed by this repository slice.
- No Theme release/version promotion is performed here.
- PR #167 is stacked on the 1.3.14 candidate from PR #166.
- Merge/release/deployment remain separate owner gates.
