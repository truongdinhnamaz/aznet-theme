# HỒ SƠ DỮ LIỆU RÈM QUỐC ANH — ĐẦU VÀO CHO PILOT AZNET THEME

**Mã tài liệu:** RQA-PILOT-DATA-01  
**Phiên bản:** 1.0  
**Ngày tổng hợp:** 22/09/2026  
**Phạm vi:** Dữ liệu thương hiệu, yêu cầu trải nghiệm, nguồn nội dung và ranh giới tích hợp của pilot Rèm Quốc Anh.  
**Trạng thái:** BẢN HỢP NHẤT ĐỂ TIẾP NHẬN — không phải quyết định thay thiết kế, lệnh triển khai hay chứng nhận nghiệm thu.  
**Website đã kiểm kê:** `https://remquocanh.vn/`  
**Repository sản phẩm:** `truongdinhnamaz/aznet-theme`  
**Project do người dùng cung cấp để tham chiếu:** `https://chatgpt.com/g/g-p-6a6b127e45d88191952b515fe9301db1/project`.

## 0. Cách dùng và giới hạn của bản hợp nhất

Tài liệu này kết nối dữ liệu Rèm Quốc Anh đã tìm được với pilot Rèm 01 hiện tại. Nội dung được truy xuất qua các tài liệu liên quan trong nguồn tệp/Library, lịch sử trao đổi, GitHub và các lệnh đọc WordPress. Không có căn cứ khẳng định đã đọc toàn bộ Project tại đường dẫn trên. Không có thao tác sửa website, repository hoặc tự gắn tệp vào Project trong lượt tổng hợp.

Đọc tài liệu này cùng `02_Kiem_ke_live_va_khoang_trong_v1.0.md`. Tài liệu 01 giải thích thương hiệu và yêu cầu; tài liệu 02 ghi nhận trạng thái kỹ thuật tại thời điểm đọc. Snapshot JSON là bản chép chọn lọc dữ liệu đã quan sát, không phải bản sao database và không được tự động import lên production.

### Phân biệt bốn loại thông tin

- **NGUỒN NGHIỆP VỤ:** Nội dung trong hồ sơ website v3.8 và các tài liệu khách hàng. Dùng làm đầu vào biên tập, không coi mọi tuyên bố là đã có chứng cứ độc lập.
- **QUYẾT ĐỊNH ĐÃ GHI NHẬN:** Yêu cầu đã khóa trong hồ sơ hoặc quyết định thiết kế D-034. Khi khác phạm vi hoặc khác thời điểm, phải đối chiếu chứ không tự hòa trộn.
- **QUAN SÁT LIVE 22/09/2026:** Giá trị do WordPress/API trả về trong lượt này. Chỉ xác nhận trường dữ liệu, không tự chứng minh giao diện, chất lượng hay độ đúng của thông tin kinh doanh.
- **ĐỀ XUẤT/CẦN XÁC MINH:** Chưa được biến thành thiết kế đã duyệt, thành tích, bảo đảm kỹ thuật hoặc thông tin công khai.

### Quyền chủ quản phải giữ

**Source owns data. Theme owns presentation. Integration contracts connect them.**

| Miền dữ liệu | Nguồn sở hữu | Theme được làm gì |
|---|---|---|
| Trang, bài, menu, media, trạng thái xuất bản, Hero native | WordPress | Hiển thị và ánh xạ bằng tham chiếu rõ ràng |
| Sản phẩm, biến thể, giá, tồn kho, danh mục, ảnh sản phẩm | WooCommerce | Trình bày qua giao diện/public API hợp lệ |
| Hành trình sản phẩm, độ phù hợp, lọc, báo giá/mua hàng | ConvertFlow khi có | Tiêu thụ public contract, không tái tạo nghiệp vụ |
| Danh tính Person/Organization, liên hệ, mạng xã hội | RootProfile khi có public contract phù hợp | Hiển thị payload công khai; không đọc kho nội bộ riêng |
| Preset, màu, bố cục, typography, responsive, shell | AZnet Theme | Sở hữu lớp trình bày; không biến thành kho dữ liệu thứ hai |

Rèm Quốc Anh là site pilot; không được hard-code tên, hotline, ảnh, Product ID hay Term ID của Quốc Anh vào lõi preset dùng chung. Dữ liệu từng site ở WordPress/WooCommerce/provider và cấu hình ánh xạ phù hợp. Nguồn: S3.

