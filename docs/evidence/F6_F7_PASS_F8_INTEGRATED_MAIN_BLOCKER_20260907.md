# Homepage F6/F7 actual-provider evidence and F8 integrated blocker

Date: 2026-09-07
Repository: `truongdinhnamaz/aznet-theme`
Canonical base: `main@815ffcd43653930d1f302a055961ac24cf5ae843`
Evidence branch: `evidence/f8-integrated-main-landmark-blocker-20260907`

## Scope

Theme-only integration verification. No ConvertFlow source was modified, vendored, committed or pushed. The provider package was consumed only as an external test carrier. Frozen AZT source documents were not changed.

Theme ownership remains site shell/presentation/native WordPress fallback. ConvertFlow remains owner of Homepage Journey rendering, resolver/state, Save/Preview/Publish, analytics/conversion and observability.

## Exact carriers

Theme production package:

- package SHA-256: `c5a01a7929da190670ecba976211a1d8c255ddf2b450eea944b45c759b126707`
- PR #30 merge commit tree: `b7eaa030c127bf1bdcdf91ed034efcae05c3bc4b`
- the PR synthetic merge candidate had the same tree before owner-approved merge, so the verified production package bytes correspond to the canonical merged production tree.

Actual ConvertFlow carrier:

- generated carrier: `convertflow-p5.239-c1-forward-integration.zip`
- outer carrier SHA-256: `7855baff57c0f4e28702a62a95c18cdcc4b1177480b6a27a27fd2203aca48c66`
- inner installable package: `convertflow-0.1.0-p5.239-course-learning-c1-forward-integration.zip`
- inner package SHA-256: `8f046828a795a1a2d2d92a1664aff580c6c4554664f0cdf1226e521d84224de8`
- provider source commit: `5da01285435a10cc9c208dd8c57f71a16e87515d`
- package evidence declares `source_package_equality=PASS`, `deterministic_two_build=PASS`, `mtime_independent_reproducibility=PASS`.

## F6 — PASS at actual-package integration harness layer

A deterministic WordPress API shim loaded the actual P5.239 provider classes and the exact Theme production package.

Verified behavior:

- actual `HomepageJourneyActions::register()` owns `admin_post_choiceguide_homepage_save` and candidate unpublish registration;
- actual `HomepagePreviewDraftAction::register()` owns `wp_ajax_choiceguide_homepage_preview_draft`;
- actual `HomepageJourneySettingsRepository::save()` performs provider-owned option persistence;
- actual `HomepagePreviewDraftStore::create()` performs provider-owned preview transient persistence;
- actual `HomepageCandidatePageManager::publish()/unpublish()` performs provider-owned `wp_update_post` status mutation;
- Theme `front-page.php` rendered the actual provider body through the normal WordPress `the_content` boundary;
- Theme rendering caused zero option updates, post-status mutations or preview-transient writes.

Harness result:

`PASS: F6 actual provider Save/Preview/Publish ownership regression`

This is package-level actual integration evidence. It is not a WordPress 6.9 browser/runtime claim.

## F7 — PASS at actual-package integration harness layer

Verified behavior:

- actual `HomepageJourneyController::register()` owns the `the_content` filter at priority 20;
- actual provider body traverses Theme's public `the_content()` boundary and replaces native Page body on an active candidate;
- actual `DecisionEventAssets` owns `choiceguide-decision-events` registration/enqueue on the Homepage surface;
- actual provider `decision-events.js` contains the `choiceguide:conversion-context-changed` listener and Homepage `journey_cta_clicked` event behavior;
- provider decision-event asset SHA-256: `079273d4366dbe56451f42aa4112567bca868dc488d958d45772851c23a99c85`;
- Theme production package contains none of the tested provider owner keys/hooks/event symbols.

Harness result:

`PASS: F7 actual provider resolver/analytics/conversion ownership regression`

Again, this is package-level actual integration evidence, not a full WordPress/browser release closure.

## F8 integrated — RED / BLOCKED

The same actual-package integration render exposes two nested `main` landmarks:

1. Theme: `<main id="main" class="aznet-theme-main aznet-theme-main--front-page">`
2. ConvertFlow P5.239: `<main class="choiceguide-homepage-journey" data-choiceguide-homepage-journey>`

Rendered reproduction:

```text
<header data-theme-shell="header"></header><main id="main" class="aznet-theme-main aznet-theme-main--front-page">
    ...
    <main class="choiceguide-homepage-journey" data-choiceguide-homepage-journey>
```

Fresh bounded regression:

```text
FAIL: integrated Homepage produces nested main landmarks: Theme owns main#main and actual ConvertFlow renderer also emits <main>.
EXIT_CODE=1
```

Regression carrier committed on this branch:

`tests/integration/f8-homepage-main-landmark-contract.php`

It requires an externally extracted actual ConvertFlow package through `CONVERTFLOW_ROOT`; it does not vendor provider bytes.

## Root-cause / ownership analysis

This is not a Theme token/CSS issue. Both products currently claim the document-level main landmark in the integrated `the_content` composition.

The current Theme source boundary intentionally keeps `front-page.php` as the site-shell owner and does not provide a Theme-side ConvertFlow PHP capability/runtime adapter. Theme therefore cannot safely detect provider presence and conditionally remove/reinterpret the site landmark without introducing a new runtime contract or a forbidden heuristic.

Removing Theme's `main` unconditionally would regress the native WordPress fallback path and move site-shell semantics away from the Theme. Targeting ConvertFlow-owned DOM from Theme would also violate the integration boundary.

Accordingly, no production Theme workaround is applied.

## Current state

- F1/F2/F3/F4/F5: retained/canonical evidence remains valid.
- F6: PASS at actual-package integration harness layer.
- F7: PASS at actual-package integration harness layer.
- F8 native: retained PASS.
- F8 integrated: **BLOCKED/RED** on nested main-landmark ownership conflict.
- F9 integration/release closure: **BLOCKED** because F8 integrated is not PASS.
- No production deploy/release claim.

## Recovery / next

External provider owner must supply a contract-compatible Homepage body that does not claim a second document-level `main` landmark when rendered through WordPress `the_content`, or an explicitly approved public integration-contract change must define a different ownership model.

Until that dependency is resolved, AZnet Theme must keep its current site-shell semantics and must not implement a heuristic workaround or modify ConvertFlow source from this repository.
