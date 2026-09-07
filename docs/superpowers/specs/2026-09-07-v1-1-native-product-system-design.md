# AZnet Theme v1.1 — Native Product System Design

**Date:** 07/09/2026  
**Status:** DESIGN APPROVED IN CHAT — WRITTEN SPEC PENDING OWNER REVIEW  
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
- add-to-cart CTA;
- meta.

Desktop có thể sticky summary nếu presentation-safe. Mobile sticky purchase projection chỉ dùng public/native CTA state; nếu không đủ capability thì feature không render.

## 9.6 Gallery

Chuẩn hóa:
- aspect ratio;
- thumbnail presentation;
- spacing;
- active/focus states;
- mobile behavior.

Không viết lại media storage/gallery domain. Native Woo fallback phải usable.

## 9.7 Variations

V1.1 chỉ polish native variation controls/selects. Không tạo swatch mapping engine.

Swatch provider chỉ có thể được Theme style/certify qua public contract riêng trong tương lai.

## 9.8 Cart / Checkout / Account

### Cart
- responsive line-item hierarchy;
- quantity/coupon/totals/CTA clarity;
- mobile không ép desktop table gây unusable layout.

### Checkout
- form readability;
- visible error/validation states;
- order summary hierarchy;
- focus/error accessibility;
- không custom checkout state machine.

### My Account
- navigation/content panel/forms/orders/address presentation;
- endpoint/account state vẫn do Woo sở hữu.

## 9.9 Commerce patterns

Ưu tiên Woo Blocks/core blocks cho category/product merchandising patterns để content editable và portable.

## 9.10 Deferred commerce features

Không ship trong core v1.1:
- AJAX filters;
- live product search;
- wishlist;
- compare;
- variation swatch engine;
- quick-view engine;
- custom checkout engine.

---

# 10. Control Center 2.0

## 10.1 Role

Control Center là bảng điều khiển cho **Theme-owned presentation configuration**, không phải admin platform cho ecosystem.

## 10.2 Sections

### Overview
- Theme version;
- active visual preset;
- Header preset;
- logo/menu readiness;
- public Woo capability;
- bounded configuration warnings.

### Design
- visual preset;
- density;
- button/control presentation presets;
- container behavior.

### Header
- Header preset;
- sticky mode;
- visibility của search/utilities/Theme-owned primitives.

### Commerce
Chỉ hiện khi Woo public capability có mặt:
- catalog density/preset;
- product layout preset;
- bounded presentation options.

### System Health
Read-only diagnostics và support snapshot.

## 10.3 Quick Setup

Optional first-run flow:

`Style -> Logo -> Menu -> Header preset -> Patterns`

Rules:
- có thể skip;
- không auto-create Page/Product/Journey/Profile/domain data;
- link sang native WordPress controls khi cần logo/menu/content;
- không lock người dùng vào wizard.

## 10.4 Settings schema

Một Theme-owned schema duy nhất:

`aznet_theme_settings`

Requirements:
- schema version;
- whitelist;
- normalization/defaults;
- capability/nonce protected save;
- migration giữa minor versions;
- reset chỉ reset Theme-owned presentation settings;
- không xóa WordPress content/provider data.

Storage mechanism cuối cùng phải dùng WordPress public Theme-owned API phù hợp; không direct private plugin storage.

## 10.5 Import / Export

Cho phép export/import JSON chỉ với Theme presentation settings.

Không export:
- Posts/Pages/Menus/Media content;
- Woo products/orders/cart state;
- RootProfile identity/trust;
- ConvertFlow Journey;
- secrets/private provider state.

Import phải schema/version validate trước mutation.

---

# 11. System Health

## 11.1 Environment

Read-only:
- WordPress version;
- PHP version;
- Theme version;
- active child-theme context nếu có.

## 11.2 Theme configuration

- Logo readiness;
- primary menu readiness;
- visual preset;
- Header preset;
- Woo presentation preset nếu capability có mặt.

