<?php
/**
 * B12 preview of the existing "Nâng cấp website" page, page ID 41978.
 *
 * Uses the EXISTING WordPress post_content without altering the database.
 * A page-scoped, explicit adapter translates only the six legacy Flatsome
 * shortcodes present in this page into semantic HTML.
 *
 * NOT a production migration: the raw content still contains legacy syntax.
 *
 * @package AZnetTheme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Explicit shortcode renderers only for this isolated pilot request.
 * Neither post_content nor global WordPress settings are modified.
 */
add_shortcode(
    'section',
    static function ( $atts, $content = '' ) {
        $atts  = is_array( $atts ) ? $atts : [];
        $label = strtolower( (string) ( $atts['label'] ?? '' ) );
        $role  = 'standard';
        foreach ( [
            'hero'      => 'hero',
            'dau hieu'  => 'signs',
            'gom gi'    => 'features',
            'quy trinh' => 'process',
            'bao gia'   => 'prices',
            'faq'       => 'faq',
            'cta'       => 'last',
        ] as $needle => $mapped_role ) {
            if ( str_contains( $label, $needle ) ) {
                $role = $mapped_role;
                break;
            }
        }
        return '<section class="az-b12-section az-b12-section--' . esc_attr( $role ) . '">'
            . do_shortcode( (string) $content )
            . '</section>';
    }
);

add_shortcode(
    'row',
    static function ( $atts, $content = '' ) {
        return '<div class="az-b12-row">' . do_shortcode( (string) $content ) . '</div>';
    }
);

add_shortcode(
    'col',
    static function ( $atts, $content = '' ) {
        $atts  = is_array( $atts ) ? $atts : [];
        $span  = max( 1, min( 12, (int) ( $atts['span'] ?? 12 ) ) );
        $small = max( 1, min( 12, (int) ( $atts['span__sm'] ?? 12 ) ) );
        $class = 'az-b12-col';
        if ( 'center' === ( $atts['align'] ?? '' ) ) {
            $class .= ' az-b12-col--center';
        }
        return '<div class="' . esc_attr( $class ) . '" style="--b12-span:' . $span . ';--b12-span-sm:' . $small . '">'
            . do_shortcode( (string) $content )
            . '</div>';
    }
);

add_shortcode(
    'accordion',
    static function ( $atts, $content = '' ) {
        return '<div class="az-b12-faq">' . do_shortcode( (string) $content ) . '</div>';
    }
);

add_shortcode(
    'accordion-item',
    static function ( $atts, $content = '' ) {
        $atts  = is_array( $atts ) ? $atts : [];
        $title = sanitize_text_field( (string) ( $atts['title'] ?? 'Câu hỏi thường gặp' ) );
        return '<details class="az-b12-faq-item"><summary>' . esc_html( $title )
            . '</summary><div class="az-b12-faq-answer">'
            . wpautop( do_shortcode( (string) $content ) )
            . '</div></details>';
    }
);

