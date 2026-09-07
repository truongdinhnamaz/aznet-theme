**AZNET THEME**

Product Charter và Ownership

Định nghĩa AZnet Theme là gì, không là gì, sở hữu phần nào và phải nhường ownership cho hệ thống nào.

| **Mã tài liệu** | AZT-01 | **Phiên bản** | v0.4 |
| --- | --- | --- | --- |
| **Trạng thái** | Working Source | **Ngày** | 07/09/2026 |

| **Định vị: AZnet Theme là house/reference WordPress theme ưu tiên cho website greenfield do AZnet triển khai. Theme phải hoạt động như một sản phẩm độc lập trên WordPress sạch; RootProfile, ConvertFlow, WooCommerce và provider khác là capability tích hợp tùy chọn, không định nghĩa readiness của core Theme.** |
| --- |

# 1. Sứ mệnh sản phẩm

Cung cấp một nền trình bày WordPress có chất lượng sản phẩm: nhất quán, responsive, accessible, có thể tái sử dụng giữa các website AZnet và tích hợp sạch với các plugin thông qua contract. Theme phải kế thừa presentation code đã được kiểm chứng khi phù hợp thay vì viết lại chỉ vì đổi owner.

# 2. Mục tiêu

- Chạy độc lập trên WordPress thuần và fail-soft khi ConvertFlow, RootProfile, WooCommerce hoặc provider khác vắng mặt; không dùng trạng thái của plugin tùy chọn để định nghĩa core Theme readiness.

- Sở hữu global design tokens, shell, layout primitives, Header/Footer và generic templates.

- Cho phép plugin dùng semantic theme tokens nhưng plugin vẫn giữ scoped fallback để chạy trên theme tương thích khác.

- Trình bày dữ liệu authoritative từ provider mà không tạo parallel store trong theme.

- Giảm duplicated historical presentation code bằng migration có provenance, regression QA và rollback.

- Tạo một reference implementation đủ tốt để AZnet dùng làm baseline cho website mới.

## 2.1. v1.1 Native Product System objective

AZnet Theme v1.1 cải thiện authoring usability và commerce presentation thông qua semantic Design System 2.0, curated native patterns, bounded Header presets, WooCommerce presentation presets, presentation-only Control Center/System Health và reusable release/performance infrastructure.

Mục tiêu là đạt mức usability của một commercial theme tốt mà không tạo proprietary page builder. WordPress-native content phải tiếp tục portable; Theme options chỉ được chứa Theme-owned presentation state.

Các deliverable v1.1 được tổ chức theo R1-R6:

- R1 — Design System 2.0 và Default/Editorial/Commerce visual presets;
- R2 — WordPress-native Pattern Library;
- R3 — Header System 2.0 với bounded presets/primitives;
- R4 — WooCommerce Presentation 2.0;
- R5 — presentation-only Control Center + System Health;
- R6 — performance, exact-main verification và deterministic release infrastructure.

# 3. Non-goals

| **Không thuộc AZnet Theme** | **Owner đúng** |
| --- | --- |
| Homepage/Product/Service Journey semantics, resolver/state/validation, conversion behavior, analytics | ConvertFlow |
| Person/Organization identity, Entity UUID, claim/evidence/readiness và authoritative profile semantics | RootProfile |
| Product, price, stock, variation, cart/order và commerce state | WooCommerce |
| Post/Page/Menu/Media/query native ownership | WordPress |
| Plugin admin workflow chỉ vì có CSS/UX tốt | Plugin sở hữu workflow đó |
| Secrets, license/signing material, private intelligence/server capability logic | Capability/domain owner tương ứng |

V1.1 cũng **không** biến Theme thành application platform. Không thuộc scope v1.1:

- proprietary page builder hoặc proprietary serialized content schema;
- drag/drop Header Builder hoặc mega-menu builder;
- AJAX product filter/search engine;
- wishlist/compare application state;
- variation swatch domain engine;
- quick-view business engine;
- custom checkout engine hoặc commerce workflow thay WooCommerce;
- provider-domain mutation/admin workflows chỉ vì Theme có Control Center;
- extension hook công khai chưa có consumer thực tế, versioning và contract-test decision.

# 4. Ownership theo surface

| **Surface** | **AZnet Theme sở hữu** | **Domain/plugin sở hữu** |
| --- | --- | --- |
| Header / Footer | Composition, responsive presentation, global tokens, generic interaction | Identity/contact/social qua provider; menu WordPress; cart/account/search WooCommerce; CTA/trust từ owner |
| Homepage | Front-page template, site shell, global visual foundation | ConvertFlow Journey structure, section semantics, source selection, validation, navigation/conversion, observability |
| Profile | Template, typography, grid, responsive composition | RootProfile identity, URL, entity/claim/evidence/readiness và provider payload |
| Contact | Page template/composition | Authoritative contact/organization data; form/conversion behavior từ owner |
| Product | Woo-compatible page shell và global styling | Woo commerce state; ConvertFlow Product Journey/Filter/Fit/Fast Conversion |
| Post/Page/Archive/Search/404 | Theme template/presentation | WordPress content/query; plugin data chỉ qua contract rõ |

