# Homepage runtime staging preflight — 25/09/2026

## Purpose

Attempt fresh WordPress/runtime verification for the canonical unified Homepage backend after owner-approved PR #278/#279 closure, without depending on GitHub-hosted runners.

Canonical checkpoint at start: `main@112abb962131764034e99163a817160288d21413`, Theme metadata `1.3.54`.

## Target selected

`https://tamduchanoi.aznet.vn` was selected because it has authenticated WPVibe draft capability and is not the current production target.

Fresh site metadata:

- WordPress `7.1.2`;
- PHP `8.4.25`;
- WPVibe plugin `1.17.2`;
- active live theme `AZnet Theme 1.3.1`;
- WP-CLI emulator available;
- live site was not published or switched during this attempt.

## Draft isolation

A stale missing-folder draft record was cleared using WPVibe's documented recovery path, then a new `aznet-theme-wpvibe-draft` was created from the live 1.3.1 theme. All theme file writes in this attempt were scoped to that draft.

## Exact-current sync attempt

The draft was incrementally synchronized from canonical GitHub source. Several current files were written successfully, including the unified Homepage backend files, but exact staging could not be completed because the tool safety layer blocked writes for required current files. Reproduced blocked examples include:

- `inc/admin/homepage-hero.php`;
- `inc/theme/team-directory.php`;
- `inc/theme/widgets.php`;
- additional unrelated current provisioning/presentation files.

Current `inc/theme/bootstrap.php` requires `team-directory.php` and `widgets.php`; current `inc/admin/bootstrap.php` requires `homepage-hero.php`. The incomplete exact file set therefore cannot be treated as the canonical Theme runtime.

## Runtime reproduction

A tokenized draft preview was generated and requested. WordPress returned the standard critical-error page before Homepage HTML rendered.

The shallowest stable root cause is **incomplete exact draft synchronization caused by blocked required file writes**. This preview failure is not evidence that canonical Theme code fails in WordPress.

## Read-only WordPress data preflight

Independent live read-only checks on the staging site confirmed:

- `show_on_front = page`;
- `page_on_front = 2` (`Luật sư Tâm Đức - Hà Nội`, published Page);
- Law 01 is configured in `aznet_theme_settings`;
- mapped Services Page `#122` has six published direct child Pages (`#174`–`#179`);
- mapped FAQ Page `#181` is published and contains five native `<details>` FAQ blocks;
- mapped Team Page `#142` currently has no published direct child Pages.

The site therefore does not match the retained R5 four-member Team fixture assumptions without mutating WordPress content, so it is not an exact R5 runtime fixture in its present state.

## Safety / mutation boundary

- No draft was published.
- No live theme was switched or overwritten.
- No live WordPress content was created/edited/deleted.
- The isolated draft remains a diagnostic artifact and must not be published as an exact-current candidate.

## QA disposition

- L1/L2 canonical source/contract PASS remains retained.
- L3 exact-current WordPress runtime: **UNKNOWN**.
- L4 browser/a11y parity: **UNKNOWN**.
- Runtime staging attempt: **BLOCKED_RUNTIME_STAGING_EXACT_SYNC** (tool safety blocked required exact file writes).

## Exact next

Use a WordPress environment that can load the complete canonical Theme `main@112abb962131764034e99163a817160288d21413` without partial-file substitution. Then run the existing Homepage runtime assertion followed by R5/Homepage browser parity. Do not infer L3/L4 PASS from the partial draft or mutate staging content merely to imitate the retained fixture.
