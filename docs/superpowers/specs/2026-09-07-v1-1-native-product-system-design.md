# AZnet Theme v1.1 — Native Product System Design

**Date:** 07/09/2026  
**Status:** OWNER APPROVED — IMPLEMENTATION PLANNING  
**Canonical repository:** `truongdinhnamaz/aznet-theme`  
**Design branch:** `design/v1.1-native-product-system`  
**Baseline:** `main@f8e1a95c903c3f246528368ae9878eba780539ff`

## 1. Purpose

AZnet Theme v1.1 nâng sản phẩm từ một WordPress theme core sạch, ổn định và ownership-safe thành một **native WordPress product system** có trải nghiệm dựng site nhanh, commerce polish tốt và vận hành/release trưởng thành hơn, nhưng không trở thành page builder, business engine hay plugin orchestration layer.

Mục tiêu trải nghiệm là lấy những giá trị tốt mà người dùng kỳ vọng ở một theme thương mại mạnh như Flatsome — dựng site nhanh, header linh hoạt, WooCommerce đẹp, responsive tốt, nhiều composition dùng ngay — nhưng triển khai bằng WordPress-native primitives, curated patterns, presentation presets và Theme-owned configuration thay vì proprietary builder.

Mệnh đề trung tâm của v1.1:

> **Flatsome-grade usability, WordPress-native architecture.**

AZT-05 v1.0 tiếp tục là hiến pháp sản phẩm. V1.1 không thay đổi bất biến: **SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**

## 2. Current-state observations motivating v1.1

V1.0 đã có nền tốt: hybrid PHP + `theme.json`, semantic token family, Header/Footer, generic templates, native Homepage, Woo presentation shells, optional provider adapters, surface-aware Woo asset loading và các release gates L0-L6.

Nhưng để đạt chất lượng sản phẩm thương mại cao hơn, các khoảng trống chính là:

1. `theme.json` và design-token vocabulary còn mỏng, chưa cung cấp authoring system đủ mạnh.
2. Chưa có curated pattern library giúp dựng site nhanh bằng Gutenberg.
3. Header hiện là một composition chuẩn/sticky duy nhất; chưa có preset system.
4. WooCommerce presentation đang thiên về shell/CSS polish, chưa có product-card system và layout presets đủ trưởng thành.
5. Control Center chưa phải canonical production capability của v1.0.
6. Provider presentation bridge như ConvertFlow còn có asset projection tải rộng hơn mức cần thiết.
7. Release workflows đã chứng minh tốt cho v1.0 candidate nhưng lifecycle sau merge/tag chưa được chuẩn hóa thành release pipeline dùng lại cho v1.x.
8. AZT-03/AZT-04 trên canonical `main` còn mô tả baseline alpha/G0 cũ; source reconciliation là prerequisite trước production v1.1.

## 3. Product principles for v1.1

### 3.1 Native before proprietary

- Dùng Gutenberg, core blocks, Woo Blocks, `theme.json`, WordPress template hierarchy và public hooks/APIs trước.
- Không tạo UX Builder, page builder, query builder hay proprietary content schema.
- Content do người dùng tạo phải còn đọc/chỉnh được sau khi đổi Theme ở mức WordPress cho phép.

### 3.2 Curated freedom, not configuration sprawl

- V1.1 ưu tiên preset và curated choices hơn hàng trăm toggle.
- Mỗi option phải giải quyết một presentation problem có tính tái sử dụng.
- Không expose setting chỉ vì có thể expose.

### 3.3 Presentation-only ownership

Theme có thể sở hữu:
- semantic tokens;
- visual/style presets;
- patterns;
- Header composition/presets;
- Woo visual presets;
- Theme presentation settings;
- System Health của chính Theme và public capability projections;
- Theme asset/release pipeline.

