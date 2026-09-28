# CÂU LỆNH TIẾP NHẬN BỘ DỮ LIỆU VÀO DỰ ÁN

**Phiên bản:** 1.0 · **Ngày:** 22/09/2026  
**Mục đích:** Cung cấp ngữ cảnh đủ rõ để tiếp tục pilot Rèm Quốc Anh mà không mất kết quả đã làm và không lẫn dữ liệu website vào lõi AZnet Theme.

## Câu lệnh dùng ngay

```text
Tiếp nhận bộ dữ liệu Rèm Quốc Anh gồm:
1. 01_Ho_so_du_lieu_pilot_v1.0.md
2. 02_Kiem_ke_live_va_khoang_trong_v1.0.md
3. 04_Snapshot_du_lieu_v1.0.json

Mục tiêu là giúp pilot Rèm Quốc Anh đúng thương hiệu, đúng dữ liệu và thuyết phục hơn. Đây không phải lệnh thiết kế lại toàn bộ website, không phải lệnh khôi phục Flatsome và không phải quyền xuất bản lên live.

Phạm vi sản phẩm vẫn là AZnet Theme, repository truongdinhnamaz/aznet-theme. Rèm Quốc Anh là site pilot của preset Rèm 01, không phải chủ sở hữu ngữ nghĩa của lõi preset. Không chuyển sang sửa ConvertFlow, RootProfile, Nagavia hoặc sản phẩm khác khi chưa có chỉ định rõ.

Trước hết, đọc các tệp đã cung cấp; đối chiếu D-034, spec docs/superpowers/specs/2026-09-21-curtain01-pilot-design.md và docs/source/AZT-04-roadmap-qa-decisions.md hiện hành. Đọc trực tiếp trạng thái site/repository mới nhất trước bất cứ thay đổi nào. Không lấy snapshot ngày 22/09/2026 thay bằng chứng hiện tại.

Phân loại thông tin thành: dữ liệu nghiệp vụ trong nguồn; quyết định thiết kế đã ghi nhận; quan sát live có thời điểm; đề xuất; xung đột/cần xác minh. Khẳng định cũ của trợ lý rằng PASS hoặc đã live chỉ là ngữ cảnh cho đến khi có bằng chứng phù hợp.

Hãy lập bản tiếp nhận nguồn của pilot và đối chiếu từng mục với hồ sơ hiện có. Bổ sung đúng tài liệu chủ quản khi có quyền ghi rõ ràng; nếu chưa có quyền/công cụ ghi Project hoặc repository, xuất bản nội dung đề nghị cập nhật, không tuyên bố đã lưu. Không thay thế nguồn gốc bằng một bản tóm tắt mới. Mọi thay đổi kiến trúc hoặc tiêu chuẩn bền vững phải được đề xuất cập nhật đúng nguồn sản phẩm.

Bảo vệ các ranh giới:
- WordPress sở hữu Page/Post/Menu/Media/Hero nội dung và trạng thái xuất bản.
- WooCommerce sở hữu Product/variation/price/stock/categories và catalogue.
- ConvertFlow sở hữu Journey/Fit/filter/quote-vs-buy khi có.
- RootProfile sở hữu identity/contact/social khi có contract phù hợp.
- Theme chỉ sở hữu trình bày và tham chiếu nguồn rõ ràng, không đọc kho riêng của plugin, không chép nghiệp vụ vào settings.

Bảo vệ phần đã làm: Hero, nội dung, URL, mapping, catalogue và candidate đã duyệt. Snapshot ghi AZnet Theme 1.3.30, preset curtain-01, Hero block 991, About 35, Knowledge term 57, Contact 45. Những ID này chỉ dùng để kiểm tra đúng site, tuyệt đối không hard-code vào preset dùng chung. Không suy rằng Page 2 có post_content rỗng thì trang chủ hỏng. Không coi tên block chứa “Preview” là trạng thái draft.

Đưa các dữ liệu thương hiệu sau vào đối chiếu pilot: định vị giải pháp rèm và kiểm soát ánh sáng cho không gian sống/công trình chuyên nghiệp; câu chuyện hai thế hệ; hai thông điệp Hero đã khóa; gallery nội thất thoáng và hình lớn; bảng nền #222222, #EBE6E1, #F7F4F0; yêu cầu Montserrat trong hồ sơ riêng. Không tự thay toàn Theme hoặc mọi preset theo nhận diện Quốc Anh. Nếu có quyết định mới hơn khác với hồ sơ, nêu xung đột và xử lý đúng chủ quản trước khi thay.

Ưu tiên kiểm tra dữ liệu phục vụ section dòng rèm:
- Vì sao danh mục Rèm vải ID 16 được sản phẩm tham chiếu nhưng không xuất hiện trong các lệnh list ở snapshot?
- Product 70 Rèm vải hoa văn có đúng khi đang được gắn Zipscreen và Roman không?
- Danh mục nào thực sự có ảnh đúng dòng rèm, sản phẩm phù hợp và trang đích hữu ích?
- Cơ chế chọn sản phẩm đại diện là gì khi 10 sản phẩm trong snapshot đều featured=false?
- Ảnh nào mới chỉ có tên file chưa khớp, ảnh nào đã được kiểm tra thị giác và quyền sử dụng?

Không tự sửa taxonomy, tự tạo sản phẩm, bật Featured hàng loạt hoặc xuất bản nội dung chỉ để đủ số card. Không tự chọn hotline giữa 0786 299 698 và 0901 699 989. Không dùng danh sách dự án sơ bộ như thành tích đã xác thực. Không suy giá rỗng thành 0 đồng, hoặc stock_status instock thành hàng may đo có sẵn.

Khung nội dung chỉ giữ ba chuyên mục đã chốt: Kiến thức về rèm; Tư vấn chọn rèm; Công trình rèm. Bộ câu hỏi sản phẩm là khung kiểm tra độ đầy đủ, không phải nguồn xác nhận thông số. Không công bố bảo hành, chống cháy, kháng khuẩn, xuất xứ, thời gian giao hoặc số lượng công trình nếu thiếu điều kiện/chứng cứ của đúng nguồn.

Đầu ra trước mắt:
1. Bảng dữ liệu đã tiếp nhận, nguồn và trạng thái.
2. Bảng xung đột/khoảng trống cần xử lý, xếp ưu tiên theo tác động thực tế.
3. Đề xuất đúng lát cắt tiếp theo cho pilot, ưu tiên section dòng rèm và dữ liệu đại diện; nêu thứ cần giữ nguyên và tiêu chí nghiệm thu.

Chưa code, chưa đổi preset, chưa rewrite dữ liệu và chưa publish trong bước tiếp nhận. Không hỏi lại dữ kiện đã có; tự làm phần kiểm tra chỉ đọc có thể thực hiện được. Chia việc thành checkpoint ngắn, tránh ôm nhiều thay đổi trong một lượt. Mọi bước triển khai tiếp theo phải theo phạm vi và quyền phê duyệt thực tế.
```

## Ghi chú sử dụng nguồn

Tài liệu 01 là hồ sơ nội dung; tài liệu 02 là ảnh chụp trạng thái bằng dữ liệu; JSON 04 là dữ liệu chọn lọc để máy đọc. Cả ba không phải backup phục hồi, không phải migration script và không trao quyền viết production.

Một Project hoặc phiên làm việc chưa nhìn thấy các tệp này sẽ không có toàn bộ nội dung chỉ từ tên tệp. Cần cung cấp tệp hoặc nội dung thực tế; không dựa vào giả định rằng một đường dẫn Project tự chia sẻ toàn bộ nguồn cho phiên khác.