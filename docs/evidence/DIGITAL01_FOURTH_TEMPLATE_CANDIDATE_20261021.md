# Digital 01 — Fourth Production Template Candidate (21/10/2026)

## Goal
Establish **Digital 01** as the fourth independent AZnet Theme presentation template for digital marketing, web services, software/digital product showcases and technology editorial. Candidate for aznet.vn; **not a production-site deployment**.

## L0 source, owner, baseline
- Canonical base: `main@b7738467b21dba1bcd63f62a11b5a248cd7f1ba9` (20/10/2026).
- AZT-05 Constitution and AZT-04 D-043 Template Extension Contract govern the implementation.
- D-043 C8 already proved the generic manifest extension with a **synthetic test fixture** only; Digital 01 is the first new production template in this slice.
- WordPress owns native Pages/Posts/blocks/query/editorial publication. WooCommerce owns Product, price, cart/checkout. Theme owns appearance, layout and preset settings. No domain copy/automatic mutation.

## Design/implementation
- `inc/theme/templates/digital-01/manifest.php`: isolated v1 manifest, visual/homepage preset identifiers; no provisioning script or legacy Hero settings.
- `assets/css/presets/digital-01.css`: site-wide bounded color/typography/header/footer presentation.
- `assets/css/components/homepage-digital-01.css`: selectively loaded Homepage style via manifest and native Page.
- `patterns/homepage-digital-01.php`: WordPress-owned native block-based starter markup containing Hero, services, product showcase, tech Posts Query Loop and CTA. WordPress rendering of block markup does not mean Gutenberg authoring is enabled; Classic Editor remains the approved native Page/Post editor. Placeholder copy is explicitly editorial content to review; no fabricated products/prices/claims.
- `inc/theme/homepage-composer.php`: generic manifest-declared `homepage.composition.mode=native` rendering path preserving the WordPress Front Page content boundary. No template-ID branch is introduced for Digital 01; corresponding effective surface/admin authoring source maps to the exact selected WordPress Page.
- RED tests committed **before** implementation: `tests/offline/digital01-template-contract.php` and `tests/offline/digital01-composer-isolation-contract.php`.

## Fresh evidence
- GitHub Actions [Digital 01 fourth template candidate](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39599913084): `static-contracts` **SUCCESS**.
- Checks passed: PHP 8.1 syntax, Digital 01 manifest/assets/native pattern, compositor native-body isolation, original Law/Curtain/Industrial manifest regression, synthetic fourth-template extension proof.
- This supports only L1–L2. L3 WordPress runtime, L4 browser/accessibility, L5 real provider/Woo compatibility, L6 approved release **UNKNOWN**.
- `https://aznet.vn/` public fetch timed out during this session; no live content inventory or comparison was established.

## Gate/rollback
- Draft PR #487 is unmerged; aznet.vn not modified.
- Must use a real WordPress 6.9+ pilot environment for activation and full responsive/keyboard/a11y review before owner approval of merge/release/deploy.
- Preserve three-pilot state and external domain data. Rollback by discarding/reverting bounded PR; no database migration.
- Exact Next: run WordPress L3/L4 smoke and editorial pattern visual QA for Digital 01 on an isolated preview; keep live site untouched.

## 21/10/2026 — Owner authoring preference and later QA
- **Owner choice: Classic Editor is preferable for Digital 01.** This reaffirms existing AZT-04 D-024; do not enable Gutenberg or change native Post/Page editor policy. No new architecture decision is needed to preserve the accepted policy.
- The block-syntax starter pattern may be **awkward to maintain in the Classic Visual tab**; visual HTML/text editing compatibility and round-trip persistence are not yet proven. Do not claim a polished Classic Editor page-authoring experience from static checks alone.
- Candidate static run [39608811607](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39608811607) PASS at `c7d4c264`, after source-map and admin authoring regression additions; earlier exact-head `8cdf48c2` retained 32 workflows with 30 SUCCESS, 1 unrelated Curtain pilot preview CDN failure and 1 Digital 01 L3 seed failure.
- Digital-specific WordPress CI [39603519610](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39603519610) failed because a `strict_types` declaration in the eval-file seed was incompatible with WP-CLI. This was corrected in `c7d4c264`. Subsequent CI [39608811450](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39608811450) reached WordPress install/activation, then failed because the seed invoked `homepage_composer_active()`, which requires an HTTP front-page request and cannot be asserted inside WP-CLI's non-request context. The seed now checks stored exact native Page/manifest/preset in WP-CLI and defers request gating to the HTTP/browser verification in commit `12a2f930`; fresh L3/L4 evidence on this later head is **PENDING/UNKNOWN**.
- A separate Curtain C5 failure at `8cdf48c2` was Chromium/Playwright CDN name-resolution `ENOTFOUND`, not evidence that Digital 01 changed Curtain output.
- **Next:** wait for exact-head Digital 01 isolated WordPress 6.9 L3/L4, inspect fresh screenshots and full error log, check Classic edit/save round-trip, then fix at the shallowest layer. Do not merge or deploy without completion of gates.

## 21/10/2026 — Real L3/L4 and Classic Text save PASS (exact head c60d325)
- **L1/L2 PASSED** [Digital 01 fourth template candidate, run 39611738062](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39611738062) for `c60d325508a371898803bd26d197abde0cddf4b0`.
- **L3/L4 PASSED** [Digital 01 WordPress runtime and browser, run 39611738495](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39611738495) for the same exact commit.
- Disposable WordPress 6.9/PHP 8.1, zero active third-party plugins, theme activation, native homepage HTTP, 1440/1024/390 browser screens, no serious/critical axe violations, keyboard anchor, authoring admin link and one Front Page native query passed.
- Owner-accepted **Classic Editor Text mode**: user-visible body text changed in native Front Page, save submitted in wp-admin, content survived reload, was visible over HTTP, and native WordPress Query Loop still rendered after save. Post-content hash changed as expected; subsequent theme-switch did not change native Post/Page bytes.
- **Classic Editor Visual tab round-trip UNKNOWN**: markup includes WordPress-native block comments; only Text tab save was proven at this checkpoint. Avoid claiming general WYSIWYG editing parity until tested.
- PR #487 remains draft/unmerged, main untouched. `aznet.vn` has no authenticated WPVibe connection on record; no live rollout evidence.
- **EXACT NEXT**: extend browser fixture on disposable WordPress to test Classic Visual tab edit/save integrity; if it breaks native Query/body, RED reproduction then minimal ownership-safe fix. Do not claim L5/site rollout or merge without fresh source/contract review.
