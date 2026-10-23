# KIỂM KÊ PILOT VÀ KHOẢNG TRỐNG DỮ LIỆU

**Mã:** RQA-PILOT-AUDIT-02 · **Phiên bản:** 1.0 · **Ngày đọc:** 22/09/2026  
**Website:** `https://remquocanh.vn/`  
**Chế độ:** Chỉ đọc. Không sửa nội dung, taxonomy, theme settings, plugin, repository hay trạng thái xuất bản.  
**Mức chứng cứ:** Dữ liệu được connector trả về; chưa nghiệm thu giao diện trình duyệt, accessibility hoặc chuyển đổi.

## 1. Tóm tắt cần dùng ngay

Trong lượt đọc này, WordPress xác nhận Theme đang active là **AZnet Theme 1.3.30** và homepage đang dùng preset **curtain-01**. Đây không còn là tình trạng chỉ có kế hoạch chuyển từ Flatsome. Tuy nhiên, không được suy từ đó rằng mọi tiêu chí pilot hoặc mọi trang đã hoàn tất.

Các điểm tác động trực tiếp tới việc làm pilot tốt hơn:

- 10 sản phẩm xuất bản được endpoint trả về đều có `featured=false`.
- Sản phẩm **70 — Rèm vải hoa văn** đang trả về hai danh mục **34 — Rèm Cuốn Zipscreen** và **29 — Rèm Roman**; cần đối chiếu sản phẩm cụ thể trước khi dùng làm đại diện cho dòng rèm.
- Ba sản phẩm thuộc **16 — Rèm vải**, nhưng cả hai lệnh liệt kê product categories trong lượt này đều không trả về term 16. Chưa xác định nguyên nhân; không kết luận term đã bị xóa.
- Có nhiều danh mục count 0; ba danh mục có `image=null` trong phản hồi categories.
- Một số tên file ảnh không khớp tên dòng rèm được gắn. Đây là dấu hiệu kiểm tra metadata, chưa phải kết luận thị giác ảnh sai.
- Hero block 991 đã publish và được settings trỏ tới; không coi chữ “Preview” trong tên block là bằng chứng block vẫn draft.

## 2. Nền tảng được xác minh qua site_info

| Trường | Giá trị trả về |
|---|---|
| Site name | Rèm Quốc Anh |
| WordPress | 7.1.1 |
| PHP | 8.1.34 |
| WPVibe plugin | 1.17.3 |
| Active theme | AZnet Theme |
| Stylesheet | aznet-theme |
| Theme version | 1.3.30 |
| Thời điểm connection verification | 2026-09-22T09:09:27.591Z, tương ứng 16:09:27.591 giờ UTC+7 |
| Phạm vi verification của connector | Authenticated read only; publishing and uploads not tested |

Danh sách theme trả về còn có `aznet-theme-wpvibe-backup`, `aznet-theme-wpvibe-draft`, `flatsome-child`, `flatsome`. Sự tồn tại của thư mục/theme không tự chứng minh có một rollback đã được thử thành công.

Các plugin liên quan xuất hiện trong danh sách site_info: WooCommerce; `choiceguide` và `choiceguide-pro`; RootProfile; Nagavia Connector for WordPress; Contact Form 7; Rank Math và Rank Math Pro; Woo Variation Swatches; AZnet DevBridge Agent; WPVibe; All-in-One WP Migration. Phản hồi này không kèm cột trạng thái/version riêng của từng plugin, nên tài liệu không khẳng định toàn bộ đều active. Không thấy một entry Website Intelligence độc lập trong danh sách này; không suy diễn trạng thái toàn hệ sinh thái từ việc đó.

## 3. Mapping Theme đang trả về

Nguồn: `wp option get theme_mods_aznet-theme --format=json`.

