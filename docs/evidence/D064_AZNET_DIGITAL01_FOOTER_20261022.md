# D-064 — AZnet Digital 01 Footer (2026-10-22)

## Ownership and baseline
- Canonical source `main@f9bdffed17d358a3a0667d4c92ce02955d955f2f`, AZnet WordPress running Theme 1.3.96. PR #503 is draft only.
- WordPress owns site logo, tagline, native navigation menus and editor-authored reusable wp_block Footer columns. Theme owns composition and CSS only.
- Verified existing AZnet Footer is sparse (logo, tagline, copyright). Registered footer menu positions `footer`, `footer-contact`, `footer-social`, `footer-policy` are **all unassigned**.
- Existing WordPress-native menu `Footer thiết kế website` ID 2578 has five authored links; `Main Menu chính` ID 1436 is assigned to header-utility. This branch **does not reassign WordPress menus or hardcode content**.
- RootProfile current public API organization route returned 404 `rootprofile_not_found`; do not infer official company facts from SEO JSON-LD or legacy Page HTML and write them to Theme.

## Implementation
- `template-parts/footer/site-footer.php`: corrects misplaced else-branch closing div. Under both menu-backed and WordPress wp_block-column paths, Footer main and bottom now remain direct siblings under Footer inner.
- `assets/css/components/site-footer.css`: scoped navy/orange Digital 01 + Professional preset footer presentation, responsive grid by actual populated source count, accessible focus and hover, 48rem one-column collapse, no made-up placeholder items. Other presets untouched.
- WordPress draft theme `aznet-theme-wpvibe-draft` created and exactly updated with these two GitHub source files; publication NOT performed. Fresh draft HTML shows the corrected Footer nesting. The WordPress menus stay unassigned; no dynamic menu content can appear until editor-owned assignments exist.

## TDD / QA
- RED: [workflow 39753409260](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39753409260) failed precisely `FAIL D-064: native menus: bottom/main escaped the Footer inner shell`.
- GREEN: [workflow 39753715874](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39753715874) passed PHP 8.1 and `tests/offline/d064-digital01-footer-contract.php` after minimal markup and scoped CSS changes.
- Retained full verification initially RED for the existing `professional-footer-mobile-spacing-contract.php` because it finds the last exact `@media (max-width: 48rem)` marker and expects legacy selectors after that marker. Re-scoped new CSS media as `@media screen and (max-width: 48rem)`, preserving effective CSS breakpoint but leaving the last exact marker for the retained contract. Exact corrected head `284446af5091587bac326d3ed13e2e31733ac24e` in CI.
- Real WordPress draft HTML after overwrite: Footer inner contains `main` and `bottom`, WordPress logo/tagline/copyright remain, no orphan menu columns. This proves a draft L3 read, **not** a responsive visual / source content / production L4 PASS.
- Header separate D-063 / PR #502 WP Rocket minified stale CSS remains independently blocked; do not claim addressed by Footer.

## Release gates / next
1. Inspect all exact-head CI results (especially V1 retained static) and verify responsive screenshots + a11y L4. No cross-layer PASS inference.
2. WordPress owner must configure actual Footer menu slots or reusable WordPress wp_block columns; the Theme must never fabricate contact, organization identity, social URLs or policies. Suggested use existing Menu 2578 for footer if owner approves and authoritative contact/social sources independently when available.
3. Preview actual populated Footer; verify linked destinations and mobile nav; preserve rollback to Theme 1.3.96 and existing menu assignments. No merge main, tag, GitHub Release or live publish without fresh owner release approval.

## Owner-approved continuation — WordPress menu preparation (unassigned, 2026-10-22)

Only WordPress-native menus have been authored, not attached to any live menu location. They are non-visible source candidates; current public Footer still remains the 1.3.96 logo/tagline/copyright surface. `wp menu location list` was rechecked after writes: **all four Footer locations still empty**. Header and other location assignments were untouched.

| Future Footer location | Prepared WordPress menu | Native source and content |
| --- | --- | --- |
| `footer` | **3497 — AZnet Footer - Dich vu** | Five verified published WP Pages: 43041 Thiết kế website, 42172 SEO website, 22161 Chăm sóc website, 41978 Nâng cấp website, 20392 Đào tạo |
| `footer-contact` | **3494 — AZnet Footer - Lien he** | Existing public Page 18 Liên hệ và tư vấn; Page 41831 Bảng giá dịch vụ |
| `footer-social` | **3495 — AZnet Footer - Ket noi** | Facebook AZnet URL displayed in the published WordPress Contact Page, `https://www.facebook.com/aznet.official` (WordPress native menu link; not a claimed RootProfile provider payload) |
| `footer-policy` | **3496 — AZnet Footer - Chinh sach** | WP published Pages 3 Quyền riêng tư, 24596 Thỏa thuận sử dụng, 28573 Bảo hành website |

The preexisting native menu ID **2578** was deliberately **not changed**, after finding its “Website giá rẻ” label points to the homepage. Do not silently repair or assign this legacy menu. The new curated menu uses verified published WordPress Pages and leaves the original user-authored menu intact.

- Native menu item IDs: contact 43543–43544, social 43545, policy 43546–43548, service 43549–43553.
- The WordPress write boundary was honored: all navigation data is stored with WordPress menu APIs; Theme code contains no AZnet-specific content or menu IDs.
- RootProfile organization REST `/rootprofile/v1/organization` remained 404 during earlier check. No company address, official email, phone, or other authoritative contact fields were invented or copied into Theme code.
- No call to `menu location assign`, no Theme publish, no canonical main merge; the live site has not gained Footer navigation yet.

### Browser L4 presentation fixture
- L4 run [39756142561](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39756142561): PASS at widths **1705, 1440, 1280, 1120, 1024, 820, 768, 390, 360** with no Footer horizontal overflow or overlapping grid regions, 4→2→1 responsive grid, 3px keyboard focus outline, native logo and all source links. The fixture uses actual published menu-link candidates injected into the public site shell, plus local candidate Footer CSS and known PHP nesting correction. **It is NOT a live WordPress preview integration PASS**.
- The browser fixture was then updated from obsolete menu 2578 links to new verified menu 3497 links at test commit `5e2530b`; exact-head CI must complete before a fresh L4 PASS claim.

### Publication hard gates
1. Final exact-head CI including retained Y3 Footer / R6 checks must finish without unresolved failures.
2. WordPress draft has canonical PHP/CSS; preview currently shows only old public data, because native Footer locations are unassigned. No user-visible data is copied into Theme code.
3. WordPress native Footer menu activation changes live production immediately. Do not assign the four menus before owner authorizes going live and validates rollback/host backup.
4. WPVibe Theme draft publication likewise requires an **explicit publish/go-live instruction** after preview and a recent site-level backup; a generic approval to prepare/configure is not publication authorization.
5. D-063 WP Rocket `/cache/min/` stale CSS problem may prevent delivery of new style bytes until site-owned minification cache is invalidated. Resolve that separate operation and recheck freshly served CSS before claiming production Footer L4 PASS.
6. Rollback: restore original four unassigned Footer menu locations if activated, restore Theme 1.3.96 from previous live backup, and avoid rewriting any published pages or plugin storage.

**EXACT NEXT:** complete final CI, retain source/branch checkpoint, then request explicit production authorization + host backup; on authorization, assign approved native menus, publish draft only if all gates remain healthy, invalidate minified CSS through supported WP Rocket admin operation, and verify live Footer geometry and links immediately.