## 1. Hồ sơ nhận diện và thông tin liên hệ

| Trường | Giá trị tìm được | Trạng thái sử dụng |
|---|---|---|
| Thương hiệu | Rèm Quốc Anh | Nhất quán trong hồ sơ và website được kiểm kê |
| Pháp nhân theo hồ sơ | Công ty TNHH Thiết kế Nội thất Quốc Anh | Đầu vào từ hồ sơ; chưa đối chiếu giấy đăng ký trong lượt này |
| Website chính | `https://remquocanh.vn/` | Đã kết nối đọc WordPress ngày 22/09/2026 |
| Tên miền triển khai cũ | `https://remquocanh.aznet.vn/` | Lịch sử/staging; không dùng như canonical mới |
| Showroom theo hồ sơ | 437A103 Đường Phú Lợi, Phường Phú Lợi, Thành phố Hồ Chí Minh | Giữ nguyên cách ghi nguồn; chuẩn hóa số nhà trước khi đưa vào schema |
| Hotline trong hồ sơ hiện hành tại 31/08 | 0786 299 698 | Xung đột với tài liệu chính sách; không mặc định đây là quyết định mới nhất tại 22/09 |
| Hotline trong hướng dẫn mua hàng | 0901 699 989 | Chưa có xác nhận giải quyết xung đột trong nguồn đã tìm |
| Tên Zalo OA | Rèm Quốc Anh - Remquocanh.vn | Đã tiếp nhận tên; URL/ID OA chưa được xác nhận |
| Mã số thuế, email chuẩn, URL mạng xã hội chuẩn | Chưa đủ dữ liệu được xác minh trong lượt này | Không tự điền hoặc suy từ tên thương hiệu |

**Quy tắc hotline:** Không tự chọn một trong hai số, không tìm-thay thế toàn website, không tự công bố số 0901 từ tài liệu mua hàng. Mọi khối liên hệ nên lấy từ một nguồn liên hệ tập trung được chủ quản xác nhận. Sự hiện diện của số điện thoại trên trang web hoặc kết quả tìm kiếm chỉ là quan sát, không tự giải quyết được xung đột nghiệp vụ. Nguồn: S1, mục 15.2D.

## 2. Định vị, khách hàng và vai trò kinh doanh

Định vị trong hồ sơ: **Rèm Quốc Anh — giải pháp rèm và kiểm soát ánh sáng cao cấp cho không gian sống và công trình chuyên nghiệp.** Website hướng tới phân khúc trung và cao cấp, không chỉ là bảng mã hàng hoặc nơi cạnh tranh bằng giá thấp. Nguồn: S1, mục 1–2.

Ba nhóm khách hàng để tổ chức nội dung:

1. **Khách hàng nhà ở:** chủ căn hộ, nhà phố, nhà vườn, villa, biệt thự; quan tâm thẩm mỹ, công năng, ánh sáng, riêng tư và ngân sách.
2. **Đối tác chuyên môn:** kiến trúc sư, nhà thiết kế, đơn vị thi công nội thất; cần vật liệu, mẫu, giải pháp lắp đặt và khả năng phối hợp.
3. **Khách hàng doanh nghiệp/dự án:** khách sạn, resort, văn phòng, nhà máy, bệnh viện, trường học, hội trường và chủ đầu tư; cần hồ sơ kỹ thuật, tiến độ, thi công, nghiệm thu và hậu mãi phù hợp.

Yêu cầu cốt lõi: khách chưa biết tên kỹ thuật của rèm vẫn tìm được giải pháp; khách chuyên môn vẫn có dữ liệu sâu để đánh giá. Hành trình không dừng ở một nút mua hàng: khám phá → hiểu và so sánh → xem bằng chứng → tư vấn/khảo sát → lựa chọn và báo giá.

WooCommerce là nền quản trị catalogue bắt buộc theo hồ sơ. Có WooCommerce không đồng nghĩa mọi sản phẩm phải bán trực tuyến, có giá cố định, có giỏ hàng hoặc thanh toán ngay. Cách báo giá/mua hàng phải theo nguồn nghiệp vụ và public contract của hệ thống liên quan.

## 3. Câu chuyện thương hiệu và nội dung lịch sử

Cốt truyện có thể khai thác: **từ một nếp nghề gia đình, qua hai thế hệ, đến năng lực cung cấp giải pháp không gian.** Nguồn: S1, mục 3, 9.4.

