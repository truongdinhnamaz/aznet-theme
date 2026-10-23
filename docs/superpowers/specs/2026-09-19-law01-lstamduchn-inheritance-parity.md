# Law 01 — lstamduchn.vn Inheritance Parity Plan

Date: 2026-09-19  
Status: owner-approved design/execution plan  
Canonical repository: `truongdinhnamaz/aznet-theme`  
Canonical baseline inspected: `main@46601c46d7e9a2cb5a45730b8ecf0153b984806d`  
Pilot: `https://lstamduchn.vn/`  
Reference: supplied Tâm Đức - Hà Nội homepage demo screenshot

## 1. Goal

Bring the active Law 01 Burgundy homepage on `lstamduchn.vn` materially closer to the approved demo **by inheritance, not rebuild**.

The implementation rule for this slice is:

> Preserve every currently-correct structure, contract, responsive behavior and source-owned mapping. Change only presentation that is demonstrably off-target, and add only the missing slots/data projections that the existing composition cannot already express.

This plan does **not** authorize replacing the existing Homepage Composer, duplicating WordPress/provider data, fabricating client facts, or rewriting the page from scratch.

## 2. Fresh baseline

Authenticated live-page inspection was performed with the WPVibe preview explicitly stopped so the comparison is against the active site rather than the existing draft-preview theme.

Observed active runtime:

- WordPress 7.1.1
- PHP 8.5.6
- AZnet Theme 1.3.12
- Law 01 Burgundy preset active
- current site identity/logo, primary menu, utility phone menu and search already render from WordPress-owned sources
- Hero, Trust, Services, Profile/Team, Latest Posts and Footer already exist in the Theme composition
- the canonical Theme already contains the approved Law 01 reference-parity structure from PR #147

Therefore this slice is a **delta/parity completion** slice, not a page-construction slice.

## 3. Ownership constraints

### Theme may own

- spacing, typography, color, border, shadow, responsive layout
- section composition and ordering
- Header/Footer presentation
- card and grid presentation
- presentational iconography
- consumer-side rendering of public/source-backed fields
- fail-soft behavior when a source is absent

### Theme must not own

- authoritative person identity, credentials or Team membership
- business statistics presented as factual claims
- WordPress post/page/category/media state
- RootProfile private storage or inferred identity
- duplicated client-domain records
- fabricated people, credentials, testimonials, customer counts or success rates

## 4. Inheritance parity matrix

