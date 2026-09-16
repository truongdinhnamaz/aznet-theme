# P4 Public Pilot QA Evidence — 2026-09-16

**Pilot:** `https://tamduchanoi.aznet.vn`  
**Canonical base:** `main@4cf5c35310f950595020f1a1048af28ec8cf4f96`  
**QA branch:** `work/p4-public-pilot-qa`  
**Final verified functional head:** `758392b03227eb446627cc4669b0b0ddf56ed71e`  
**Scope:** public, unauthenticated pilot routes only. This evidence does not claim authenticated WordPress Admin/System Health QA, provider L5 certification, release, or deployment.

## 1. Goal and ownership boundary

Prove the real public law-site pilot against the current AZnet Theme presentation without relying on TinyFish or another external browser service. GitHub Actions owns only the QA runner. WordPress remains owner of Posts/Pages/categories/query/content; optional providers remain owners of their domain state. The Theme is evaluated only for presentation behavior within its ownership boundary.

The harness deliberately does **not** hard-code `/wp-admin`, login credentials, private provider routes, `/ho-so/`, `/gioi-thieu/`, slug/title/Page-ID heuristics, or private plugin storage/API reads.

## 2. RED -> investigation -> GREEN

### Initial RED

Run `35044970478` failed as intended because the public-pilot browser harness did not yet exist:

`FAIL: P4 public pilot browser harness is missing`

This established the new QA contract before implementation.

### First live matrix

Run `35045043300` on head `e21166bf8222c0ad5d02b9c948eb86b4c5e388a3` reached the real pilot and exercised all seven public route types at four viewports. The only blocking finding was Axe rule `label` on the representative Post at all four viewports.

Artifact `10426866497` showed the exact targets were ten authored task-list checkboxes:

`.task-list-item:nth-child(N) > input[type="checkbox"]`

All targets were inside the Theme's WordPress-content boundary `.aznet-theme-article__content`, which renders `the_content()`. Repository search found no Theme frontend generator for `.task-list-item`; Theme checkbox generation is confined to Theme admin/provisioning controls. The issue is therefore recorded as authored-content semantics, not rewritten or silently owned by the Theme.

A focused regression contract was added to require narrow attribution of only axe `label` violations whose targets are inside `.aznet-theme-article__content`. Other critical/serious Axe violations remain blocking. The harness does not blanket-ignore accessibility failures within article content.

Run `35045423083` supplied the intended RED for that ownership-attribution regression. A subsequent harness-only token mismatch was corrected without weakening the gate.

### Final GREEN

Run `35045540722` completed **SUCCESS** on exact head `758392b03227eb446627cc4669b0b0ddf56ed71e`.

Final artifact:

- artifact ID: `10427345921`
- artifact name: `p4-public-pilot`
- artifact ZIP digest: `sha256:cf84a35e722f4b5b8c944c42dee3f1bdb344f191e07529b002636afb42420b78`
- artifact contains `summary.json`, exact head identity, and screenshots for every route/viewport pair.

## 3. Public route matrix

Routes are discovered through WordPress public REST APIs for native Post/Page/Category, plus native search/no-results and a generated 404 request. No authoritative domain routing is inferred.

Tested route types:

1. Homepage
2. representative native Post
3. representative native Page
4. representative Category archive
5. search with results
6. search with no results
7. 404

Viewports:

- `1440 x 1000`
- `1024 x 900`
- `390 x 844`
- `320 x 800`

Total: **28 route/viewport checks**.

## 4. Final public findings

Final `summary.json` records:

- `blocking_failures = []`;
- expected HTTP status on every tested route (`200`, except expected `404` route);
- expected WordPress native body class present on every tested route;
- horizontal overflow maximum: `0px`;
- `<main>` count: exactly `1` on every tested route;
- H1 count: at least `1` on every tested route;
- Theme-owned console errors: `0`;
- Theme-owned uncaught page errors: `0`;
- Theme-owned request failures: `0`;
- remaining Theme/unattributed Axe critical/serious violations: `0`.

The generated 404 route produces the expected browser console 404 resource message for its own request; it is recorded as an external/expected observation and is not a Theme runtime defect.

## 5. Authored-content accessibility observation

The representative Post contains ten task-list checkbox controls without labels. Axe classifies this as critical rule `label`. The same authored-content issue is observed at all four viewports.

Ownership classification: `CONTENT_AUTHORED_SEMANTICS`.

This observation is **not** silently fixed by the Theme because the controls arrive through WordPress-owned post content. It should be corrected at the content/source-owner layer if the pilot owner wants the article itself to reach zero accessibility findings.

This classification is narrow: only unlabeled form controls proven inside `.aznet-theme-article__content` receive this authored-content attribution. Theme presentation/a11y failures remain blocking.

## 6. Optional integrations

`rootprofile_public_surface = NOT_OBSERVED_ON_TESTED_ROUTES`.

This means the tested public route set did not expose `.aznet-theme-profile-surface`. It does **not** prove RootProfile absence, takeover, or L5 compatibility. Historical System Health evidence remains separate and cannot substitute for current authenticated/provider evidence.

WooCommerce is not required for this public pilot Core QA. ConvertFlow remains outside this public-path proof unless a documented public Theme-consumable surface is actually encountered.

## 7. What this proves

**P4 PUBLIC PILOT QA: PASS** for the tested unauthenticated route matrix on the real pilot, at L4 public-browser scope, with Theme-owned presentation/a11y/runtime checks green.

This public QA also proves the previously reported environment condition "current QA runner cannot reach the pilot" no longer applies to public routes from GitHub Actions.

## 8. What remains BLOCKED / UNKNOWN

- authenticated WordPress Admin/System Health re-verification;
- current authenticated Theme configuration/state beyond public output;
- authenticated update/theme-switch/rollback on the pilot;
- RootProfile current public-surface/L5 certification when no such surface was observed in this matrix;
- ConvertFlow L5 certification;
- destructive duplicate-theme cleanup;
- Git tag / GitHub Release / final production deployment.

Therefore **full P4 is not claimed PASS**. The correct aggregate state is: **PUBLIC QA PASS / AUTHENTICATED QA BLOCKED**.

## 9. Rollback and next

This slice adds QA/evidence only; it does not change production Theme PHP/CSS/JS or mutate the pilot. Rollback is deletion/revert of the QA harness/workflow/evidence branch changes.

**Exact next:** obtain a secure authenticated pilot path if available and execute the remaining admin/System Health portion without destructive changes. Until then keep authenticated P4 status BLOCKED. P5 remains gated and requires separate explicit owner approval.
