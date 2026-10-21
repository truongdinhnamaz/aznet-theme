# Digital 01 — aznet.vn read-only adoption inventory (21/10/2026)

## Scope and evidence level
Authenticated read-only observations through the connected WPVibe site and public rendered HTML only. No WordPress Page/Post/Product/option/theme/plugin mutation, no deployment, no backup claim. This is **site-specific evidence**, not Theme code or provider-authoritative domain storage.

### Environment
- Target `https://aznet.vn/`, WordPress 7.1.3, PHP 8.4.26, active AZnet Theme `1.3.92`.
- WPVibe authenticated admin **read** verified; WP-CLI available. WordPress / WooCommerce, Rank Math, RootProfile and other optional plugins present; presence does not establish plugin integration PASS.
- WordPress Reading option `show_on_front=page`, `page_on_front=2`.
- WordPress Page **2**: `aznet-viet-nam`, publicly published as homepage `https://aznet.vn/`; title **Xây dựng và phát triển website cho Doanh nghiệp**. `template=page-blank.php` is saved in WordPress; this file is absent from the canonical Theme `main@b7738467` tree. Existing Content must remain WordPress-owned and cannot be replaced as an implicit side effect of selecting a presentation preset.
- Read-only `theme mod get aznet_theme_settings` returned `null`. This does not prove that frontend has no effective defaults/compatibility behavior.

### Existing Homepage structure (rendered HTML, public)
The public DOM visibly contains **9 opening + 9 closing** legacy `[section]` shortcode delimiters, some rendered as paragraph text, therefore shortcode portability is not proven. The current body carries authored `az-*` classes, embedded HTML, inline SVG, links and one H1. This content is not interchangeable with a new Digital 01 starter pattern by a preset-only change.

Observed legacy sections in reading order:
1. Hero
2. Proof bar
3. Vai tro website
4. Dich vu
5. Quy trinh
6. Nguoi phu trach
7. Kien thuc
8. FAQ
9. CTA cuoi

Actual public section themes: business website role, web build/redesign, SEO/content, conversion/operation, process, named responsible person, knowledge, FAQs and contact. Avoid inventing metrics, endorsements, experience, guarantees or identity in Theme package.

### Public-safe WordPress/WooCommerce source references (not Theme domain data)
- Native public Pages include: Website design WordPress (id 43041), SEO service (42172), website upgrade (41978), landing page design (41990), content-knowledge Page (42835), public site policies and multiple other services.
- Native Posts exist, with categories `Kiến thức SEO và Website` (703), `Marketing cho website` (45), `Tư vấn Thiết kế website` (50), `AI ứng dụng` (9), `Cập nhật AZnet` (6) etc. Counts are snapshot evidence, not a Theme-owned data store.
- Published WooCommerce products observed through public Woo API: `Website Intelligence by AZnet` (43077), `Plugin Tính Giá Xây Dựng cho WordPress` (42955), `EWWW Image Optimizer` (1468). WooCommerce owns identities, product state, price, checkout and fulfillment. No Theme-saved duplication.
- Public Homepage links observed, including `/thiet-ke-web-doanh-nghiep/`, `/nang-cap-website/`, `/dich-vu-seo-website/`, `/dich-vu-cham-soc-website/`, `/dich-vu/`, `/cap-nhat-aznet/`. Their destinations/redirects have **not** yet passed route or availability verification; do not infer valid routing from slug similarity.

### Content-to-presentation mapping proposal (non-authoritative)
| Existing Homepage section | Digital 01 presentation target | Source/ownership |
| --- | --- | --- |
| Hero | Hero / primary CTA | Exact WordPress Page content, no automatic claims |
| Proof bar | Trust/verification | Published public-safe evidence only |
| Vai tro website | Why/strategic value | WordPress-authored business explanation |
| Dich vu | Service cards | Exact WP Page refs/links reviewed per site |
| Quy trinh | Process steps | WordPress-authored explanatory content |
| Nguoi phu trach | About/responsible person | Exact existing public Page / RootProfile public contract if available; no identity heuristics |
| Kien thuc | Latest posts / editorial | WordPress public Query Loop |
| FAQ | FAQ content | WordPress-authored Page |
| CTA cuoi | Final consultation CTA | WordPress-authored approved contact details |
| (Digital 01 addition) | Product/software catalog cards | WooCommerce-owned published products through public APIs or editor-authored reviewed links |

## Required adoption gates
- Preserve original Page 2 content, permalink, published SEO state, and any Theme-independent product/customer data; never auto-apply starter copy to this live Page.
- Create a **separate WordPress-owned preview/draft Page only after an approved site write plan**. Review real content, media, links and claims without taking over the existing Homepage. Ensure the migration handles legacy `[section]` source rather than presuming shortcodes are native.
- Test typography, 9-section composition, 3 viewport a11y, Classic **Text + Visual save** on preview, headers/menus/footer, article Query, Woo/provider-present mode, existing service links, SEO canonical/redirects and rollback before cutover.
- Do not claim L5 integration, pilot adoption, deployment or release readiness from isolated CI.
- **Next:** improve the Digital 01 reusable starter presentation to cover the existing nine section roles without embedding aznet.vn/domain data. Verify the changed pattern against WordPress Classic Editor in disposable CI. Do not change the live site.
