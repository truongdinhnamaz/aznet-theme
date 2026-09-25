**AZNET THEME**

Kiến trúc Theme và Integration Contracts

Kiến trúc lớp, dependency policy, provider boundary và chiến lược tích hợp giữa AZnet Theme với WordPress, ConvertFlow, RootProfile và WooCommerce.

| **Mã tài liệu** | AZT-02 | **Phiên bản** | v0.14 |
| --- | --- | --- | --- |
| **Trạng thái** | Working Source | **Ngày** | 19/09/2026 |

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
- Public extension hook mới không được tạo chỉ vì “có thể cần sau này”. Hook/contract mới cần consumer thực tế, owner/source decision, versioning strategy và contract tests trước khi trở thành public surface.

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

## 5.1. Typography mặc định của Theme

AZnet Theme sở hữu font presentation và semantic typography tokens. Font mặc định toàn Theme là `Roboto, Arial, sans-serif`; body/UI và heading cùng tiêu thụ Theme-owned family tokens thay vì mỗi surface tự hard-code font riêng.

- `--aznet-theme-font-family-base` là family mặc định cho body/UI.
- `--aznet-theme-font-family-heading` là semantic heading family và mặc định tham chiếu family base.
- Roboto được self-host trong Theme; runtime không phụ thuộc Google Fonts/CDN bên ngoài.
- Chỉ ship các weight thực sự dùng; implementation hiện dùng 400 Regular, 500 Medium và 700 Bold. Selector cần emphasis trung gian phải chọn weight gần nhất có chủ đích, không relabel/synthesize font file.
- `theme.json` và frontend phải dùng cùng family để giữ editor/frontend parity.
- Provider/plugin có thể consume semantic typography token khi cần presentation alignment nhưng không sở hữu, lưu hoặc mutation Theme typography.

# 6. Cấu trúc source tree - baseline

Mục tiêu là làm ownership nhìn thấy ngay trong source tree và cô lập adapter khỏi presentation.

| **Path** | **Trách nhiệm** |
| --- | --- |
| assets/css/ | Foundation, semantic tokens, components generic, responsive utilities có owner ở theme |
| assets/js/ | Interaction generic của theme: navigation, disclosure, accessibility/progressive-enhancement helpers |
| inc/theme/ | Theme setup, settings, supports, assets, template helpers không domain-specific |
| inc/integrations/ | Adapter/availability checks cho provider công khai; mỗi owner một module rõ |
| inc/admin/ | Chỉ Theme-owned presentation settings UI và read-only public capability diagnostics |
| template-parts/ | Header/Footer/generic compositional parts và bounded component primitives |
| patterns/ | WordPress-native block patterns; không phải proprietary data/content store |
| templates/ hoặc template hierarchy root | Page/Post/Archive/Search/404/front-page shell |
| woocommerce/ | Chỉ override presentation thực sự cần thiết; tránh fork template nếu hooks/CSS/Blocks đủ |
| tests/ | Contract, runtime, accessibility smoke và visual baseline tooling |

# 7. Anti-patterns bị cấm

- Đọc trực tiếp option/meta/table của plugin để “cho nhanh”.
- Copy choiceguide_* / _choiceguide_* keys, resolver precedence, schema, analytics state sang theme.
- Copy RootProfile entity/claim/evidence/readiness storage hoặc authoritative semantics sang theme.
- Fork WooCommerce commerce logic hoặc coi theme là owner của price/stock/variation/order.
- Đưa Flatsome-specific assumption vào core theme mà không cô lập thành adapter/compatibility layer.
- Tạo shared library chỉ vì vài đoạn duplicate khi chưa có ít nhất hai consumer thực sự và contract ổn định.
- Tạo proprietary page-builder content schema, Header Builder store hoặc application state chỉ để mô phỏng commercial theme feature count.

# 8. Technical baseline cho v1.x

