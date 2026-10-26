<?php
/**
 * Disposable WP-owned Page + logo + navigation fixture for Digital 01 Brand 2026 L3/L4.
 * WordPress 6.9 test server only. Never execute on aznet.vn production.
 */
$front_id = (int) trim(file_get_contents('/tmp/digital01-front-id.txt'));
if ($front_id < 1 || !get_post($front_id)) {
    throw new RuntimeException('Base native Page fixture missing');
}
$group = static function (string $class, string $contents, bool $full = false): string {
    $attrs = ['className' => $class, 'layout' => ['type' => 'default']];
    if ($full) $attrs = ['align' => 'full'] + $attrs;
    $classes = 'wp-block-group ' . ($full ? 'alignfull ' : '') . $class;
    return '<!-- wp:group ' . wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' -->'
         . '<div class="' . esc_attr($classes) . '">' . $contents . '</div><!-- /wp:group -->';
};
$columns = static fn(string $cls, string $inner): string =>
    '<!-- wp:columns ' . wp_json_encode(['className' => $cls]) . ' --><div class="wp-block-columns ' . esc_attr($cls) . '">' . $inner . '</div><!-- /wp:columns -->';
$col = static fn(string $inner): string => '<!-- wp:column --><div class="wp-block-column">' . $inner . '</div><!-- /wp:column -->';
$p = static fn(string $txt, string $cls=''): string =>
    '<!-- wp:paragraph' . ($cls === '' ? '' : ' ' . wp_json_encode(['className'=>$cls])) . ' --><p' . ($cls === '' ? '' : ' class="'.esc_attr($cls).'"') . '>' . $txt . '</p><!-- /wp:paragraph -->';
$h = static fn(int $n,string $txt): string => '<!-- wp:heading {"level":'.$n.'} --><h'.$n.' class="wp-block-heading">'.$txt.'</h'.$n.'><!-- /wp:heading -->';
$buttons = '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/lien-he/">Nhận tư vấn miễn phí →</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/gioi-thieu/">Xem giới thiệu</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';
$art = '<!-- wp:html --><div class="aznet-digital01__hero-art" role="img" aria-label="Minh họa laptop hiển thị website">'
     . '<div class="aznet-digital01__laptop" aria-hidden="true"><div class="aznet-digital01__laptop-screen">'
     . '<div class="aznet-digital01__laptop-bar"><strong>AZnet</strong><span>Trang chủ</span><span>Dịch vụ</span><span>Dự án</span></div>'
     . '<div class="aznet-digital01__laptop-slogan">Kiến tạo giá trị<br>bền vững cho doanh nghiệp<span>Khám phá ngay →</span></div>'
     . '</div><div class="aznet-digital01__laptop-base"></div></div>'
     . '<div class="aznet-digital01__float-badge aznet-digital01__float-badge--speed" aria-hidden="true"><span class="aznet-digital01__badge-icon">⚡</span><p>Tối ưu hiệu suất<small>Trải nghiệm nhanh</small></p></div>'
     . '<div class="aznet-digital01__float-badge aznet-digital01__float-badge--growth" aria-hidden="true"><span class="aznet-digital01__badge-icon">↗</span><p>Tăng trưởng<small>Doanh thu online</small></p></div>'
     . '<div class="aznet-digital01__float-badge aznet-digital01__float-badge--trust" aria-hidden="true"><span class="aznet-digital01__badge-icon">✓</span><p>Bảo mật &amp; ổn định</p></div>'
     . '</div><!-- /wp:html -->';
$hero = $group('aznet-digital01__hero', $columns('aznet-digital01__hero-grid',
    $col($p('WEBSITE · WORDPRESS · DIGITAL SOLUTIONS','aznet-digital01__eyebrow') .
         $h(1,'Xây dựng website <em>tăng trưởng thực sự</em>') .
         $p('Website WordPress chuyên nghiệp, hiệu suất cao và phát triển bền vững.','aznet-digital01__intro') .
         $buttons . $group('aznet-digital01__hero-checks', $p('Tối ưu tốc độ') . $p('Chuẩn SEO') . $p('Hỗ trợ dài hạn'))) .
    $col($art)), true);