Theme không sở hữu:
- WordPress content/query/menu/media lifecycle;
- Woo product/cart/checkout/account truth;
- RootProfile identity/trust;
- ConvertFlow Journey/conversion/filter semantics;
- private provider state;
- external plugin admin workflows.

### 3.4 Progressive enhancement and fail-soft behavior

- JS enhancement không được là điều kiện để đọc nội dung hoặc dùng navigation cơ bản.
- Provider vắng/lỗi/unsupported phải giữ native/core path usable.
- Feature không đủ public capability phải disable/degrade an toàn.

## 4. Explicit non-goals for v1.1

Không đưa vào v1.1:

- page builder hoặc drag-and-drop Header Builder;
- mega-menu builder;
- AJAX filter engine;
- live product-search engine;
- wishlist/compare engine;
- variation swatch domain engine;
- quick-view business/application engine;
- custom checkout engine;
- RootProfile/ConvertFlow source implementation;
- direct private option/meta/CPT/table/class reads của provider;
- arbitrary custom CSS/JS manager như một mini-platform;
- build stack/framework mới nếu chưa có nhu cầu implementation chứng minh.

Các capability tương tác cao như advanced mega menu, live search hoặc AJAX navigation có thể được đánh giá cho v1.2 sau evidence thật.

## 5. Architecture overview

V1.1 có năm trụ cột:

1. **Design System 2.0 + Visual Presets**
2. **Native Pattern Library**
3. **Header System 2.0**
4. **WooCommerce Presentation 2.0**
5. **Control Center + System Health + Performance/Release 2.0**

Luồng kiến trúc tổng quát:

`Source/native public data -> Theme presentation adapter -> semantic primitives -> preset/composition -> rendered surface`

Không có bước nào cho phép Theme biến presentation projection thành authoritative domain truth.

---

# 6. Design System 2.0

## 6.1 Four-layer token model

### Layer 1 — Foundation tokens

Giá trị vật lý/tỷ lệ dùng lại:
- spacing scale;
- typography size/line-height/weight scale;
- container widths;
- radii;
- elevation/shadow tiers;
- control sizes;
- image aspect-ratio vocabulary;
- motion durations/easings;
- layout/gap scales.

Foundation token không mang site/domain semantics.

### Layer 2 — Semantic tokens

Naming theo vai trò thay vì màu/site cụ thể. Tối thiểu gồm:

- `--aznet-theme-color-primary`
- `--aznet-theme-color-on-primary`
- `--aznet-theme-color-surface`
- `--aznet-theme-color-surface-subtle`
- `--aznet-theme-color-surface-inverse`
- `--aznet-theme-color-on-inverse`
- `--aznet-theme-color-text`
- `--aznet-theme-color-text-muted`
- `--aznet-theme-color-border`
- `--aznet-theme-color-focus`
- `--aznet-theme-color-success`
- `--aznet-theme-color-warning`
- `--aznet-theme-color-danger`

Typography, elevation, control và motion cũng dùng semantic roles tương tự.

### Layer 3 — Component tokens/primitives

Button, card, input, navigation, product card và các component khác tham chiếu semantic/foundation tokens. Hard-coded presentation values chỉ dùng khi component thực sự cần giá trị riêng và phải có lý do.

### Layer 4 — WordPress mapping

`theme.json` ánh xạ curated portions của design system vào editor:
- palette;
- font sizes;
- spacing presets;
- content/wide widths;
- typography settings;
- selected appearance capabilities.

Không bật toàn bộ `appearanceTools` vô điều kiện. Editor freedom phải giữ guardrails để site không tự phân mảnh design system.

## 6.2 Visual presets and WordPress-native feasibility gate

V1.1 cần ba visual presets:

- **AZnet Default** — general/business baseline.
- **AZnet Editorial** — typography/content hierarchy mạnh hơn cho knowledge/editorial/EEAT surfaces.
- **AZnet Commerce** — density/hierarchy tối ưu hơn cho Woo surfaces.

