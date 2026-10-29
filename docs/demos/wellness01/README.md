# WELLNESS 01 — Design Packet và native WordPress pattern

**Trạng thái:** DESIGN CANDIDATE — chưa phê duyệt production, chưa merge/release.  
**Dự án:** AZnet Theme — `truongdinhnamaz/aznet-theme`.  
**Mẫu tham chiếu do người dùng cung cấp:** https://vananhyoga.com/ — website hiện không truy xuất được trong lần kiểm tra 29/10/2026; không khẳng định đã đối chiếu giao diện hoặc sao chép bố cục trang này.

## 1. Bài toán và định vị

Tạo một mẫu website **đa lĩnh vực cho cơ sở tổ chức lớp học, phiên hướng dẫn, hoạt động vận động hoặc chăm sóc thân–tâm**, thay vì chỉ dành cho Yoga. Tên mẫu nội bộ: **Wellness 01**. Khả năng tái sử dụng dựa vào hành trình khách hàng: hiểu hình thức tập/dịch vụ → chọn chương trình → xem lịch/hình thức tham gia → xác minh người hướng dẫn → liên hệ/đăng ký qua owner phù hợp.

**Ba biến thể nội dung tham khảo:** (A) Yoga/Pilates/Barre; (B) Fitness/Aerobic/Dance/Mobility; (C) Thiền/Hơi thở/Thư giãn/Chuyển động chậm. Có thể dùng cho hướng dẫn cá nhân hoặc workshop. **Không mặc định** áp dụng nguyên vẹn cho phòng khám/trị liệu y khoa, spa có yêu cầu chuyên biệt, marketplace hay LMS.

## 2. Cấu trúc frontend đề xuất

1. Header điều hướng và một CTA chính đến nguồn đăng ký hoặc liên hệ thực.
2. Hero: giá trị, nhóm đối tượng, ảnh thật từ Media Library, CTA xem chương trình.
3. Chương trình/dịch vụ: card giới thiệu nội dung, cấp độ, thời lượng/điều kiện khi có.
4. Điểm khác biệt có thể chứng minh; không chèn số liệu giả.
5. Giới thiệu studio/chuyên gia và không gian thực tế.
6. Lịch và đăng ký: chỉ render thông tin từ lịch WordPress hợp lệ hoặc booking provider; không tự quyết định khả dụng, số chỗ hay trạng thái.
7. Hình thức tham gia: buổi thử, lớp nhóm, hướng dẫn cá nhân; chỉ công bố giá/gói khi nguồn thật có.
8. FAQ; bài viết native WordPress; CTA cuối; footer/contact.

**Giao diện:** editorial ấm (linen / sage / clay), khoảng trắng rộng, typography giàu tương phản; desktop/tablet/mobile; CTA rõ ràng; keyboard focus và reduced-motion. Bảng màu và tên bộ môn là biến thể của demo, không phải domain truth. Tránh ép mọi ngành vào icon hoa sen hoặc tư thế Yoga.

## 3. Ranh giới ownership / integration

| Dữ liệu và hành vi | Chủ sở hữu | Vai trò của Theme |
| --- | --- | --- |
| Trang, bài viết, ảnh, menu, nội dung biên tập | WordPress native | Pattern và bố cục |
| Thương hiệu, người hướng dẫn, claim/chứng chỉ được xác minh | RootProfile khi có contract phù hợp; nếu không có, nội dung WordPress theo chính sách biên tập | Chỉ hiển thị public-safe projection/nội dung native; không suy đoán identity |
| Dịch vụ/sản phẩm, giá/gói bán qua WooCommerce | WooCommerce | Trình bày public Woo output; không lưu giá hoặc transaction |
| Booking/lịch chỗ, khả dụng, huỷ lịch, thành viên | Provider đặt lịch do cơ sở chọn | Chỉ nhúng widget/shortcode/public API tương thích; khi vắng provider dùng contact/native fallback |
| Journey chuyển đổi nếu có ConvertFlow | ConvertFlow | Hiển thị public integration; không copy resolver/conversion |
| Website preview bản đơn lẻ | Nội dung fixture minh họa | Không phải website/booking hệ thống thật |

Theme **không** làm appointment engine, CRM, membership ledger, LMS, hồ sơ chuyên môn hay mô-đun thanh toán mới. Không đọc private option/meta/table của plugin. Mẫu phải sống được trên WordPress sạch.

## 4. Deliverables ứng viên