# 5. Nguyên tắc thiết kế hệ thống

1. Ownership before reuse: trước khi port code phải biết code chịu trách nhiệm cho presentation, domain, mixed hay adapter.

1. Generic before specific: chỉ primitive thực sự generic mới thuộc theme; renderer gắn chặt Journey semantics vẫn thuộc ConvertFlow.

1. Contract before data access: theme không đọc option/meta/table/private class của plugin.

1. Fail-soft before coupling: thiếu provider/plugin không được fatal; component phụ thuộc có thể ẩn hoặc dùng fallback hợp lệ.

1. Provenance before cleanup: chưa biết origin/canonical thì chưa xóa duplicate.

1. Regression before retirement: code cũ chỉ được retire sau khi destination path và fallback/compatibility path PASS.

1. Reversibility while uncertain: feature stream mới ưu tiên lát cắt nhỏ, có rollback và học nhanh thay vì bulk rewrite.

1. Performance by necessity: AZnet Theme chỉ tải presentation asset và capability thực sự cần cho surface hiện tại. Không đưa chức năng tổng quát vào theme chỉ để tăng feature count. Mục tiêu không phải "nhẹ hơn Flatsome" như một benchmark marketing, mà là tránh chi phí runtime không phục vụ trải nghiệm đang được render.

1. Native before proprietary: authoring/composition ưu tiên WordPress core blocks, patterns và Woo public Blocks/APIs. Theme không tạo content format riêng chỉ để tăng độ tiện dụng.

1. Curated before unlimited: Theme được phép cung cấp bounded presets/patterns/settings có chất lượng cao; không cần tái tạo mọi quyền tự do của một page builder.

# 6. Definition of Done cho AZnet Theme v1.0

- Activate/install/update được trên WordPress sạch 6.9+ / PHP 8.1+; template hierarchy, native Homepage fallback, Page/Post/Archive/Search/404 không fatal.

- Global tokens, Header/Footer, generic templates và native Homepage shell có owner rõ trong Theme; responsive, accessibility và surface-aware performance core PASS.

- Optional integration code chỉ consume public/versioned/capability-detected contract, không duplicate authoritative data; provider absent/error/unsupported phải fail-soft.

- Failure thuộc provider/plugin ngoài Theme không chặn core v1.0 nếu WordPress-clean core vẫn PASS, Theme-side absence/failure path an toàn và Theme không vi phạm public contract/ownership.

- Nếu một provider/version được công bố là certified compatibility, relevant runtime/browser/integration matrix phải PASS trước khi gắn certification; certification được version hóa riêng khỏi core readiness.

- Mỗi code slice reuse có provenance/test/migration; v1.0 package phải reproducible, unzip/reverify, upgrade/theme-switch và rollback path được chứng minh.

- Mỗi code slice reuse có provenance, test bảo vệ và trạng thái migration.

| **Điều kiện bắt buộc: **Nếu một yêu cầu UI chỉ có thể làm bằng cách copy domain data hoặc gọi private internals, đó là dấu hiệu contract chưa đủ - không phải lý do để phá ownership. |
| --- |

# 7. Nguyên tắc release independence

- Core Theme v1.x được quyết định bởi lời hứa sản phẩm của Theme trên WordPress sạch và các QA layer thuộc Theme.

- Optional integrations được duy trì như compatibility/certification tracks. Chúng có thể BLOCKED/UNKNOWN mà không biến core Theme thành unfinished nếu fail-soft boundary đã đúng.

- Known external defect phải được ghi rõ owner + evidence + unblock condition; Theme không tự code dependency hoặc phá contract để biến integration thành PASS.

- Chỉ khi một integration được tuyên bố là bắt buộc cho release hoặc lỗi nằm trong Theme-owned implementation thì failure đó mới trở thành core release blocker.

# 8. v1.1 product quality rule

V1.1 được xem là hoàn thành khi các capability đã chọn đạt QA tương ứng và được chứng minh trên final candidate bytes, không phải khi Theme có feature-count ngang một commercial theme khác. Khi so sánh với các điểm mạnh của Flatsome hoặc theme thương mại tương tự, AZnet Theme chỉ tiếp thu **outcome có giá trị** như authoring speed, visual consistency, Header/Woo usability và operational confidence; không sao chép proprietary builder/domain ownership hoặc architecture coupling để đạt outcome đó.