// WordPress-native authoring; links and labels belong to the Page owner.
$offer_rows = [
  ['01 · DỊCH VỤ', 'Dịch vụ AZnet', 'Thiết kế, tối ưu và phát triển website theo nhu cầu doanh nghiệp.',
    [['Thiết kế website','/thiet-ke-website/'],['SEO website','/dich-vu-seo/']], 'Xem dịch vụ', '/dich-vu/'],
  ['02 · SẢN PHẨM SỐ', 'Sản phẩm AZnet phát triển', 'Plugin và giải pháp số do AZnet xây dựng cho website WordPress.',
    [['Website Intelligence','/website-intelligence/'],['Plugin tính giá','/plugin-tinh-gia-xay-dung-cho-wordpress/']], 'Khám phá sản phẩm', '/san-pham/'],
  ['03 · HỆ SINH THÁI', 'Công cụ hệ sinh thái', 'Công cụ bên ngoài được lựa chọn và tài nguyên AI hỗ trợ website.',
    [['EWWW Image Optimizer','/ewww-image-optimizer/'],['Công cụ video AI','/cong-cu-video-ai-chon-loc-boi-aznet-viet-nam/']], 'Xem công cụ', '/ewww-image-optimizer/'],
];
$offer_cards = '';
foreach ($offer_rows as [$label,$title,$description,$related,$action,$action_url]) {
    $links = '';
    foreach ($related as [$link_title,$link_url]) {
        $links .= $p('<a href="' . esc_url($link_url) . '">' . esc_html($link_title) . '</a>');
    }
    $offer_cards .= $group('aznet-digital01__offer-card',
        $p(esc_html($label),'aznet-digital01__offer-kind') .
        $h(3,esc_html($title)) .
        $p(esc_html($description),'aznet-digital01__offer-description') .
        $group('aznet-digital01__offer-links', $links) .
        $p('<a href="' . esc_url($action_url) . '">' . esc_html($action) . ' →</a>', 'aznet-digital01__offer-more'));
}
$offers = $group('aznet-digital01__offer-paths aznet-digital01__section',
    $group('aznet-digital01__offer-inner',
        $p('DỊCH VỤ · SẢN PHẨM AZNET · CÔNG CỤ HỆ SINH THÁI','aznet-digital01__eyebrow') .
        $h(2,'Ba hướng giải pháp, một nền tảng phát triển website.') .
        $p('Chọn dịch vụ chuyên môn, sản phẩm do AZnet phát triển hoặc công cụ được tuyển chọn phù hợp với nhu cầu.','aznet-digital01__intro') .
        $group('aznet-digital01__offer-grid',$offer_cards)), true);

$service_rows = [
    ['W', 'Thiết kế website WordPress', 'Website hiện đại, linh hoạt và dễ quản trị.', '/dich-vu/'],
    ['🚀', 'Tối ưu hiệu suất Website', 'Tăng tốc độ và trải nghiệm sử dụng.', '/dich-vu/'],
    ['▥', 'SEO & Tăng trưởng nội dung', 'Phát triển khả năng được tìm thấy.', '/dich-vu/'],
    ['🛒', 'WooCommerce & Bán hàng', 'Cửa hàng trực tuyến dễ quản trị.', '/dich-vu/'],
];
$cards = '';
foreach ($service_rows as [$icon,$label,$description,$url]) {
    $cards .= $group('aznet-digital01__service',
        $p(esc_html($icon), 'aznet-digital01__service-icon') .
        $h(3, esc_html($label)) . $p(esc_html($description)) .
        $p('<a href="'.esc_url($url).'">Tìm hiểu thêm →</a>', 'aznet-digital01__text-link'));
}
$services = $group('aznet-digital01__services aznet-digital01__section',$group('aznet-digital01__services-grid',$cards),true);
$features='';
foreach ([
    ['♧','Kinh nghiệm thực chiến','Các dự án với doanh nghiệp thực.'],
    ['◇','Giải pháp tối ưu','Kết hợp công nghệ và chiến lược.'],
    ['☏','Hỗ trợ tận tâm','Đồng hành phát triển lâu dài.'],
    ['▥','Hướng đến hiệu quả thật','Ưu tiên kết quả kinh doanh.'],
] as [$icon,$label,$description]) {
    $features .= $group('aznet-digital01__brand-feature',
        $p(esc_html($icon),'aznet-digital01__brand-feature-icon') .
        $group('aznet-digital01__brand-feature-copy',$h(3,esc_html($label)).$p(esc_html($description))));
}
$why = $group('aznet-digital01__brand-why',
    $columns('aznet-digital01__brand-why-grid',
        $col($p('VÌ SAO CHỌN AZNET','aznet-digital01__eyebrow') .
             $h(2,'Giải pháp toàn diện cho <em>doanh nghiệp số</em>') .
             $p('Không chỉ là giao diện mà còn là website có khả năng tạo giá trị.','aznet-digital01__intro')) .
        $col($group('aznet-digital01__brand-feature-grid',$features))),true);

