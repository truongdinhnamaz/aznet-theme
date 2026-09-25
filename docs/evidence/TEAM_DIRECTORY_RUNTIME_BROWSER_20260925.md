# Team Directory Runtime / Browser Evidence — 2026-09-25

**Implementation head under test:** `645ad001fca301e37aebfed823567d5ea1e75bb4`  
**Branch:** `work/homepage-map-v1-3-38-baseline`  
**Scope:** D-038 WordPress-native Team directory.

## L1 / focused contract state

PASS at source/static level on the exact branch:

- Team parent resolves only from `homepage_source_value( 'law-01', 'team' )`.
- Public Team member resolver reads only published direct child Pages in `menu_order title` order.
- Homepage requests at most four members.
- Shared Team card maps Page title → name, excerpt → role and Featured Image → portrait.
- Homepage Team no longer contains generated/reference person fallbacks.
- Team admin has bounded child editing, draft-only Add Member and WordPress media-picker wiring.
- Team directory Page is activated only by exact mapped Page ID.
- Law 01 provisioning requires/reuses the Team parent and creates no sample person role/store.
- Static ownership scan found no Team CPT, Team personnel store, `get_page_by_path()`, RootProfile private key/name, or Team reference-art fallback in the changed production scope.

Focused RED evidence captured during implementation:

- Team read-model contract failed because Team module/card did not exist.
- Homepage Team contract failed because `team_directory_members( 4 )` was absent.
- Team authoring contract failed because `render_homepage_team_authoring()` was absent.
- Team Page contract failed because `team_page_is_mapped()` was absent.
- Team provisioning contract failed because the new parent title/required role were absent.

Those paths were then implemented minimally and exact-branch static predicates are GREEN.

## L3 / L4 workflow attempts

The following workflows were requested on the exact implementation head and then retried once after systematic investigation:

| Gate | Run | Retry job(s) | Result |
| --- | ---: | --- | --- |
| V1 Core Pull Request CI | 36089428494 | 107928910957, 107928910963 | UNKNOWN — both completed failure with **0 steps** |
| R5 Control Center Browser Quality | 36089428765 | 107928918654 | UNKNOWN — **0 steps** |
| Homepage Composer Law 01 Browser Quality | 36089428526 | 107928924268 | UNKNOWN — **0 steps** |
| Y1 Page Experience | 36089428497 | 107928934168, 107928934344 | UNKNOWN — both **0 steps** |
| Law Site Provisioning Browser Quality | 36089428663 | 107928940049 | UNKNOWN — **0 steps** |

GitHub returned no executable step records and no job logs for the original failures. The one allowed retry reproduced the same pre-runner state. Therefore these are infrastructure failures before test execution, not code/test failures.

## Runtime/browser coverage prepared in source

The exact branch contains runtime/browser assertions for:

- six published Team members + one draft member;
- Homepage/admin first-four order parity;
- draft exclusion;
- one missing portrait and one missing role;
- Homepage **Xem tất cả** → exact Team parent;
- Team directory rendering all published children;
- Team Page and Homepage axe serious/critical checks;
- Team admin at 1440, 1024, 782 and 390px widths;
- keyboard focus on **Thêm nhân sự**;
- existing Team Page reuse without rename/duplication;
- new-site Team parent creation with zero sample children.

These assertions have **not executed on GitHub runners** because the jobs never started.

## Ownership

No new external provider contract is introduced. Team child Pages remain WordPress editorial content and are not authoritative RootProfile Person entities.

## Status

- **PASS:** L0/L1 architecture, ownership, exact-source implementation, focused RED→GREEN/static contracts.
- **UNKNOWN:** L3 WordPress runtime, L4 browser/visual/a11y and retained whole-suite CI because GitHub Actions failed before runner allocation.
- **NOT AUTHORIZED:** release version promotion, package publication or production deployment.