| **Baseline** | **Quyết định đã khóa** |
| --- | --- |
| **Theme architecture** | Hybrid PHP theme + theme.json. Không dùng FSE làm source-of-truth cho template composition ở v1.x; block editor/theme.json vẫn được hỗ trợ cho settings, styles và tokens. |
| **WordPress minimum** | 6.9+. Theme khai báo Requires at least tương ứng và test activation/runtime trên floor trước khi nâng yêu cầu. |
| **PHP minimum** | 8.1+. Không dùng cú pháp/API bắt buộc PHP 8.2+ nếu chưa có Decision Log nâng floor. |
| **Naming family** | aznet-theme là lexical root duy nhất. Tránh choiceguide_* và tránh generic prefix có nguy cơ xung đột. |
| **Build tooling** | Không tự thêm bundler/build framework. Asset pipeline giữ tối giản cho tới O-007 hoặc nhu cầu module/minification thực tế. |

Compatibility QA phải tách minimum-floor verification khỏi current-stack verification; không được biến “requires” thành tuyên bố certification cho tổ hợp chưa chạy test.

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

# 11. v1.1 Native Product System architecture

V1.1 nâng mức tiện dụng/polish của Theme nhưng không thay đổi product ownership hoặc biến Theme thành application engine.

## 11.1. Design System 2.0

Kiến trúc token:

`foundation -> semantic -> component -> WordPress mapping`

- Foundation values là implementation detail của Theme.
- Semantic roles là contract ổn định cho Theme presentation và public token consumers.
- Component tokens chỉ tồn tại khi một Theme component thực sự cần chúng.
- `theme.json` mapping đưa curated semantic vocabulary vào block editor mà không chuyển template ownership sang FSE.
- Existing public `--aznet-theme-*` semantics không được silently repurpose; compatibility alias được giữ khi đổi implementation vocabulary.

### Visual preset feasibility rule

Trên WordPress 6.9 support floor, R1 phải chứng minh liệu native style-variation selection có đáp ứng hybrid PHP architecture và editor/frontend parity hay không.

- Nếu PASS: dùng native mechanism.
- Nếu không PASS: dùng Theme-owned semantic visual-preset mapping.
- Không được chuyển Theme sang FSE/block-template ownership chỉ để có style variations.

Outcome v1.1 được khóa ở ba visual preset: `default`, `editorial`, `commerce`; implementation mechanism phải evidence-gated.

## 11.2. Theme-owned presentation settings

### Homepage Hero source rule

Law 01 Hero no longer requires a dedicated WordPress Page as the primary authoring model. The accepted model is **Hero Library + WordPress-native synced Hero content**:

- Theme owns the Hero visual library and presentation variant only.
- WordPress owns the actual Hero content as one ordinary `wp_block` synced-pattern entity containing Core blocks only.
- Theme stores only a typed `homepage_hero_block` reference plus allow-listed `homepage_hero_variant` presentation state.
- These two keys are backward-compatible additions to the existing `aznet_theme_settings` family; `schema_version` remains `3` and no second Theme Mod or migration store is introduced.
- The Theme MUST NOT copy Hero heading/body/image/CTA content into `aznet_theme_settings`.
- For the primary Hero Library authoring path, **all user-editable Hero copy belongs to the same WordPress-owned synced Hero**: eyebrow/label, H1, value/supporting copy, CTA labels/links, media and trust/supporting messages. Theme code may style these elements but must not become the authoritative content source.
- The legacy Site Title / Front Page title / Site Tagline projection and legacy generic trust strings are compatibility-only. Once a valid published `homepage_hero_block` exists, the rendered Hero must not depend on those fallback strings.
- Control Center must expose the synced Hero as the single authoring destination for new UX; changing Site Title, Tagline or Front Page title is not the supported way to edit a configured Hero.
- Changing `homepage_hero_variant` MUST change presentation only and MUST NOT rewrite the synced Hero block content.
- The Theme MAY create the initial synced Hero block only through an explicit, nonce/capability-protected user action from the Hero Library. Initialization is **draft-first**: the new `wp_block` is stored as a draft and the typed reference may point to it, but public Hero resolution still requires `publish`, so the existing legacy/fallback Hero remains live until the user explicitly publishes the new Hero in the native block editor. Generic Theme activation, preset switching and ordinary settings save remain content-mutation free.
- The created `wp_block` content must use ordinary Core blocks and remain editable in the native WordPress editor. No proprietary block type, opaque serialized builder schema or second content store is allowed.
- Theme switching must not delete the synced Hero block. The content remains WordPress data even when another Theme no longer renders AZnet-specific presentation classes.
- Rollback is content-safe: changing the referenced Hero `wp_block` back to `draft` makes public resolution fall through to the retained legacy Page/Site path while keeping the block ID/reference and authored content intact.
- The Hero Library may provide presentation variants such as split, centered, inverse and media-left over the same WordPress-owned content source.

