# Industrial 01 — Flatsome-to-AZnet switch readiness, ANP pilot (2026-11-01)

**Status:** site-specific QA evidence + Proposed reusable process. **NOT** a promoted AZT-04 architecture/public contract change or release authorization.

## Source, site and scope

- Canonical Theme repo: `truongdinhnamaz/aznet-theme`. WordPress site: `https://anpvietnam.vn`, current live AZnet Theme 1.3.98, Draft Theme preview PR #552 `fc6725654ab20767fecd17412416b2ad12a6c2bd`, QA Draft PR #550.
- Theme owns **presentation** only (AZT-01/02). WordPress owns Page content and query; WooCommerce owns categories and commerce; ConvertFlow owns Homepage Journey, quote state and resolver. Migrating from Flatsome cannot grant Theme authority to edit vendor domain stores or infer routing by Page ID, URL or slug.
- Current promoted Nagavia Website Quality Standard at `https://nagavia.vn/api/v1/quality-standards/website` returned target HTTP error, so this is **provisional NWQS fallback**, not a centrally promoted standard.

## Read-only inventory / actual observations

1. Read-only WPVibe WordPress REST listing returned **9 published Pages**, including front page ID 2 (`/`) and separate published Page ID 12758 (`/trang-chu-2/`). Returned rendered content has **zero detectable visible UX Builder shortcode token** across all nine. This does **not** verify historical raw post_content, Woo product descriptions, all posts, CPTs or inactive builder template storage.
2. Independent TinyFish **public live**, not WPVibe's draft-context GET, inspected five routes: `/`, `/gioi-thieu/`, `/lien-he/`, `/xe-nang-cu-nhat-ban/`, `/ac-quy-xe-nang-dien-komatsu-3-tan-chinh-hang-gs/`. All five showed current `wp-theme-aznet-theme` body class and no rendered Flatsome `[ux_*]` residue in the sampled page markup. This is a sampled PASS, not complete migration certification.
3. **Confirmed semantic REVIEW, WordPress/Woo-owned:** live `/xe-nang-cu-nhat-ban/` contains exactly two H1 in actual `main#main`: Woo archive title `<h1 class="woocommerce-products-header__title page-title">Xe Nâng cũ Nhật Bản</h1>` and a separate `<h1>Xe nâng cũ Nhật Bản – Chọn theo tình trạng thực tế và nhu cầu vận hành</h1>` inside Woo category `term-description > section.anp-category-intro`. WPVibe Woo category GET `id=15` independently confirmed the second H1 exists in authoritative term description content. **Not an automatic Google ranking penalty; do not overwrite full description or silently mutate Woo term source from Theme.** Owner-side content correction with exact revision/rollback is needed.
4. **Confirmed public route REVIEW, WordPress+ConvertFlow ownership unresolved:** `/trang-chu-2/` is published and the public URL loads with `wp-theme-aznet-theme` and `choiceguide-homepage-surface-active`, showing two visible H1 `Trang chủ` and `ANP Việt Nam`. Front page canonical WordPress URL is separately `/`. This may be an intended landing Page or a stale duplicate with a mapped Homepage Journey; **do not infer from slug or delete/redirect without owner mapping evidence**. Canonical/robots/internal-link and routing policy unresolved.
5. **Theme-owned homepage visual regression:** previous owner screenshot showed unstyled three-card WordPress Gutenberg block on production; Draft PR #552 fixed actual nested `main > .aznet-theme-homepage--industrial-01 > article[data-aznet-homepage-surface]`, with real WP draft computed 1/2/3 grid at 390/768/1440 CSS widths, centered shell, gold 4px card borders and navy CTA, no measured overflow in target article. Targeted L4 PASS in prior evidence only, not full site/release.
6. **Provider-owned Komatsu baseline:** live/draft both have 8px document overflow at 390 and 1440, but not 768; P5.240 ConvertFlow Core baseline. A separate unmerged ConvertFlow Draft PR #132 holds a Core P5.240-ANP.HF1 candidate; package/stage/live gates remain blocked. Do not port plugin CSS internals into Theme.
7. Independent TinyFish native-viewport visual QA of category and contact preview did not identify a confirmed gross card/field overlap or broken thumbnail; agent did not set exact numeric viewports and its category H1 count (1) was contradicted by authoritative rendered Woo DOM H1 count (2). **Use exact DOM evidence for semantic diagnostics, not agent narrative alone.**

## Additional regression detector in the existing QA runner (Proposed reusable QA, not runtime code)