Tên “style variation” chỉ được dùng cho cơ chế WordPress native nếu support floor WordPress 6.9 thực sự cho hybrid PHP Theme áp dụng/chọn variation theo cách đáp ứng yêu cầu frontend + editor. R1 phải có một feasibility contract trên WordPress 6.9 trước khi khóa mechanism.

Decision rule đã khóa:

1. Nếu WordPress-native style variation mechanism đáp ứng hybrid architecture hiện tại, dùng native mechanism.
2. Nếu native variation selection không đáp ứng hybrid PHP Theme trên support floor, dùng **Theme-owned visual preset mapping** chỉ để đổi semantic token/editor presentation settings; không chuyển kiến trúc sang block/FSE theme và không tạo content/domain state riêng.
3. Cả hai đường phải cho frontend và editor nhận cùng semantic vocabulary, giữ content portable và cùng QA contract.

Như vậy outcome `Default / Editorial / Commerce` là bắt buộc; implementation mechanism được quyết định bằng evidence trên support floor chứ không bằng giả định.

## 6.3 Backward compatibility

- Existing `--aznet-theme-*` public semantic variables đã ship ở v1.0 không được silently đổi meaning.
- Token rename/removal cần compatibility alias hoặc migration plan trong v1.x.
- Public provider projections chỉ map từ canonical AZnet semantic tokens.

---

# 7. Native Pattern Library

## 7.1 Goal

Cho người dùng đạt flow:

`choose style -> insert pattern -> replace content -> publish`

mà không học proprietary builder.

## 7.2 Initial scope

V1.1 nhắm khoảng **16-20 curated patterns**, không chạy theo số lượng.

### Hero
- centered hero;
- split text/image hero;
- inverse/dark hero;
- commerce/category hero.

### Trust / conversion presentation
- logo/trust strip;
- feature icon grid;
- stats strip;
- testimonial cards;
- full-width CTA;
- split CTA.

### Content
- intro/lead section;
- alternating image/content;
- article/card grid;
- featured articles;
- FAQ presentation;
- quote/authority section.

### Commerce
- product category grid;
- featured-products shell;
- promotion banner;
- product-benefits section.

### Site utility
- contact section;
- newsletter presentation shell;
- footer CTA.

Final pattern count có thể thấp hơn 20 nếu quality gate không đạt; không thêm filler pattern để đạt quota.

## 7.3 Pattern implementation rules

- Ưu tiên core blocks và Woo Blocks.
- Pattern không chứa site-specific authoritative URL/data.
- Không embed private provider data hoặc domain state.
- Pattern có thể chứa placeholder/example content rõ ràng để người dùng thay.
- Không tạo CPT/store riêng.
- Không yêu cầu AZnet JS runtime để content tồn tại.

## 7.4 Responsive intent

Mỗi pattern phải có deliberate desktop/tablet/mobile behavior. Mobile không chỉ là “shrink desktop”.

Ví dụ split hero:
- desktop: two-column composition;
- tablet: balanced/text-first composition;
- mobile: single-column, readable type, accessible CTA, media sizing tránh layout shift.

## 7.5 Pattern Quality Contract

Pattern chỉ ship khi:

1. dùng semantic tokens/primitives;
2. không site-specific authoritative data;
3. không private provider state;
4. keyboard/a11y hợp lý;
5. heading hierarchy không cố định sai semantics;
6. responsive desktop/tablet/mobile;
7. editor và frontend không lệch nghiêm trọng;
8. không kéo asset không cần;
9. xóa pattern khỏi content không để lại Theme domain data;
10. theme switch để lại WordPress block content có thể đọc/chỉnh ở mức native block compatibility.

---

# 8. Header System 2.0

## 8.1 Architecture

Mô hình:

`primitive renderers -> preset composer -> header surface`

Không copy 80% markup sang nhiều preset files độc lập.

## 8.2 Presets

V1.1 có bốn preset:

