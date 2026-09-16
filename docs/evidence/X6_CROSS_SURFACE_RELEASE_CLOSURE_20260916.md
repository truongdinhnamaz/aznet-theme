# X6 Cross-surface QA & Release Closure — 2026-09-16

## Scope

Technical closure evidence for AZnet Theme v1.2 WordPress Experience Completion on the isolated branch `feat/v1.2-x6-cross-surface-release` and its owner-approved canonical integration.

X6 verifies the integrated WordPress-Core X1-X5 experience across L1-L4 and L6. Theme remains PRESENTATION OWNER. WordPress remains authoritative for comments, search/query/routing, content/media, pagination/navigation, native form submission/validation semantics and native data lifecycle. Provider L5 is outside this milestone.

## Canonical base revalidation

Before X6 integration, canonical `main` was:

- `main`: `dddcee1cbd9a406f365ec57ece40097581e0bcf3`
- merge title: `Merge pull request #89 from truongdinhnamaz/ops/tamduc-identity-contact`
- exact-main verification run: `35126047491` — `SUCCESS`

The earlier X6 branch had forked from `6d6459f4497d1b7612c36622a13e7c5c71ba2a73`. The only net main-side delta since that fork was the bounded Tâm Đức site-operations/evidence set from PR #88/#89, with no overlapping X6 production implementation edits.

The X6 branch was therefore synchronized by a normal two-parent merge commit, not by history rewrite:

- synchronized candidate SHA: `c145f72cc7ac4783eb9f4a2f0b11c1d26e178445`
- synchronized candidate tree: `3594fab0c89d015711e914325734646d1e7df159`
- parents: prior X6 head `3661ba32ef55eca0b919fda1aea226f492333939` + canonical `main@dddcee1cbd9a406f365ec57ece40097581e0bcf3`
- compare after sync: branch `ahead`, `behind_by=0`; merge base equals canonical `main@dddcee1c...`

## Pre-promotion RED/PASS checkpoint

Exact pre-promotion test head:

- SHA: `08e12fa7d4feee3930b2ce12d4713dc425d1aada`
- commit: `test: add X6 version promotion RED contract`
- Theme metadata at that checkpoint: exact `1.1.0`

The focused promotion contract required both production version declarations to become exact `1.2.0`; while metadata was still `1.1.0`, its intended failure was:

`style.css Theme Version must be exactly 1.2.0 in X6 promotion.`

Fresh pre-promotion technical evidence on that exact head:

- X6 run: `35098577606` — `SUCCESS`
- artifact: `10447571392`
- artifact digest: `sha256:71a909cb839b43b38efa74424f992063f0ef88f0b0bc1e26da7e3b9995694807`
- WordPress: `6.9`
- integrated browser matrix: `32/32`, blocking failures `0`
- deterministic package: `aznet-theme-1.1.0.zip`
- package SHA-256: `9c8b24416d1692fb2f4e67e1557ffd539dd00ddaeaa05bace97d6605875962c6`
- packaged PHP files linted: `95`
- all retained workflows triggered on the same pre-promotion head completed successfully.

The product owner explicitly approved crossing the metadata-promotion gate after the pre-promotion checks were green. That approval covered the atomic `1.1.0 -> 1.2.0` metadata promotion only; it did not authorize canonical merge, tag/GitHub Release/publication or production deployment.

## Atomic 1.2.0 promotion

Production metadata promotion was bounded to the two version declarations:

- promotion commit: `682285c34e808bc4148702419fd857065a2a3bf1` — `release: promote Theme metadata to 1.2.0`
- byte-preservation follow-up: `60186106769819f8f108dafade816b2441d48e79` — `fix: preserve metadata file trailing newlines`

The promoted candidate retained matching exact `1.2.0` declarations in `style.css` and `AZNET_THEME_VERSION`.

No provider integration, client/domain data, custom query/search/comment/gallery/form engine or private provider storage was introduced by the promotion.

## Fresh synchronized final-candidate verification

Exact synchronized candidate:

- SHA: `c145f72cc7ac4783eb9f4a2f0b11c1d26e178445`
- tree: `3594fab0c89d015711e914325734646d1e7df159`
- Theme metadata: exact `1.2.0`

X6 release-closure workflow:

- run: `35126908185` — `SUCCESS`
- artifact: `10459925538` (`x6-cross-surface-release-evidence`)
- artifact digest: `sha256:c6b480168282e7dc9eea8f749d303012763cabf16213a6e69619c62f96f16e03`

Fresh checks on the exact candidate all passed:

- PHP `8.1` static / retained contract gate;
- WordPress `6.9` clean runtime with zero active third-party plugins;
- native X6 fixture and runtime surface/asset boundary verification;
- integrated Playwright/axe matrix: `32/32`, blocking failures `0`;
- horizontal overflow `0` on all recorded cases;
- critical/serious axe blockers `0`;
- Theme-caused page/console/request blocking failures `0`;
- keyboard-focus checks PASS on applicable native controls;
- deterministic package built twice with byte identity;
- exact source/package identity PASS;
- packaged PHP lint count: `95`;
- exact-package switch-away to `twentytwentyfive` and switch-back continuity PASS;
- WordPress-owned sentinel data continuity PASS;
- Theme Mod continuity after switching back PASS;
- ownership/promotion-boundary contract PASS.