| Mốc theo hồ sơ | Nội dung | Điều kiện biên tập |
|---|---|---|
| 2000 | Khởi đầu từ cửa hàng may và thi công rèm gia đình tại Bình Dương | Hồ sơ chọn 2000 thay tài liệu cũ ghi 2002; giữ tư liệu chứng minh |
| 2008 | Truyền nghề cho người thân và đồng hương | Dùng như câu chuyện trong nguồn; cần tư liệu người thật khi dựng phóng sự/ảnh |
| 2011 | Thế hệ tiếp nối nhận công việc ở tuổi 18 | Không tự bổ sung tên, chân dung hay chi tiết gia đình |
| 2013 | Thành lập Công ty TNHH Thiết kế Nội thất Quốc Anh | Cần hồ sơ pháp nhân nếu công bố như dữ kiện pháp lý được kiểm chứng |
| 2015 | Hướng tới phân khúc trung và cao cấp | Có thể dùng để giải thích định hướng thương hiệu |
| 2019 | Nguồn nêu tham gia các hiệp hội/cộng đồng doanh nhân | Cần xác minh tư cách hội viên, tên tổ chức và bằng chứng |
| 2020 đến nay | Mở rộng loại hình không gian và công trình | Không tự chuyển thành số lượng, hạng sao hoặc danh sách khách hàng đã xác thực |

Triết lý trong nguồn: **“Hãy mang đến cho khách hàng nhiều giá trị hơn số tiền họ bỏ ra.”**

Năm giá trị được ghi nhận: uy tín; trung thực trong tư vấn; chỉn chu từng chi tiết; chất lượng tạo giá trị lâu dài; làm tốt hơn điều khách hàng mong đợi.

Không dùng các cụm “hàng nghìn công trình”, “khách sạn 4–5 sao”, “hàng đầu”, tỷ lệ hài lòng hoặc số năm cụ thể như bằng chứng thực tế khi chưa có hồ sơ hỗ trợ. Tầm nhìn trở thành thương hiệu uy tín không đồng nghĩa hiện tại đã đạt vị trí dẫn đầu.

## 4. Ngôn ngữ thiết kế riêng của Quốc Anh

Định hướng trong hồ sơ v3.8: **gallery nội thất cao cấp, sáng thoáng, hình ảnh lớn, tiết chế chữ, nhịp sáng–tối có chủ đích**. Concept: **“Nghệ thuật của ánh sáng và nếp vải.”** Nguồn: S1, mục 7, 9.

| Thành phần | Yêu cầu trong hồ sơ | Lưu ý khi đưa sang AZnet Theme |
|---|---|---|
| Nền tối | `#222222` | Không lấy lại navy từ phiên bản cũ |
| Nền sáng chính | `#EBE6E1` | Không phủ vàng/champagne đậm toàn site |
| Nền khối sáng | `#F7F4F0` | Là lớp nền bổ trợ, không bắt buộc mọi phần phải là card |
| Accent | Lấy từ logo; dùng tiết chế | Không tự tạo màu thương hiệu cụ thể khi chưa đối chiếu logo |
| Font | Montserrat, một family; weight tối đa 600 theo hồ sơ | Đây là yêu cầu riêng trong hồ sơ; chưa đo computed font trên live. Không suy từ font mặc định của Theme rằng đã có quyết định đổi font Quốc Anh |
| Bố cục | Ảnh lớn, editorial, bất đối xứng, gợi khung cửa và nếp vải | Không biến mọi section thành lưới card vuông đồng nhất |
| Container | Nền section có thể toàn chiều rộng; nội dung có lề và cùng trục Header | D-034 tiếp tục giữ outer full-bleed / inner constrained |
| Motion | Nhẹ, có mục đích, có reduced motion | Không khôi phục phụ thuộc slider Flatsome |

Mỗi section giữ một nhiệm vụ thuyết phục và một hành động ưu tiên. Rút gọn nội dung trước khi tăng padding. Mô tả khoảng 2–4 dòng là định hướng bố cục, không phải số dòng cứng cho mọi thiết bị.

Các nguồn tham chiếu thẩm mỹ trong hồ sơ: Acacia Fabrics về độ thoáng; Silent Gliss về hình ảnh kiến trúc sạch; Coulisse về chất liệu và tính nghệ thuật. Đây chỉ là định hướng trong nguồn, không phải cho phép sao chép hình ảnh hay nhận diện. Không dùng ảnh của đơn vị khác như công trình Quốc Anh.