```json
{
  "schema_version": 3,
  "visual_preset": "curtain-01",
  "homepage_preset": "curtain-01",
  "homepage_hero_variant": "split",
  "homepage_hero_block": 991,
  "homepage_hero_page": 0,
  "homepage_about_page": 35,
  "homepage_knowledge_terms": [57],
  "homepage_contact_page": 45,
  "homepage_services_page": 0,
  "homepage_team_page": 0,
  "homepage_process_page": 0,
  "homepage_faq_page": 0,
  "header_preset": "standard",
  "header_sticky": "sticky",
  "footer_preset": "standard",
  "woo_catalog_preset": "grid"
}
```

Giá trị 0 trong những trường nêu trên là tình trạng chưa map ở chính trường đó; không đủ để kết luận một nội dung không tồn tại ở bất kỳ nơi nào khác. Không suy toàn bộ hình thức Hero từ nhãn `split` khi chưa kiểm tra renderer và CSS.

Menu primary đang map ID 17. Những ID này chỉ có ý nghĩa trên site đã đọc, không được đưa thành mặc định của preset dùng chung.

### Front Page #2

`GET /wp/v2/pages/2?context=edit` trả về:

- ID 2; tên Rèm Quốc Anh; status publish.
- Link `https://remquocanh.vn/`; slug `sample-page`.
- `content.raw` và `content.rendered` đều là chuỗi rỗng; template rỗng.
- Modified: `2026-09-22T11:19:16` theo trường API.

**Không kết luận trang chủ trống từ post_content rỗng.** Preset có thể dựng các section bằng mapping; nguồn Hero/About/Knowledge/Contact đã được quan sát trong settings. Muốn kết luận mất nội dung phải kiểm tra runtime/frontend.

### Hero block #991

Status publish; title: `Hero Rèm 01 — Preview 3 ảnh Crossfade`; modified `2026-09-22T11:57:37` theo trường API. Raw content gồm group và ba core cover block, mỗi cover minHeight 620px, dimRatio 0.

| Thứ tự trong raw block | Media ID | Đường dẫn nguồn |
|---|---:|---|
| 1 | 669 | `/wp-content/uploads/2026/08/rem-quoc-anh-03.png` |
| 2 | 670 | `/wp-content/uploads/2026/08/rem-quoc-anh-02.png` |
| 3 | 671 | `/wp-content/uploads/2026/08/rem-quoc-anh-01.png` |

Raw block chứa eyebrow “HƠN HAI THẬP KỶ GÌN GIỮ NGHỀ RÈM”, H1 “Từ nghề truyền thống đến giải pháp không gian”, mô tả “Rèm được lựa chọn theo ánh sáng, công năng và thẩm mỹ của từng không gian.” Các phần chữ có class `screen-reader-text`; chưa đối chiếu CSS/renderer để kết luận hiển thị thị giác hoặc heading thực tế trên frontend.

Không lấy minHeight, alt rỗng hoặc class riêng lẻ để tuyên bố accessibility PASS/FAIL. Cần kiểm tra bối cảnh: ảnh trang trí hay nội dung, cách Theme dựng Hero và cây heading cuối cùng.

## 4. Sản phẩm được endpoint xuất bản trả về

Nguồn: `GET /wc/v3/products?per_page=100&status=publish`, trường chọn lọc `id,name,slug,permalink,featured,catalog_visibility,price,stock_status,categories`.

| ID | Sản phẩm | Danh mục API trả về | Giá raw | Featured |
|---:|---|---|---|---|
| 611 | Rèm vải đơn sắc | 16: Rèm vải | `""` | false |
| 610 | Rèm Vải 2 Lớp | 16: Rèm vải | `""` | false |
| 608 | Rèm voan | 16: Rèm vải | `""` | false |
| 607 | Rèm cuốn chống cháy | 33: Rèm Cuốn | `""` | false |
| 606 | Rèm cuốn xuyên sáng | 33: Rèm Cuốn | `""` | false |
| 605 | Rèm cuốn cản sáng | 33: Rèm Cuốn | `""` | false |
| 604 | Rèm cầu vồng chống cháy | 27: Rèm Cầu Vồng | `""` | false |
| 603 | Rèm cầu vồng xuyên sáng | 27: Rèm Cầu Vồng | `""` | false |
| 522 | Rèm cầu vồng cản sáng | 27: Rèm Cầu Vồng | `990000` | false |
| 70 | Rèm vải hoa văn | 34: Rèm Cuốn Zipscreen, 29: Rèm Roman | `0` | false |

