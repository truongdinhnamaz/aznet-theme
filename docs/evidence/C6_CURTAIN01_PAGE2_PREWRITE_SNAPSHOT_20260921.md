# C6 Curtain 01 — Page #2 pre-write rollback snapshot

**Captured:** 21/09/2026  
**Pilot:** `https://remquocanh.vn/`  
**Mode:** authenticated READ ONLY  
**Canonical repo at capture:** `main@a55cfc2f9235edd1653bac9c8d9fe37b38b0ac94`

## Snapshot status

**PASS — rollback evidence captured.**  
No Page, option, menu, Theme, plugin or provider data was changed while producing this evidence.

## Site/runtime identity

- WordPress: **7.1.1**
- PHP: **8.1.34**
- Active Theme: **AZnet Theme 1.3.30** (`aznet-theme`)
- Inactive rollback Themes present:
  - **Flatsome Child 3.0**
  - **Flatsome 3.20.7**
- Connected runtime verification: authenticated read-only.
- Canonical repository and live Theme version are **not proven byte-identical**. Repo source remains authoritative; live `1.3.30` is recorded only as installed-site evidence.

## Front Page identity

- `show_on_front = page`
- `page_on_front = 2`
- Page ID: **2**
- Title: **Rèm Quốc Anh**
- Slug: `sample-page`
- Status: `publish`
- Current legacy template: `page-transparent-header-light.php`
- Modified local: `2026-09-01T15:52:04`
- Modified GMT: `2026-09-01T08:52:04`
- Revision count: **83**
- Predecessor revision: **935**
- Raw content length: **4006 characters**

## Current Theme settings state

Read-only `theme_mods_aznet-theme` contains:

- Primary menu location => menu term **17**
- no stored `aznet_theme_settings` key was present in the captured Theme-mod option.

Therefore this snapshot does **not** assert effective Rèm 01/Homepage preset values from repository defaults, especially because live Theme 1.3.30 byte identity with canonical repo has not been proven.

## Current menu rollback state

Primary menu: **Main menu** (term 17), 7 top-level items:

| Position | Item | Object |
| --- | --- | --- |
| 1 | Trang chủ | Page #2 |
| 2 | Giới thiệu | Page #35 |
| 3 | Sản phẩm | Page #130 |
| 4 | Báo giá | Page #726 |
| 5 | Công trình | Page #41 |
| 6 | Kiến thức | Page #43 |
| 7 | Liên hệ | Page #45 |

Menu location assignments:

- `primary` => **Main menu**
- `header-utility` => unassigned
- `footer` => unassigned
- `footer-contact` => unassigned
- `footer-social` => unassigned
- `footer-policy` => unassigned

## Intended typed destination mappings

These are migration targets, not live writes:

- About => WordPress Page **#35**
- Knowledge => WordPress category term **#57** (`Tư vấn chọn rèm`)
- Contact => WordPress Page **#45**
- Shop/catalogue => WooCommerce-owned catalogue; Shop Page **#130**
- Hero => **BLOCKED / NO EXISTING WORDPRESS `wp_block` SOURCE**

Fresh authenticated read-only discovery of `/wp/v2/blocks?context=edit&per_page=100` returned an empty collection. The `wp_block` post type is registered and available, but the pilot currently has no reusable block that can be selected as the exact Hero source.

Therefore the Hero source cannot be frozen by read-only selection. Existing Page #2 references three WordPress Media URLs, but D-034 forbids turning those URLs into a Theme hardcoded/heuristic authoritative mapping. Creating the WordPress-owned Hero block is the next live database mutation and requires an explicit owner gate.

## Exact Page #2 raw rollback body

The following is the exact `post_content` captured through authenticated WordPress REST at this checkpoint.