// Extend the disposable-only fixture through the entire native WordPress Page.
// WordPress owns fixture posts/Media; Theme only styles their public markup.
// These examples are NOT authoritative AZnet identity, catalog or commerce data.
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
$qa_media = [];
foreach ([
    'portrait' => 'assets/starter/law01/editorial-1.webp',
    'product_a' => 'assets/starter/law01/editorial-2.webp',
    'product_b' => 'assets/starter/law01/editorial-3.webp',
    'article' => 'assets/starter/law01/hero.webp',
] as $key => $relative) {
    $source = get_stylesheet_directory() . '/' . $relative;
    $temp = tempnam(sys_get_temp_dir(), 'aznet-d01-qa-');
    if (!is_file($source) || false === $temp || !copy($source, $temp)) {
        throw new RuntimeException('Disposable WordPress Media fixture source missing: ' . $relative);
    }
    $media = media_handle_sideload([
        'name' => 'aznet-d01-qa-' . $key . '.webp',
        'type' => 'image/webp',
        'tmp_name' => $temp,
        'error' => 0,
        'size' => filesize($temp),
    ], 0, 'Disposable WordPress-owned Brand Light QA image');
    if (is_wp_error($media)) {
        @unlink($temp);
        throw new RuntimeException('Disposable WordPress Media import: ' . $media->get_error_message());
    }
    $url = wp_get_attachment_image_url((int) $media, 'full');
    if (!is_string($url) || '' === $url) {
        throw new RuntimeException('Disposable WordPress Media URL missing: ' . $key);
    }
    $qa_media[$key] = ['id' => (int) $media, 'url' => $url];
}
$post_ids = get_posts([
    'post_type' => 'post', 'post_status' => 'publish',
    'numberposts' => 3, 'fields' => 'ids',
]);
if (3 !== count($post_ids)) {
    throw new RuntimeException('Disposable WordPress Query must have 3 posts');
}
foreach ($post_ids as $post_id) {
    if (!set_post_thumbnail((int) $post_id, $qa_media['article']['id'])) {
        throw new RuntimeException('Disposable WordPress featured image assignment failed');
    }
}

$wide = static function (string $cls, string $inside): string {
    $attrs = ['align' => 'wide', 'className' => $cls, 'layout' => ['type' => 'default']];
    return '<!-- wp:group ' . wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        ' --><div class="wp-block-group alignwide ' . esc_attr($cls) . '">' .
        $inside . '</div><!-- /wp:group -->';
};
$image = static function (array $media, string $cls, string $alt): string {
    $attrs = [
        'id' => $media['id'], 'sizeSlug' => 'full',
        'linkDestination' => 'none', 'className' => $cls,
    ];
    return '<!-- wp:image ' . wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        ' --><figure class="wp-block-image size-full ' . esc_attr($cls) . '">' .
        '<img src="' . esc_url($media['url']) . '" alt="' . esc_attr($alt) .
        '" class="wp-image-' . (int) $media['id'] . '"/></figure><!-- /wp:image -->';
};

$steps = '';
foreach ([
    ['01', 'Khảo sát', 'Hiểu mục tiêu và hiện trạng.'],
    ['02', 'Khách hàng', 'Xác định nhu cầu và hành trình.'],
    ['03', 'Cấu trúc', 'Sắp xếp nội dung và giải pháp.'],
    ['04', 'Triển khai', 'Xây dựng và kiểm thử.'],
    ['05', 'Cải tiến', 'Theo dõi và nâng cấp liên tục.'],
] as [$number, $heading, $description]) {
    $steps .= $group('aznet-digital01__step',
        $p(esc_html($number), 'aznet-digital01__step-number') .
        $h(3, esc_html($heading)) . $p(esc_html($description)));
}
$process = $group('aznet-digital01__process aznet-digital01__section',
    $group('aznet-digital01__process-inner',
        $p('QUY TRÌNH TRIỂN KHAI', 'aznet-digital01__eyebrow') .
        $h(2, 'Từ kế hoạch đến kết quả được kiểm chứng.') .
        $p('Năm bước minh họa bằng nội dung WordPress thử nghiệm.', 'aznet-digital01__intro') .
        $group('aznet-digital01__process-grid', $steps) .
        $p('<a href="/du-an/">Xem dự án ví dụ →</a>', 'aznet-digital01__text-link')), true);

