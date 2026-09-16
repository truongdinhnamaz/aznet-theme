# AZnet Theme v1.2.0 Production Deployment — 2026-09-17

## Scope

Owner-approved production deployment of the already-published AZnet Theme `1.2.0` release to `https://tamduchanoi.aznet.vn`.

This is a bounded WordPress/site-operations action. Theme remains PRESENTATION OWNER. WordPress remains authoritative for site content, Media, Menu, Theme Mods and native lifecycle state. Provider L5 certification/takeover is outside this deployment.

## Release identity

Published target package:

- annotated tag: `v1.2.0`;
- exact technical source: `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`;
- GitHub Release: `390148740` (`AZnet Theme 1.2.0`);
- asset: `aznet-theme-1.2.0.zip` (`568495178`);
- SHA-256: `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`.

Rollback package retained from the previous verified production release:

- `aznet-theme-1.1.0.zip`;
- SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.

## Pre-deployment state

The bounded deployment run observed before mutation:

- active AZnet Theme: `1.1.0`;
- Standalone Core: `ready`;
- visual preset: `editorial`;
- Header preset: `standard`;
- custom logo: present;
- primary menu: present;
- Homepage H1: `Luật sư Tâm Đức – Hà Nội`;
- public desktop: HTTP 200, exactly one `main`, exactly one H1, horizontal overflow `0`;
- official logo present at the previously approved WordPress Media URL;
- public `tel:` links absent, consistent with Theme 1.1.0 not registering the `header-utility` location;
- approved WordPress-owned contact menu: ID `15`, name `Tâm Đức - Liên hệ chính thức`, exactly one item `024 3716 4123` -> `tel:+842437164123`;
- `header-utility` location not registered before Theme replacement.

## Deployment execution

Temporary isolated branch: `ops/deploy-v1.2.0-tamduc`.

One-shot deployment run `35135759389` completed `SUCCESS` on head `28e8fe33d4a7ebfb9640eaf3543a799ac5b5b29f`.

Before mutation the workflow downloaded both exact published target and rollback packages and verified their SHA-256 digests. It then used WordPress core Theme Upload/Replace UI only.

Applied mutations:

- active Theme package replaced `1.1.0 -> 1.2.0`;
- existing exact WordPress-owned menu ID `15` assigned to the newly available `header-utility` menu location.

No provider storage, WordPress content, Media identity, menu item content, SEO state, provisioning state or plugin state was mutated by the deployment.

Deployment-run after-state verification reported:

- active Theme exact `1.2.0`;
- Standalone Core still `ready`;
- `header-utility` registered and assigned to menu ID `15`;
- approved menu item unchanged: `024 3716 4123` -> `tel:+842437164123`;
- desktop and mobile homepage HTTP 200;
- exactly one `main` and one H1;
- horizontal overflow `0`;
- official logo retained;
- approved `tel:` link present; desktop utility link visible.

Deployment artifact:

- artifact ID `10462759627`;
- digest `sha256:89b8fa09fb33d380c4c5add63b285d2a10206c4e9f37ef2a619482728e3b2384`.

Rollback was not invoked because the bounded deploy/verification completed successfully.

## Verification false-negative and root cause

The first independent post-deploy verifier run `35136241719` failed before its public matrix because it tried to identify the approved menu by exact equality against the decorated label in the WordPress Menu dropdown.

Fresh read-only diagnostic run `35136541142` proved production state itself was intact:

- Theme `1.2.0`;
- Standalone Core `ready`;
- plain Menu screen exposed menu ID `15` as `Tâm Đức - Liên hệ chính thức (Header Utility Menu)`;
- Menu Location `header-utility` selected value `15`, selected text `Tâm Đức - Liên hệ chính thức`;
- direct edit of menu ID `15` showed exact authoritative menu name `Tâm Đức - Liên hệ chính thức` and the exact approved phone item;
- diagnostic errors: none;
- diagnostic artifact `10462264912`, digest `sha256:6256c591ec9f0db4609c2b7bc20c5ac89c6be8814942c5e03b158323b451beb6`.

