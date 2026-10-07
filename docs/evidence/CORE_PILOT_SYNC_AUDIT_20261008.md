# CORE / PILOT SYNCHRONIZATION AUDIT — 08/10/2026

## Scope

Audit and bounded cleanup of AZnet Theme Core/preset boundaries, using current GitHub source as the implementation/state authority and `docs/source/AZT-02-architecture.md` + `AZT-04-roadmap-qa-decisions.md` as the architecture/roadmap authorities.

Canonical repository: `truongdinhnamaz/aznet-theme`.

## Starting canonical checkpoint

- Canonical `main` before this audit: `3226908cae6488e4e957e578cafbedeaba45f5a2`.
- Theme metadata on that main: `1.3.86`.
- D-042 peer pilots:
  - Law 01: `lstamduchn.vn`
  - Curtain / Rèm 01: `remquocanh.vn`
  - Industrial 01: `minhnguyen.vn`
- D-043 requires the same extension contract to retain three-pilot regression plus a synthetic fourth-template proof.
- D-048 requires shared Page presentation changes to remain explicit/presentation-owned and not route by slug/title/URL/Page ID.

## Fresh pilot inventory

Authenticated read-only inventory on 08/10/2026:

| Pilot | Active Theme | Preset state | WordPress / PHP | Disposition |
| --- | --- | --- | --- | --- |
| Law 01 — lstamduchn.vn | 1.3.86 | schema 3; homepage `law-01`; visual `editorial` | WP 7.1.3 / PHP 8.5.6 | Version-aligned with canonical metadata; package-byte identity not proven |
| Rèm 01 — remquocanh.vn | 1.3.86 | schema 3; homepage + visual `curtain-01` | WP 7.1.3 / PHP 8.1.34 | Version-aligned; package-byte identity not proven |
| Industrial 01 — minhnguyen.vn | 1.3.79 | schema 3; homepage + visual `industrial-01` | WP 7.1.3 / PHP 8.2.34 | Version drift: 7 patch versions behind 1.3.86 |

A Theme version string is not package-byte identity evidence.

Fresh public mobile Lighthouse on all three Homepages returned Accessibility 100/100 and Best Practices 100/100. This is current public-page quality evidence only; it does not prove source/package identity.

## Root cause found

Generic `assets/css/components/page.css` had accumulated Rèm Quốc Anh-specific presentation:

- `.page-id-726 ...` quote-page selectors;
- `.page-id-45 ...` contact-page selectors;
- `rqa-*` authored marker selectors.

The existing RQA regression contracts also required those site/Page-ID selectors to remain in generic Page Core. That contradicted the current D-042/D-043/D-048 direction: generic Core must remain site/template neutral and must not infer presentation routing from Page ID.

## TDD evidence

### RED 1 — generic Core hygiene

PR #429 exact RED head `70c09bd9252675eef88a347b8c86737bd017259a`.

V1 Core static-contracts failed for the intended reason:

`FAIL: generic page.css still contains pilot-specific selector .page-id-`

### GREEN 1 — isolate the presentation

The RQA presentation was first moved out of generic `page.css`; a scoped compatibility path retained the shipped visual behavior.

Temporary verification run `37702502642`, job `113069036928`, completed SUCCESS and included:

- generic Page Core hygiene;
- RQA quote/contact retained contracts;
- D-043 C7 three-pilot manifest regression;
- D-043 C8 synthetic fourth-template proof;
- full `scripts/verify-v1-core.sh`.

### RED 2 — remove Page-ID routing from compatibility

Run `37702915172`, job `113070373657`, failed as intended:

`FAIL: legacy compatibility must use authored presentation markers, not Page-ID selectors`

The RQA quote/contact tests were then pointed at explicit authored marker scoping and the RED remained valid on run `37702991029`, job `113070617255`.

### GREEN 2 — preset-owned authored-marker compatibility

Final production-code candidate at `630e33d3f30f97747d002175b14d3d9f08e04585`:

- generic `page.css` contains no RQA or Page-ID selectors;
- generic `inc/theme/assets.php` contains no RQA-specific asset branch;
- the retained RQA presentation lives inside `assets/css/presets/curtain-01.css`;
- quote compatibility is scoped through `body.aznet-theme-preset--curtain-01:has(.rqa-quote-trust)`;
- contact compatibility is scoped through `body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero)`;
- no slug/title/URL/Page-ID PHP routing was introduced.

Temporary exact-code verification run `37703678012`, job `113072869447`, completed SUCCESS. Log evidence includes:

- `PASS: generic Page Core/assets are site-neutral and Curtain legacy presentation is isolated in its preset.`
- all retained RQA quote/contact presentation contracts PASS;
- `PASS: D-043 C7 three-pilot manifest-path regression`;
- `PASS: D-043 C8 synthetic fourth-template extension proof`;
- `PASS: reusable v1 core verification`.

The temporary verification workflow was removed after capturing this evidence; it is not intended to enter canonical main.

## Rèm 01 draft runtime evidence

The exact candidate production files were applied only to the existing WPVibe draft on `remquocanh.vn`; production was not published.

Fresh draft readback verifies:

- `/bao-gia-rem/`: Curtain preset is active; `rqa-quote-trust` marker present; old separate compatibility asset absent.
- `/lien-he/`: Curtain preset is active; `rqa-contact-hero` marker present; old separate compatibility asset absent.
- `/kien-thuc/`: Curtain preset active; no quote/contact marker leakage; `aznet-theme-latest-posts-cards` retained.
- The final compatibility presentation comes from the normal Curtain preset stylesheet, not a generic Core Page selector.

Earlier draft mobile Lighthouse on the quote page returned Accessibility 100/100 and Best Practices 100/100. Fresh PR browser workflows remain the deeper repository gate for the final candidate.

## Current classification

### PASS

- L0 architecture/source boundary: D-042/D-043/D-048 remain unchanged.
- L1/L2 candidate Core hygiene: generic Page Core and generic asset loader are site-neutral.
- Retained RQA quote/contact contract: PASS.
- D-043 C7 three-pilot manifest regression: PASS on exact production-code candidate.
- D-043 C8 synthetic fourth-template proof: PASS on exact production-code candidate.
- Rèm 01 draft DOM/runtime preservation: PASS at the checked routes.

### UNKNOWN / not claimed

- Final PR #431 full L3/L4 matrix is not claimed until the corresponding exact-head workflows finish.
- Package/release byte identity is not claimed.
- Production byte identity for Law 01, Rèm 01 or Industrial 01 is not proven by matching version strings.
- Industrial 01 remains on Theme 1.3.79 and therefore is not current with 1.3.86 metadata.
- No production pilot update is authorized or claimed by this audit.

## Rollback

- Repository cleanup is a bounded branch/PR and can be reverted as one slice.
- Rèm 01 changes are draft-only; the live Theme remains untouched by this audit.
- Any future pilot rollout requires its own fresh backup/rollback and per-site runtime/browser verification.

## Exact next

1. Finish exact-head PR #431 retained L3/L4/release-adjacent checks.
2. Perform whole-branch review against D-042/D-043/D-048.
3. If clean, stop at the owner-approved merge gate for PR #431.
4. After canonical merge, decide pilot rollout separately: Industrial 01 requires an update; Law/Rèm require byte-identity/current-package disposition, not version-number inference.
