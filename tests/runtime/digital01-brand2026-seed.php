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
$all = $group('aznet-digital01 aznet-digital01--brand-2026',$hero.$services.$why,true);
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
