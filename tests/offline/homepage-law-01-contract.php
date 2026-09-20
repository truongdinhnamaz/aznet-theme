<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$css = $root . '/assets/css/components/homepage-law-01.css';
if (! is_file($css)) { fwrite(STDERR, "FAIL: Law 01 stylesheet missing\n"); exit(1); }
$cssSource = file_get_contents($css);
assert(str_contains($cssSource, '.aznet-theme-homepage--law-01'));
assert(str_contains($cssSource, '--law01-gold-text: #8a632b;'), 'Light-surface eyebrow color must retain WCAG contrast on white/cream.');
assert(str_contains($cssSource, '.aznet-theme-law01-analysis .aznet-theme-law01-meta { color: #b8c5d3; }'), 'Analysis metadata must use a light contrast-safe color on the dark card surface.');
assert(str_contains($cssSource, '.aznet-theme-law01-hero .aznet-theme-law01-eyebrow,'), 'Dark-surface eyebrow override must retain the brighter gold role.');

$baseCss = file_get_contents($root . '/style.css');
assert(str_contains($baseCss, '.aznet-theme-main {'));
assert(str_contains($baseCss, 'var(--aznet-theme-container-wide)'), 'Generic Theme main must remain constrained outside Law 01.');
foreach ([
    '.aznet-theme-main--front-page',
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-block: 0;',
] as $needle) {
    assert(str_contains($cssSource, $needle), "Law 01 full-width surface contract missing: {$needle}");
}
assert(str_contains($cssSource, '.aznet-theme-homepage--law-01 .aznet-theme-law01-container'));
assert(str_contains($cssSource, 'var(--aznet-theme-container-shell)'), 'Law 01 inner content must remain constrained while section surfaces go full width.');

$assets = file_get_contents($root . '/inc/theme/assets.php');
assert(str_contains($assets, 'homepage-law-01.css'));
assert(str_contains($assets, "'law-01'"));
assert(str_contains($assets, 'homepage_composer_active()'));
assert(str_contains($assets, 'function asset_content_version('), 'Theme must provide content-derived asset cache busting for changed scoped assets.');
assert(str_contains($assets, "asset_content_version( '/assets/css/components/homepage-law-01.css', \$version )"), 'Law 01 stylesheet URL must change when its bytes change even if Theme metadata version is unchanged.');
assert(str_contains($assets, "hash_file( 'sha256', \$path )"), 'Law 01 cache key must derive from file bytes rather than only the Theme version.');

$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');
$order = ['hero','services','about','team','topics','latest','analysis','news','process','faq','final-cta'];
foreach ($order as $slug) {
    $path = $root . '/template-parts/homepage/law-01/' . $slug . '.php';
    assert(is_file($path), "Missing Law 01 section {$slug}");
}
$all = '';
foreach (glob($root . '/template-parts/homepage/law-01/*.php') as $file) { $all .= file_get_contents($file); }
foreach (['500+', '98%', '1.000+', '1000+', 'Nguyễn Văn A', 'testimonial', 'certification'] as $forbidden) {
    assert(! str_contains($all, $forbidden), "Fabricated default found: {$forbidden}");
}
assert(str_contains($all, 'homepage_direct_published_children'));
assert(str_contains($all, 'homepage_latest_posts'));
assert(str_contains($all, 'homepage_ledger_ids'));
assert(str_contains($all, 'homepage_ledger_add'));
assert(str_contains($all, 'contact_surface_model'));

// Client-variant contract: generic presentation capability, never a Tâm Đức data fork.
$settingsSource = file_get_contents($root . '/inc/theme/settings.php');
assert(str_contains($settingsSource, "'homepage_law01_variant'"), 'Law 01 must expose a Theme-owned presentation variant setting.');
assert(str_contains($settingsSource, "'navy-gold'"));
assert(str_contains($settingsSource, "'burgundy-gold'"));

