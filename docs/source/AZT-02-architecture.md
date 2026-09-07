**AZNET THEME**

Kiến trúc Theme và Integration Contracts

Kiến trúc lớp, dependency policy, provider boundary và chiến lược tích hợp giữa AZnet Theme với WordPress, ConvertFlow, RootProfile và WooCommerce.

| **Mã tài liệu** | AZT-02 | **Phiên bản** | v0.4 |
| --- | --- | --- | --- |
| **Trạng thái** | Working Source | **Ngày** | 03/09/2026 |

| **Kiến trúc lõi: **Theme chỉ sở hữu presentation/configuration/reference cần thiết. Dữ liệu authoritative và business state ở lại owner. Boundary giữa hai bên là public/versioned provider contract hoặc adapter tối thiểu. |
| --- |

# 1. Mô hình lớp

| **Lớp** | **Trách nhiệm** | **Luật phụ thuộc** |
| --- | --- | --- |
| Domain owners | ConvertFlow, RootProfile, WooCommerce, WordPress native state | Không phụ thuộc AZnet Theme để giữ khả năng chạy trên theme khác |
| Public integration contracts | Normalized bounded payload, capabilities/version, hooks/interfaces | Ổn định hơn storage nội bộ; không expose private implementation |
| AZnet Theme integrations | Consumer adapters, token mapping, availability checks | Phụ thuộc contract công khai, không phụ thuộc storage/private class |
| AZnet Theme presentation | Tokens, shell, templates, composition, responsive/a11y interaction | Không giữ domain state; render từ normalized data hoặc fallback hợp lệ |

# 2. Dependency policy

- AZnet Theme không được là hard dependency của ConvertFlow, RootProfile hoặc WooCommerce.
- ConvertFlow/RootProfile có thể consume semantic theme tokens khi AZnet Theme hiện diện, nhưng phải có scoped fallback.
- Theme được phép phát hiện capability/provider theo public API và thay đổi presentation tương ứng.
- Theme không được suy luận dữ liệu authoritative khi provider vắng mặt. Fallback chỉ dùng dữ liệu WordPress/theme mà bản chất thực sự thuộc phạm vi fallback đó.
- Theme switching phải không làm mất domain data vì theme không sở hữu domain storage.

# 3. Contract requirements

| **Yêu cầu** | **Tiêu chuẩn** |
| --- | --- |
| Public | Tên/hook/interface nằm trong API được owner cam kết, không gọi private class/method. |
| Versioned | Có version/capability để consumer phân biệt payload và hỗ trợ migration. |
| Bounded | Chỉ expose dữ liệu cần cho use case presentation, không đẩy toàn bộ storage model ra ngoài. |
| Normalized | Consumer nhận shape ổn định, không phải biết option/meta/table nào đang lưu dữ liệu. |
| Read-oriented | Theme render dữ liệu; mutation/domain workflow đi qua owner API, không ghi storage trực tiếp. |
| Fail-soft | Provider absent/error không fatal; component ẩn hoặc fallback hợp lệ. |
| Testable | Có contract tests cho present/absent/old-new compatible cases. |

# 4. Provider boundary theo use case

| **Use case** | **Provider/owner** | **Theme cần nhận** | **Khi vắng mặt** |
| --- | --- | --- | --- |
| Organization / Person / Contact basic | RootProfile provider v1 — rootprofile/presentation/provider/v1 | Bounded normalized resources organization, person, contact; compatibility/basic presentation payload | Ẩn phần phụ thuộc hoặc fallback WordPress/theme được xác định rõ; không suy luận authoritative data |
| Full Person / Organization Profile | RootProfile provider v2 — rootprofile/presentation/provider/v2 | person_profile, organization_profile; contract=rootprofile.presentation; version=2; resolved public-safe profile read model | Không tự dựng identity/profile semantics từ user meta/storage; provider absent/error/mismatch => fail-soft |
| Commerce shell | WooCommerce | Public template/functions/state cần cho display | Theme vẫn chạy như WordPress site không commerce |
| Homepage Journey | ConvertFlow | Public rendering/integration boundary của Journey | Front page fallback của theme/WordPress; không tự clone Journey semantics |
| CTA / trust surfaces | Owner tương ứng | Normalized payload + capability | Ẩn hoặc fallback presentation hợp lệ; không copy trust/conversion state |
| Current request Profile / Contact surface context | RootProfile current-surface v1 — rootprofile/presentation/current-surface/v1 | contract=rootprofile.current_surface; version=1; surface=person_profile\|organization_profile\|contact; nested public-safe presentation payload from the existing RootProfile provider family | Không suy luận request identity/route; không takeover. Provider absent/error/mismatch => fail-soft và giữ fallback/owner hiện hành. |

