**AZNET THEME**

Hiến pháp sản phẩm

Khóa các bất biến về bản chất sản phẩm, quyền sở hữu, tính độc lập, chất lượng và điều kiện phát hành của AZnet Theme.

| **Mã tài liệu** | AZT-05 | **Phiên bản** | v1.0 |
| --- | --- | --- | --- |
| **Trạng thái** | ĐÃ PHÊ DUYỆT - PRODUCT CONSTITUTION | **Ngày** | 07/09/2026 |

| **MỆNH ĐỀ TRUNG TÂM: AZnet Theme trước hết là một WordPress theme độc lập, hoàn chỉnh và có chất lượng sản phẩm. Tích hợp với plugin là capability tùy chọn; không plugin nào được định nghĩa sự tồn tại hoặc readiness của Theme.** |
| --- |

# 0. Thẩm quyền của Hiến pháp

- AZT-05 là nguồn có thẩm quyền cao nhất trong phạm vi nội bộ AZnet Theme đối với các bất biến sản phẩm và release constitution.

- ECOSYSTEM-ARCH-01 và source owner của domain bên ngoài vẫn có quyền cao hơn đối với ranh giới liên sản phẩm và semantics của domain họ sở hữu.

- AZT-01/02/03/04 và Implementation Slice Map phải triển khai phù hợp Hiến pháp; không được dùng roadmap, code hoặc evidence để âm thầm sửa Hiến pháp.

- Mọi sửa đổi Hiến pháp phải được product owner phê duyệt rõ, cập nhật source bị ảnh hưởng trước production code và để lại provenance.

# Điều 1. Bản chất sản phẩm

- AZnet Theme là house/reference WordPress theme của AZnet, không phải application framework, business engine, page builder hay plugin orchestration layer.

- Sản phẩm phải có giá trị sử dụng thực khi cài trên WordPress sạch, không yêu cầu RootProfile, ConvertFlow, WooCommerce hoặc bất kỳ plugin AZnet nào.

- Plugin có thể làm Theme mạnh hơn nhưng không được trở thành điều kiện tồn tại, kích hoạt, render, update, rollback hoặc phát hành core Theme.

# Điều 2. Lời hứa sản phẩm

- Cung cấp nền trình bày WordPress nhất quán, responsive, accessible, có thể tái sử dụng, có provenance và vận hành an toàn trên support floor đã công bố.

- Một website dùng AZnet Theme phải có site shell, Header/Footer, generic content templates, native Homepage fallback và design system đủ hoàn chỉnh để hoạt động mà không cần ecosystem plugin.

- Theme phải ưu tiên trải nghiệm WordPress chuẩn và khả năng thay thế thành phần hơn việc tăng số lượng tính năng.

# Điều 3. Quyền sở hữu và ranh giới

| **Nguyên tắc** | **Hiến định** |
| --- | --- |
| **Theme sở hữu** | Design tokens, site shell, template hierarchy, layout/composition, typography, generic interaction, responsive/accessibility presentation và asset loading của chính Theme. |
| **WordPress sở hữu** | Post/Page/Menu/Media/query/editorial lifecycle và các native semantics của nền tảng. |
| **Domain owner sở hữu** | Identity/trust, Journey/conversion, commerce state hoặc domain semantics tương ứng. |
| **Integration contract** | Chỉ public/versioned/capability-detected contract kết nối Theme với provider; Theme không đọc private storage hoặc tự tạo parallel truth. |

# Điều 4. WordPress-native first

- Theme dùng WordPress template hierarchy, public APIs, theme.json và native content boundary làm nền; không tái tạo WordPress thành platform riêng.

- Không clone page builder, query engine, commerce engine, identity system hoặc plugin admin workflow vào Theme chỉ để “đủ tính năng”.

- Gutenberg/theme.json được dùng cho settings/styles/tokens và khả năng soạn thảo phù hợp; kiến trúc hybrid PHP + theme.json tiếp tục cho tới khi source decision thay đổi.

# Điều 5. Tích hợp là capability tùy chọn

- Mọi provider/plugin integration phải fail-soft khi provider vắng mặt, lỗi, không tương thích hoặc capability không đủ.

- Theme chỉ công bố “certified integration” cho version/provider đã có evidence ở layer tương ứng; không suy diễn certification sang version khác.

- Lỗi nằm trong provider bên ngoài không được giữ core Theme ở alpha vô thời hạn nếu WordPress-clean core, absence/failure path và ownership boundary của Theme vẫn PASS.

- Nếu integration hiện diện nhưng không thể bảo đảm an toàn, Theme phải degrade/disable feature liên quan hoặc giữ native fallback thay vì phá ownership hay nhúng workaround private.

# Điều 6. Chất lượng tối thiểu không thương lượng