$homepageAdmin = file_get_contents($root . '/inc/admin/homepage.php');
assert(str_contains($homepageAdmin, 'homepage_law01_variant'), 'Homepage admin must expose the Law 01 variant selector.');
assert(str_contains($homepageAdmin, 'Burgundy + Gold'));
assert(str_contains($composer, 'homepage_law01_variant()'), 'Composer must project the normalized variant into presentation markup.');
$variantPath = $root . '/assets/css/components/homepage-law-01-variants.css';
assert(is_file($variantPath), 'Law 01 variant stylesheet must exist.');
$variantCss = file_get_contents($variantPath);
assert(str_contains($variantCss, '.aznet-theme-homepage--law-01-burgundy-gold'), 'Burgundy variant styling must be scoped to Law 01.');
assert(str_contains($assets, 'homepage-law-01-variants.css'), 'Variant asset must be surface-aware and loaded only with Law 01.');
foreach ([
    'grid-template-columns: minmax(0, 46%) minmax(0, 54%);',
    'padding: clamp(2.75rem, 4.2vw, 4.5rem) clamp(2rem, 3.4vw, 4.25rem);',
    'font-size: clamp(2.55rem, 3.6vw, 3.4rem);',
    'overflow-wrap: normal;',
    'word-break: normal;',
] as $needle) {
    assert(str_contains($variantCss, $needle), "Law 01 Hero title responsive-wrap regression missing: {$needle}");
}
assert(! str_contains($variantCss, 'max-width: 10.5ch;'), 'Legacy Law 01 Hero title must not be artificially capped to a narrow character width.');
assert(! str_contains($variantCss, 'max-width: 13ch;'), 'D-030 Hero Library title must not inherit the old narrow character cap.');
assert(str_contains($variantCss, '.aznet-theme-law01-hero--media-left .aznet-theme-homepage-hero-content__layout { grid-template-columns: minmax(0, 46%) minmax(0, 54%); }'), 'Media-left Hero must preserve the wider copy column after reversing media/copy order.');

foreach ([
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-services {',
    'border-top: 1px solid rgba(143, 17, 27, .1);',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-editorial {',
    'background: linear-gradient(180deg, #fffdf8 0%, #fff8ee 100%);',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-article-card {',
    'box-shadow: 0 .55rem 1.6rem rgba(74, 46, 31, .06);',
] as $needle) {
    assert(str_contains($variantCss, $needle), "Final Law 01 visual polish contract missing: {$needle}");
}

$setupSource = file_get_contents($root . '/inc/theme/setup.php');
$headerTemplate = file_get_contents($root . '/template-parts/header/site-header.php');
$utilityCssPath = $root . '/assets/css/components/header-utility.css';
assert(str_contains($setupSource, "'header-utility'"), 'WordPress must own hotline/contact links through a native menu location.');
assert(str_contains($headerTemplate, "header/utility-navigation"), 'Header must render the optional utility menu without storing contact truth in Theme settings.');
assert(is_file($utilityCssPath), 'Header utility presentation must have its own surface-aware asset.');
$utilityCss = file_get_contents($utilityCssPath);
assert(str_contains($utilityCss, '.aznet-theme-site-header__utility-menu'), 'Header utility links need presentation on every surface where the menu is present.');
assert(str_contains($utilityCss, '.aznet-theme-site-header__utility-nav'), 'Header utility navigation layout must be isolated in its component asset.');
assert(str_contains($assets, 'enqueue_header_utility_asset'), 'Header utility asset must use capability-driven loading.');
assert(str_contains($assets, "has_nav_menu( 'header-utility' )"), 'Header utility CSS must not load when its WordPress menu is absent.');

$heroSource = file_get_contents($root . '/template-parts/homepage/law-01/hero.php');
$servicesSource = file_get_contents($root . '/template-parts/homepage/law-01/services.php');
$teamSource = file_get_contents($root . '/template-parts/homepage/law-01/team.php');
assert(str_contains($heroSource, "setting( 'homepage_hero_block', 0 )"), 'Hero must prefer the WordPress-native synced Hero block reference.');
assert(str_contains($heroSource, 'homepage_block_reference'), 'Hero synced content must resolve through the bounded wp_block helper.');
assert(str_contains($heroSource, "setting( 'homepage_hero_page', 0 )"), 'Legacy Hero Page reference must remain for compatibility.');
assert(str_contains($heroSource, 'homepage_page_reference'), 'Legacy Hero Page source must still resolve through the bounded Page helper.');
assert(str_contains($heroSource, "get_bloginfo( 'description' )"), 'Legacy Site Tagline fallback must preserve existing Law 01 Hero presentation when the dedicated Hero Page is unmapped.');
assert(str_contains($servicesSource, 'get_the_excerpt( $parent )'), 'Services intro must come from the mapped Services Page excerpt.');
assert(str_contains($teamSource, 'homepage_direct_published_children'), 'Team presentation must use WordPress-owned child Pages when available.');
assert(str_contains($teamSource, 'get_the_post_thumbnail'), 'Team cards must support WordPress featured images.');
foreach (['Tâm Đức', 'Tam Duc', 'Trọn Tâm với khách', 'Vẹn Đức với nghề'] as $clientString) {
    assert(! str_contains($settingsSource . $composer . $setupSource . $headerTemplate . $all, $clientString), "Client data must not be hard-coded into Theme production code: {$clientString}");
}

echo "PASS: Law 01 presentation contract\n";