- **Standard** — brand + primary nav + utilities.
- **Compact** — chiều cao/density thấp hơn, ưu tiên content.
- **Commerce** — brand + prominent search + account/cart + commerce-friendly navigation composition.
- **Overlay** — transparent/hero overlay presentation với safe fallback về Standard khi surface không phù hợp.

## 8.3 Header zones/primitives

- `brand`
- `primary_navigation`
- `search`
- `utility_navigation`
- `commerce_actions`
- `cta`
- `mobile_trigger`

Preset chỉ compose vị trí/visibility/presentation của các primitive.

## 8.4 Data sources

- Logo: WordPress custom logo.
- Menus: WordPress registered menu locations.
- Search: WordPress native search.
- Account/cart: public Woo functions/capabilities.
- CTA: Theme-owned presentation configuration + WordPress URL/text only; không chứa Journey semantics.

Có thể bổ sung menu location `utility` nếu implementation chứng minh cần. Không tạo menu store riêng.

## 8.5 Responsive navigation

V1.1 chuyển mobile interaction sang accessible navigation panel/drawer nếu complexity vượt khả năng `<details>` hiện tại.

Requirements:
- keyboard open/close;
- ESC close;
- focus containment khi modal-like panel active;
- focus return về trigger;
- scroll containment;
- no horizontal overflow;
- JS fail thì navigation cơ bản vẫn usable theo fallback markup.

Không thêm JS framework.

## 8.6 Sticky modes

- `off`
- `sticky`
- `sticky-compact`

Sticky enhancement phải:
- tránh CLS đáng kể do Theme;
- không làm mất keyboard focus;
- tôn trọng `prefers-reduced-motion`;
- fail-safe khi JS không chạy.

## 8.7 Search and commerce actions

- Standard/Compact: inline hoặc compact native search presentation.
- Commerce: search nổi bật hơn.
- Account/cart chỉ render khi public Woo capability có mặt.
- Optional item-count/mini-cart chỉ được thêm nếu public Woo capability rõ và không cần private session/storage reads.

## 8.8 Extension boundary

Header architecture phải giữ internal zone/primitive boundary đủ sạch để mở rộng sau này, nhưng **v1.1 không ship một public navigation-item hook chỉ để “chuẩn bị tương lai”**.

Public extension contract chỉ được thêm khi đồng thời có:
- ít nhất một concrete consumer/use case;
- ownership rõ;
- source decision/versioning;
- contract tests.

Nếu gate đó phát sinh trong v1.1, hook có thể dùng family `aznet_theme_*`; tên/payload cuối được khóa ở source trước production code. Nếu không có use case thật, extension point giữ internal và public hook được defer.

Điều này giữ khả năng tiến tới mega-menu/content extension sau này mà không vi phạm YAGNI.

## 8.9 Deferred

Không mega-menu builder trong v1.1.

---

# 9. WooCommerce Presentation 2.0

## 9.1 Ownership

WooCommerce tiếp tục sở hữu product, price, stock, variation, cart, checkout, account state và query semantics. Theme chỉ compose/present public/native output.

## 9.2 Product Card System

Chuẩn hóa primitive cho:
- media/image ratio;
- title;
- price hierarchy;
- rating presentation;
- badge slot;
- CTA presentation;
- focus state;
- spacing/density.

Ba density presets:
- `Comfortable`
- `Balanced`
- `Compact`

Theme không tự tính sale/stock/price.

## 9.3 Catalog presets

- `Grid`
- `Compact Grid`
- `Editorial Catalog`

Theme có thể chọn presentation density/columns trong bounded settings. Woo/WordPress vẫn quyết định query, filters, sort và pagination semantics.

Toolbar/filter drawer chỉ là shell nếu public capability tương ứng tồn tại.

## 9.4 Single Product presets

- **Classic** — gallery trái, summary phải.
- **Focus** — gallery ưu tiên hơn, purchase summary rõ hơn.
- **Story** — purchase region ở đầu, long-form content/benefits phía dưới có composition rộng hơn.