## 11.3 Public capability diagnostics

- WooCommerce: available / absent / unsupported where applicable;
- RootProfile public capability: supported / unsupported / absent;
- ConvertFlow public capability: supported / unsupported / absent.

Không đọc private option/meta/table/class để suy luận diagnostics.

## 11.4 Warning semantics

Theme chỉ phát biểu điều nó có thẩm quyền chứng minh.

Đúng:
- “WooCommerce không có mặt; commerce presentation đang không hoạt động.”

Sai:
- kết luận plugin khác hỏng vì private option/storage mà Theme không sở hữu.

## 11.5 Support Snapshot

`Copy System Report` xuất text/JSON an toàn gồm:
- versions;
- Theme presets;
- public capability states;
- relevant Theme feature flags.

Không chứa password, API key, DB credential, secrets hoặc private provider state.

---

# 12. Performance Architecture 2.0

## 12.1 Asset classes

### Core tokens
Load globally khi Theme active.

### Core shell
Header/Footer/base layout assets cần cho site shell.

### Component assets
Chỉ enqueue khi component/surface thật sự present.

### Woo assets
Product, Archive, Cart, Checkout, Account tách theo surface.

### Provider projection
Chỉ enqueue khi public capability/surface tương ứng thật sự cần.

ConvertFlow presentation projection hiện đang unconditional trong v1.0 asset path là một v1.1 cleanup target. Fix phải giữ public token contract và chứng minh provider-absent path không regression.

## 12.2 JavaScript policy

Không tạo monolithic `theme.js` nếu không cần.

Interactive modules có thể gồm:
- `header-navigation.js`
- `sticky-header.js`

và chỉ load theo capability/preset.

Không thêm React/Vue/jQuery dependency cho bounded Theme interactions nếu native JS đủ.

## 12.3 Performance baseline and budget-lock gate

Trước implementation behavior đáng kể, R1 capture fresh v1.0 baseline trên WordPress sạch. Exact numerical budgets được ghi vào authoritative roadmap/plan từ baseline đó trước khi một v1.1 performance claim được dùng làm release gate.

Budget decision phải bao gồm ít nhất:
- total Theme-owned CSS/JS transfer/load delta trên clean core routes;
- Theme-caused layout-shift threshold/regression allowance;
- unexpected subresource/console error = 0;
- surface/provider asset absence assertions.

Cho tới khi baseline được đo và budget được khóa, không được claim v1.1 “nhẹ hơn” bằng marketing number suy đoán.

Minimum invariants ngay từ đầu:
- no provider asset when provider/capability absent;
- no Woo-surface asset trên non-Woo route;
- no unexpected failed subresource;
- native responsive-image behavior giữ được;
- no unexpected console error;
- reduced-motion respected;
- navigation/content cơ bản usable khi JS disabled.

---

# 13. Release Engineering 2.0

## 13.1 Standard lifecycle

`PR candidate -> merge main -> exact-main post-merge verification -> release candidate -> owner approval -> tag -> GitHub Release`

Không suy diễn PASS giữa các bước.

## 13.2 PR gates

Tùy surface bị ảnh hưởng, tối thiểu:
- PHP/static/naming/private-storage scans;
- contract/TDD regressions;
- WordPress-clean runtime;
- browser/responsive/a11y;
- package integrity khi release-relevant.

Production feature/bugfix tiếp tục RED -> intended failure -> minimal GREEN -> regression.

## 13.3 Main gates

Mọi production merge vào `main` phải có fresh workflow trên **exact canonical main SHA** cho relevant release-critical suite. V1.1 workflow triggers phải hỗ trợ `push: main` thay vì chỉ feature branch/PR.

## 13.4 Release candidate gate

Release workflow phải:

