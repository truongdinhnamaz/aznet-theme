# aznet.vn v1.3.98 production update preflight

Date: 27 October 2026 (Vietnam time). Repository: `truongdinhnamaz/aznet-theme`.
Scope: bounded, read-only deployment preparation. This is not a deployment, full installed-package attestation, hosting restore test, or provider certification.

## Goal and ownership

Prepare the separately gated update of existing `aznet.vn` from Theme 1.3.97 to the official 1.3.98 ZIP. Retain the current Brand Light 2026 presentation, original Theme slug, WordPress content/front-page/menu/media/settings, and all provider-owned state. No product rule changes are proposed.

Canonical rules: [source governance](../SOURCE_GOVERNANCE.md), [AZT-05](../source/AZT-05-product-constitution.md), [AZT-02](../source/AZT-02-architecture.md), and [AZT-04](../source/AZT-04-roadmap-qa-decisions.md). Historical DOCX checkpoints are not current implementation state.

## Canonical release and retained QA evidence

- Fresh GitHub API read: canonical main and latest published release `v1.3.98` point to `d4ecae7914b2d7e9107dfdf50e7de9d693481d92`; PR [#533](https://github.com/truongdinhnamaz/aznet-theme/pull/533) is merged.
- Official asset metadata: `aznet-theme-1.3.98.zip`, 654,161 bytes, 242 packaged runtime files; SHA-256 `dcca1a3a84707b0ec1ea7c11a75870ece44fba6e96dd0b75494c052b47341360`. This session read the published GitHub asset digest and retained release evidence; it did not download/re-hash the binary.
- Fresh exact-main workflow-status read: [V1 40529351105](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40529351105), [X6 40529351026](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40529351026), and Curtain 01 40531104594 are completed SUCCESS. Four historical publication workflows are SKIPPED, not PASS.
- GitHub comparison from accepted 1.3.97 base `6239e4ef25b4bfc1b62d0cacd47d94c9abcfde7d` to current main changes eight repository paths. Within the runtime package, only the paired `style.css` and `functions.php` version markers change 1.3.97 to 1.3.98. The other six paths are CI/version-regression infrastructure.
- The retained clean WordPress 7.1.3/PHP 8.5 and browser evidence recorded in the release/QA-only PR #541 is prior disposable-environment evidence, not a fresh production test in this session. Current host PHP 8.4.26 and actual site DB/plugins are separate evidence scopes.
- Deployment has no evidenced new behavior relative to the accepted 1.3.97 source. Do not present a version-marker update as a fix for any secondary-page content/layout defect.

## Fresh authenticated production baseline

WPVibe `site_info`, `theme list`, read-only Theme file access, `theme mod list`, `option get page_on_front`, and read-only WordPress posts SELECT were used.

| Item | Observed |
| --- | --- |
| WordPress / PHP | 7.1.3 / 8.4.26 |
| Active Theme / slug | AZnet Theme 1.3.97 / `aznet-theme` |
| Existing Theme backup | `aznet-theme-wpvibe-backup`, inactive, 1.3.97 |
| WPVibe Theme draft | None listed |
| Visual / homepage preset | `digital-01` / `digital-01` |
| Front Page | `page_on_front=43515` |
| Logo / Custom CSS Post ID | 43442 / -1 |
| Header / sticky / catalog | standard / sticky / off |
| Footer | professional |
| Menu locations | primary=1523; header-utility=1436; footer=3497; footer-contact=3494; footer-social=3495; footer-policy=3496 |

Brand Light is an authored presentation layer of the current native front Page; do not switch it by inferring a preset from its slug/title. The front-page ID above is a native WordPress configuration readback, not a routing heuristic.

### Native Page continuity fingerprints

These MD5 values are read-only continuity fingerprints of WordPress-owned `post_content`, not a cryptographic backup or SEO/provider payload.

| Page ID | Type / status | Modified GMT | Characters | Content MD5 |
| --- | --- | --- | ---: | --- |
| 2 | page / publish | 2026-10-26 04:18:24 | 38042 | `1a12330f3c0f1ef5675f95f5728e2e55` |
| 18897 | page / publish | 2026-10-22 05:45:11 | 34276 | `e98c8e8e4b37f17f7f0991c9e8e0cdaa` |
| 43515 | page / publish | 2026-10-26 11:57:49 | 30996 | `6a71b4825c571788186f23df52fcee63` |

## Installed-source comparison and limitations

- WPVibe read-only listing completed both pages: 223 distinct visible source paths. Applying the canonical release packager exclusions yields 242 runtime paths, of which exactly 223 have extensions visible to the WPVibe wp-content listing. All 223 visible names match; zero missing or unexpected visible source names.
- The remaining 19 package paths comprise fonts, SVGs and WebP images outside that listing's allowed extensions. Their installed bytes/presence are UNKNOWN, not missing.
- Four files were returned completely with numbered lines: `style.css`, `functions.php`, `inc/theme/assets.php`, and `assets/css/components/homepage-digital-01-brand-2026.css`. Reassembled returned text matches the exact canonical release text, allowing only the two approved version substitutions in style/functions and line-ending/trailing-newline normalization.
- Returned-text SHA-256 of `inc/theme/assets.php`: `5414be09be2e495f404ffb2ccca4ce77ac8b420aee4cc942cb88330e034cd44f`, equal to canonical source.
- Returned-text SHA-256 of Brand Light CSS: `3a8d924cefc8a8a881f55673c8071a7d2d931ac63064006a74156f95eaa490fc`, equal to canonical source; public asset fingerprint is `1.3.97-3a8d924cefc8`.
- Backup `style.css` and `functions.php` returned-text hashes match the active 1.3.97 pair: `fd1880919a86ff2a15947fd352bc6b84a8536edc8c65236551ee1ad9cd712ff8` and `f5914cd714a997ef69230807d6d2bf5bafb3ae79380ea3e1ab8495cf786486c3`.
- WPVibe explicitly warns that secret patterns may be redacted. These are hashes of returned/reconstructed text, not direct server archive hashes. Four text matches and a complete path list do not attest all installed files, backup completeness, or the official ZIP's full byte identity.

## Fresh public render baseline

WPVibe fetched the rendered public homepage HTML (127,081 characters returned, no truncation observed):

- One H1: “Xây dựng website tăng trưởng thực sự”.
- Title: “AZnet – Thiết Kế Và Phát Triển Website Doanh Nghiệp”.
- Canonical: `https://aznet.vn`.
- Brand Light CSS loaded with the matching fingerprint above.
- Zero literal legacy `section/row/col/ux_*` shortcode tokens and zero visible PHP fatal/parse/warning/deprecated markers in this returned HTML.

This proves only the bounded public HTML baseline. No new desktop/mobile visual, a11y, checkout/email transaction, authenticated admin, full-site crawl, or provider L5 PASS is claimed.

## Recovery and publication gate

**PASS:** L0 current-state/release reconciliation, 223/223 visible path-name comparison, four bounded source-text comparisons, matching backup version-marker pair, native configuration/content fingerprints, and bounded current public HTML smoke.

**BLOCKED / UNKNOWN:**

1. No independently accessible current full hosting backup has been established for `aznet.vn`. Backup timestamp, inclusion of database/uploads/files, and the available restoration route still need owner confirmation or read-only host evidence. There is no known backup plugin in the observed active-plugin list.
2. The existing WPVibe folder is a Theme-files-only safety copy. Two matching marker files do not prove a complete recoverable snapshot. Do not replace this evidence with a hosting-restore PASS.
3. The source-release approval does not itself authorize production installation. The retrievable continuation asks to continue; a separate, concrete owner decision for installing official 1.3.98 on `aznet.vn` is not established here.
4. This session's direct ChatGPT conversation link was blocked by a sign-in wall, and Personal Context was unavailable. Continuation was reconstructed from the supplied screenshot plus current canonical GitHub and authenticated site evidence; no claim to have read the old conversation in full.

No WordPress file, draft, content, Theme setting, plugin state, database data, backup, live version, tag or release was mutated during this preflight.

## Concrete cutover and rollback after the gate

- Revalidate canonical release asset/digest, active 1.3.97, front-page assignment, content fingerprints and existing backups immediately before installation. Any drift invalidates this snapshot and must be assessed.
- With explicit installation approval and a confirmed recent full-site backup/restore route, use the clean official 1.3.98 ZIP under the original `aznet-theme` slug. Do not publish an unverified draft or overwrite the existing WPVibe backup to create an installer.
- Verify installed version pair, Brand Light asset hash, stable settings/menu/front-page/content fingerprints, live homepage/About and relevant public commerce/provider projections. Record only the evidence layers actually checked.
- On material regression, restore the captured pre-update Theme directory to the original `wp-content/themes/aznet-theme` path through the confirmed recovery route; keep native content/settings intact. Do not simply activate the backup folder under another stylesheet slug, which can select different Theme mods.
- Owner confirmation is not an independent restore rehearsal; report that distinction if no rehearsal has occurred.

**EXACT NEXT:** Obtain one owner confirmation covering the recent full hosting backup (files, database and uploads), its restoration route, and authorization to install the exact official 1.3.98 ZIP on `aznet.vn`. Then execute the bounded update and fresh production verification without repeating already valid release QA.