Không custom-product-page builder trong v1.1.

## 9.5 Purchase summary

Visual hierarchy cho:
- title;
- rating;
- price;
- short description;
- variations;
- quantity;
- CTA;
- meta.

Desktop có thể dùng sticky summary khi layout cho phép. Mobile sticky purchase bar chỉ được thực hiện như projection của native Woo CTA nếu public capability an toàn và không duplicate add-to-cart state machine. Nếu không đạt gate, defer bar khỏi v1.1.

## 9.6 Gallery

Theme chuẩn hóa presentation của native/public gallery:
- aspect ratio;
- thumbnail rail/grid;
- spacing;
- focus/active state;
- mobile behavior.

Không tạo media store hoặc gallery engine riêng. Khi enhanced JS vắng/lỗi, ảnh vẫn phải usable theo native/fallback document flow.

## 9.7 Variations

V1.1 core chỉ làm đẹp native Woo variation controls/selects. Không implement swatch engine hoặc tự mapping term -> variation.

Swatches tương lai phải đến từ Woo/public provider capability và một certification/adapter riêng.

## 9.8 Cart / Checkout / Account

### Cart
- responsive item hierarchy;
- quantity/coupon/totals/primary CTA rõ;
- không ép desktop table gây mobile overflow.

### Checkout
- form readability;
- visible validation/errors;
- order summary hierarchy;
- focus/keyboard accessibility;
- không custom checkout state machine.

### My Account
- endpoint navigation/panel/form/table presentation;
- Woo vẫn sở hữu account/endpoints/state.

## 9.9 Commerce patterns

Ưu tiên Woo Blocks/core blocks cho category/product merchandising compositions. Pattern không query bằng Theme domain engine.

## 9.10 Asset rule

Product, Archive, Cart, Checkout, Account và interactive enhancement có asset scope riêng. Không tạo global Theme Woo JS/CSS bundle chỉ vì Woo active.

---

# 10. Control Center 2.0

## 10.1 Purpose

Control Center là bảng điều khiển Theme-owned presentation state, không phải admin platform của ecosystem.

Năm khu vực:

- **Overview** — version/preset/logo/menu/public capability summary.
- **Design** — visual preset + bounded presentation choices.
- **Header** — Header preset/sticky/search/utility visibility.
- **Commerce** — Woo presentation preset/options; chỉ hiện khi public Woo capability tồn tại.
- **System Health** — read-only Theme/environment/public capability diagnostics.

Không thêm tab/toggle chỉ để tăng feature count.

## 10.2 Quick Setup

Flow:

`Style -> Logo -> Menu -> Header preset -> Patterns`

- Người dùng có thể skip.
- Logo/Menu action đi tới WordPress native controls.
- Không tự tạo Page, Menu, Product, Journey hoặc provider data.
- Pattern selection/chèn bằng WordPress-native editor.

## 10.3 Settings schema

Một schema duy nhất:

`aznet_theme_settings`

Requirements:
- versioned schema;
- defaults;
- strict allow-list normalization;
- migration giữa minor versions khi cần;
- reset chỉ Theme presentation settings.

Must not contain:
- WordPress content/menu/media data;
- Woo product/cart/order state;
- RootProfile/ConvertFlow domain state;
- secrets/private provider snapshot.

## 10.4 Import / Export

JSON import/export chỉ Theme-owned presentation settings.

Payload phải có Theme/schema version và được validate/normalize trước khi apply.

Không export data do owner khác sở hữu.

---

# 11. System Health

## 11.1 Environment projection

Read-only:
- WordPress version;
- PHP version;
- Theme version;
- child-theme state nếu có.

## 11.2 Theme configuration

- custom logo configured/not configured;
- primary menu configured/not configured;
- active visual preset;
- Header preset;
- Woo presentation preset khi áp dụng.