1. xác nhận candidate ở canonical `main`;
2. xác nhận version metadata consistent;
3. chạy full regression phù hợp;
4. build package hai lần;
5. chứng minh byte-identical;
6. ghi SHA-256;
7. unzip/exact-byte reverify;
8. clean WordPress activation/smoke từ chính packaged bytes;
9. lưu artifact + rollback reference;
10. dừng tại explicit owner approval nếu tag/release chưa được duyệt.

Không rebuild một package khác sau candidate PASS rồi publish mà không reverify bytes mới.

## 13.5 Tag/release

Tag và GitHub Release phải trỏ đúng main SHA/package evidence đã duyệt. Production deployment là hard gate riêng nếu có.

---

# 14. Integration and ownership boundary

## 14.1 WooCommerce

Theme chỉ dùng public Woo APIs/hooks/Blocks/capabilities. Không direct-read session/private product storage để điều khiển authoritative behavior.

## 14.2 RootProfile

V1.1 không thay E5 ownership. Theme chỉ consume public profile/contact projection khi certified; absence/error/unsupported giữ core usable.

## 14.3 ConvertFlow

V1.1 không fix F8 provider defect bằng private workaround. Native Homepage vẫn là core path. ConvertFlow presentation tokens chỉ được load khi public capability/surface cần sau asset-scope refactor.

## 14.4 Provider certification

Provider-specific compatibility vẫn là certification track. Không claim certified nếu chưa có fresh provider/version evidence ở layer cần thiết.

---

# 15. Error handling and fail-soft rules

- Invalid Theme setting -> normalize về safe default, không fatal.
- Missing menu/logo -> render usable fallback/absence state.
- Woo absent -> commerce settings/surfaces không phá non-commerce site.
- Provider absent/error/unsupported -> adapter không render capability phụ thuộc; native fallback giữ nguyên.
- JS error -> content/navigation cơ bản vẫn usable.
- Import settings invalid -> reject trước mutation và báo lỗi actionable.
- Unsupported schema version -> do not guess migration; fail safe.

Theme không tự chữa external provider bằng authoritative heuristics/private reads.

---

# 16. Data lifecycle, migration and rollback

## 16.1 Theme settings migration

- schema-versioned;
- migration function bounded theo version;
- idempotent where practical;
- regression test old -> new;
- rollback behavior documented nếu setting mới không tồn tại ở version cũ.

## 16.2 Theme switch

Switch Theme không được xóa:
- WordPress content/menu/media;
- Woo domain data;
- RootProfile/ConvertFlow provider data.

Theme-owned settings có thể còn trong WordPress storage theo public Theme API nhưng không được mutation external data khi inactive/switching.

## 16.3 Pattern portability

Inserted patterns trở thành WordPress block content; Theme không giữ parallel instance registry cần thiết để content tồn tại.

---

# 17. QA strategy

QA layer discipline của AZT-04 tiếp tục áp dụng.

## L0 — Source / State
- v1.0 source reconciliation hoàn tất trước production v1.1;
- owner/dependency/gate rõ;
- exact next bounded.

## L1 — Static
- lint;
- naming;
- forbidden private storage/API scans;
- asset registration/scope assertions;
- escaping/sanitization review cho touched paths.

## L2 — Contract / TDD
- token backward compatibility;
- visual preset behavior/mapping;
- setting normalization/migration;
- preset composition;
- surface-aware assets;
- provider fail-soft;
- pattern registration/ownership rules.

## L3 — Runtime
WordPress 6.9+ / PHP 8.1+:
- clean activation;
- core routes;
- Theme settings save/reset/migrate;
- Woo absent/present relevant routes;
- no Theme-caused fatal/warning/uncaught.

## L4 — Browser / Visual / A11y
Desktop/tablet/mobile and at least 320px narrow case where relevant:
- Header presets;
- mobile navigation keyboard/focus;
- patterns editor/frontend parity samples;
- Woo product/catalog/cart/checkout/account;
- Control Center;
- no duplicate IDs/landmark regressions;
- reduced motion;
- axe critical/serious = 0 for Theme-controlled issues;
- no unexpected console errors/overflow.

