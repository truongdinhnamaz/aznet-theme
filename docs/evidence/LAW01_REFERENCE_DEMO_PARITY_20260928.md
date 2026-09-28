# Law 01 Reference Demo Parity — 1.3.57 Candidate

Date: 2026-09-28  
Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/law01-phase-a-native-clean-20260928`  
Reference demo: user-supplied Law 01 homepage screenshot  
Pre-candidate exact head: `7a263fb2b903af10e0eb6fdbe59d7fc1cf846eaa`  
Candidate version: `1.3.57`

## Goal

Bring Theme-owned Law 01 presentation closer to the supplied reference demo while preserving WordPress/source ownership and existing public projections.

This slice does not fabricate missing Service or Team source data and does not claim fresh pixel-level validation against the live pilot because automated live capture remains blocked by the pilot redirect/challenge path.

## Demo-aligned Theme changes

### Header

- Law 01 no longer adds a separate topbar above the primary header.
- Desktop Law 01 header row is compacted to the reference-oriented 78px presentation.
- Existing WordPress logo, navigation, search and consultation actions remain source-owned.

### Hero

- Fallback Hero desktop ratio now favors copy at 54/46, matching the reference composition.
- Hero Library WordPress title is presented uppercase through CSS only; source text is not mutated.
- Hero Library primary CTA now uses the Law 01 burgundy presentation token instead of the previous navy treatment.
- Existing WordPress Hero block remains authoritative for title, copy, actions, media and editable trust messages.

### Services source-gap presentation

Live WordPress source inspection found six published child Pages under Services, but only four currently have renderable nonblank titles. Theme continues to exclude the two blank-title Pages rather than infer labels from their slugs.

The public Services grid is now count-aware on wide desktop:
- 4 renderable cards → four columns;
- 5 renderable cards → five columns;
- 6 renderable cards → retained six-column reference layout.

No missing Service title is invented by Theme.

### About / Team band

Homepage Team portrait cards are presented without the heavy directory-card border/background/shadow used outside the Homepage reference band.

Live WordPress source inspection found two published Team child Pages, but only one currently has a renderable nonblank title. Theme continues to exclude the blank-title Page rather than infer identity from its slug.

The Homepage Team grid now exposes the public member count and uses bounded wide-desktop layouts for 1–3 legitimate members, avoiding a sparse four-column row while keeping the reference four-slot layout when four members exist.

### Footer

The current branch includes the demo-oriented Law 01 Footer composition and duplicate-social guard established immediately before this parity checkpoint. Footer remains Theme presentation over WordPress-native site/menu data.

## TDD / regression trail

- Hero 54/46 + Homepage Team tile treatment: RED contract → GREEN production.
- Browser Hero ratio assertion updated only after the old 46% expectation failed against the accepted 54/46 production target.
- Count-aware Services/Team layout: RED contract failed because Team public count was not exposed → GREEN markup/CSS.
- Hero Library uppercase + burgundy CTA: RED contract failed on missing uppercase treatment → GREEN CSS.
- Retained core then exposed a stale navy CTA static expectation; test aligned to the accepted burgundy token.
- Hero Library L4 then exposed a stale browser palette probe using `--law01-navy`; browser assertion aligned to `--law01-client-burgundy`.

## Exact pre-candidate QA

- Law 01 Reference Regression — run `36382879183` — SUCCESS
- Homepage Composer Law 01 Browser Quality — run `36382879140` — SUCCESS
- R3 Header Static Contracts — run `36382879086` — SUCCESS
- R3 Header Browser Quality — run `36382879165` — SUCCESS
- Y3 Footer System — run `36382879181` — SUCCESS
- Release Version Consistency Gate on pre-candidate metadata — run `36382879139` — SUCCESS

Homepage Browser Quality includes WordPress 6.9 runtime, Services source-gap runtime, Team parity, five viewport Law 01 browser/visual/a11y verification, synced Hero precedence and Hero Library browser verification.

## Live source gaps retained

The Theme intentionally does not repair authoritative WordPress content gaps:

- Services child Pages #178 and #179 currently have blank titles.
- Team child Page #326 currently has a blank title.
- Demo numeric claims such as `10+`, `500+` and `95%` are not introduced without authoritative source data.

These gaps affect how closely the live site can match the content shown in the reference screenshot, but they are not Theme-owned facts.

## BLOCKED / UNKNOWN

Fresh live-pilot L4 comparison remains UNKNOWN because automated capture of `lstamduchn.vn` continues to hit the redirect/challenge path. REST source inspection is available; exact CI WordPress/browser evidence is green.

## Rollback

Candidate remains isolated on `work/law01-phase-a-native-clean-20260928`. No merge, release, tag or deployment is part of this checkpoint.

Rollback: revert to pre-candidate exact head `7a263fb2b903af10e0eb6fdbe59d7fc1cf846eaa` or bounded parity commits.

## Exact next

Verify the exact `1.3.57` candidate head. If green, build and provide an installable ZIP for pilot installation. Live pilot visual verification follows installation and remains a separate approval/deployment step.
