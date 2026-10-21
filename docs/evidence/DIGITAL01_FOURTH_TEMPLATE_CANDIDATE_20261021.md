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
- `patterns/homepage-digital-01.php`: editable native WordPress blocks with Hero, services, digital products, tech Posts Query Loop and CTA. Placeholder copy is explicitly editorial content to review; no fabricated products/prices/claims.
- `inc/theme/homepage-composer.php`: explicit Digital 01 passthrough preserving the native Front Page content boundary. Prevents unsupported fourth preset from accidentally invoking Law 01 parts.
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