| Surface | Current live state | Reference target | Status | Required delta | Owner/source |
| --- | --- | --- | --- | --- | --- |
| Header shell | Existing sticky Law01 header, shared shell | Same overall composition | **KEEP** | Preserve current shell/responsive/navigation behavior | Theme presentation |
| Brand lockup | Logo + one-line site title | Logo with stronger two-line legal-office lockup | **ADJUST** | Presentation only; do not duplicate site identity data | WordPress identity -> Theme |
| Primary nav | Six existing menu items | Same general menu pattern | **KEEP** | No structural rebuild | WordPress menu |
| Search | Existing search input + text submit | Compact search pill/icon treatment | **ADJUST** | CSS/markup presentation only if current semantic search remains intact | Theme |
| Header CTA | Utility phone currently occupies action area | Distinct burgundy/red consultation CTA | **ADD / MAP** | Prefer an existing WordPress-owned destination/contact action; no hardcoded client-domain state | WordPress/menu/page -> Theme |
| Hero shell | Existing 48/52 Law01 grid | 48/52 reference composition | **KEEP** | Do not rebuild | Theme |
| Hero eyebrow | “Văn phòng luật sư” | Equivalent | **KEEP** | Minor typography only if needed | Theme |
| Hero H1 | Site title | Branded office name | **ADJUST CONTENT SOURCE, NOT TEMPLATE** | Use current WordPress-owned authoring/mapping flow | WordPress |
| Hero supporting copy | Current value line is very short | Multi-line value proposition | **ADD CONTENT** | Populate via existing WordPress-owned Hero authoring path | WordPress |
| Hero slogan | Current slogan matches brand phrase | Same role as demo | **KEEP** | Typography/spacing only | WordPress -> Theme |
| Hero CTAs | Primary + secondary already exist | Two CTA pattern | **KEEP / POLISH** | Preserve destinations; tune visual treatment | Theme |
| Hero contact row | One phone number | Two contact items in demo | **ADD ONLY IF SOURCE EXISTS** | Render additional utility contact only when supplied by WordPress/public source | WordPress |
| Hero image | Existing 16:9 law-office image | Premium office/court-law visual matching reference mood | **REPLACE MEDIA** | New WordPress Media asset; retain existing slot | WordPress media |
| Trust strip | Four items already render | Four compact gold trust items | **KEEP / ADJUST COPY** | Preserve four-item structure; wording remains source-safe/general | Theme / source-backed copy |
| Services section | Six cards already render | Six compact cards in one row on wide desktop | **KEEP** | No structural rewrite | Theme + WordPress pages |
| Service order/titles | Current domain-specific titles | Reference category set/order differs | **ADJUST DATA/MAPPING** | Reorder/map existing legitimate service Pages; do not hardcode client data into Theme | WordPress Pages |
| Service excerpts | Existing source-backed text | More compact cards | **ADJUST PRESENTATION** | Keep excerpts source-backed; clamp/hide only as approved visual behavior | Theme |
| About block | Existing heading, lead, CTA | Richer two-column-feeling editorial block | **KEEP / ENRICH** | Use existing About source; add copy through WordPress, not Theme literals | WordPress |
| Business statistics | Not rendered | Demo shows three metrics | **BLOCKED UNTIL AUTHORITATIVE** | Do not add 10+/500+/95% or equivalents without legitimate source | Business/source owner |
| Team section shell | Exists, heading + CTA | Four portrait cards | **KEEP SHELL / ADD DATA PROJECTION** | Preserve existing Team band; populate cards only from legitimate mapped source | WordPress presentation input / RootProfile authoritative when contract exists |
| Team portraits | No people currently render | Four aligned portraits | **BLOCKED FOR REAL PEOPLE** | Real personnel require authoritative identity + real/approved images; synthetic people allowed only in non-client demo fixtures | RootProfile / WordPress media |
| Team names/roles | Not rendered | Name + role beneath portrait | **BLOCKED FOR AUTHORITATIVE CLAIMS** | No inference from slug/title/URL | RootProfile/public contract |
| Latest posts section | Three native posts already render | Three equal-height article cards | **KEEP** | Existing composition retained | WordPress query |
| Latest images | All three currently reuse same image | Three distinct subject-relevant images | **REPLACE MEDIA** | Assign individual featured images to posts | WordPress media/post |
| Latest card rhythm | Existing 3:2 images and excerpts | Compact equal-height reference cards | **ADJUST** | CSS only | Theme |
| Footer shell | Existing standard footer | Four-column branded footer | **KEEP FOUNDATION / ENRICH** | Extend presentation around existing footer source projections | Theme |
| Footer identity | Logo/title/slogan already render | Stronger branded column | **KEEP / POLISH** | Presentation only | WordPress identity |
| Footer contact | Empty beyond identity | Address/phone/email column | **ADD ONLY FROM SOURCE** | Render WordPress/public owner data when present | WordPress/public source |
| Footer quick links | Not populated | Quick-link column | **ADD/MAP** | Reuse WordPress menu; do not duplicate links in Theme settings | WordPress menu |
| Footer social | Not populated | Social icons | **ADD ONLY FROM SOURCE** | Public-safe social URLs only | source owner / public contract |
| Responsive | Existing Theme behavior already covered historically | Desktop/tablet/mobile parity | **KEEP + REGRESSION** | Any delta must preserve current no-overflow/menu/a11y behavior | Theme |
| Accessibility | Existing semantic headings/nav/buttons | Same visual target without semantic regression | **KEEP + REGRESSION** | No visual-only shortcut that breaks labels/focus/contrast | Theme |