Không copy nội dung logo/menu vào một Theme store.

## 11.3 Public capabilities

Chỉ dùng public capability/contract:
- Woo available/absent;
- RootProfile supported/unsupported/absent khi public contract cho phép;
- ConvertFlow supported/unsupported/absent khi public contract cho phép.

Theme không kết luận “plugin lỗi vì option X thiếu” bằng private storage inspection.

## 11.4 Support Snapshot

Có safe copy/report output chứa allow-listed:
- versions;
- Theme presets;
- capability states;
- relevant Theme feature flags.

Không chứa:
- password/API key/token;
- DB credentials;
- private provider payload;
- user secret.

---

# 12. Performance Architecture

## 12.1 Asset layers

1. Core tokens — always.
2. Core shell — Header/Footer/base presentation.
3. Component assets — only when surface/component needs them.
4. Woo assets — per Product/Archive/Cart/Checkout/Account/block surface.
5. Provider projection — only when public capability/surface proves it is needed.

Current unconditional ConvertFlow projection is a v1.1 optimization target. If no public provider capability exists to condition it safely, record BLOCKED and retain safe current behavior rather than add private/heuristic detection.

## 12.2 JavaScript

Không tạo monolithic `theme.js`.

Các enhancement độc lập, ví dụ:
- `header-navigation.js`;
- `sticky-header.js`.

Chỉ enqueue khi feature cần. Không thêm React/Vue/jQuery dependency chỉ cho Theme generic interaction.

## 12.3 Measured baseline

Trước optimization, capture v1.0 clean-WP performance/asset baseline. Budget cuối dựa trên measured evidence, không đặt một con số KB tùy ý trước khi đo.

Minimum invariants:
- no Woo assets trên non-Woo core route;
- no optional provider asset khi provider/surface không cần, trừ khi public capability gate bị BLOCKED và evidence đã ghi cost/safe fallback;
- no unexpected failed subresources;
- no Theme-caused console error;
- no Theme-caused pathological CLS/overflow;
- reduced-motion respected.

---

# 13. Release Engineering 2.0

## 13.1 Pipeline

`PR candidate -> merge main -> exact-main verification -> release candidate -> owner approval -> tag -> GitHub Release`

## 13.2 Pull request gates

Theo affected scope:
- PHP lint/static;
- ownership/private-storage scan;
- RED/GREEN contract tests;
- WordPress runtime;
- browser/responsive/a11y;
- package regression khi gần release.

## 13.3 Exact-main verification

Mọi production-relevant push/merge vào `main` phải trigger fresh verification trên exact merge SHA. Green PR tree là evidence có giá trị nhưng không thay fresh exact-main run cho release closure.

## 13.4 Deterministic release candidate

Release workflow phải:

1. xác nhận canonical `main` + version metadata;
2. full regression;
3. build package hai lần;
4. verify byte-identical;
5. SHA-256;
6. unzip/exact-byte compare;
7. lint packaged PHP;
8. activate/exercise extracted package on clean WordPress;
9. retain exact artifact + evidence;
10. không rebuild bytes khác để publish mà không reverify.

## 13.5 Owner gate

Version promotion/tag/GitHub Release vẫn là owner approval gate. Deployment sang production website/hạ tầng khác là gate riêng nếu source không bundle chúng thành cùng một action.

---

# 14. QA Strategy

## L0 — Source / State
- exact canonical baseline;
- R0 source reconciliation;
- owner/dependency/allowed/forbidden rõ.

## L1 — Static
- PHP lint;
- naming;
- forbidden storage/private API;
- escaping/sanitization;
- package exclusion;
- asset ownership/scope.

## L2 — Contract / TDD
Mỗi behavior production mới:

`RED -> fail đúng intended reason -> minimal GREEN -> related regression`

