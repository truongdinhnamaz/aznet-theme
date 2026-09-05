# AZnet Theme

AZnet Theme là một WordPress theme presentation-first được phát triển cho AZnet. Theme sử dụng hệ token CSS và `theme.json` (Full Site Editing) để cung cấp một nền tảng linh hoạt, dễ tùy chỉnh và tương thích tốt với WooCommerce.

Demo: (thêm link demo ở đây khi có)

Phiên bản hiện tại: 0.1.0-alpha.7

Tính năng chính
- Token-driven CSS (CSS custom properties) cho color, spacing, radius, container.
- Hỗ trợ Full Site Editing qua `theme.json` (v3).
- Wiring PHP modular (namespace, inc/theme/*) và enqueue assets có điều kiện.
- Tích hợp cơ bản với WooCommerce (template/style cho product, cart, checkout, account).
- Template cơ bản: header, footer, single, archive, page, 404, template-parts.

Yêu cầu
- WordPress >= 6.9
- PHP >= 8.1
- (Tùy chọn) WooCommerce để sử dụng các template và style shop

Cách cài đặt nhanh
1. Clone repo:

   ```bash
   git clone https://github.com/truongdinhnamaz/aznet-theme.git
   ```

2. Copy vào thư mục themes của WordPress:

   ```bash
   cp -r aznet-theme /path/to/wordpress/wp-content/themes/
   ```

3. Kích hoạt theme:

   - Qua WP Admin: Appearance → Themes → Activate AZnet Theme
   - Hoặc dùng WP-CLI:

   ```bash
   wp theme activate aznet-theme
   ```

4. (Nếu dùng WooCommerce) Cài và kích hoạt plugin WooCommerce để xem trang cửa hàng và sản phẩm.

Starter/demo content
- Thư mục `docs/STARTER_CONTENT.md` có hướng dẫn tạo nội dung mẫu và import. Nên chuẩn bị một site demo với sample products, pages, và navigation để show cho khách.

Phát triển & đóng góp
- Mở PR nếu bạn sửa code. Thêm test nếu có thay đổi logic lớn.

Hỗ trợ
- Email: hello@aznet.vn (thay đổi theo contact của AZnet)
- Mô tả bug/feature request bằng Issue trên GitHub

License
- Đây là một theme GPL-compatible. File LICENSE có chi tiết.

Ghi chú nhanh (todo)
- Thêm screenshot.png kích thước 1200×900 để hiển thị trên WordPress.org/GitHub
- Tạo demo site công khai và import starter content
- Hoàn thiện README với link demo và hướng dẫn nâng cao