**Không tự coi việc hợp nhất này là duyệt thay typography hoặc màu trên live.** Phải đối chiếu yêu cầu riêng với quyết định triển khai mới nhất trước khi thay presentation.

## 5. Hero và phần mở đầu phải bảo vệ

Hai thông điệp đã khóa trong hồ sơ:

> Hơn hai thập kỉ gìn giữ nghề rèm  
> Từ nghề truyền thống đến giải pháp không gian

Cấu trúc: một Hero, ba ảnh luân phiên, chữ và CTA cố định. Không tách thành ba banner quảng cáo với ba bộ nội dung. CTA khám phá trong hồ sơ là **“Khám phá các dòng rèm”**; **“Đặt lịch khảo sát”** ở Header hoặc điểm chuyển đổi phù hợp, không tranh vị trí ngang hàng một cách máy móc. Nguồn: S1, mục 7.3, 9.4.

Hình ảnh phục vụ cảm nhận không gian, ánh sáng, chất liệu và độ rủ. Hero phải giữ ảnh sáng, không phủ tối toàn màn hình theo thói quen. Chỉ xử lý tương phản cục bộ khi cần. Chữ không nhảy khi chuyển ảnh; ảnh đầu được ưu tiên hiệu suất; các ảnh sau không cùng được tải ưu tiên vô điều kiện.

Quan sát ngày 22/09: Theme đang ánh xạ Hero tới WordPress block **991**, block có ba media **669, 670, 671**, và chứa hai thông điệp trên với cách viết “KỶ” trong nội dung hiện tại. Tài liệu 02 ghi chi tiết. Không cần sửa chính tả để đồng nhất hai nguồn nếu việc đó không thuộc nhiệm vụ.

**Điểm chuyển nền tảng:** giữ mục tiêu thị giác và nội dung, nhưng bỏ yêu cầu kỹ thuật “phải dùng Slider/Banner UX Builder” của hồ sơ cũ. D-034 quy định nội dung Hero thuộc WordPress native; Theme sở hữu trình bày. Không tạo trình thông dịch shortcode Flatsome.

## 6. Bản đồ trang chủ: đối chiếu chiến lược, không tự thay thiết kế đã duyệt

Hồ sơ cũ ghi hành trình: Hero → bằng chứng nhanh → danh mục WooCommerce → sản phẩm nổi bật → câu chuyện hai thế hệ → chọn theo không gian → bộ sưu tập → công trình → quy trình → kiến thức → CTA. Đây là đầu vào chiến lược theo S1, không phải lệnh nhồi đủ 11 section vào bản pilot hiện tại.

Spec D-034 của Rèm 01 ghi chín vai trò: Hero; cửa vào bộ sưu tập/giải pháp; sản phẩm nổi bật; use case/không gian/ứng dụng; công trình; giới thiệu/nghề/kinh nghiệm; quy trình/cam kết; kiến thức; CTA liên hệ. Spec cũng yêu cầu thiếu nguồn thì ẩn hoặc suy giảm hợp lý, không tạo dữ liệu giả. Nguồn: S3.

| Vai trò | Dữ liệu nên lấy | Yêu cầu bảo toàn |
|---|---|---|
| Định vị đầu trang | Hero block đã chọn, media thật | Không ghi đè Hero đang dùng chỉ vì tài liệu cũ khác cách dựng |
| Khám phá dòng rèm | WooCommerce product categories được chọn tập trung | Tên, ảnh, slug, URL theo chủ dữ liệu; không nhập lại trên Page |
| Sản phẩm đại diện | Product thật, cơ chế chọn rõ ràng | Chưa bật Featured không được tự gắn nhãn “bán chạy” |
| Chọn theo tình huống | Page/nguồn WordPress hoặc public projection ConvertFlow | Không tự dựng Fit engine trong Theme |
| Năng lực thực tế | Bài công trình và ảnh có quyền sử dụng | Không lấy tên ứng viên thành khách hàng đã xác minh |
| Câu chuyện hai thế hệ | Page giới thiệu, tư liệu con người | Không thay bằng số liệu trang trí |
| Quy trình và hậu mãi | Nội dung chính sách, quy trình đã xác nhận | Không rút gọn làm mất điều kiện, ngoại lệ |
| Kiến thức | Posts và category mapping | Giữ đúng hệ ba chuyên mục đã chốt |
| Chuyển đổi | Trang liên hệ và bề mặt báo giá do owner cung cấp | Không nhân bản lead/quotation state trong Theme |

