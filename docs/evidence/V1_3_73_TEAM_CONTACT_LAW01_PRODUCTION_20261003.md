# AZnet Theme v1.3.73 Team Contact Release + Law 01 Production — 03/10/2026

## Scope

Owner-approved GitHub publication of AZnet Theme `1.3.73` and owner-approved production deployment to the Law 01 pilot `https://lstamduchn.vn/`.

This checkpoint covers the D-047 Team phone/Zalo slice only. It does not widen Theme ownership beyond the two accepted WordPress Page meta facts.

## Canonical implementation

- PR #382 merged to `main@b2cefd4c84199b9dcbe1b3a3529957b4ee3915a0`.
- Theme metadata: `1.3.73`.
- Exact-main verification run `37098100546`: static-contracts SUCCESS; clean-runtime-browser SUCCESS.
- Exact-main X6 run `37098100472`: SUCCESS.
- X6 integrated browser verification: SUCCESS.
- Deterministic package built twice with identical SHA-256.
- Exact-package source identity and packaged PHP lint: PASS.
- Exact-package lifecycle/theme-switch/rollback continuity: PASS.
- D-047 promotion boundary `1.3.72 -> 1.3.73`: PASS.

## Package identity

- File: `aznet-theme-1.3.73.zip`
- Production files: 216
- PHP files: 151
- SHA-256: `b7d542c89efa703f88bbdc396dc4642d2e915d7f3eddb2af28bcc28abd6aad75`

## GitHub publication

- Publication run: `37098312263` — SUCCESS.
- Annotated tag: `v1.3.73`.
- Tag dereference: `b2cefd4c84199b9dcbe1b3a3529957b4ee3915a0`.
- GitHub Release ID: `402335321`.
- Release state: public, draft=false, prerelease=false.
- Release asset ID: `607174020`.
- Public asset size: 570,451 bytes.
- Public asset SHA-256: `b7d542c89efa703f88bbdc396dc4642d2e915d7f3eddb2af28bcc28abd6aad75`.
- Temporary publication workflow was removed from `ops/publish-v1.3.73` after successful readback verification.

## Law 01 production deployment

Target: `https://lstamduchn.vn/`.

Preconditions:
- Owner explicitly confirmed a fresh backup before deployment.
- Stale WPVibe draft was explicitly approved for deletion and removed.
- A fresh draft was cloned from the active Theme `1.3.72`.
- The bounded production files changed by the D-047 slice were updated from exact canonical `1.3.73` source; Theme version markers were advanced to `1.3.73`.
- Draft public preview rendered successfully before publish.

Publication:
- WPVibe published the draft to live `aznet-theme`.
- WPVibe saved the previous live theme directory as `aznet-theme-wpvibe-backup`.
- Rank Math sitemap cache and object cache were automatically purged.

Fresh production verification:
- WordPress: `7.1.2`.
- PHP: `8.5.6`.
- Active Theme: `AZnet Theme 1.3.73`.
- Standard Theme template floor is present with no reported missing templates.
- Fresh public mobile Lighthouse: Accessibility **100/100**, Best Practices **100/100**.
- No production content migration or automatic extraction of phone numbers from existing Team titles was performed.

## Ownership boundary

D-047 remains exact:
- `_aznet_theme_team_phone`
- `_aznet_theme_team_zalo`

These are bounded WordPress Page meta facts for the WordPress-native Team member surface. They are not RootProfile Person/Contact authority and are not duplicated into `aznet_theme_settings`.

## Unknown / not inferred

- Authenticated production wp-admin interaction with the new Team phone/Zalo fields was not freshly browser-tested on `lstamduchn.vn` after deployment.
- Existing personnel titles that already contain `(Tel: ...)` were not parsed, inferred or rewritten automatically.
- No other pilot was deployed by this operation.

## Disposition

**PASS — v1.3.73 technical publication + Law 01 production deployment/public L4.**

Rollback checkpoint: `aznet-theme-wpvibe-backup`.