get_header();
?>
<main id="main" class="aznet-theme-main aznet-theme-main--page az-b12-preview">
    <article id="post-<?php the_ID(); ?>" <?php post_class( 'aznet-theme-page aznet-theme-page--full-bleed az-b12-article' ); ?>>
        <div class="az-b12-qa" role="note">
            Bản thử nghiệm giao diện B12 — chưa xuất bản, không phải bảng giá hoặc cam kết dịch vụ chính thức.
        </div>
        <?php
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();

                // This is preview-only copy governance. Source remains intact in WP.
                $raw = (string) get_post_field( 'post_content', (int) get_the_ID() );
                $claim_changes = [
                    'giữ nguyên nội dung, dữ liệu và bảo toàn thứ hạng tìm kiếm hiện có.' =>
                        'có phương án bảo vệ dữ liệu, URL và giảm rủi ro biến động thứ hạng tìm kiếm.',
                    'Bảo toàn thứ hạng' => 'Giảm rủi ro thứ hạng',
                    'Giữ SEO' => 'Kiểm soát SEO',
                    'Chuẩn SEO & giữ thứ hạng' => 'Kiểm soát rủi ro SEO',
                    'Chuẩn SEO &amp; giữ thứ hạng' => 'Kiểm soát rủi ro SEO',
                    'Tối ưu on-page, thiết lập 301 cho URL cũ để bảo toàn SEO.' =>
                        'Rà soát SEO on-page và áp dụng chuyển hướng 301 khi thật sự cần, để giảm thiểu rủi ro.',
                    'Bàn giao & thiết lập 301' => 'Bàn giao và kiểm tra URL',
                    'Bàn giao &amp; thiết lập 301' => 'Bàn giao và kiểm tra URL',
                    'Bàn giao kèm hướng dẫn, thiết lập 301 giữ thứ hạng SEO.' =>
                        'Bàn giao hướng dẫn, kiểm tra URL và chuyển hướng khi cần.',
                    'Chuẩn SEO on-page + thiết lập 301 giữ thứ hạng' =>
                        'Rà soát SEO on-page và chuyển hướng khi cần',
                    'Giữ nguyên nội dung & dữ liệu hiện có' =>
                        'Lập phương án giữ và di chuyển dữ liệu hiện có',
                    'Từ 10.000.000đ' => 'Báo giá sau khảo sát',
                    'Bảo hành lỗi kỹ thuật 3 tháng' => 'Bảo hành theo phạm vi hợp đồng được xác nhận',
                    '90+' => 'Theo kết quả đo',
                    'Không, nếu làm đúng. AZnet giữ cấu trúc URL hợp lý và thiết lập chuyển hướng 301 cho các URL cũ để bảo toàn thứ hạng và lưu lượng hiện có.' =>
                        'Không thể đảm bảo tuyệt đối. AZnet kiểm tra URL, nội dung và chuyển hướng khi cần để giảm rủi ro; lưu lượng và thứ hạng cần được đo lại sau khi nâng cấp.',
                    'Có. AZnet giữ nguyên nội dung, hình ảnh và dữ liệu hiện có; nếu chuyển sang nền tảng mới (WordPress) cũng sẽ di chuyển dữ liệu sang đầy đủ.' =>
                        'AZnet lập phương án sao lưu và di chuyển dữ liệu theo phạm vi được xác nhận. Khả năng chuyển đổi phụ thuộc chất lượng dữ liệu và nền tảng hiện có.',
                    'Tùy hiện trạng và phạm vi, thường từ 7–15 ngày làm việc. AZnet thống nhất tiến độ cụ thể ngay từ đầu.' =>
                        'Tùy hiện trạng và phạm vi; tiến độ cụ thể được thống nhất sau khảo sát.',
                    'Làm mới website, bứt phá doanh thu' =>
                        'Làm mới website phù hợp với mục tiêu kinh doanh',
                ];
                $raw = str_replace( array_keys( $claim_changes ), array_values( $claim_changes ), $raw );

                // Keep the original click-to-call CTA and add a direct,
                // verified-format Zalo link as a secondary contact path.
                $raw = str_replace(
                    '<a class="az-cta-phone" href="tel:0972782255"> 0972 78 22 55</a>',
                    '<a class="az-cta-phone" href="tel:0972782255"> 0972 78 22 55</a>'
                        . '<a class="azbtn azbtn-o-light" href="https://zalo.me/0972782255" target="_blank" rel="noopener nofollow">Nhắn Zalo tư vấn</a>',
                    $raw
                );

                // The current page has section, row, col, accordion, accordion-item.
                // Translate only those known tags; do not remove unknown syntax silently.
                $html = do_shortcode( $raw );

                // Restore the original featured image into the isolated preview.
                if ( has_post_thumbnail() ) {
                    $photo = '<figure class="az-b12-featured">'
                        . get_the_post_thumbnail( get_the_ID(), 'large' )
                        . '<figcaption>Hình đại diện hiện có của trang Nâng cấp website</figcaption>'
                        . '</figure>';
                    $pos = strpos( $html, '</section>' );
                    if ( false !== $pos ) {
                        $insert_at = $pos + strlen( '</section>' );
                        $html = substr_replace( $html, $photo, $insert_at, 0 );
                    }
                }

                echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- existing trusted WordPress page HTML rendered in draft only.
            }
        }
        ?>
    </article>
</main>
<?php get_footer(); ?>