Cả 10 bản ghi có `catalog_visibility=visible`, `stock_status=instock`. Đây là trạng thái trong WooCommerce, không xác nhận hàng sẵn vật lý hay thời gian giao của từng cấu hình may đo.

Trong 10 bản ghi, tám giá là chuỗi rỗng, một giá là `990000`, một giá là `0`. Chưa đọc currency, đơn vị tính, variation pricing hoặc quy tắc ConvertFlow của từng sản phẩm; không diễn giải snapshot thành bảng giá bán hợp lệ. Giá rỗng không phải giá 0, giá 0 không phải bằng chứng hàng miễn phí, và không tự gắn báo giá/mua hàng chỉ từ những trường này.

### Hệ quả đối với khối nổi bật

Nếu renderer chỉ query Featured, tập dữ liệu này không cung cấp sản phẩm nào. Cần xác minh cơ chế chọn hiện hành rồi thống nhất tập sản phẩm đại diện; không tự bật Featured hàng loạt, không hard-code bản sao tên/ảnh/giá vào Theme, không gọi một tập chọn tay là “bán chạy” khi không có số liệu.

### Dấu hiệu phân loại cần rà

Sản phẩm 70 có tên Rèm vải hoa văn nhưng đang được gắn Zipscreen và Roman. Một rèm vải có thể xuất hiện ở nhiều ngữ cảnh, nên cần xem sản phẩm và cấu hình thực trước khi kết luận sai; tuy nhiên chưa nên dùng mapping này như chuẩn phân loại cho một preset ngành rèm.

## 5. Danh mục và ảnh đại diện

Hai lệnh đọc được dùng: `GET /wc/v3/products/categories?per_page=100&hide_empty=false` và `term list product_cat --fields=term_id,name,slug,parent,count --format=json`. Cả hai trả về cùng tập 16 danh mục dưới đây. Trường Media ID lấy từ phản hồi REST categories.

| Term ID | Danh mục được trả về | Parent ID | Count API | Media ID |
|---:|---|---:|---:|---|
| 27 | Rèm Cầu Vồng | 0 | 3 | 16 |
| 59 | Rèm chuyên dụng | 0 | 0 | 865 |
| 33 | Rèm Cuốn | 0 | 3 | 18 |
| 34 | Rèm Cuốn Zipscreen | 18 | 1 | null |
| 60 | Rèm hội trường | 59 | 0 | 861 |
| 58 | Rèm lá dọc | 0 | 0 | 684 |
| 62 | Rèm ngăn lạnh | 59 | 0 | 862 |
| 63 | Rèm nhà tắm | 59 | 0 | 863 |
| 29 | Rèm Roman | 0 | 1 | 20 |
| 18 | Rèm sáo | 0 | 1 | 84 |
| 28 | Rèm Sáo Gỗ | 18 | 0 | 19 |
| 32 | Rèm Sáo Nhôm | 18 | 0 | null |
| 31 | Rèm Tổ Ong | 0 | 0 | 73 |
| 30 | Rèm Triple | 18 | 0 | null |
| 64 | Rèm trong kính | 59 | 0 | 864 |
| 61 | Rèm y tế | 59 | 0 | 622 |

Không cộng `count` của các term để suy số sản phẩm duy nhất: taxonomy có quan hệ cha–con, một sản phẩm có thể ở nhiều danh mục, và phản hồi có điểm chưa nhất quán.

### Điểm chưa nhất quán của term 16

Product 611, 610 và 608 tham chiếu category 16 — Rèm vải, nhưng lệnh list không trả về category này. Chưa đọc trực tiếp `GET /wc/v3/products/categories/16`, chưa kiểm tra điều kiện loại trừ/default category/filter. Vì vậy trạng thái là **CẦN ĐỐI CHIẾU**, không phải “Rèm vải đã bị xóa”. Không tự tạo category thay thế và không sửa danh mục liên quan khi chưa xác định nguyên nhân.

