# U0 Control Center Browser / Visual / A11y Verification

Date: 2026-09-03
QA layer: L4 Browser / Visual / A11y
Production code candidate: `4b4b79e179356cd9db8dca81064e748e931da883`
Production work branch before this evidence commit: `f14aa4d4d248bb7b9f63c1211f4bc71864fe00d4` (production bytes equivalent to the candidate; adds L3 evidence only)
Verification branch: `test/u0-control-center-browser-v2`
Verification PR: #20 (test-only; must remain unmerged)

## Fresh closure run

- GitHub Actions run: `33754966491`
- Job: `100647084179`
- Test-harness head: `fed11833f743e0af3fd4d25d008c4a3b8c76d792`
- Artifact: `u0-control-center-browser-evidence`
- Artifact ID: `9893110797`
- Artifact SHA-256: `a5475fd8d3601a9d7e5463869718d65f6a93a3ad2a6cf0013538584a28bfb310`

## Runtime matrix

- WordPress: `7.1`
- PHP: `8.1.34`
- AZnet Theme header version: `0.1.0-alpha.7`
- Node: `22.23.2`
- Playwright: `1.55.0`
- Chromium: `140.0.7339.16`
- MySQL: `8.0` service in isolated GitHub-hosted runtime

## Automated PASS scope

Authenticated administrator browser verification passed for both agreed viewports:

| Viewport | Size | Horizontal overflow | Visible keyboard focus | Axe critical/serious |
| --- | --- | ---: | --- | ---: |
| desktop | 1440x1000 | 0px | PASS (`Thiết lập Logo`) | 0 |
| compact | 1024x768 | 0px | PASS (`Thiết lập Logo`) | 0 |

Both cases also verified visible Control Center content/actions:

- `AZnet Theme`
- `Logo`
- `Primary Menu`
- `WooCommerce`
- `Lưu thiết lập nền`
- `Đặt lại thiết lập AZnet Theme`

The runtime PHP gate reported no `PHP Fatal error`, no `PHP Warning`, and no `Uncaught` error.

## Manual screenshot review

The downloaded artifact SHA-256 matched GitHub exactly. Both screenshots were opened and reviewed directly:

- `control-center-1440x1000.png`
- `control-center-1024x768.png`

Review found no clipped status card, no overlapping WordPress admin chrome, no hidden save/reset control, no destructive horizontal overflow, and no obvious unreadable/colliding presentation at the tested viewports.

## Artifact structure independently checked

The artifact contained:

- `browser-summary.json`
- `axe/desktop.json`
- `axe/compact.json`
- both screenshots
- `runtime-report.txt`
- `php-error.log`
- `wp-server.log`

Independent artifact inspection confirmed both Axe JSON files contain zero `critical`/`serious` violations and `browser-summary.json` contains exactly two passing cases.

## UNKNOWN / not inferred

- This does not prove U5 provider integration or L5 coexistence.
- This does not prove U6 customer-ready release.
- Upgrade/package continuity remains a separate Task 7 gate.
- RootProfile E5-C remains separately contract-gated.
- No customer site was deployed or changed by this verification.

## Provenance rule

This evidence commit must not change production Theme bytes. Upgrade-continuity verification must use the exact U0 production candidate `4b4b79e179356cd9db8dca81064e748e931da883`, or a tree proven production-byte equivalent to it.
