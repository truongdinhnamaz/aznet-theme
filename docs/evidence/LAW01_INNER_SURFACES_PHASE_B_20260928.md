# Law 01 Inner Surfaces Phase B — Candidate Evidence

Date: 2026-09-28  
Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/law01-phase-a-native-clean-20260928`  
Pre-candidate exact head: `08427bd52666479ceadfaf7c47dff1284b92b1fc`  
Candidate version: `1.3.56`

## Scope

Phase B follows the public Law 01 journey beyond Homepage without changing ownership boundaries:

- native About / generic Page presentation;
- Services root;
- representative Service detail and sibling navigation;
- Team directory;
- Contact Page;
- native Law 01 Category archive;
- native editorial Post / pagination/navigation;
- Search / 404 retained behavior where unchanged.

Theme remains presentation owner. WordPress/provider sources remain authoritative.

## Production changes

### Services inner surfaces

`services_page_children()` now consumes `homepage_renderable_child_pages()` instead of issuing a parallel raw child query.

`service_page_siblings()` now consumes the same public projection, excludes the current Page, then applies its bounded display limit.

Result:

- published Service Pages with blank titles remain WordPress source data but do not render as blank public cards;
- valid later siblings can fill the public presentation limit;
- no title/meaning is inferred from slug, URL, Page ID, author, meta or provider-private state;
- Services Homepage, Services root and Service-detail siblings now share one public renderability rule.

### Archive Contact CTA

`archive_contact_page_url()` now resolves Contact through:

`homepage_source_value( 'law-01', 'contact' )`

instead of reading the legacy `homepage_contact_page` setting directly.

The scoped resolver preserves compatibility fallback while preventing Archive presentation from drifting away from the accepted Law 01 source model.

## Regression additions

Y1 runtime fixture now includes a published untitled Service child.

Browser verification asserts:

- Services root still renders only the expected renderable cards;
- no Service card heading is blank;
- Service-detail siblings still render only expected renderable siblings;
- untitled source gaps do not leak into inner-page presentation.

## Exact pre-candidate QA

- Law 01 Reference Regression — run `36379349738` — SUCCESS
- Y1 Page Experience — run `36379349652` — SUCCESS
  - offline/core: PASS
  - WordPress 6.9 runtime: PASS
  - Services root/detail, Team, Contact, generic Page browser/a11y matrix: PASS
  - ownership/diff boundary: PASS
- Law 01 Category Archive Choice 1 — run `36379349561` — SUCCESS
  - offline/core: PASS
  - WordPress 6.9 runtime: PASS
  - desktop/tablet/mobile browser + axe: PASS
  - native query/ownership boundary: PASS
- X4 Pagination Navigation — run `36379349605` — SUCCESS
  - static/core: PASS
  - WordPress 6.9 Archive/Search/Post/Page/404 runtime: PASS
  - browser verification: PASS
  - ownership/diff boundary: PASS

## Retained surfaces

- About remains a native WordPress Page through the generic Page presentation path.
- Team directory already consumes `team_directory_public_members()`.
- Contact remains mapped by explicit Law 01 source and consumes provider data only through the public contact surface when available.
- Editorial single Post remains WordPress-native; Theme does not reconstruct RootProfile identity or ConvertFlow TOC state.
- Search/404 production code was not changed by Phase B; retained behavior was not invalidated.
- Native category main query remains owned by WordPress; Theme changes presentation only.

## BLOCKED / UNKNOWN

Pilot `lstamduchn.vn` remains read-only and outside this candidate execution path. Fresh pilot L4 is still UNKNOWN because the previously observed redirect/challenge state has not been replaced by a proven exact-candidate staging path.

No provider L5 claim is added. No RootProfile/ConvertFlow/WooCommerce private storage is read.

## Rollback

The candidate remains isolated on `work/law01-phase-a-native-clean-20260928`.

No merge, release, tag or deployment is part of this checkpoint.

Rollback options:

1. move/revert to pre-candidate exact head `08427bd52666479ceadfaf7c47dff1284b92b1fc`;
2. revert the bounded Services inner-surface projection commit;
3. revert the bounded Archive scoped-source commit.

## Exact next

Run exact-head verification for the `1.3.56` candidate commit, then perform the required whole-branch review before any merge/release decision.