Chỉ đề xuất đổi thứ tự hoặc thêm section khi có lý do và dữ liệu thực. Không coi bản đồ trong tài liệu này là một quyết định D-034 mới. Không dùng dữ liệu Quốc Anh để sửa mẫu luật hoặc tất cả website khác.

## 7. Hệ sản phẩm: 15 nhóm nội dung không đồng nghĩa 15 danh mục đã khóa

Nguồn S1 ghi danh sách làm việc gồm: rèm vải; rèm cầu vồng; rèm sáo gỗ; rèm sáo nhôm; rèm tổ ong; rèm Roman; rèm cuốn; rèm ngang; rèm đứng/rèm lá dọc; rèm động cơ; rèm hội trường; rèm ngăn lạnh; rèm y tế; rèm nhà tắm; rèm trong kính.

Các điều kiện chưa được phép bỏ qua:

- Tài liệu khách hàng từng ghi 14 danh mục, liệt kê 16 tên và mô tả 15 nhóm. Không chốt số danh mục chính thức chỉ bằng cách đếm tên.
- “Rèm đứng” và “rèm lá dọc” có thể là một khái niệm; không tạo hai taxonomy trùng nghĩa.
- “Rèm động cơ” là khả năng vận hành/giải pháp xuyên nhiều dòng; phải xác định dùng như hub hay thuộc tính, không coi là một loại vải.
- “Rèm ngang” cần phân biệt với sáo gỗ/sáo nhôm và đối chiếu catalogue trước khi khóa.
- Nhóm chuyên dụng có thể gom hội trường, ngăn lạnh, y tế, nhà tắm, trong kính; chỉ mở Page/danh mục khi có nội dung thực đủ dùng.
- Rèm động cơ, rèm nhà tắm và rèm trong kính còn có phần chỉ là khung nội dung theo hồ sơ, không tự hoàn thiện bằng thông số tưởng tượng.

Riêng rèm vải, các nhóm biên tập đã được nêu gồm đơn sắc, hoa văn, linen, voan, chống nắng, chống cháy và hai lớp. Danh sách là nền khai thác nội dung, không phải lệnh tự tạo Product mới.

Trên pilot đã đọc được 10 sản phẩm xuất bản; chi tiết và những điểm chưa nhất quán ở tài liệu 02. Catalogue hiện có và cây nội dung mong muốn phải được phân biệt rõ.

## 8. Khung nội dung sản phẩm và bộ câu hỏi

Tài liệu điều phối S4 đăng ký workbook `BỘ CÂU HỎI.xlsx`; tệp tìm được có tên `BỘ CÂU HỎI(1).xlsx`. Sheet được quy định dùng là **“Tổng hợp bộ câu hỏi đã bổ sung”**, được tài liệu điều phối ghi gồm **244 câu hỏi**, không dùng sheet “100 câu” thay thế. Trong lượt này đã truy xuất thông tin đăng ký và một số đoạn nội dung; chưa xuất bản sao đầy đủ workbook hoặc kiểm đếm độc lập toàn bộ 244 dòng.

Khung 12 nhóm để kiểm tra một nội dung sản phẩm:

1. Loại rèm và hệ phụ kiện.
2. Vật liệu, mã vải, màu sắc và xuất xứ.
3. Kích thước, khổ vải và giới hạn kỹ thuật.
4. Tính năng và điều kiện đạt tính năng.
5. Động cơ, điều khiển và tương thích.
6. Mức độ phù hợp, không gian và trường hợp cần cân nhắc.
7. Khảo sát, đo đạc và lắp đặt.
8. Giá, đơn vị tính và yếu tố ảnh hưởng báo giá.
9. Bảo hành, chứng nhận và bằng chứng kỹ thuật.
10. Thời gian sản xuất/giao theo cấu hình.
11. Vận chuyển và điều kiện thi công.
12. Sửa đổi, bảo trì và hỗ trợ sau bán.

Bộ câu hỏi là công cụ tìm khoảng trống, không phải nguồn xác nhận câu trả lời. Không đưa 244 câu vào mỗi sản phẩm; không sao chép câu hỏi đại lý/công nợ/chiết khấu vào nội dung bán lẻ khi không liên quan. Không lặp cùng một ý ở tổng quan, bảng thông số và FAQ.