Root cause: WordPress decorates the dropdown option label with the assigned location suffix `(Header Utility Menu)`. The verifier had coupled identity checking to that presentation label. No production rollback or Theme code change was required.

A regression contract was then written RED. Run `35136712546` failed for the intended missing `approvedMenuFromLocation` behavior. The verifier was minimally changed to resolve the menu ID through the authoritative WordPress Menu Location assignment and then directly verify the menu's exact stored name/item. A too-specific static syntax marker was corrected without changing runtime behavior.

## Fresh independent post-deployment verification

Final read-only run `35136876330` completed `SUCCESS` on exact verification head `35db56c9deb4bb9e2ae1ff3371d368b21f1db640`.

Exact authenticated/read-only verification:

- active Theme: exact `1.2.0`;
- Standalone Core: `ready`;
- visual preset: `editorial`;
- Header preset: `standard`;
- logo: present;
- primary menu: present;
- approved contact menu: ID `15`, exact name, exactly one exact phone item;
- `header-utility`: registered and assigned to `15`;
- desktop 1440: HTTP 200, one `main`, one H1, overflow `0`, official logo present, exact phone link visible, page errors `0`, console errors `0`;
- mobile 390: HTTP 200, one `main`, one H1, overflow `0`, official logo present, exact phone link present in the responsive navigation DOM, page errors `0`, console errors `0`;
- verifier mutation flag: `false`.

Retained broad public-pilot matrix in the same run:

- 28 route/viewport cases across Homepage, representative Post, Page, Category, Search, no-results and native 404 at desktop/tablet/mobile widths;
- blocking failures: `0`;
- Theme-owned blocking findings: `0`;
- maximum horizontal overflow: `0`;
- blocking axe findings: `0`;
- Theme console errors: `0`;
- Theme page errors: `0`;
- Theme request failures: `0`.

The representative Post still exposes content-authored unlabeled checkbox observations. These are classified `CONTENT_AUTHORED_SEMANTICS` by the retained pilot harness and are not a Theme-owned deployment blocker.

Fresh post-deploy artifact:

- artifact ID `10464380425`;
- digest `sha256:fd9c5eca758d9d10037835776fe215cc39e01d1ebaea54b4dbb820421628789c`.

## Operational cleanup

All one-shot deployment, post-deployment verifier and diagnostic workflow/harness files were removed from the isolated ops branch after evidence capture.

Cleanup head `5917ee3c3474be6a1f12113c0cb31ec865dce1fa` compares against canonical `main@b00b2fe5e30a44b47e99ffb049e4ad351c5c8e11` with `files=[]`: the ops branch retains history/evidence only and has zero net repository-file delta.

No deployment helper is intended for canonical `main`.

## PASS

At the tested Theme-owned/site-operations scope:

- published AZnet Theme `1.2.0` is active on `tamduchanoi.aznet.vn`;
- Standalone Core remains `ready`;
- existing WordPress-owned content/settings/menu identity were retained;
- official logo remains present;
- official phone is now presented through the Theme 1.2 public `header-utility` capability without duplicating contact truth into Theme-owned storage;
- fresh authenticated/read-only verification PASS;
- fresh 28-case public browser/a11y regression PASS with zero Theme-owned blocker.

## Not claimed

- Provider L5 certification is not claimed.
- RootProfile/ConvertFlow/WooCommerce provider correctness beyond the already-defined optional boundaries is not inferred.
- Live-filesystem checksum equivalence with the release ZIP is not claimed; package identity was verified before WordPress core replacement, and active version/public behavior were verified after deployment.
- Content-authored accessibility semantics are not converted into Theme ownership.

## State

**v1.2.0 PUBLICATION + PRODUCTION DEPLOYMENT PASS** at the verified Theme-owned/site-operations scope.

## NEXT

Reconcile AZT-03/AZT-04 and the derived execution/source manifest to this deployment checkpoint. No new provider L5, architecture, release or external-product work is opened by this deployment closure.