### Cổng dữ liệu cho section dòng rèm

Danh mục có `count > 0` chưa chắc đủ chất lượng hiển thị. Cần cả tên, phân loại đúng, ảnh đúng dòng rèm, nguồn ảnh hợp lệ, đích đến có giá trị và cơ chế mapping rõ ràng. Các danh mục chưa có sản phẩm có thể là nội dung dự kiến; không nên được trình bày như catalogue đã hoàn chỉnh chỉ để đủ số ô.

### Metadata ảnh cần kiểm tra

| Danh mục | Media ID / tên asset trả về | Lý do cần rà |
|---|---|---|
| Rèm lá dọc, 58 | 684, `rem_vai_hai_lop_villa_nghi_duong.png` | Tên asset mô tả rèm vải hai lớp, không trùng dòng lá dọc |
| Rèm sáo, 18 | 84, `Rem-vai-hoa-van-kate-1.png` | Tên asset mô tả rèm vải hoa văn |
| Rèm tổ ong, 31 | 73, `Rem-vai-hoa-van-1.png` | Tên và alt trả về mô tả rèm vải hoa văn |
| Zipscreen, 34 | null | Chưa có ảnh category trong phản hồi |
| Sáo nhôm, 32 | null | Chưa có ảnh category trong phản hồi |
| Triple, 30 | null | Chưa có ảnh category trong phản hồi |

Bảng trên là so khớp metadata, chưa có kiểm tra pixel của các ảnh. Không tự xóa ảnh, không khẳng định đó là ảnh giả. Trong phản hồi sản phẩm có ảnh, media 677 còn xuất hiện hai lần trong danh sách images của Product 610; cần kiểm tra gallery thực trước khi sửa dữ liệu hoặc UI.

## 6. Xung đột giữa nguồn cũ, quyết định sản phẩm và quan sát hiện tại

| Vấn đề | Các lớp nguồn | Cách xử lý an toàn |
|---|---|---|
| Nền tảng | Hồ sơ v3.8 còn Flatsome/UX Builder; D-034 loại runtime dependency; live AZnet Theme 1.3.30 | Kế thừa nghiệp vụ/thẩm mỹ, không khôi phục cách dựng cũ |
| Hotline | 0786 299 698 và 0901 699 989 | Giữ trạng thái xung đột; đọc/xác nhận nguồn liên hệ authoritative |
| Font | Hồ sơ Quốc Anh yêu cầu Montserrat; Theme có lịch sử font chung khác | Xác minh quyết định riêng và computed style; không áp font chung thành quyết định thương hiệu |
| Thứ tự homepage | Hồ sơ có hành trình 11 chương; D-034 mô tả 9 vai trò | Dùng để gap analysis, không tự ghi đè thiết kế đã duyệt |
| Số dòng rèm | Nguồn nghiệp vụ 14/15/16 tên; list live 16 term nhưng thiếu 16 trong khi products tham chiếu | Tách ontology mong muốn với dữ liệu live; chưa chốt taxonomy tự động |
| Công trình/khách hàng | Danh sách sơ bộ có tên dự án lớn | Không chuyển thành logo strip hoặc testimonial đã xác thực |
| Trạng thái block | Tên chứa “Preview”, status publish, settings đang trỏ vào | Trạng thái kỹ thuật dựa status/mapping, không dựa tên |
| Trạng thái nghiệm thu | Có các báo cáo lịch sử; lượt này chỉ đọc API | Không nâng thành browser/visual/a11y PASS |

## 7. Khoảng trống ưu tiên và nhiệm vụ đúng owner