Khung một trang sản phẩm để đối chiếu với ConvertFlow: nhận diện và tóm tắt → điểm nổi bật → thông số có điều kiện → mức độ phù hợp và điểm cần cân nhắc → hình ảnh/video → công trình liên quan → cam kết/FAQ áp dụng → báo giá hoặc mua hàng đúng chế độ. Đây là mô tả nhu cầu nội dung, không cho phép Theme nhận quyền sở hữu dữ liệu ConvertFlow.

Các yêu cầu trải nghiệm được ghi nhận trong lịch sử dự án gồm: ba nhóm “Nhu cầu / Không gian / Ứng dụng”; không thêm lại “Loại công trình” như một nhóm Fit chỉ vì hồ sơ cũ có từ này; gallery có lightbox; một công trình là một thực thể, không nhân thành nhiều card chỉ vì có nhiều ảnh; cam kết/FAQ riêng sản phẩm ưu tiên khi cơ chế nguồn cho phép. Phải đối chiếu public contract và quyết định hiện hành của ConvertFlow trước khi triển khai lại.

## 9. Quy trình mua hàng, giao/lắp và bảo hành

Các nội dung sau là dữ liệu nghiệp vụ theo hồ sơ cập nhật ngày 31/08/2026, không phải chính sách mới được ban hành trong lượt này. Nguồn: S1, mục 15.2; S6.

**Quy trình:** tư vấn/lựa chọn → khảo sát, đo thực tế → xác nhận sản phẩm và báo giá → đặt cọc/xác nhận đơn → sản xuất → giao và lắp → nghiệm thu/bàn giao → bảo hành/bảo trì.

**Thanh toán:** nhà ở/cá nhân 50% đặt cọc, 50% sau lắp đặt và nghiệm thu; doanh nghiệp/dự án có tiến độ thông thường 30/30/30/10 theo các mốc trong thỏa thuận, nhưng hợp đồng cụ thể được ưu tiên; hàng có sẵn không cần sản xuất riêng thanh toán 100% theo hồ sơ. Không tạo một bảng thanh toán cứng áp dụng cho mọi trường hợp.

**Thay đổi/hủy:** nguồn nêu khả năng hỗ trợ trong 24 giờ sau xác nhận khi sản phẩm chưa đưa vào sản xuất. Khi đã cắt vải/gia công, chi phí phát sinh không thể được tự diễn giải là hoàn lại toàn bộ. Nội dung chính sách công khai phải giữ đầy đủ điều kiện thay vì rút thành một badge “đổi trả dễ dàng”.

**Giao/lắp:** thời gian và chi phí phụ thuộc sản phẩm, nguồn hàng, số lượng, kích thước, địa điểm và điều kiện công trình. Không tự hứa một số ngày cố định, miễn phí toàn quốc hoặc mọi trường hợp đều có đội lắp của Quốc Anh.

| Nhóm | Khung trong nguồn | Điều kiện bắt buộc giữ |
|---|---|---|
| Động cơ | 3–5 năm | Tùy thương hiệu/dòng; đổi 1–1 chỉ khi đủ điều kiện nhà sản xuất/nhà phân phối |
| Sản phẩm rèm | 12–24 tháng | Tùy dòng, chất liệu, chính sách; thời hạn cụ thể phải gắn hồ sơ sản phẩm |
| Phụ kiện hệ rèm vải | 5 năm | Lỗi kỹ thuật thuộc phạm vi bảo hành |
| Phụ kiện hệ rèm sáo | 2 năm | Lỗi kỹ thuật thuộc phạm vi bảo hành |
| Sau bảo hành | Tiếp nhận kiểm tra, bảo trì, sửa chữa | Chi phí nếu có phải trao đổi trước |

Không chuyển khung này thành “bảo hành tất cả 5 năm” hoặc “1 đổi 1 vô điều kiện”. Thông tin tài khoản ngân hàng không được đưa vào preset hoặc snapshot này; khi làm trang thanh toán phải đọc và xác minh tại nguồn nghiệp vụ riêng.

## 10. Công trình, hình ảnh và bằng chứng

