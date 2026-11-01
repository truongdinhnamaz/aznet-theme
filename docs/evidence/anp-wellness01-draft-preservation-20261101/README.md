# ANP WPVibe Wellness 01 draft text preservation — 2026-11-01

Purpose: preserve the **text content** of three unrelated Theme-owned Wellness 01 files observed in the existing `aznet-theme-wpvibe-draft` on `anpvietnam.vn` before constructing any independent Industrial 01 release candidate.

Source: authorized WPVibe ANP connection, read-only `read_file` calls. The file snapshots in this directory are reconstructed from numbered WPVibe responses with normalized LF line endings; **they are not byte-for-byte proofs of original on-server files**. No WordPress draft/live content was changed and no binary assets were read.

| WPVibe draft path | Stored evidence path | Text status vs PR #544 |
| --- | --- | --- |
| `assets/css/components/homepage-wellness-01.css` | `homepage-wellness-01.css.txt` | differs from line 2 (66 vs 69 lines) |
| `inc/theme/templates/wellness-01/manifest.php` | `manifest.php.txt` | normalized text same (35 lines) |
| `patterns/homepage-wellness-01.php` | `homepage-wellness-01.php.txt` | differs from line 14 (96 vs 97 lines) |

Four binary WebP files exist in BOTH WPVibe draft listing and the distinct, unmerged [Wellness 01 PR #544](https://github.com/truongdinhnamaz/aznet-theme/pull/544). Reference Git blob SHA on that PR:
- `group.webp`: size 81640, blob `5f521bc4785bd8feadaf11f11072c3c268d9055e`
- `guide.webp`: size 86792, blob `bdf405d4324b4ad09c1d826dbe07c35ee3036eab`
- `meditation.webp`: size 117468, blob `6c97f545d1028e8ce080ca7f8696335a66ed3a82`
- `mobility.webp`: size 104428, blob `a7fb12e4dbecbaf334789900e04806e1d520ab2b`

**Important:** WPVibe reported approximately 79.7, 84.8, 114.7, 102.0 KiB, compatible with Git sizes, but that is NOT binary byte parity. Do not delete, replace or publish the contaminated draft based on this evidence. The raw WebP files remain on the existing draft and source PR #544; preservation of draft-specific binary bytes is still unverified.

Related Industrial 01 release candidate: [PR #552](https://github.com/truongdinhnamaz/aznet-theme/pull/552) head `fc6725654ab20767fecd17412416b2ad12a6c2bd`, based on main `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`. This evidence branch is **not** a production package and must not be merged as part of #552.

Release gate: produce an independent CLEAN package from pinned PR #552 source; ensure only its 5 intended production-file modifications are included, excluding unrelated draft-only Wellness assets. Before deployment, independently verify fresh full hosting + database backup and tested rollback, complete required real-WordPress QA and obtain explicit merge/publish approval. No live theme changes from this preservation activity.
