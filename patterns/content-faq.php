<?php
/**
 * Title: Content — FAQ
 * Slug: aznet-theme/content-faq
 * Categories: aznet-theme-content
 * Description: Plain WordPress FAQ presentation using native Details blocks without schema ownership.
 */
?>
<!-- wp:group {"align":"wide","className":"aznet-theme-pattern aznet-theme-pattern--content-faq","style":{"spacing":{"padding":{"top":"var:preset|spacing|section","bottom":"var:preset|spacing|section"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide aznet-theme-pattern aznet-theme-pattern--content-faq" style="padding-top:var(--wp--preset--spacing--section);padding-bottom:var(--wp--preset--spacing--section)">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Câu hỏi thường gặp', 'aznet-theme' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:details -->
<details class="wp-block-details"><summary><?php esc_html_e( 'Câu hỏi mẫu thứ nhất?', 'aznet-theme' ); ?></summary><!-- wp:paragraph {"placeholder":"Nhập câu trả lời"} --><p><?php esc_html_e( 'Câu trả lời mẫu — thay bằng thông tin chính xác do nguồn nội dung của bạn sở hữu.', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></details>
<!-- /wp:details -->
<!-- wp:details -->
<details class="wp-block-details"><summary><?php esc_html_e( 'Câu hỏi mẫu thứ hai?', 'aznet-theme' ); ?></summary><!-- wp:paragraph {"placeholder":"Nhập câu trả lời"} --><p><?php esc_html_e( 'Câu trả lời mẫu — trình bày ngắn gọn, dễ đọc và không gắn thêm schema tự động.', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></details>
<!-- /wp:details -->
<!-- wp:details -->
<details class="wp-block-details"><summary><?php esc_html_e( 'Câu hỏi mẫu thứ ba?', 'aznet-theme' ); ?></summary><!-- wp:paragraph {"placeholder":"Nhập câu trả lời"} --><p><?php esc_html_e( 'Câu trả lời mẫu — có thể thêm, xóa hoặc sắp xếp bằng block WordPress chuẩn.', 'aznet-theme' ); ?></p><!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group -->