Backward compatibility remains required. Resolution precedence is:

1. valid `homepage_hero_block` synced Hero content;
2. legacy valid `homepage_hero_page` mapping for sites already using the v1.3.4-v1.3.10 Page model;
3. legacy pre-v1.3.4 public WordPress Site/Front Page projection when neither explicit Hero source is valid.

The legacy Page reference is compatibility-only for new UX. The Theme MUST NOT auto-delete, auto-migrate or overwrite that Page. A future explicit migration action may copy content into a WordPress-native synced Hero block only after separate source/UX approval.

This keeps ownership clean while reducing authoring friction: WordPress owns Hero content/media; AZnet Theme owns Hero presentation, the visual library and typed source selection.

Theme-owned presentation settings dùng **một** versioned Theme Mod schema: `aznet_theme_settings`.

Rules:

- strict allow-list normalization;
- schema version được lưu cùng presentation settings;
- chỉ lưu Theme presentation state như visual/Header/commerce presets;
- không lưu WordPress Post/Page/Menu/Media data;
- không lưu WooCommerce product/cart/order/session truth;
- không lưu RootProfile/ConvertFlow domain state hoặc private provider snapshot;
- không lưu secrets, credentials, signing/license material.

Admin UI, import/export và support snapshot phải đi qua cùng normalized schema; không tạo second settings store.

## 11.3. Native Pattern Library

Pattern architecture: **core/Woo Blocks first, portable content, no proprietary store**.

- Core patterns dùng WordPress blocks và Theme presentation classes/tokens.
- Woo-dependent patterns chỉ register khi exact public Woo block capability tồn tại; provider absent => pattern không register, Theme vẫn hoạt động.
- Pattern example content không trở thành authoritative business/trust data.
- Sau theme switch, saved WordPress block content phải còn là portable block content, không biến thành shortcode/opaque builder payload.

## 11.4. Header System 2.0

Architecture:

`WordPress/Woo public data -> primitive renderers -> bounded preset composer -> Header surface`

- Presets: Standard, Compact, Commerce, Overlay.
- Preset thay composition/visibility/presentation, không tạo domain store.
- Logo/Menu/Search dùng native WordPress public APIs/data.
- Commerce actions chỉ dùng Woo public functions/capabilities.
- Mobile/sticky behavior là progressive enhancement; navigation phải còn usable khi JS fail.
- Overlay chỉ được dùng trên surface được Theme xác định an toàn bằng public WordPress condition; không dùng slug/title/Page-ID/URL heuristic.
- Không drag/drop Header Builder hoặc mega-menu application engine.

## 11.5. WooCommerce Presentation 2.0

Architecture:

`Woo public/native output -> Theme surface adapter -> bounded presentation preset`

- WooCommerce tiếp tục sở hữu product, variation, price, stock, cart, checkout, order, account và query truth.
- Theme được cung cấp product-card/catalog/single-product/cart/checkout/account presentation presets.
- Hooks/CSS/Blocks-first; template override chỉ là exception khi concrete failing test chứng minh public hooks/CSS không đủ và source review chấp thuận.
- Không wishlist/compare, AJAX filter/search, swatch domain engine, quick-view business engine hoặc custom checkout engine.
- Woo asset loading giữ surface-aware; Woo absent không làm ảnh hưởng WordPress-clean core.

