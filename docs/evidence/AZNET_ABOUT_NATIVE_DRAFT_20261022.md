# aznet.vn /gioi-thieu: WordPress-native migration candidate — 22/10/2026

## Ownership and scope

- **Source owner:** WordPress native Page `18897` (`/gioi-thieu`) owns published editorial content, title, featured media, page template and SEO metadata. AZnet Theme is only the presentation consumer.
- **Baseline:** `aznet.vn` runs AZnet Theme **1.3.95**, WordPress 7.1.3 and PHP 8.4.26. Canonical Theme `main@0cd22f023a3b63c90a17cb73188e812f59ed7256`; no runtime Theme, WooCommerce, RootProfile, ConvertFlow or Rank Math state was changed for this content-migration candidate.
- **Approval:** The owner approved proceeding from the previous scoped NEXT to create a separate WordPress draft migration, preserve the original and run verification. This is **not** approval to publish or modify the live Page, merge PRs or deploy another Theme release.
- **Reason:** Public Page 18897 and the WPVibe Theme preview show a generic Page header/excerpt containing the literal Flatsome `[section]` shortcode, plus unexpanded wrappers in the body and an extra H1. The page still points at obsolete `page-blank.php`. This is an editorial/source migration, not a mandate for Theme to emulate Flatsome private layout APIs.

## Source and recovery

- Live Page **18897**, status **publish**, `post_modified=2026-06-19 22:46:46`, remained unchanged on fresh WP-CLI read. Its original `post_content` is 21,090 characters, with **9 Flatsome sections** and one embedded H1.
- Created unpublished archival Page **43537**, slug `aznet-about-original-18897-20261022`. Fresh WP-CLI content read verified **exact 21,090-character source equality** to the still-published Page 18897. Neither Page was overwritten.
- WordPress-native candidate Page **43538**, slug `aznet-gioi-thieu-native-review-20261022`, status **draft**, uses Theme-owned native template `page-templates/landing.php`, WordPress featured-media reference **23485**, editor title marked clearly as a preview draft.
- Candidate content was generated from the live WordPress source, not hardcoded into the Theme. The original 9 authored areas were restructured as nine native section groups; headings, paragraphs, columns, cards, and CTA use Gutenberg core block markup. The first group opts into `aznet-theme-page-hero` with a nested native H1, satisfying the existing presentation guard, with no title/slug/Page-ID heuristic. Two meaningful authored SVG diagrams were retained as native `core/html` illustration blocks; decorative background SVGs were replaced by block presentation (not silently represented as photographic asset parity).

## Scoped verification (fresh)

- The candidate on creation contained all **27** original HTML paragraph contents, **22** heading contents, **10** original links (seven anchors and three contact links), and both meaning-bearing SVG illustrations. No `[section]`, `[row]`, or `[col]` remains in the candidate.
- Fresh WP-CLI saved-content read confirms **34,061 characters**, **one H1**, seven distinct `id=az-*` destinations, both `role=img` SVGs and **zero** unexpanded Flatsome section tokens. A simple stack check of the saved Gutenberg opening and closing comments and block-attribute JSON found zero nesting or JSON errors; 157 opening and 157 closing block markers. This is **serialized/static QA**, not a Gutenberg editor/browser certification.
- **RED→GREEN focused draft-only correction:** the Zalo button HTML had `target=_blank` and `rel=nofollow noopener` but the block comment did not carry the corresponding `linkTarget`/`rel` fields; a surgical `content/edit` updated one comment and a fresh WP-CLI read confirmed the fields. Page remains draft.
- Fresh live Page 18897 still displays the legacy shortcodes. This is expected; the production content, permalink, Page ID, SEO owner metadata and Theme runtime were intentionally untouched.

## Open gates and risks

- **L3/L4 draft browser visualization: UNKNOWN/BLOCKED_BY_PREVIEW_ACCESS.** The attempted unauthenticated `?page_id=43538&preview=true` renders the site's 404 document, not draft 43538. No claim that real desktop/mobile rendering, edit-screen validation, focus/accessibility, or Lighthouse is PASS. The WordPress-authorized editor preview must be reviewed separately.
- **Editorial truth gate:** the original statements `200+` projects, `8 năm` experience, `99%` satisfaction and the promised 30-minute advice turnaround were **retained for review, not verified**. The owner/authoritative data should substantiate or correct these before public cutover.
- WP REST creation returned nonfatal PHP warnings concerning `stdClass::$id` and `stdClass::$post_parent`, yet both drafts were verified by separate WP-CLI reads. Treat the warning source as a separate WordPress/REST concern; do not attribute it to Theme without evidence.
- WPVibe has a previously edited unpublished **Theme draft** with provisional Header CSS scoped to Digital 01. That Theme draft was not published, reconciled into canonical GitHub, or accepted at L4 as part of this content slice. Do not combine it with this WordPress Page migration release implicitly.

## EXACT NEXT

Obtain an authenticated WordPress editor preview of Page **43538** and inspect desktop/mobile, Gutenberg block validity, logo/Header, H1/anchors/links, SVG fidelity, and SEO handling. Resolve any verified draft defects within WordPress content at the shallowest layer. Verify source-backed marketing claims. Only then seek **separate explicit authorization** to replace Page **18897** content/template in place (same ID, same SEO ownership), using Page **43537** plus normal WordPress revisions for rollback; perform fresh L3/L4 and SEO regression after cutover. Never publish the archival Page or replace this evidence with a claim of live PASS.