| Ưu tiên | Việc cần làm | Nơi xử lý | Bằng chứng kết thúc |
|---|---|---|---|
| P0 | Xác định nguồn liên hệ chuẩn, xử lý hotline xung đột | Chủ website / identity owner | Một cấu hình được xác nhận, các bề mặt đồng nhất |
| P0 | Đối chiếu category 16 và mapping Product 70 | WooCommerce / catalogue owner | Taxonomy và sản phẩm được xác minh, không tạo trùng |
| P1 | Chọn dòng rèm đại diện thực sự đủ dữ liệu | WooCommerce + explicit Theme mapping | Mỗi mục có ảnh đúng, sản phẩm/đích đến hữu ích |
| P1 | Chốt tập sản phẩm đại diện và query | WooCommerce + Theme presentation | Không có khối rỗng/lặp, không nhãn “bán chạy” giả |
| P1 | Kiểm tra ảnh và quyền sử dụng | Media/editorial owner | Phân loại ảnh thật/minh họa rõ, crop/alt theo ngữ cảnh |
| P1 | Bổ sung quy trình và bằng chứng công trình | WordPress content; RootProfile/ConvertFlow khi đúng miền | Nội dung có nguồn, mappings đúng, không cần Theme lưu lại nghiệp vụ |
| P2 | Đối chiếu typography, màu, nhịp bố cục riêng Quốc Anh | AZnet Theme | Candidate đúng nguồn thiết kế, không tác động các preset khác |
| P2 | Kiểm tra cả ba chuyên mục và liên kết nội dung | WordPress / biên tập | Không bài mồ côi, có đường dẫn hợp lý tới catalogue/công trình |
| Gate | Kiểm tra browser và tích hợp trước xuất bản thay đổi tiếp | QA / deployment owner | Bằng chứng đúng viewport, đúng build, quyền xuất bản và rollback |

Đây là đề xuất thứ tự dựa trên snapshot, không phải implementation plan đã được duyệt. Với yêu cầu hiện tại, bước tiếp theo hợp lý là tiếp nhận nguồn và xử lý lát cắt dữ liệu của section dòng rèm, không tự triển khai lại toàn trang.

## 8. Phạm vi kiểm tra chưa thực hiện

Chưa kiểm tra trực quan desktop/tablet/mobile, contrast, keyboard, focus, overflow, heading sau render, CLS/LCP, form gửi thành công, luồng báo giá, email, checkout, tính đúng của public-contract integration, schema hay redirect toàn site. Truy xuất công khai trang chủ bằng web tool trong lượt này gặp lỗi truy cập; không có căn cứ xác định nguyên nhân là WAF, hosting, theme hoặc site đang hỏng.

Spec D-034 yêu cầu ma trận trình duyệt tối thiểu 1440/1024/390 và 320 khi có rủi ro; các chỉ tiêu này là tiêu chí cần kiểm tra, không phải kết quả đã đạt ở đây.

Chưa xác minh lại trạng thái candidate “section dòng rèm” được nhắc ở cuộc trò chuyện trước; chưa truy xuất package/hash hoặc quyết định publish cụ thể của candidate đó. Không thay thế hay đưa lên live bất cứ candidate nào từ gói dữ liệu này.

## 9. Nguồn tái kiểm tra

Những lệnh dưới đây là định danh nguồn đọc, không phải script yêu cầu chạy hàng loạt:

- `WPVibe.site_info(site_url="https://remquocanh.vn")`.
- `GET /wp/v2/pages/2?context=edit`.
- `GET /wp/v2/blocks/991?context=edit`.
- `GET /wc/v3/products?per_page=100&status=publish` với fields chọn lọc.
- `GET /wc/v3/products/categories?per_page=100&hide_empty=false`.
- `wp option get theme_mods_aznet-theme --format=json`.
- `wp term list product_cat --fields=term_id,name,slug,parent,count --format=json`.
- Hồ sơ `14_remquocanh.vn_v3.8(1).txt`, đặc biệt các mục 7, 9, 10, 13, 15.2.
- GitHub spec `docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md`.

Mọi số liệu trong tài liệu là snapshot ngày 22/09/2026. Khi tiếp tục sửa hoặc triển khai, phải đọc lại đúng dữ liệu đang live; không dùng snapshot để ghi đè thay đổi mới của người dùng.