## L5 — Integration
Only for declared certification targets:
- public contract/version/capability;
- absence/failure behavior;
- ownership boundary;
- actual provider package/runtime when claiming certification.

## L6 — Completion / Release
- full regression;
- exact-main verification;
- deterministic package;
- SHA/unzip/exact-byte;
- clean install;
- upgrade/theme-switch/rollback;
- source/evidence closure;
- explicit owner release gate.

---

# 18. Implementation decomposition

Đây là **umbrella product design**, không phải một giant implementation slice. V1.1 phải được triển khai bằng các bounded milestones/plans; không code tất cả trong một branch lớn.

## R0 — v1.0 closure/source reconciliation prerequisite

Before v1.1 production code:
- reconcile AZT-03 baseline from alpha state to canonical v1.0 implementation state;
- reconcile AZT-04 roadmap from G0-active to v1.0 core closure and v1.1 roadmap;
- record tag/release publication truth accurately (do not claim published if unavailable);
- update AZT-01/AZT-02 for the approved v1.1 product/architecture capabilities;
- leave AZT-05 unchanged because constitutional invariants remain unchanged.

## R1 — Design System 2.0
- token expansion/backward compatibility;
- WordPress 6.9 visual-preset/style-variation feasibility gate;
- `theme.json` curated mappings;
- three visual presets;
- baseline visual/performance evidence and numerical budget lock.

## R2 — Pattern Library
- registration architecture;
- first curated pattern batches;
- responsive/editor/frontend/a11y gates.

## R3 — Header System 2.0
- primitive decomposition;
- presets;
- responsive navigation;
- sticky modes;
- asset scoping.

## R4 — WooCommerce Presentation 2.0
- product card system;
- catalog presets;
- single-product presets;
- cart/checkout/account polish;
- commerce patterns.

## R5 — Control Center + System Health
- settings schema/migration;
- Overview/Design/Header/Commerce/System Health;
- Quick Setup;
- import/export;
- support snapshot.

## R6 — Performance + Release Engineering 2.0
- provider asset scoping cleanup;
- JS module scoping;
- exact-main workflows;
- reusable deterministic release pipeline.

## R7 — v1.1 closure
- full L0-L6 regression;
- version promotion only after final candidate evidence;
- package/SHA/rollback;
- source closure;
- owner merge/release gates.

Milestone IDs/names có thể được AZT-04 chuẩn hóa trong R0; các plan sau đó phải dùng IDs canonical từ source đã accept.

---

# 19. Source impact

Design này là architecture/product-roadmap change và **không được đi thẳng vào production code chỉ dựa trên spec**.

Required source impact before implementation:

- **AZT-03 — required:** update current baseline/provenance state from stale alpha/G0 facts to actual canonical v1.0 state.
- **AZT-04 — required:** close v1.0 G path accurately, add v1.1 milestone/QA sequencing and retain external compatibility blockers as non-core tracks.
- **AZT-02 — required:** document approved v1.1 architecture additions: Design System 2.0 mapping, pattern boundary, Header preset composition, Theme-owned settings/Control Center boundary, asset/release architecture.
- **AZT-01 — required:** extend the product capability promise to include curated native authoring/patterns, bounded presentation presets and Theme-owned Control Center/System Health while preserving ownership/non-goals.
- **AZT-05 — no change expected:** page-builder prohibition, WordPress-native first, presentation ownership and optional integration rules remain governing invariants. Any future request that changes those invariants is a separate constitutional hard gate.
- **AZT-00 — no change expected:** source priority/governance is unchanged.
- **AZT-EXEC-MAP — derived update required after AZT-04:** regenerate/update only after authoritative roadmap/source is accepted.

No source document may claim a Git tag/GitHub Release exists until publication is actually verified.

## 19.1 Historical Control Center provenance