| **Dimension** | **Floor** |
| --- | --- |
| **Runtime** | Không fatal/warning do Theme trên support floor; template fallback hợp lệ; update/theme switch không làm mất domain data. |
| **Responsive** | Core surfaces hoạt động ở desktop/tablet/mobile, không overflow phá layout và không phụ thuộc một viewport đặc biệt. |
| **Accessibility** | Landmark hợp lệ, keyboard/focus sử dụng được, heading/label có nghĩa, reduced-motion/contrast được tôn trọng ở mức Theme kiểm soát. |
| **Performance** | Surface-aware assets; không tải toàn bộ ecosystem globally; không thêm build stack nếu chưa có nhu cầu đã được quyết định. |
| **Security** | Escape/sanitize đúng context; không leak secret/private provider state; không direct storage access. |
| **Compatibility** | WordPress clean là release-critical. Optional plugin compatibility là capability/certification track, không thay định nghĩa core readiness. |

# Điều 7. Thiết kế và khả năng mở rộng

- Semantic tokens và generic primitives đi trước site-specific styling; tên public contract theo vai trò semantic, không theo màu/website cụ thể.

- Feature mới chỉ vào Theme khi vấn đề thật thuộc presentation/configuration của Theme và có giá trị tái sử dụng hợp lý; không hấp thụ domain chỉ vì dữ liệu đang ở gần UI.

- YAGNI: không tạo page builder, mega-framework, shared library, bundler hoặc abstraction mới nếu use case hiện tại chưa cần.

# Điều 8. Provenance, migration và rollback

- Code reuse phải có origin, responsibility, destination và regression evidence; Mixed code phải split trước khi đổi owner.

- Historical code chỉ retire sau destination + compatibility/fallback + regression + rollback PASS.

- Không sửa snapshot/source lịch sử để làm đẹp current state; source mới supersede, evidence lịch sử được giữ nguyên.

# Điều 9. Hiến pháp phát hành v1.x

| **Gate** | **Quy tắc** |
| --- | --- |
| **Core release gate** | WordPress sạch trên support floor; core Theme surfaces, responsive/a11y, package integrity, upgrade/theme-switch và rollback PASS. |
| **Optional provider gate** | Theme-side adapter/absence/failure behavior phải an toàn. Provider-specific runtime correctness chỉ là release blocker khi Theme tự công bố provider/version đó là bắt buộc đối với release hoặc lỗi nằm trong Theme-owned code. |
| **Known external defect** | Được ghi BLOCKED/UNKNOWN ở compatibility track; không “fix” bằng private API, heuristic authoritative routing hoặc copy domain logic. |
| **Version promotion** | Chỉ sau fresh evidence trên canonical candidate bytes; lint/static không được suy diễn thành runtime/browser/integration. |
| **Owner approval** | Production deploy, release tag và các takeover/merge gate được source quy định vẫn cần phê duyệt rõ. |

# Điều 10. Kỷ luật thực thi

- Milestone → Slice → Micro-slice → Gate → PASS/BLOCKED → Next; mỗi slice bounded, reversible và chỉ claim đúng evidence layer.

- Feature/bugfix production dùng RED → minimal GREEN → regression. Failure ở tầng sâu phải hạ về tầng nông nhất tái hiện ổn định trước khi sửa.

- Không mở lại phần đã PASS nếu không có invalidation cụ thể. Không để optional integration kéo critical path core Theme đi chệch mục tiêu phát hành.

# Điều 11. Những điều Theme phải từ chối

- Đọc/ghi private option/meta/CPT/table/class của plugin khác như integration contract.

- Suy luận authoritative identity/routing bằng slug, title, Page ID, URL hoặc heuristic.

- Tạo parallel store cho identity, product, Journey, commerce, claim/evidence hoặc strategy.

- Giữ release core Theme chỉ vì provider tùy chọn chưa sửa lỗi thuộc domain của họ.

- Đánh đổi accessibility, rollback, provenance hoặc source ownership để “cho chạy được”.

- Mở rộng scope v1.0 bằng feature mới không cần thiết cho lời hứa core Theme.

# Điều 12. Cơ chế sửa đổi Hiến pháp

- PATCH: làm rõ câu chữ nhưng không đổi bất biến, release constitution hoặc ownership.

- MINOR: thêm nguyên tắc mới tương thích với các điều hiện hành; phải nêu source impact.

- MAJOR: đổi bản chất sản phẩm, core release gate, product independence hoặc ownership; bắt buộc product-owner approval và cập nhật AZT-00/01/02/04 bị ảnh hưởng trước code.

| **TUYÊN NGÔN: Theme phải đủ tốt để đứng một mình. Ecosystem integration là sức mạnh cộng thêm, không phải chiếc nạng định nghĩa sản phẩm.** |
| --- |