Nguồn hồ sơ liệt kê các ứng viên cần xác minh, trong đó có Tứ Trà; Spa Lumiere Maison; biệt thự tại Thủ Dầu Một; nhà phố KDC Hiệp Thành 3; chung cư Define; Sonion tại VSIP 1. Một số tên dự án, khách sạn và khu công nghiệp khác cũng xuất hiện trong danh sách sơ bộ. **Đây không phải danh sách khách hàng đã được xác thực trong lượt này.** Nguồn: S1, mục 13.

Một công trình đủ điều kiện công bố cần có: tên được phép sử dụng; địa điểm; loại không gian; nhu cầu ban đầu; giải pháp Quốc Anh thực hiện; dòng rèm/sản phẩm; phạm vi công việc; ảnh có quyền sử dụng; thời gian/kết quả được phép công bố; liên kết nội dung liên quan. Không suy ra Quốc Anh thi công toàn bộ một khu đô thị/khách sạn từ việc có một hạng mục trong đó.

Năm nhóm tài sản hình ảnh nên quản lý: không gian hoàn thiện; cận chất liệu; ray/động cơ/phụ kiện; tư vấn–may–lắp–nghiệm thu; con người và lịch sử. Mỗi ảnh nên có trạng thái nguồn gốc, quyền sử dụng, chủ thể, dòng rèm, bối cảnh, alt và vị trí dự định hiển thị.

Ảnh minh họa hoặc ảnh định hướng không được trình bày thành công trình thật. Tên file đẹp, ảnh đã upload hay ảnh có Media ID không phải bằng chứng tính xác thực. Bản tổng hợp hiện tại chưa thực hiện kiểm tra thị giác toàn bộ thư viện media.

## 11. Kiến trúc nội dung và E-E-A-T

Chiến lược ngày 23/08/2026 chốt đúng ba chuyên mục: **Kiến thức về rèm — giúp khách hiểu; Tư vấn chọn rèm — giúp khách chọn; Công trình rèm — giúp khách tin.** Nguồn: S2.

Bài kiến thức phải giải thích được lựa chọn và giới hạn; bài tư vấn phải giúp quyết định trong một tình huống cụ thể; bài công trình phải có bằng chứng và phạm vi công việc. Không mở thêm hàng loạt chuyên mục để chứa từ khóa. Không lấy tăng số bài làm bước đầu tiên khi thông tin sản phẩm, tác giả, kiểm duyệt và chứng cứ còn yếu.

Một nội dung dùng cho pilot nên có người chịu trách nhiệm, nguồn kiểm chứng phù hợp, ngày cập nhật và liên kết đúng tới sản phẩm/công trình liên quan. Danh tính tác giả/tổ chức đi qua nguồn identity phù hợp; Theme không tự gắn chức danh chuyên gia hay kinh nghiệm cho một tài khoản WordPress.

Lượt đọc hiện tại ghi nhận homepage đang map Knowledge tới term **57**. Chỉ từ một mapping này không thể kết luận toàn website đã đủ hoặc đang thiếu ba chuyên mục; cần kiểm kê taxonomy bài viết riêng khi thực thi.

## 12. Những gì phải giữ và những gì không được nhập lại

**Giữ:** dữ liệu và kết quả đang chạy; URL thật; WordPress Page/Post/Media; catalogue WooCommerce; Hero nguồn 991 theo snapshot; nội dung hai thế hệ; bảng màu và yêu cầu thương hiệu trong hồ sơ để đối chiếu; ranh giới D-034; cơ chế rollback hiện hành khi có thay đổi được duyệt.

**Không nhập lại như chuẩn mới:** shortcode Flatsome/UX Builder; theme options Flatsome làm kho nội dung mới; hotline xung đột; ảnh chưa rõ nguồn gốc; danh sách khách hàng chưa chứng minh; thông số chống cháy/kháng khuẩn/UV/cản sáng không gắn catalogue; nhãn bán chạy do suy đoán; Product/Term/Media ID riêng Quốc Anh hard-code vào lõi.

**Không suy từ “đã xuất bản” thành “đúng”:** trạng thái publish chỉ nói về xuất bản. Giá, danh mục, ảnh và bằng chứng vẫn phải được kiểm tra. **Không suy từ “đọc API được” thành “giao diện PASS”.**

## 13. Thứ tự xử lý đề xuất cho pilot

Ưu tiên đầu tiên là làm sạch tham chiếu và dữ liệu đại diện: nhận diện liên hệ tập trung; xác minh danh mục Rèm vải và mapping sản phẩm; chọn các dòng rèm có ảnh và sản phẩm đúng; xác định tập sản phẩm đại diện. Sau đó mới bổ sung khối câu chuyện, công trình, quy trình và kiến thức từ nguồn có thật.

