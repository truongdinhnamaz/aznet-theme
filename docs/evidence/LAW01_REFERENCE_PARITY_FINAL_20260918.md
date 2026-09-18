# Law 01 Reference Parity Final — 18/09/2026

## Scope

Owner-approved Theme-owned presentation refinement for the Law 01 `burgundy-gold` variant, using the supplied Tâm Đức - Hà Nội law-firm homepage screenshot as the canonical visual reference for this slice.

This checkpoint supersedes the intermediate presentation interpretation that made the Hero inner grid viewport-wide. Full-width section surfaces remain allowed, but Header, Hero inner content, Trust, Services, Profile, Latest and Footer align to one wide shared inner shell.

No architecture, public provider contract or domain ownership change is introduced.

## Canonical base and PR

- Canonical base: `main@9781d29a9d1ba32a643ffe3cfe9d47c8c4ba4c1c` after PR #145.
- Theme metadata on the canonical base: `1.3.6`.
- PR #146 was closed as superseded before merge because the owner clarified that the supplied screenshot must be followed first.
- Active implementation: PR #147, branch `feat/law01-reference-parity-final`.
- Exact verified production head before source-only checkpoint updates: `aacd6fe4abc4d5f3d49b6bfd85570df66688db46`.

## Presentation decisions

The accepted reference composition for this slice is:

- full-width section surfaces with one shared `96rem` inner shell;
- Hero inner composition at 48/52 on wide desktop;
- WordPress-owned site logo + site-title lockup in Header, and the same identity pattern in Footer;
- WordPress-owned Header Utility menu may be reused in the Hero as phone/hotline links;
- four-item trust strip;
- six compact service cards on wide desktop; the mapped Services excerpt remains source-backed in markup but is visually omitted in the compact Burgundy reference composition;
- About and Team share one 48/52 Profile band on wide desktop;
- About remains text-led in this reference variant instead of introducing a second large About image panel;
- Team presentation retains up to four WordPress-owned direct child Page portrait slots, with the Team CTA below the portrait row;
- Latest remains three WordPress-native Posts with 3:2 media and equal-height card rhythm;
- responsive stacking remains required at narrower widths.

The supplied screenshot includes business statistics. Those values are not invented or hardcoded by the Theme. They may only be rendered if a legitimate public source owns and supplies them.

## Ownership boundary

RootProfile-backed authoritative Team membership remains `BLOCKED_EXTERNAL_CONTRACT` at issue #112.

The existing mapped Team Page/direct published child Page fixture is presentation input only. It does not certify, infer or replace authoritative RootProfile identity/membership. The Theme does not read provider-private storage and does not infer authoritative membership from slugs, authors, URLs or other heuristics.

## Verification

Exact head `aacd6fe4abc4d5f3d49b6bfd85570df66688db46` completed **18/18 triggered workflows SUCCESS, 0 failure**.

Coverage includes:

- V1 Core Pull Request CI;
- Homepage Composer Law 01 Browser Quality;
- R3 Header Static + Browser Quality;
- Y3 Footer System;
- D-027 Standalone Core L3/L4/Exact Package + retained regression;
- R1/R2/R6 retained presentation/performance gates;
- F Homepage native package/runtime regression;
- X3/X4 retained surface regressions;
- Law Site Provisioning Candidate Package.

Law 01 browser run: `35363025279`.

Artifact:
- `homepage-law01-browser-quality`
- artifact ID `10555328476`
- digest `sha256:4c0af3b17d8d38768f994f2dacd3dbe20f5da5b693ec28c331df7fc632d520ba`

The browser artifact covers 1920x1080, 1440x1000, 1024x900, 390x844 and 320x760. The 1440 screenshot was manually inspected against the supplied reference after the final production changes. Fixture images/content are synthetic QA inputs; they are not Theme-owned client truth.

## Release state

This checkpoint is presentation-candidate evidence only. It does not authorize or claim:

- merge of PR #147;
- a new Theme version/package;
- Git tag or GitHub Release;
- production deployment.

Those remain separate gates.