Contracts tối thiểu:
- settings normalization/migration;
- visual preset mapping;
- pattern registration/portability boundaries;
- Header preset/fail-soft;
- Woo presentation preset/surface scope;
- Control Center capability/nonce/import-export;
- provider absence/failure;
- release workflow/version/package.

## L3 — Runtime
WordPress 6.9+ / PHP 8.1+:
- activation;
- clean WP core routes;
- Theme settings continuity;
- optional provider absence;
- Woo surfaces khi Woo enabled;
- package-extracted activation.

## L4 — Browser / Editor / A11y
Viewports tối thiểu:
- 1440x1000;
- 1024x900;
- 390x844.

Với core/pattern/Header/Woo/admin surfaces phù hợp:
- overflow <= 1px;
- duplicate IDs = 0;
- keyboard/focus usable;
- axe critical/serious = 0 cho Theme-controlled defects;
- no unexpected console/page errors;
- reduced-motion;
- editor/frontend parity cho presets/patterns.

## L5 — Integration
Chỉ claim cho exact provider/version đã chạy:
- public contract only;
- absent/error/unsupported fail soft;
- token projection/coexistence;
- provider-specific correctness không suy diễn sang version khác.

## L6 — Release
- exact-main fresh evidence;
- full retained regression;
- deterministic package x2;
- SHA-256;
- unzip/exact compare;
- package activation smoke;
- update/theme-switch/rollback;
- source/evidence closure;
- explicit owner release gate.

---

# 15. Delivery Slices

## R0 — v1.0 closure/source reconciliation

Before production v1.1 code:
- reconcile AZT-03 canonical baseline/version from stale alpha state to the actual v1.0 merged state;
- reconcile AZT-04 G0/G8 status and open v1.1 roadmap;
- update AZT-01 product objective/non-goals for v1.1;
- update AZT-02 architecture for Design System/Patterns/Header/Woo/Control Center/settings/release boundaries;
- refresh AZT-EXEC-MAP;
- update SOURCE_MANIFEST/checkpoint;
- keep AZT-05 unchanged unless a contradiction is found.

Exit: source PR approved/merged; no production change bundled.

## R1 — Design System 2.0

1. settings schema foundation;
2. token expansion + backward aliases;
3. WordPress 6.9 visual-preset feasibility probe;
4. Default/Editorial/Commerce implementation;
5. editor/frontend L4 parity.

Exit: stable design vocabulary consumed by later slices.

## R2 — Native Pattern Library

1. category/registration foundation;
2. Hero patterns;
3. Trust/CTA patterns;
4. Content patterns;
5. Commerce/Utility patterns with Woo absence behavior;
6. representative editor/frontend/a11y/portability matrix.

Exit: curated set passes Pattern Quality Contract.

## R3 — Header System 2.0

1. Header settings;
2. primitive renderer split;
3. Standard/Compact/Commerce/Overlay composer;
4. accessible mobile navigation progressive enhancement;
5. sticky-compact enhancement;
6. full preset browser/a11y/fail-soft matrix.

Exit: no builder; WordPress/Woo source state preserved.

## R4 — WooCommerce Presentation 2.0

1. Woo presentation settings;
2. Product Card System + catalog presets;
3. Single Product Classic/Focus/Story;
4. gallery/summary/native variation polish;
5. Cart/Checkout/Account polish;
6. Woo Blocks asset compatibility;
7. real Woo runtime/browser matrix + clean-WP absence regression.

Exit: commerce presentation polish without commerce ownership.

## R5 — Control Center + System Health

1. admin bootstrap/screen-scoped assets;
2. Overview/Design/Header/Commerce/System Health;
3. save/reset security;
4. safe import/export;
5. public-only diagnostics/support snapshot;
6. Quick Setup guidance;
7. authenticated browser/a11y + update/theme-switch continuity.

Exit: settings UX mature without domain/admin takeover.

## R6 — Performance + Release 2.0