`tests/browser/industrial01-sitewide-audit.mjs` continues to require explicit approved routes, full-page screenshots, Playwright/axe, exact 390/768/1440 viewport and preview/live identity. It now also carries source-owner-labeled **REVIEW-only** findings:

| Stable candidate rule | Observation | Source owner | Default action |
| --- | --- | --- | --- |
| `AZT-I01-MIG-01` | Rendered Main text visibly contains a legacy Flatsome UX Builder token (`[ux_...]`, `[row]`, etc.) | WordPress content / builder source | Review original editor format; migrate source through owning API, keep original revision |
| `AZT-I01-MIG-02` | Site loads assets still pointing to `/wp-content/themes/flatsome/` or `flatsome-child/` | Asset author/source owner | Locate actual asset dependency; repair owned enqueue/reference in proper layer |
| `AZT-I01-MIG-03` | Visible ConvertFlow quote anchor references a missing `#target` | ConvertFlow | Block conversion QA and request provider-owned fix |
| Existing H1 rule | No/unnamed/multiple H1 in rendered DOM | WordPress term/page or owning presentation template | Owner-specific diagnosis; **never assume 2 H1 = SEO penalty** |
| Existing visual/axe checks | overflow, broken media, layout/contrast/keyboard | Theme vs plugin determined by concrete DOM/CSS provenance | Theme patch only when Theme-owned, otherwise owner handoff |

False positives such as legitimate shortcode examples in tutorial text are **REVIEW**, not proof of a defect and never an automatic deletion. Missing/incomplete measurement must return UNKNOWN, never PASS. No real preview token is committed or logged.

## Proposed practical conversion workflow (not yet an accepted architecture change)

**Preflight** on a staging copy or isolated Theme preview:
- Read-only inventory of site-owned pages/posts/Woo terms, legacy builder blocks/shortcodes/assets and menus using authorized public APIs; preserve original storage and source/provenance.
- Compare the same critical routes before/after theme switch at 390/768/1440 with screenshot + DOM + axe + HTML/console/assets/network and checkout/quote/customer journey checks.
- Prioritize visitor-visible regressions: unstyled Gutenberg columns/CTA, missing category images, wrong header/cart fallback, hidden menu, broken product interactions, duplicate layouts and broken media. Record source owner, symptom, severity, rollback and evidence.
- **Deterministic repair first**: Theme-owned CSS/renderer/adapter issue -> bounded RED→GREEN code PR -> preview QA. WordPress content issue -> authorized native revision. Woo/ConvertFlow/RootProfile -> provider-owned public-contract workflow; no private schema reads/copy or heuristic identity. Failed dependencies are BLOCKED, not silently masked.
- **AI optional, outside Theme runtime**: an authorized server-side QA/remediation coordinator may use the OpenAI API to classify screenshots/DOM diffs and propose a bounded diff/PR plus tests; it must never hold a public secret in CSS/JS, mutate domain stores, or autonomously publish a site. The OpenAI API key belongs in an authorized server-side secret store, subject to explicit API organization permission, cost cap, redaction of private data and model-output review.
- **Release** only after browser/visual/a11y and provider integration gates, verified staging rollback and fresh full site+DB backup, separate explicit owner go-live approval, then anonymous public production check. A blanket promise of "fix every possible future issue" is not a verifiable acceptance criterion.

**Sources on AI billing/security:** OpenAI ChatGPT/API billing and credentials are distinct; see https://help.openai.com/en/articles/9039756-managing-billing-for-chatgpt-and-the-api-platform and https://developers.openai.com/api/reference/overview. Some eligible ChatGPT paid subscriptions can claim API credits but eligibility and claimed credit availability are not established for this account.

## Current completion classification

- **PASS**: sampled live status/DOM for five routes; nine-page rendered-source shortcode sample; Theme-owned homepage preview target 390/768/1440 from prior direct browser evidence; QA migration guard focused V8 source checks.
- **REVIEW**: Woo category #15 second H1; published `/trang-chu-2/` mapped Journey; native other page semantics; full site media/visual.
- **BLOCKED/UNKNOWN**: comprehensive browser/axe 14-route matrix on real WordPress at numeric widths, old Flatsome original live screenshots/raw builder data, full integration/rollback, live publication approval and promoted NWQS registry.
- **EXACT NEXT**: close first confirmed Theme-owned visitor-visible issue in Draft PR #552 / preview with proof; keep the migration guard in Draft PR #550 and route the two source-owned findings for owner action. When the owned QA and rollback gates PASS, request explicit publish approval; never infer production PASS from a draft.
