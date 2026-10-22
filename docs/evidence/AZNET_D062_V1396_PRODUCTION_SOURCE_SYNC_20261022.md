# D-062 — AZnet Theme 1.3.96 site publication and canonical-source synchronization

**Date:** 22/10/2026. **Scope:** AZnet Theme presentation + source/provenance only, site `aznet.vn`. **No provider/domain mutation.**

## Ownership, authorization and release boundary

- Owner explicitly ordered Theme 1.3.96 site publication, then confirmed **“Đã sao lưu đầy đủ”** before WPVibe cutover. This is a **user-reported** complete host backup (database + `wp-content/uploads` + then-active Theme); hosting backup bytes or successful restore were **not independently inspected**.
- Owner later answered **“duyệt”** directly to the stated NEXT of synchronizing the already-published Theme candidate to canonical GitHub `main` through PR #501. This is approval for the bounded source merge, **not** for a GitHub tag/Release or another site deploy.
- WordPress retains Page 2/Homepage and Page 18897/About content/media/SEO ownership. Theme owns the D-057 Header, D-058 Landing full-width/inverse, D-059 paired 1.3.96 release metadata, D-060 authored About Showcase V2 presentation, D-061 Homepage Products container presentation. No code or private storage from RootProfile/ConvertFlow/WooCommerce moved into Theme.

## Exact candidate / verification

- GitHub [composed Draft PR #501](https://github.com/truongdinhnamaz/aznet-theme/pull/501) release runtime parent `f3ee4f57a4f2f3b09cf37704ad942047c9fbf37c` stacked D-059/D-060 and the exact D-061 blob-based additions. Existing PRs #498/#499/#500 remain traceable; no history overwritten.
- WPVibe draft and candidate had **236 identical runtime paths**. Seven changed runtime files matched exact candidate text, with only the expected ` (WPVibe Draft)` header suffix in the draft's `style.css`. Separate Brand Light 2026 CSS/PHP remained preserved in its own GitHub branch and intentionally excluded from this Theme release; the leftover PHP was removed from the unpublished WPVibe draft only after being archived and byte-parity checked.
- Exact previous candidate head PR CI: **44 triggered workflows, 43 SUCCESS, 0 FAIL, 1 SKIPPED** (optional D-050C real-site private-source L5). One C5 Curtain local WordPress navigation initially `ERR_EMPTY_RESPONSE` and later succeeded on a same-head job rerun. Fresh CI on any amended source-only PR head and on post-merge `main` must be recorded separately.
- Dedicated Homepage Products Chromium [run #39742440401 attempt 2](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39742440401) after cutover: 6/6 tested widths 360/390/768/1024/1440/1920 PASS. L4 methodology applies the identical candidate D-061 CSS rule as an overlay on the actual public Page 2 DOM; this is not full no-overlay pixel-level visual certification. Screenshot/geometry evidence artifact **12411653261**.

## Actual aznet.vn deployment / rollback

- One authorized `WPVibe.publish_draft_theme(site_url=https://aznet.vn, saved_customizations=set_aside)` returned success; WP Rocket cache, Rank Math sitemap cache and object cache were purged.
- Fresh authenticated WPVibe `site_info` and Theme inventory confirmed active **AZnet Theme 1.3.96**, WP 7.1.3, previous **Theme 1.3.95** retained as `aznet-theme-wpvibe-backup`. WPVibe backup protects **Theme files only** and must not be confused with the separate owner-reported full-host backup. Do not overwrite/delete either recovery asset without a separate approved operation.
- Public Homepage HTML renders live `wp-theme-aznet-theme`, **one H1**, native Hero and Products; `homepage-digital-01.css?ver=1.3.96-81179f0ad9bf` loads from actual live Theme and About V2 CSS does **not** load on Homepage.
- Public `/gioi-thieu/` renders existing Page 18897, **one H1**, authored `aznet-theme-about-showcase` opt-in and correctly scoped `about-showcase-v2.css?ver=1.3.96-edf955bb69c8`; Homepage Digital01 stylesheet is absent there.
- Live `assets/css/components/homepage-digital-01.css` (**31,122 characters**), `assets/css/components/about-showcase-v2.css` (**21,946 characters**), `inc/theme/assets.php` (**31,978 characters**) each match canonical candidate content after LF normalization. Site remains out of maintenance mode.
- WordPress source Page 2 `publish` / modified `2026-10-22 05:27:42`; About Page 18897 `publish` / modified `2026-10-22 12:45:11` unchanged by Theme publication.

## Outstanding, not silently promoted to PASS

- Generic numeric-like figure captions and titles visible in Homepage Page 2 remain **WordPress editorial data quality** work, not Theme CSS work.
- Existing About Rank Math canonical metadata issue is separately owned by the SEO owner; Theme must not direct-write plugin private storage.
- Mobile Lighthouse prior snapshot had accessibility contrast finding (96/100); not proven fully resolved by this release. Optional external-provider integration L5 is separately SKIPPED/UNKNOWN.
- Live Theme publication is **not** itself a canonical `main` merge, deterministic GitHub Release ZIP or annotated version tag. This source sync records a separate owner-approved GitHub merge action and must capture its actual result in later immutable PR/Actions evidence.
- **Recovery:** preserve live Theme 1.3.95 backup, owner-reported site backup, WordPress revisions and the exact pre-merge main SHA `0cd22f023a3b63c90a17cb73188e812f59ed7256`. Source rollback must be performed as a bounded revert; do not force-overwrite `main`.

**EXACT NEXT:** Merge the composed PR #501 into canonical `main` only when its exact amended head is GREEN, then run fresh exact-main CI. Keep GitHub tag/Release as a separately approval-gated action.