Đề xuất thiết kế phải giải thích: dùng dữ liệu nào, giúp khách quyết định gì, thuộc owner nào, thiếu nguồn thì xử lý ra sao, ảnh hưởng phần đã duyệt thế nào. Không phát sinh một đợt thiết kế lại toàn website chỉ từ nhiệm vụ tiếp nhận dữ liệu.

Khi có quyết định bền vững mới, cập nhật hồ sơ pilot và tài liệu nguồn đúng phạm vi; không lưu duy nhất trong câu trả lời chat. Chỉ viết implementation plan/code sau phạm vi được duyệt; chỉ xuất bản khi có quyền triển khai cụ thể.

## 14. Danh mục nguồn và khả năng truy vết

**S1 — Hồ sơ website:** `14_remquocanh.vn_v3.8(1).txt`; mã WEB-PROJECT-PROFILE-RQA; cập nhật 31/08/2026; tìm trong Library. Nội dung đã đọc trực tiếp gồm định vị, nhận diện, lịch sử, Hero, hành trình trang chủ, taxonomy, công trình và chính sách. Bản v3.8 là bản tìm được trong lượt này, không tuyên bố không có bản mới hơn ở nơi chưa truy xuất. File ID: `file_0000000098a4820b8dab2f2c6ef6159b`.

**S2 — Chiến lược nội dung:** `RQA_CHIEN_LUOC_NOI_DUNG_SEO_EEAT_TRIEN_KHAI_DONG_BO(1).docx`; phiên bản 1.0, ngày 23/08/2026; ba chuyên mục, nguyên tắc nguồn và chứng cứ. File ID: `file_000000008eb08211aa184c5ed79615fb`.

**S3 — Quyết định/pilot sản phẩm:** `docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md` trong `truongdinhnamaz/aznet-theme`; đọc qua GitHub ngày 22/09/2026; blob SHA trả về `901143a93b0f255050aba87c9ce7cf36d93b6808`. D-034 cũng xuất hiện trong `docs/source/AZT-04-roadmap-qa-decisions.md`. Spec có một số đoạn mô tả tình trạng access tại thời điểm khởi tạo ngày 21/09; không lấy chúng thay kết quả đọc live ngày 22/09. URL: `https://github.com/truongdinhnamaz/aznet-theme/blob/main/docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md`.

**S4 — Điều phối/bộ câu hỏi:** `00_DIEU_PHOI_NGUON_v6.4(1).txt`, mục 13.2; workbook tìm được `BỘ CÂU HỎI(1).xlsx`, File ID `file_00000000d3e08230a350dd4762640cba`. Dùng số lượng 244 theo tài liệu điều phối; chưa kiểm đếm độc lập workbook.

**S5 — Nội dung sơ bộ:** `NỘI DUNG SƠ BỘ (3).docx`; dùng như nguồn khách hàng ban đầu, không được vượt quyền hồ sơ đã hợp nhất.

**S6 — Quy trình/chính sách:** `HƯỚNG DẪN MUA HÀNG VÀ THANH TOÁN.docx` và phần hợp nhất chính sách tại S1 mục 15.2. Xung đột hotline được giữ nguyên trạng thái.

**S7 — Snapshot WordPress:** WPVibe `site_info`; `GET /wp/v2/pages/2`; `GET /wp/v2/blocks/991`; `GET /wc/v3/products`; `GET /wc/v3/products/categories`; `option get theme_mods_aznet-theme --format=json`; `term list product_cat --fields=term_id,name,slug,parent,count --format=json`. Tất cả là đọc. Chi tiết ở tài liệu 02 và JSON 04.

**S8 — Lịch sử trao đổi:** các quyết định sản phẩm/UX được truy hồi qua personal context. Đây là ngữ cảnh bổ trợ; các khẳng định cũ của trợ lý về PASS/live phải được kiểm tra lại, không dùng thay bằng chứng hiện tại.

Giới hạn sao chép: thao tác lấy raw byte của hồ sơ và workbook không được công cụ cấp đường materialize trong lượt này. Bộ bàn giao là bản tổng hợp mới dựa trên nội dung truy xuất được, không phải bộ sao nguyên bản toàn bộ nguồn hay Project.