## 11.6. Control Center và System Health

Control Center là **presentation settings console**, không phải domain/admin platform.

- Chỉ mutate `aznet_theme_settings` sau capability/nonce/normalization.
- Logo/Menu/Patterns được link sang native WordPress controls thay vì clone editor riêng.
- Quick Setup chỉ hướng dẫn/chọn Theme presentation; không tự tạo Page/Product/Profile/Journey/Menu data.
- System Health là read-only diagnostic từ WordPress environment, Theme settings và public provider capability functions.
- Provider status không được suy luận từ private constants/classes/options/storage; unknown phải báo `unknown/not-detected`.
- Import/export chỉ chứa normalized Theme presentation settings và phải bounded/sanitized.

## 11.7. Performance và release infrastructure

- Assets tiếp tục surface/capability-aware; không global ecosystem bundle.
- Performance claim cần measured evidence, không dựa feature-count/marketing comparison.
- PR verification và exact-main post-merge verification là hai evidence points khác nhau.
- Release candidate phải deterministic, byte-reproducible, SHA-256, unpack/reverify và clean-WordPress activation smoke.
- Optional provider optimization chỉ được thực hiện qua public/versioned capability. Nếu capability thiếu, ghi BLOCKED_EXTERNAL_CONTRACT và giữ safe behavior; không private-detect để tối ưu.


# 12. Homepage Composer v1

Homepage Composer là Theme-owned presentation composition, không phải content/domain engine.

- Content Map chỉ lưu typed references tới WordPress Page/Category hiện hữu; không copy title/body/excerpt/contact/profile/query-result data.
- Presentation Preset chỉ lưu Theme-owned layout/visual behavior và có thể đổi mà không mutation WordPress content.
- `front-page.php` phải giữ WordPress Loop của Front Page và đúng một `the_content()` execution để WordPress/public provider filters giữ nguyên boundary.
- Theme được render mapped WordPress-native presentation sections quanh body boundary đó nhưng không được reconstruct ConvertFlow Journey semantics.
- Reference validation dựa trên object type/publication state. Missing/invalid reference phải fail-soft; cấm title/slug/URL heuristic repair.
- Law 01 v1 không có RootProfile team-collection integration vì chưa tồn tại accepted public collection contract cho use case này.
- Applying Homepage preset không được create/rewrite/delete/publish/reclassify WordPress content.

# 13. Law Site Provisioning v1

AZnet Theme may own an explicit, bounded setup workflow that creates ordinary WordPress-native starter objects through public WordPress APIs only after an authorized user confirms an explicit change plan.

This bootstrap capability does not transfer ongoing Page/Post/Category/Menu ownership to Theme.

Required constraints:
- Theme activation performs no content provisioning.
- Provisioning and Homepage preset switching are separate operations.
- Existing-site reuse is explicit and typed; title/slug/URL matching cannot silently establish semantic source ownership.
- Created objects become ordinary WordPress content and survive Theme switching.
- Idempotency uses bounded provisioning provenance plus the current explicit Content Map.
- Failed-run rollback may remove only objects created by that failed run and may restore only captured configuration/settings changed by the run.
- After a successful handoff there is no Theme-owned destructive “delete all demo content” action.
- Starter copy is non-authoritative and is not synchronized after successful provisioning.


# 12. Law Site Provisioning v1.1 — recommendation, starter content/media và index-safety

- Blueprint machine key: `law01-v1-1`; `law01-v1` provenance remains compatible.
- Recommendation state is proposal-only and never authoritative mapping.
- Exact normalized display-label matching is allowed only to preselect UI suggestions; no slug/URL/fuzzy/synonym inference is authoritative.
- Starter Posts and attachments are created only through an explicit confirmed provisioning plan and become ordinary WordPress-owned content/media after creation.
- On a confirmed new/mostly-empty site plan, publishing starter Posts may be coupled to explicit WordPress-native `blog_public=0` consent. Declining that consent falls back to Draft starter Posts.
- Existing/active sites must never receive whole-site noindex merely to publish starter content. Without an accepted public per-Post SEO-owner contract, starter Posts remain Draft.
- Theme must not direct-write Rank Math, Yoast or other private SEO plugin storage/API.
- Current explicit Content Map and WordPress publication/media state remain authoritative over recommendations and provisioning provenance.