- Existing design PR #16 and unmerged U0 production candidate/evidence around PR #22 are **historical/reference evidence only**.
- If this written v1.1 spec is approved, it supersedes PR #16 as the current design direction for Control Center.
- R5 must re-evaluate reusable Theme-owned code from old U0 against current `main`, current source and v1.1 settings/schema requirements; it must not wholesale-merge stale alpha-era production code merely because earlier tests passed.
- Historical PASS evidence can guide regression scope but cannot be promoted to v1.1 PASS without fresh evidence on current candidate bytes.

---

# 20. Definition of Done for v1.1

V1.1 is complete only when:

1. canonical source reflects v1.0 closure and approved v1.1 architecture/roadmap;
2. Design System 2.0 and `Default / Editorial / Commerce` visual presets are production-ready using the evidence-selected mechanism;
3. curated native pattern library passes its quality contract;
4. four Header presets pass responsive/keyboard/fail-soft gates;
5. Woo product/catalog/product-page/cart/checkout/account presentation meets the v1.1 contract without taking commerce ownership;
6. Control Center only mutates Theme-owned presentation settings and passes migration/reset/import/export tests;
7. System Health uses only public/Theme-owned facts and support snapshot excludes secrets;
8. optional provider assets are capability/surface-aware;
9. exact-main post-merge verification exists and is fresh for release candidate;
10. deterministic final package is built/rebuilt byte-identically, SHA-verified, unzipped/reverified and activated on clean WordPress;
11. update/theme-switch/rollback pass;
12. provider certification claims are limited to fresh evidence actually obtained;
13. explicit owner approval is obtained for release tag/deploy gates.

Passing static tests alone cannot satisfy this definition.

---

# 21. Rollback and recovery

Each production milestone must be independently reversible.

- Work occurs on bounded work/feature branches.
- Previous v1.0 package/main SHA is the starting rollback reference until superseded by an accepted v1.1 checkpoint.
- Setting-schema migrations require fixture/backward-compatibility tests before merge.
- Asset-scope cleanup must preserve the previous public presentation contract where provider compatibility relies on it.
- Pattern/content work cannot depend on a Theme-local registry whose removal destroys content.
- No destructive retirement without canonical destination + regression + rollback evidence.

---

# 22. Decision summary

Approved design choices:

- Keep AZT-05 constitution and do **not** build a page builder.
- Prefer Native Product System over Builder-lite or minimalist-only evolution.
- Design System 2.0 uses layered semantic tokens mapped selectively into `theme.json`.
- Deliver `Default / Editorial / Commerce` visual presets; choose native style-variation vs Theme-owned token-preset mechanism by WordPress 6.9 evidence without changing hybrid architecture.
- Ship a curated, quality-gated Gutenberg/Woo Blocks pattern library.
- Header v1.1 uses `Standard / Compact / Commerce / Overlay` presets and primitives, not drag-drop builder.
- Do not publish speculative Header hooks without a concrete consumer/contract gate; defer mega-menu builder.
- Woo v1.1 focuses on product cards, catalog/product presets, gallery/summary and cart/checkout/account polish.
- Defer AJAX filters/live search/wishlist/compare/swatches/quick-view/custom checkout engines from Theme core.
- Control Center remains presentation-only with one versioned Theme settings schema.
- System Health only reports Theme-owned/public capability facts.
- Provider and Woo assets become stricter surface/capability scoped.
- Release engineering must verify exact `main` after merge and deterministic packaged bytes before tag/release.
- V1.0 source reconciliation is a hard prerequisite before production v1.1 implementation.

## 23. Planning and next gate

After owner reviews and approves this written spec:

1. invoke the planning phase;
2. write the first detailed implementation plan for **R0 source reconciliation** and a concise master dependency map for R0-R7;
3. after R0 source acceptance, create bounded milestone plans for R1-R7 as each becomes exact next rather than one giant implementation plan;
4. execute production feature/bugfix slices with RED -> intended failure -> minimal GREEN -> regression and the AZT QA layers;
5. stop only at true hard gates defined by source.

No production code is authorized by this spec-review step alone.