1. measured performance/asset baseline;
2. complete Theme asset-scope matrix;
3. public-contract gate for ConvertFlow projection or BLOCKED evidence;
4. reusable PR + exact-main CI;
5. deterministic v1.x candidate workflow;
6. retire superseded milestone workflows only after coverage evidence;
7. exact-main R1-R6 regression;
8. RED exact-version contract then atomic `1.1.0` promotion;
9. final package verification;
10. owner-gated tag/GitHub Release.

Exit: v1.1 L0-L6 technical closure and publication provenance.

---

# 16. Source Impact

After approval of this written spec and before production code:

### AZT-01
Add v1.1 product objective/non-goals: native authoring, curated presets/patterns, bounded Control Center, no page builder.

### AZT-02
Ratify:
- four-layer Design System;
- visual-preset feasibility rule;
- pattern portability;
- Header primitive/preset architecture;
- Woo presentation boundary;
- single Theme settings schema;
- read-only capability diagnostics;
- asset/release architecture.

### AZT-03
Reconcile stale alpha/current baseline to actual v1.0 state; keep historical provenance unchanged.

### AZT-04
Close the v1.0 G critical path accurately, open R0-R6, add accepted v1.1 decisions and QA gates.

### AZT-EXEC-MAP
Replace G as active path with R0-R6 dependencies.

### AZT-05
**No constitutional change expected.** If implementation discovers a requirement that needs a page builder, domain ownership transfer, mandatory provider dependency or changed release constitution, stop and return to product-owner constitutional approval instead of coding around it.

---

# 17. Rollback and Migration

- `aznet_theme_settings` is versioned and normalized; incompatible future schema change requires migration before use.
- Existing v1.0 semantic token names retain compatibility aliases in v1.x.
- Header preset change is presentation-only; switching back to Standard restores default composition without content migration.
- Patterns persist as WordPress block content; changing Theme must not delete content.
- Woo presets never migrate commerce data.
- Control Center reset removes only Theme presentation settings.
- Workflow retirement occurs only after replacement verification and remains recoverable through Git history.
- Final release records prior stable package/commit for rollback.

---

# 18. Acceptance Criteria

V1.1 can be called complete only when:

1. R0 authoritative source is reconciled and merged.
2. Default/Editorial/Commerce visual outcomes pass editor/frontend/browser evidence.
3. Curated Pattern Library passes portability/responsive/a11y gates.
4. Header Standard/Compact/Commerce/Overlay passes keyboard/JS-off/fail-soft/browser matrix.
5. Woo Product/Archive/Cart/Checkout/Account presentation 2.0 passes supported Woo runtime/browser matrix while Woo-absent core remains green.
6. Control Center settings are one allow-listed Theme Mod schema, authenticated and continuity-safe.
7. System Health contains only public/environment/Theme-owned information and support snapshot is privacy-safe.
8. Theme asset matrix proves no unnecessary Woo/global component loading; provider optimization obeys public contract or is explicitly BLOCKED.
9. Exact-main post-merge verification is fresh.
10. Final `1.1.0` package is deterministic, SHA-256 recorded, unpack/reverified, activated from extracted bytes, update/theme-switch/rollback proven.
11. Optional external provider defects remain clearly separated from core release readiness unless source changed.
12. Tag/GitHub Release occurs only after explicit owner approval.

---

# 19. Deferred candidates for v1.2+

Evaluate only with real demand/evidence:
- advanced mega menu;
- live/AJAX product search;
- AJAX catalog navigation/filtering;
- swatch provider certification;
- quick view;
- richer Header composition beyond four presets;
- additional pattern packs;
- deeper performance budgets based on accumulated field data.

No deferred feature is implicitly approved for implementation by this spec.

---

# 20. Exact next after written-spec approval

1. Write implementation plan for R0-R6 with bite-sized TDD/QA tasks.
2. Execute **R0 source reconciliation first**.
3. Do not modify production PHP/CSS/JS until R0 source gate is approved/merged.