# 5. Semantic design token contract

Global theme tokens là integration surface chiều ngược lại: plugin presentation có thể đọc token semantic của theme, nhưng không được phụ thuộc cứng vào giá trị cụ thể. Public CSS custom properties dùng family prefix --aznet-theme-*; tên token tiếp tục theo vai trò semantic, không theo màu vật lý. Token versioning và cơ chế expose PHP->CSS chỉ được thêm khi có use case thực tế.

| **Nhóm token** | **Mục đích** | **Nguyên tắc** |
| --- | --- | --- |
| Color semantic | surface, text, muted, border, accent, positive/warning/critical | Tên theo vai trò, không theo màu vật lý như blue-500 trong contract công khai |
| Typography | font family, body/heading scale, weight, line-height | Plugin dùng semantic level; theme quyết định visual implementation |
| Spacing/layout | space scale, container widths, gutter, radius | Tránh hard-code giữa plugin/theme; có fallback scoped |
| Elevation/interaction | shadow, focus ring, transition | Accessibility và motion preference phải được tôn trọng |
| Header geometry | sticky offset / admin bar aware reference | Chỉ expose geometry cần cho integration, không expose DOM internals |

| Decision D-009 đã khóa prefix family: aznet-theme. Public CSS custom properties dùng --aznet-theme-*; PHP namespace dùng AZnet\\Theme; procedural functions dùng aznet_theme_; constants dùng AZNET_THEME_. |
| --- |

# 6. Cấu trúc source tree - đề xuất baseline

Đây là đề xuất implementation cho skeleton, chưa phải quyết định bất biến. Mục tiêu là làm ownership nhìn thấy ngay trong source tree và cô lập adapter khỏi presentation.

| **Path đề xuất** | **Trách nhiệm** |
| --- | --- |
| assets/css/ | Foundation, tokens, components generic, responsive utilities có owner ở theme |
| assets/js/ | Interaction generic của theme: navigation, disclosure, accessibility helpers |
| inc/theme/ | Theme setup, supports, assets, template helpers không domain-specific |
| inc/integrations/ | Adapter/availability checks cho provider công khai; mỗi owner một module rõ |
| template-parts/ | Header/Footer/generic compositional parts |
| templates/ hoặc template hierarchy root | Page/Post/Archive/Search/404/front-page shell |
| woocommerce/ | Chỉ override presentation thực sự cần thiết; tránh fork template nếu hooks/CSS đủ |
| tests/ | Contract, runtime, accessibility smoke và visual baseline tooling |

# 7. Anti-patterns bị cấm

- Đọc trực tiếp option/meta/table của plugin để “cho nhanh”.
- Copy choiceguide_* / _choiceguide_* keys, resolver precedence, schema, analytics state sang theme.
- Copy RootProfile entity/claim/evidence/readiness storage hoặc authoritative semantics sang theme.
- Fork WooCommerce commerce logic hoặc coi theme là owner của price/stock/variation/order.
- Đưa Flatsome-specific assumption vào core theme mà không cô lập thành adapter/compatibility layer.
- Tạo shared library chỉ vì vài đoạn duplicate khi chưa có ít nhất hai consumer thực sự và contract ổn định.

# 8. Technical baseline cho Milestone A/B

