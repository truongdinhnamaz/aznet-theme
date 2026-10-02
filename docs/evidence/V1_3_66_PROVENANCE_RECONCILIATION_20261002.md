# AZnet Theme 1.3.65 Live Lineage → 1.3.66 Canonical Reconciliation — 02/10/2026

## Trigger

Fresh state reconciliation found that the canonical repository and live pilots were no longer using the same version identity.

## Fresh read-only live evidence

### lstamduchn.vn
- WordPress: 7.1.2
- PHP: 8.5.6
- Active Theme: AZnet Theme 1.3.65
- Connection verification: verified, authenticated read-only

### minhnguyen.vn
- WordPress: 7.1.2
- PHP: 8.2.33
- Active Theme: AZnet Theme 1.3.65
- Connection verification: verified, authenticated read-only

These live facts agree with the existing Law 01, Industrial 01 and Team management production evidence recorded on 02/10/2026.

## Canonical repository state before reconciliation

- Canonical main: `51e15aa706d1e9a04bc06dcde5d362e3075dbc72`
- `style.css`: 1.3.57
- `AZNET_THEME_VERSION`: 1.3.57
- Latest public GitHub Release: v1.3.24

Therefore 1.3.57 is not the newest deployed Theme identity.

## Provenance decision

Do not reuse 1.3.65 for the new canonical package because the live 1.3.65 pilot bytes have not been proven byte-identical to current canonical source.

The next canonical candidate is **1.3.66**.

This follows the established provenance rule used earlier in the project: once a version identity has already been used by a distinct or superseded artifact/byte set, do not silently reuse that version for different bytes.

## TDD / implementation

- RED commit: `8e88b1710eabf370ef854468d0653fdfd41ea268` adds the required 1.3.66 X6 promotion boundary before implementation.
- GREEN workflow commit: `e68f580d39e5cb86c2f6e7a04e4a8174850e74e4` adds the bounded 1.3.57 → 1.3.66 X6 metadata transition.
- Metadata promotion:
  - `style.css` → 1.3.66
  - `functions.php` / `AZNET_THEME_VERSION` → 1.3.66

## Scope boundaries

This slice changes canonical version identity and release-verification guards only. It does not:
- claim byte identity between live 1.3.65 pilots and canonical source;
- publish a Git tag or GitHub Release;
- deploy 1.3.66 to any pilot;
- change provider/domain ownership;
- resolve Rèm 01 external access;
- implement RootProfile or Template Distribution dependencies.

## Exit gate

The 1.3.66 candidate must pass fresh repository/package/runtime/browser QA on its exact head before Core technical completion is restated.

Publication/tagging and production deployment remain separate explicit approval gates.
