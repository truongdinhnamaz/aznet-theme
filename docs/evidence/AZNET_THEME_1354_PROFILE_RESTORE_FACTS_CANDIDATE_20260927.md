# AZnet Theme 1.3.54 Profile Restore Facts Candidate — 27/09/2026

## Goal

Create the next installable Theme candidate after the user's last delivered package `aznet-theme-1.3.53-profile-restore-facts-candidate.zip`, preserving the proven Profile restore/facts behavior while inheriting all canonical AZnet Theme work now present on 1.3.54.

## Source and artifact identity

- Prior delivered base package: `aznet-theme-1.3.53-profile-restore-facts-candidate.zip`.
- Prior base SHA256: `345b6b30a3826d043ca23bd57f9b8e9d8ef0a1c8b778fa44a3c5d3cc04e39923`.
- Exact canonical source for the new candidate: `main@6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`.
- Theme metadata/runtime: `1.3.54`.
- New artifact: `aznet-theme-1.3.54-profile-restore-facts-candidate.zip`.
- New artifact SHA256: `b6bb3b73a42d6b88f11146ff51bca043032f28fe7986fc001e9c5aab17d13cfb`.
- Personal Library path: `/AZnet Theme/aznet-theme-1.3.54-profile-restore-facts-candidate.zip`.

## Inheritance verification

The old 1.3.53 package and new 1.3.54 package were both unpacked and compared by relative file path.

- 1.3.53 package files: 196.
- 1.3.54 package files: 198.
- Old files missing from 1.3.54: **0**.
- New files added relative to the delivered 1.3.53 package:
  - `assets/js/admin/template-library.js`
  - `inc/theme/homepage-surface-map.php`

This establishes package-level additive inheritance: every file shipped in the user's 1.3.53 candidate remains present in the 1.3.54 candidate. Existing files may contain canonical 1.3.54 changes.

## Profile restore/facts compatibility

The build gate initially exposed two stale source-text contracts that expected direct legacy `setting(...)` calls. The accepted D-041 architecture had already moved About/Team/Services reads through the shared effective-source resolver while retaining the legacy keys as fallback inside `homepage_source_value()`.

The build-only QA branch therefore modernized those two contracts to verify behavior rather than the superseded implementation detail:

- About/Team/Services still resolve the old 1.3.53 legacy mappings when scoped mappings are not explicitly initialized.
- Explicit scoped mappings supersede legacy values after initialization.
- Process/FAQ Profile facts retain scoped-first + legacy-fallback compatibility.
- The Profile facts for Services / Quy trình / Hỏi đáp remain protected.

Those QA-only test changes are **not included in the Theme package**. The package itself is built from exact canonical `6fd5db...`.

## Fresh executable build evidence

GitHub Actions run `36310502953`, job `108595373429` executed on PHP 8.1 and completed **success**.

Successful steps:

- exact canonical-source guard;
- inherited Profile restore/facts contracts;
- deterministic package build twice + byte comparison;
- package identity and syntax verification;
- artifact upload.

Artifact ID: `10929341083`.

## Package verification

Fresh package verification:

- ZIP integrity: PASS.
- `style.css` version: 1.3.54.
- `AZNET_THEME_VERSION`: 1.3.54.
- PHP files checked: 137; syntax failures: 0.
- JavaScript files checked: 5; syntax failures: 0.
- No `tests/` directory is shipped in the installable package.
- Deterministic build check: PASS.

## Boundaries

This is an installable **candidate artifact**, not a published GitHub Release and not a production deployment.

- No tag/release was created.
- No production site was updated.
- `remquocanh.vn` production remains on its separately recorded production version.
- Authenticated wp-admin L3/L4 remains a separate QA gate.