$about = $wide('aznet-digital01__about aznet-digital01__section',
    $columns('aznet-digital01__about-grid',
        $col($image($qa_media['portrait'], 'aznet-digital01__portrait', 'Chân dung minh họa trong fixture kiểm thử')) .
        $col($p('CON NGƯỜI CHỊU TRÁCH NHIỆM', 'aznet-digital01__eyebrow') .
             $h(2, 'Người phụ trách dự án') .
             $p('Hồ sơ mẫu minh họa bố cục hai cột, không đại diện danh tính thật.', 'aznet-digital01__intro') .
             $p('WordPress sở hữu nội dung hồ sơ; Theme chỉ sở hữu trình bày.', 'aznet-digital01__intro') .
             $p('<a href="/gioi-thieu/">Tìm hiểu thêm →</a>', 'aznet-digital01__text-link'))));

$product_cards = '';
foreach ([
    ['product_a', 'Công cụ WordPress mẫu', 'Ứng dụng mẫu'],
    ['product_b', 'Plugin tiện ích mẫu', 'Plugin mẫu'],
] as [$media_key, $title, $eyebrow]) {
    $product_cards .= $group('aznet-digital01__product',
        $image($qa_media[$media_key], 'aznet-digital01__product-image', $title . ' — ảnh kiểm thử') .
        $group('aznet-digital01__product-copy',
            $p(esc_html($eyebrow), 'aznet-digital01__eyebrow') .
            $h(3, esc_html($title)) .
            $p('<a href="/san-pham/">Xem danh mục →</a>', 'aznet-digital01__text-link')));
}
$products = $group('aznet-digital01__products aznet-digital01__section',
    $group('aznet-digital01__section',
        $p('SẢN PHẨM MẪU', 'aznet-digital01__eyebrow') .
        $h(2, 'Công cụ có giao diện dễ tiếp cận.') .
        $p('Dữ liệu minh họa; không chứa trạng thái WooCommerce.', 'aznet-digital01__intro') .
        $group('aznet-digital01__products-grid', $product_cards)), true);

$native_query = '<!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->'
    . '<div class="wp-block-query"><!-- wp:post-template -->'
    . '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->'
    . '<!-- wp:post-date {"fontSize":"sm"} /-->'
    . '<!-- wp:post-title {"level":3,"isLink":true} /-->'
    . '<!-- wp:post-excerpt {"moreText":"Đọc tiếp"} /-->'
    . '<!-- /wp:post-template --></div><!-- /wp:query -->';
$articles = $wide('aznet-digital01__articles aznet-digital01__section',
    $p('BÀI VIẾT WORDPRESS', 'aznet-digital01__eyebrow') .
    $h(2, 'Kiến thức từ dữ liệu xuất bản mẫu.') .
    $p('Ba bài viết được truy vấn bởi WordPress Query Loop thực.', 'aznet-digital01__intro') .
    $native_query .
    $p('<a href="/kien-thuc/">Xem danh sách bài viết →</a>', 'aznet-digital01__text-link'));

$faq_items = '';
foreach ([
    ['Cần chuẩn bị thông tin gì?', 'Xác định mục tiêu và phạm vi dự án.'],
    ['Có thể cải tiến website hiện hữu?', 'Việc đánh giá phụ thuộc hiện trạng thực tế.'],
    ['Ai chịu trách nhiệm quản trị?', 'WordPress và đơn vị vận hành sở hữu nội dung.'],
    ['Giao diện có hỗ trợ điện thoại?', 'Theme trình bày nội dung theo viewport.'],
    ['Bắt đầu liên hệ bằng cách nào?', 'Sử dụng liên kết tư vấn được công bố.'],
] as [$question, $answer]) {
    $faq_items .= '<!-- wp:details {"className":"aznet-digital01__faq-item"} -->'
        . '<details class="wp-block-details aznet-digital01__faq-item"><summary>' .
        esc_html($question) . '</summary>' . $p(esc_html($answer)) .
        '</details><!-- /wp:details -->';
}
$faq = $wide('aznet-digital01__faq aznet-digital01__section',
    $columns('aznet-digital01__faq-grid',
        $col($p('FAQ MẪU', 'aznet-digital01__eyebrow') .
             $h(2, 'Các câu hỏi thường gặp.') .
             $p('Kiểm tra tương tác details/summary gốc WordPress.', 'aznet-digital01__intro')) .
        $col($group('aznet-digital01__faq-list', $faq_items))));

$cta_buttons = '<!-- wp:buttons --><div class="wp-block-buttons">'
    . '<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/lien-he/">Liên hệ tư vấn</a></div><!-- /wp:button -->'
    . '<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/san-pham/">Xem sản phẩm</a></div><!-- /wp:button -->'
    . '</div><!-- /wp:buttons -->';