## 5. Asset plan

The current component slots are sufficient for the principal media needs; no new media subsystem is justified.

Required/optional assets:

1. **Hero image — required for parity**
   - target: warm premium law office, wood, books, scales, restrained burgundy/gold mood
   - no AI-rendered text/logo should be baked into the image
   - logo/brand text stays real HTML or approved real logo media

2. **Team portraits — conditional**
   - for a real client site: use real/approved people images and authoritative names/roles
   - normalize crop, background, lighting and framing at presentation/media level
   - synthetic portraits are acceptable only for generic template fixtures, never as purported Tâm Đức personnel

3. **Latest post images — required for visual quality**
   - three distinct featured images
   - topic-appropriate and source-owned by WordPress Media
   - no Theme hardcoding of image URLs

## 6. Implementation order

### Micro-slice A — Presentation-only parity, zero new domain data

RED:
- write/extend static/browser assertions for the approved inherited composition:
  - existing sections remain present
  - no rebuild/duplicate section
  - Header search/CTA styling target
  - Footer multi-column presentation can fail-soft when source columns are absent
  - Services/Profile/Latest current source mappings remain intact

GREEN:
- minimal CSS/template presentation changes only

Regression:
- existing Law01 Browser Quality
- Header/Footer suites
- mobile navigation
- no horizontal overflow
- covered Axe critical/serious blockers remain zero

### Micro-slice B — WordPress-owned content/media completion

Use existing WordPress authoring/mapping paths to improve:
- Hero supporting copy
- Hero media
- service order/content where legitimate Pages already exist
- About copy
- featured images for latest Posts
- footer menu/contact projections where WordPress already owns them

No Theme production code should be required for a field that the current composition already supports.

### Micro-slice C — Team projection

Proceed only with a legitimate source.

Until the authoritative provider contract is available, the Theme may continue rendering approved WordPress Page/direct-child presentation input only as non-authoritative presentation. It must not infer or certify identity/membership.

### Micro-slice D — Business metrics

Remain blocked unless a legitimate source owns and supplies them.

Do not implement “placeholder facts” on the real client site.

## 7. Acceptance gate

The inherited implementation passes only when fresh evidence on `lstamduchn.vn` shows:

- the existing Law01 composition is preserved rather than replaced
- no duplicate homepage sections or parallel data store exist
- desktop visual hierarchy materially matches the supplied reference at 1440px
- layout remains valid at 1024px, 390px and 320px
- Hero/Services/Profile/Latest/Footer maintain one coherent inner shell
- mobile menu/focus behavior remains correct
- no horizontal overflow
- no blocking Axe critical/serious finding in covered surfaces
- Theme-owned requests have no blocking failure
- missing provider/business facts fail-soft rather than being fabricated
- no RootProfile/private-provider storage read is introduced

“Looks similar” is not sufficient to claim integration/data PASS. Visual parity, runtime behavior and source ownership are separate gates.

## 8. Rollback

- Theme production changes must be isolated on a work/feature branch.
- WordPress content/media changes must be enumerated separately before mutation and retain the prior IDs/values needed for reversal.
- No destructive deletion is required for this plan.
- Existing PASS behavior is retained unless a fresh regression demonstrates invalidation.

## 9. Current checkpoint

### PASS

- The current live homepage already contains the principal Law01 composition required by the reference.
- The Theme has an accepted/merged reference-parity foundation.
- The active site already supplies WordPress identity, navigation, phone utility menu, Hero media slot, Services, About, Team shell, Latest Posts and Footer identity.
- Rebuild is unnecessary and explicitly out of scope.

### BLOCKED / UNKNOWN

- Real Team people, roles and portraits are not currently demonstrated by an authoritative public contract.
- Business statistics from the demo are not authorized facts.
- Additional footer contact/social fields are not assumed until a legitimate source supplies them.

## 10. Exact next

Implement **Micro-slice A: presentation-only inheritance parity** from this plan using RED -> minimal GREEN -> regression on a dedicated feature branch, without changing client-domain data or provider ownership.