# 16. Standalone Core runtime independence — D-027

AZnet Theme has **zero mandatory third-party runtime dependency**. WordPress + AZnet Theme alone must provide a complete Core product on the support floor. Optional providers may add presentation capabilities through approved public/versioned contracts but may not complete an otherwise partial Core.

## 16.1 Core runtime invariant

- Core install/activate/setup/author/render must work with zero active third-party plugins.
- Core includes Design System, Header/Footer, native Logo/Menu/Search presentation, Homepage Composer, Theme-owned presets including `law-01`, Quick Setup/Law Site Provisioning, native Post/Page/Archive/Search/404 presentation, native author fallback, Control Center, System Health core readiness and Theme-owned settings portability.
- Provider absence is a normal state, not a Core error. Core admin must not require, nag for or redirect users to install optional plugins.
- Optional-provider UI is capability-driven: hide provider-specific workflows when the public capability is absent rather than rendering a disabled required-plugin surface.
- System Health must separate **Standalone Core** readiness from **Optional Integrations** availability. Optional `Not present` must not downgrade Core readiness.

## 16.2 Provisioning independence

Law01 provisioning must complete from resources shipped with the Theme package plus WordPress public APIs. The supported setup path must not require an AZnet API, mandatory CDN, remote template repository, license server, AI service, plugin marketplace or third-party demo importer. Starter media may be bundled and imported into WordPress Media Library; created content remains WordPress-owned after successful provisioning.

## 16.3 Optional provider boundary

- WooCommerce absent => complete non-commerce Theme; present => additive commerce presentation only, with Woo retaining commerce truth.
- RootProfile absent => WordPress-native author/site presentation; present => additive identity/Profile presentation through public contracts only.
- ConvertFlow absent => native Homepage Composer remains complete; present => additive Journey projection only, with ConvertFlow retaining Journey/conversion semantics.
- SEO plugins remain optional coexistence targets; Theme must not write private SEO-plugin storage to provide standalone behavior.

## 16.4 QA/release invariant

The exact final package must pass a zero-plugin standalone path before Core Ready/publication: install -> activate -> setup -> provision -> representative runtime/browser/a11y -> update/theme-switch continuity. Optional L5 compatibility certification is additive and cannot substitute for Standalone Core PASS. Development/QA tools such as GitHub Actions, WP-CLI, Playwright and axe are allowed because they are not website runtime dependencies.

A QA runner's inability to resolve/authenticate to a pilot site is recorded as `P4 PILOT ACCESS BLOCKED`; it is not evidence that the Theme requires a connector/plugin.

# 17. WordPress-native Team Directory — D-038

Law 01 Team directory uses the explicitly mapped WordPress Team parent Page. Each team member is a direct child Page.

- WordPress owns member title/name, excerpt/role, Featured Image/portrait, body/biography, publication state, permalink, hierarchy and `menu_order`.
- AZnet Theme owns only the typed Team Page mapping, bounded admin authoring presentation, Homepage/directory presentation and provisioning orchestration.
- No Team CPT, personnel repeater/meta store, JSON personnel blob, second Theme Mod store or private provider storage is allowed.
- Team child Pages are editorial website content and MUST NOT be treated as authoritative RootProfile Person identity/profile truth.
- Team resolution MUST use the exact mapped Team parent Page ID. Title, slug, URL and fuzzy heuristics are forbidden.
- Homepage Team may render at most four published direct children in `menu_order title` order; the mapped Team directory Page may render all published direct children.
- Missing portrait is presentation-safe text-only output. Theme MUST NOT substitute generated/reference/AI person imagery.
- Add-member authoring is draft-first and must create only an ordinary child Page after capability/nonce validation.
- Existing mapped Team Pages are reused and are not auto-renamed, duplicated or destructively migrated.

