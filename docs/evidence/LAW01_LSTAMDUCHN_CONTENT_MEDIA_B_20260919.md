# Law 01 — lstamduchn.vn Content / Media Inheritance Micro-slice B

Date: 2026-09-19  
Status: runtime checkpoint; no Theme code delta  
Canonical repo baseline: `main@b20c5329ef4724969f46954bc9e1c9ac21e3e19f`  
Pilot: `https://lstamduchn.vn/`

## Goal

Complete the next bounded parity delta by reusing WordPress-owned content/media already present on the pilot site.

This slice deliberately does **not** rebuild the homepage, does not change provider ownership, and does not fabricate Team identities, credentials, social links, addresses, customer counts, satisfaction rates, or other client-domain facts.

## Source / ownership

- Theme remains presentation owner.
- WordPress owns the edited Page/Post/Menu/Media state.
- Existing Theme mappings are reused as-is.
- No RootProfile/private storage read was introduced.
- No Theme PHP/CSS/JS changed in this micro-slice.
- No release/package/deploy action occurred.

## Baseline before mutation

### Front page — Page ID 2

- title: `Luật sư Tâm Đức - Hà Nội`
- excerpt: empty
- featured media: `182`
- the active Law01 fallback mapped:
  - H1 <- WordPress site title
  - value proposition <- front-page title
  - supporting lede <- front-page excerpt
  - slogan <- WordPress tagline
  - image <- front-page featured image

### About — Page ID 34

- title: `Giới thiệu`
- excerpt: `Luật sư Tâm Đức – Hà Nội hỗ trợ khách hàng trong các vấn đề dân sự, đất đai, hình sự, hôn nhân gia đình, lao động và pháp lý doanh nghiệp.`

### Latest native Posts

- Post 147 featured media: 65
- Post 144 featured media: 65
- Post 140 featured media: 65

The three homepage cards therefore rendered the same image.

Existing suitable WordPress Media assets already available:
- 182 — `aznet-law01-hero.webp`
- 183 — `aznet-law01-editorial-1.webp`
- 184 — `aznet-law01-editorial-2.webp`
- 185 — `aznet-law01-editorial-3.webp`
- 186 — `aznet-law01-editorial-4.webp`

### Footer menu locations

- `footer`: empty
- `footer-contact`: empty
- `footer-social`: empty
- `footer-policy`: empty
- existing `Main menu` and `Tâm Đức - Liên hệ chính thức` were already legitimate WordPress-owned sources.

## Applied WordPress-owned delta

### Hero content — Page ID 2

Title changed to:

`Giải pháp pháp lý rõ ràng cho cá nhân và doanh nghiệp`

Excerpt changed to:

`Đồng hành cùng bạn trên các vấn đề pháp lý với sự tận tâm, chuyên nghiệp và minh bạch.`

Featured media **retained** at attachment 182. No duplicate Hero asset was created in this slice.

Fresh rendered runtime confirmed:

- H1 remains sourced from the existing WordPress site title: `Văn phòng Ls Tâm Đức - Hà Nội`
- value proposition now renders the new Page title
- supporting lede now renders the new Page excerpt
- slogan remains `Trọn Tâm với khách – Vẹn Đức với nghề`
- CTA destinations remain the existing Contact and Services Pages
- quick-contact phone remains the existing WordPress utility menu source
- Hero media remains attachment 182

### About content — Page ID 34

Title changed to:

`Văn phòng Luật sư Tâm Đức - Hà Nội`

Excerpt changed to:

`Với phương châm “Trọn Tâm với khách – Vẹn Đức với nghề”, Văn phòng Luật sư Tâm Đức - Hà Nội hướng tới các giải pháp pháp lý rõ ràng, thực tiễn và phù hợp với nhu cầu của cá nhân và doanh nghiệp.`

The primary navigation item for this Page was explicitly preserved as `Giới thiệu` after the Page title change so navigation semantics did not drift.

### Latest native Post media

- Post 147 -> attachment 185
- Post 144 -> attachment 184
- Post 140 -> attachment 183

Fresh rendered runtime confirmed that the three Latest cards now render three distinct WordPress Media images while retaining the native Post query/content.

### Footer source projections

Reused existing menus rather than creating duplicate Theme data:

- `Main menu` -> `footer`
- `Tâm Đức - Liên hệ chính thức` -> `footer-contact`

Fresh runtime confirmed Footer now renders:
- WordPress identity/logo/tagline
- a navigation column
- a contact column containing the existing phone source

No address/email/social data was invented.

## Runtime evidence

Fresh rendered HTML on the active pilot confirmed:

### Hero

- `Giải pháp pháp lý rõ ràng cho cá nhân và doanh nghiệp`
- `Đồng hành cùng bạn trên các vấn đề pháp lý với sự tận tâm, chuyên nghiệp và minh bạch.`
- attachment 182 remains the Hero image
- existing CTA/menu destinations remain intact

### About / Team band

- About heading now renders `Văn phòng Luật sư Tâm Đức - Hà Nội`
- updated About summary renders
- Team shell remains present
- no fabricated member cards are rendered

### Latest

Distinct images render for posts 147 / 144 / 140 from attachments 185 / 184 / 183.

### Header / Footer

- primary Header navigation preserved `Giới thiệu`
- Footer source projections are now populated from existing WordPress menus

## PASS

- **L0 ownership/source:** PASS for this bounded delta.
- **L3 WordPress runtime/content projection:** PASS for Hero copy, About copy, three distinct Latest featured images, Footer primary/contact menu projections.
- Existing Theme composition was inherited; no rebuild.
- No parallel domain store or private provider read.
- No fake people, credentials, business metrics, addresses, email addresses, or social profiles.

## BLOCKED / UNKNOWN

- **L4 visual parity on the actual live pilot after this WordPress data mutation:** not claimed in this checkpoint.
- Team member identities/roles/portraits remain blocked until a legitimate authoritative source exists.
- Footer social/address/email remain absent because no legitimate source has been established.
- Business metrics such as `10+`, `500+`, `95%` remain blocked.
- A new reference-grade Hero image was not generated/uploaded in this micro-slice; existing attachment 182 was retained.
- The active pilot still runs the previously deployed Theme package; this evidence does not claim that merged PR #162 has been released/deployed to the pilot.

## Rollback

WordPress rollback values:

- Page 2 title -> `Luật sư Tâm Đức - Hà Nội`
- Page 2 excerpt -> empty
- Page 2 featured media remains 182 (no rollback required)
- Page 34 title -> `Giới thiệu`
- Page 34 excerpt -> `Luật sư Tâm Đức – Hà Nội hỗ trợ khách hàng trong các vấn đề dân sự, đất đai, hình sự, hôn nhân gia đình, lao động và pháp lý doanh nghiệp.`
- Post 147 featured media -> 65
- Post 144 featured media -> 65
- Post 140 featured media -> 65
- Footer menu location -> empty
- Footer Contact menu location -> empty
- Primary menu item 127 title -> `Giới thiệu` (already preserved)

## Resource checkpoint

During final verification WPVibe reported 299 / 300 calls used in the rolling 24-hour window. No operation failed, but further live WordPress work was intentionally stopped at this recoverable checkpoint rather than risk partial progress.

## Exact next

After the WPVibe call window has capacity, run the next bounded live-data check:

1. verify authoritative Team/provider availability without private storage or heuristic identity;
2. if absent, keep Team fail-soft and proceed only with Theme-independent media/reference work;
3. generate/prepare a higher-fidelity Hero asset as a separate reversible media micro-slice if the existing attachment 182 is visually insufficient;
4. run fresh live L4 visual/a11y verification before any release/deploy claim.