- `patterns/homepage-wellness-01.php`: Gutenberg pattern sử dụng **Core blocks**, nội dung thay được ngay trong editor, không cần plugin.
- `tests/wellness01-native-pattern-contract.php`: fail-first CLI contract; kiểm định slug/category, heading, block family, no forbidden storage/domain behavior và CTA anchors.
- `docs/demos/wellness01/README.md`: packet này, tách khỏi authoritative source docs vì không thay kiến trúc, ownership hay roadmap.
- `Wellness_01_website_mau.html`: preview HTML độc lập, ba chế độ nội dung và lịch giả lập rõ ràng; **không** phải production Theme HTML, không đặt lịch/thu tiền.

## 5. QA / gates / rollback

**Đã kiểm tra cục bộ:** HTML fixture 3 biến thể / 8 section / anchor-id uniqueness; JS syntax; kiểm tra contrast chủ đạo (ink/cream, sage/cream, clay/cream); PHP lint; CLI native pattern RED (file chưa tồn tại) → GREEN; Gutenberg block attribute JSON + nesting 184 markers hợp lệ. Đây **không** phải L3 WordPress runtime, L4 browser/a11y hay L5 provider integration PASS. Local Chromium fixture QA sau sửa lỗi badge tablet: **10 viewport (320–1440 px) × 3 biến thể nội dung PASS**, không tràn ngang/JS exception/anchor sai; lịch mẫu, CTA bất hoạt và mobile menu/keyboard focus được kiểm tra. Screenshot fixture tại 1440/1024/390. Đây chỉ là **bằng chứng browser của bản HTML demo**, không phải WordPress L4/a11y audit đầy đủ. Native Gutenberg pattern đã thêm regression cho trường hợp chú thích HTML bị tạo thành block freeform ngoài root: RED trước fix, GREEN 13/13 local sau fix.

**Exit gate trước merge:** WordPress 6.9+ trên support floor; native pattern insert/editor save/re-open; không có invalid blocks; không có lỗi PHP; responsive 1440/1024/390; keyboard/focus/reduced-motion/contrast; no-global-assets; absent/present booking provider fail-soft; full regression; explicit owner review/approval cho merge và mọi production release/deploy.

**Rollback:** Hủy draft PR/revert đúng các file candidate, không ảnh hưởng source/domain/content trên website nào. Không chạm main hoặc production trước approval.

**Exact next sau local static:** QA ở WordPress thật với pattern được chèn vào Page nháp, chụp desktop/tablet/mobile và sửa lỗi phát hiện từ tầng nông nhất. Sau đó mở owner review; không tự merge.

## 6. Checkpoint 29/10/2026 — GitHub PR #544

- Canonical base tại lúc mở PR: `main@9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`; feature branch `work/wellness01-native-pattern-20261029`. Bản mẫu không thay domain/booking provider hoặc site production.
- L1/L2 local: PHP lint PASS; native block grammar, unique anchors, pure-Core blocks, no freeform prefix, domain/private-read guard PASS (13 checks; lỗi prefix có RED trước khi sửa).
- Standalone preview L4 **fixture-only PASS**: 10 widths × 3 variants, no overflow, no pageerror, CTA demo never submits. Initial tablet overflow (orbit badge: 9 px) reproduced RED and fixed GREEN with scoped media query; SHA-256 preview `843ccbbbdccf72bdf243f72026754e9a058aa9bec882ecbd7e012f469abaa3fa`.
- **GitHub Actions BLOCKED/UNKNOWN:** 12/12 workflows on the candidate report failure *without executed job steps/logs*. Đây không phải code regression PASS/FAIL. Nguyên nhân runner/account/billing chưa xác minh. Không rerun vô căn cứ khi job chưa thực thi được.
- **WordPress L3, native Gutenberg editor save/reopen, WP L4/axe, provider L5, L6 package/merge đều UNKNOWN.** Không claim production readiness. Có kịch bản WP 6.9 + PHP 8.1 + browser/axe dự thảo bên ngoài canonical branch, nhưng chưa chạy thực trong WP; không coi test script là bằng chứng PASS.
- Rollback: revert các file chỉ có trong nhánh hoặc đóng draft PR. Main/production vẫn nguyên.
- **EXACT NEXT:** khôi phục khả năng chạy GitHub Actions runner của repo; trên đúng PR head chạy WordPress-clean L3, Gutenberg draft save/reopen và desktop/tablet/mobile L4/a11y; sau đó mới quyết định review/merge.