Deterministic promoted candidate package:

- file: `aznet-theme-1.2.0.zip`
- SHA-256: `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`

All 19 observed same-head pull-request workflows completed `SUCCESS`, including X1-X6, V1 Core PR CI, D-027 retained/package gates, R6 performance, lifecycle/rollback, release-version consistency, Homepage/Law provisioning regressions and retained browser quality gates.

## Final source/evidence closure head before merge

After the production candidate was proven, Task 9 added durable evidence, reconciled AZT-03/AZT-04/AZT-EXEC-MAP/SOURCE_MANIFEST, retired the temporary `_tmp-x6-red.yml`, and corrected the v1.1 release-tree provenance token. These were source/evidence/CI-hygiene changes and did not alter Theme production behavior.

The exact final PR head was:

- PR #87 head: `ff459b99a348d3004a3a4e11474b7c5ff19508f2`
- X6 exact-final-head run: `35128572748` — `SUCCESS`
- final-head artifact: `10460502819`
- final-head artifact digest: `sha256:b8d7713003ae0245184242f48165b5a82e95d5bc696cd03d55992f8e330ba9be`
- all 18 observed exact-head PR workflows: `SUCCESS`
- PR state before merge: open, non-draft, mergeable, exact head unchanged.

## Owner-approved canonical merge

The product owner explicitly approved merge of PR #87 after the exact final head was GREEN.

GitHub merged PR #87 using a normal merge with expected-head protection:

- expected head: `ff459b99a348d3004a3a4e11474b7c5ff19508f2`
- canonical merge SHA: `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`
- merge tree: `70c04929afe38e87d317bad3414157e9e2bd6f74`
- parents: `dddcee1cbd9a406f365ec57ece40097581e0bcf3` + `ff459b99a348d3004a3a4e11474b7c5ff19508f2`
- merge signature: GitHub verified.

Canonical `main` now points to `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`.

## Fresh exact-main technical closure

### V1 Exact Main Verification

Run `35129331899` on exact `main@c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad` completed `SUCCESS`.

Both jobs completed successfully:

- `static-contracts` — `SUCCESS`;
- `clean-runtime-browser` — `SUCCESS`, including WordPress 6.9 install, exact-main clean runtime routes, Playwright/axe browser verification and evidence upload.

Artifacts:

- `10460675823` — `v1-main-static-contracts`, digest `sha256:f24f0b6fba2f8cbb3aa1099ce225616b584113c590e912ed171a4ef8f97995b3`;
- `10460660919` — `v1-main-clean-runtime-browser`, digest `sha256:7edf42b87e1d0d1c30743761bfa6292f83984af44c94e12b1f85333aba5ef709`.

### X6 push-to-main release closure

Run `35129331927` on the same exact merge SHA completed `SUCCESS`.

Fresh exact-main X6 evidence confirms:

- checkout SHA `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`;
- checkout tree `70c04929afe38e87d317bad3414157e9e2bd6f74`;
- Theme metadata exact `1.2.0`;
- WordPress `6.9` zero-plugin runtime;
- integrated 32/32 Playwright/axe matrix PASS;
- deterministic double-build PASS;
- exact source/package identity PASS;
- packaged PHP lint PASS for `95` files;
- exact-package switch-away/switch-back lifecycle PASS;
- ownership/promotion boundary PASS.

Exact-main package:

- file: `aznet-theme-1.2.0.zip`;
- SHA-256: `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`;
- X6 artifact: `10459889475`;
- artifact digest: `sha256:f537b5f6ed33a53dcad8041c3d8cc0aef32c5bb6dbd8c10c5102e76624128fb9`.

## Integration hygiene

The temporary `_tmp-x6-red.yml` workflow existed only to capture the earlier test-first RED phase. It was retired before final integration; historical RED provenance remains in Git history and this evidence record rather than as a misleading live `RED` workflow.

No Tâm Đức business/client data is added to X6 production runtime. Tâm Đức site-operations files already canonical on `main` remain WordPress/site-operations evidence and are not converted into Theme-owned state.

## Rollback / recovery

Before publication, X6 can be reverted at the canonical merge boundary if necessary. No v1.2.0 tag/GitHub Release or production deployment has been created by this technical closure.

WordPress-owned content/state and external provider/domain data are not deleted or migrated by X6.

## PASS

- X1-X5 integrated WordPress-Core experience: retained L1-L4 PASS.
- X6 exact final candidate: fresh L1-L4/L6 PASS.
- Canonical PR #87 merge: PASS with expected-head protection.
- Exact-main V1 verification: PASS on merge SHA `c6b1ebac...`.
- Exact-main X6 release closure: PASS on the same merge SHA.
- Atomic Theme metadata: exact `1.2.0` on canonical main.
- Deterministic exact-main package/source identity/lifecycle: PASS.
- Provider/domain ownership boundaries retained.

## BLOCKED / NOT CLAIMED

- Provider L5 is not claimed.
- No annotated tag or GitHub Release for v1.2.0 is claimed.
- No production deployment of Theme 1.2.0 is claimed.

## State

**X6 TECHNICAL PASS / PUBLICATION GATED**

## NEXT

Request owner approval for **v1.2.0 publication disposition — annotated tag + GitHub Release only**. Production deployment remains a later separate approval.