| **Baseline** | **Quyết định đã khóa** |
| --- | --- |
| **Theme architecture** | Hybrid PHP theme + theme.json. Không dùng FSE làm source-of-truth cho template composition ở v0.x; block editor/theme.json vẫn được hỗ trợ cho settings, styles và tokens. |
| **WordPress minimum** | 6.9+. Skeleton phải khai báo Requires at least tương ứng và test activation/runtime trên floor trước khi nâng yêu cầu. |
| **PHP minimum** | 8.1+. Không dùng cú pháp/API bắt buộc PHP 8.2+ nếu chưa có Decision Log nâng floor. |
| **Naming family** | aznet-theme là lexical root duy nhất. Tránh choiceguide_* và tránh generic prefix có nguy cơ xung đột. |
| **Build tooling** | Milestone A không bắt buộc bundler. Asset pipeline giữ tối giản cho tới O-007 hoặc nhu cầu module/minification thực tế. |

Compatibility QA nên tách minimum-floor verification khỏi current-stack verification; không được biến “requires” thành tuyên bố certification cho tổ hợp chưa chạy test.

# 9. RootProfile dual-provider baseline — E0

- E0 ratifies two independent same-process public provider protocols; không gộp hoặc repurpose phiên bản hiện hành.
- Provider v1: hook rootprofile/presentation/provider/v1; resources organization, person, contact; behavior/payload hiện hành giữ compatibility.
- Provider v2: hook rootprofile/presentation/provider/v2; resources person_profile, organization_profile; contract marker rootprofile.presentation; version integer 2.
- Provider v2 required public identity fields: uuid, display_name, profile_url. Readiness vocabulary: READY | READY_WITH_WARNINGS | NEEDS_REVIEW | NOT_READY | UNKNOWN.
- RootProfile tiếp tục sở hữu Entity UUID, canonical/profile URL, public eligibility, Contact facts, Claim/Evidence/Relationship/Responsibility/Readiness và resolved Profile section semantics.
- AZnet Theme chỉ consume read-only normalized payload; không đọc option/meta/CPT/table/private class của RootProfile, không tạo parallel truth và không mutation authoritative facts.
- Provider absent, exception, unsupported version/resource hoặc malformed payload phải fail-soft; Theme không direct-storage fallback hoặc tự suy luận authoritative identity/contact/profile data.
- Contact tiếp tục ở v1; Provider v2 là additive cho full Profile Surface và không hấp thụ Contact.
- O-004 được đóng tại E0 bằng dual-provider baseline này. Mọi thay đổi payload semantics trong tương lai phải do RootProfile source owner version hóa và Theme chỉ cập nhật consumer theo public contract.

# 10. RootProfile current-surface context — E5

- E5 bổ sung một public read contract riêng để RootProfile, với tư cách source owner của request/profile mapping, có thể cho Theme biết request hiện tại thuộc surface presentation nào mà không expose private routing/storage internals.
- Hook: rootprofile/presentation/current-surface/v1. Contract marker: rootprofile.current_surface. Version: integer 1. Surface allow-list: person_profile, organization_profile, contact.
- Payload chỉ là context + nested public-safe presentation read model đã được owner resolve. Theme không nhận internal Person/Page ID, CPT/meta/table key hoặc dữ liệu dùng để tái dựng authoritative routing.
- RootProfile tiếp tục sở hữu canonical/profile URL, mapped Contact Page, request resolution, public eligibility và authoritative identity/contact/profile semantics. AZnet Theme chỉ consume context để chọn presentation renderer.
- Current-surface context KHÔNG đồng nghĩa với quyền production takeover. Việc Theme chiếm render path là gate riêng, chỉ được xem xét sau runtime/browser/a11y evidence và approval tương ứng.
- Theme-side consumer/dispatcher được phép tồn tại ở trạng thái dormant và fail-soft. Contract vắng, throw, version/resource mismatch hoặc malformed payload phải trả về no-render/fallback hợp lệ; cấm slug/title/Page-ID heuristic hoặc direct-storage fallback.
- Provider v1 organization/person/contact và Provider v2 person_profile/organization_profile vẫn giữ nguyên trách nhiệm/compatibility đã ratify tại E0; current-surface v1 là additive request-context boundary, không repurpose hai provider hiện hữu.