$cta = $group('aznet-digital01__cta',
    $group('aznet-digital01__cta-inner',
        $p('BẮT ĐẦU KẾ HOẠCH', 'aznet-digital01__eyebrow') .
        $h(2, 'Website của bạn cần cải thiện điều gì?') .
        $p('Mẫu WordPress chỉ để kiểm tra bố cục và CTA.') .
        $cta_buttons), true);

$all = $group('aznet-digital01 aznet-digital01--brand-2026',
    $hero . $offers . $services . $why . $process . $about . $products . $articles . $faq . $cta, true);
$result=wp_update_post(['ID'=>$front_id,'post_content'=>wp_slash($all)],true);
if(is_wp_error($result))throw new RuntimeException('WordPress fixture Page update: '.$result->get_error_message());
if(!str_contains((string)get_post($front_id)->post_content,'aznet-digital01--brand-2026')){
    throw new RuntimeException('WordPress did not persist new branded content');
}
$nav = wp_create_nav_menu('AZnet Brand Fixture Native Navigation');
$services_menu_id = 0;
if (is_wp_error($nav)) throw new RuntimeException($nav->get_error_message());
foreach ([
  ['Trang chủ','/'], ['Dịch vụ','/dich-vu/'], ['Dự án','/du-an/'],
  ['Bảng giá','/bang-gia/'], ['Kiến thức','/kien-thuc/'],
  ['Giới thiệu','/gioi-thieu/'], ['Liên hệ','/lien-he/'],
  ['Nhận tư vấn','/lien-he/']
] as [$label,$url]) {
  $added=wp_update_nav_menu_item($nav,0,['menu-item-title'=>$label,
    'menu-item-url'=>home_url($url),'menu-item-type'=>'custom','menu-item-status'=>'publish']);
  if(is_wp_error($added))throw new RuntimeException($added->get_error_message());
  if ($label === 'Dịch vụ') $services_menu_id=(int)$added;
}
if($services_menu_id <= 0) throw new RuntimeException('Native services parent menu item missing');
foreach ([['Dịch vụ SEO','/dich-vu-seo/'],['Website bán hàng','/thiet-ke-website-ban-hang/']] as [$title,$url]) {
  $child=wp_update_nav_menu_item($nav,0,[
    'menu-item-title'=>$title,
    'menu-item-url'=>home_url($url),
    'menu-item-type'=>'custom',
    'menu-item-parent-id'=>$services_menu_id,
    'menu-item-status'=>'publish'
  ]);
  if(is_wp_error($child))throw new RuntimeException($child->get_error_message());
}
$locations=get_theme_mod('nav_menu_locations',[]);
$locations['header-utility']=(int)$nav;
set_theme_mod('nav_menu_locations',$locations);
$logo_file = '/tmp/digital01-brand/logo.png';
if (file_exists($logo_file)) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $media = [
        'name'=>'aznet-logo-fixture.png', 'type'=>'image/png', 'tmp_name'=>$logo_file,
        'error'=>0, 'size'=>filesize($logo_file)
    ];
    $logo_id = media_handle_sideload($media,0,'WordPress-owned disposable AZnet logo');
    if(is_wp_error($logo_id))throw new RuntimeException($logo_id->get_error_message());
    set_theme_mod('custom_logo',(int)$logo_id);
}
// WordPress Landing Page preview witness: isolate non-front-page placement
// as used by site-owned Page 43515 (without changing Page 2 or any external site).
$landing_content = str_replace(
    'aznet-digital01 aznet-digital01--brand-2026',
    'aznet-digital01 aznet-digital01--brand-2026 aznet-theme-page-hero',
    $all
);
$landing_id = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_name' => 'digital01-brand2026-landing-check',
    'post_title' => 'Fixture Landing Title Only',
    'post_content' => wp_slash($landing_content),
], true);
if (is_wp_error($landing_id) || (int) $landing_id <= 0) {
    throw new RuntimeException('Failed creating disposable Landing Page witness');
}
update_post_meta((int) $landing_id, '_wp_page_template', 'page-templates/landing.php');
if ('page-templates/landing.php' !== get_page_template_slug((int) $landing_id)) {
    throw new RuntimeException('Disposable Landing Page template not persisted');
}
file_put_contents('/tmp/digital01-brand/landing-id.txt', (string) $landing_id);
echo "PASS: Disposable native WP Page, menu and optional real logo. Page ID {$front_id}.\n";