```text
[section label="RQA NEXT 01 - Hero Bright Editorial" padding="0px" class="rqa-homepage rqa-home-hero rqa-surface-light"]

[ux_slider label="RQA Hero 3 ảnh" arrows="false" bullet_style="dashes-spaced" timer="4600" class="rqa-home-hero__slider rqa-home-media-root"]

[ux_banner label="Hero 01 - Không gian sáng" height="760px" height__sm="640px" height__md="700px" bg="https://remquocanh.vn/wp-content/uploads/2026/08/rem-quoc-anh-03.png" bg_size="cover" bg_pos="62% 50%" class="rqa-home-hero__slide rqa-home-hero__slide--1"]

[text_box width__sm="60" position_x="50" position_y="50"]


[/text_box]

[/ux_banner]
[ux_banner label="Hero 02 - Nếp vải và ánh sáng" height="760px" height__sm="640px" height__md="700px" bg="https://remquocanh.vn/wp-content/uploads/2026/08/rem-quoc-anh-02.png" bg_size="cover" bg_pos="56% 50%" class="rqa-home-hero__slide rqa-home-hero__slide--2"]

[text_box width__sm="60" position_x="50" position_y="50"]


[/text_box]

[/ux_banner]
[ux_banner label="Hero 03 - Chiều sâu không gian" height="760px" height__sm="640px" height__md="700px" bg="https://remquocanh.vn/wp-content/uploads/2026/08/rem-quoc-anh-01.png" bg_size="cover" bg_pos="64% 50%" class="rqa-home-hero__slide rqa-home-hero__slide--3"]

[text_box width__sm="60" position_x="50" position_y="50"]


[/text_box]

[/ux_banner]

[/ux_slider]
[row label="RQA Hero Static Text" v_align="middle" class="rqa-shell rqa-hero-static-layer"]

[col span="7" span__sm="12" span__md="8"]


[/col]

[/row]

[/section]
[section class="rqa-about rqa-about-section rqa-home-categories"]

[row v_align="middle" class="rqa-home-story__row"]

[col span="6" span__sm="12" class="rqa-home-story__content-col"]

[ux_html]

<div class="rqa-about-story__content">
<p class="rqa-kicker">Hơn hai thập kỷ gìn giữ nghề rèm</p>

<h2 id="rqa-home-story-title">Hai thế hệ, một nếp nghề</h2>
<p class="rqa-lead">Từ một cửa hàng may rèm gia đình khởi đầu năm 2000, nghề rèm tại Quốc Anh được tiếp nối qua hai thế hệ bằng sự tận tâm, chữ tín và tinh thần làm tốt hơn điều khách hàng mong đợi.</p>

<blockquote class="rqa-about-quote">“Hãy mang đến cho khách hàng nhiều giá trị hơn số tiền họ bỏ ra.”

<cite>Triết lý làm nghề của Quốc Anh</cite></blockquote>
<div class="rqa-home-story__actions"><a class="rqa-button rqa-button--dark" href="/gioi-thieu/">Đọc câu chuyện Quốc Anh<span aria-hidden="true">→</span>
</a><a class="rqa-text-link" href="/bo-suu-tap/">Khám phá các dòng rèm<span aria-hidden="true">→</span>
</a>

</div>
</div>
[/ux_html]

[/col]
[col span="6" span__sm="12" class="rqa-home-story__visual-col"]

[ux_html]

<figure class="rqa-home-story__visual">
<div class="rqa-home-story__frame"><img src="/wp-content/uploads/2026/08/rem_cua_quoc_anh_cap_nhat_kien_thuc_noi_that-scaled.jpg" alt="Đội ngũ Rèm Quốc Anh cập nhật kiến thức và lựa chọn mẫu vải nội thất" /></div>
<figcaption class="rqa-home-story__continuity">
<div><strong>2000</strong>
Khởi đầu từ một cửa hàng may rèm gia đình</div>
<span class="rqa-home-story__continuity-arrow" aria-hidden="true">→</span>
<div><strong>Hôm nay</strong>

Giải pháp rèm và kiểm soát ánh sáng cho không gian sống và
công trình chuyên nghiệp</div>
</figcaption></figure>
[/ux_html]

[/col]

[/row]

[/section]
[row class="rqa-home-categories__row"]

[col padding="0px"]

[ux_product_categories style="overlay" slider_nav_style="simple" slider_nav_position="outside" image_height="100%" image_radius="100" image_overlay="rgba(0, 0, 0, 0.19)" image_hover="overlay-remove-50" image_hover_alt="zoom" text_pos="middle" text_size="large" text_hover="bounce"]


[/col]

[/row]
[section class="rqa-about rqa-about-section rqa-home-products"]

[row class="rqa-home-products__row"]

[col padding="0px"]

[ux_products type="row" animate="fadeInDown" equalize_box="true" orderby="title"]


[/col]

[/row]

[/section]
[section]

[row]

[col span__sm="12"]

[title text="Kiến thức"]

[blog_posts style="normal" columns="3" columns__md="1" image_height="56.25%"]


[/col]

[/row]

[/section]
```

## Drift guard before any live migration

Before any write, re-read Page #2 and abort if any of the following differ from this snapshot:

- raw `post_content`;
- template;
- modified timestamp;
- Front Page assignment;
- menu location assignment;
- target mapped source IDs;
- active Theme/version.

A mismatch requires a new review/snapshot; do not overwrite.

## Rollback payload

A later approved live migration must be able to restore:

1. the exact Page #2 raw body above;
2. template `page-transparent-header-light.php`;
3. Front Page assignment to Page #2;
4. primary menu term 17 and its seven captured items;
5. prior Theme settings state (no captured `aznet_theme_settings` key);
6. prior active Theme state, if the cutover changes Theme activation.

Do not delete WordPress/WooCommerce/RootProfile/ConvertFlow data during presentation rollback.

## C6 disposition

- **C6 PLAN:** PASS
- **PRE-WRITE ROLLBACK SNAPSHOT:** PASS
- **EXACT HERO OWNER SOURCE:** BLOCKED
- **LIVE PAGE #2 MIGRATION:** NOT EXECUTED
- **REAL-PILOT L4/L5:** UNKNOWN / OneShield browser path still blocks direct parity evidence

## Exact next

**Hard gate:** owner approval to create one WordPress-owned Hero reusable block from the already inventoried WordPress Media assets. This must not change Page #2 yet. After the Hero block receives an exact ID and the rollback snapshot is revalidated, Page #2 migration remains a separate subsequent approval gate.
