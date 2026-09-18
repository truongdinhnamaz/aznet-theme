<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$files = [
    'css' => $root . '/assets/css/components/homepage-law-01-variants.css',
    'composer' => $root . '/inc/theme/homepage-composer.php',
    'hero' => $root . '/template-parts/homepage/law-01/hero.php',
    'services' => $root . '/template-parts/homepage/law-01/services.php',
    'profile' => $root . '/template-parts/homepage/law-01/profile.php',
    'latest' => $root . '/template-parts/homepage/law-01/latest.php',
];

foreach ($files as $label => $path) {
    assert(is_file($path), "Client-ready Law 01 {$label} file missing: {$path}");
}

$css = file_get_contents($files['css']);
$composer = file_get_contents($files['composer']);
$hero = file_get_contents($files['hero']);
$services = file_get_contents($files['services']);
$profile = file_get_contents($files['profile']);
$latest = file_get_contents($files['latest']);

foreach ([
    '--law01-client-burgundy:',
    '--law01-client-gold:',
    '.aznet-theme-law01-hero__trust',
    'grid-template-columns: repeat(6, minmax(0, 1fr));',
    '.aznet-theme-law01-profile__about-grid',
    '.aznet-theme-law01-profile__team-band',
    '.aznet-theme-law01-grid--articles',
    'grid-column: auto;',
    '.aznet-theme-site-header',
    '.aznet-theme-site-footer',
    '@media (max-width: 960px)',
    '@media (max-width: 640px)',
    '@media (prefers-reduced-motion: reduce)',
] as $needle) {
    assert(str_contains($css, $needle), "Client-ready Law 01 CSS contract missing: {$needle}");
}

assert(str_contains($composer, "[ 'hero', 'services', 'profile' ]"), 'Composer must render the combined client-ready profile band.');
assert(str_contains($composer, "'burgundy-gold' === \$variant ? [ 'latest' ] : [ 'topics', 'latest', 'analysis', 'news', 'process', 'faq', 'final-cta' ]"), 'Burgundy Law 01 visual closure must match the approved demo section sequence after native content.');
assert(! str_contains($composer, "[ 'hero', 'services', 'about', 'team' ]"), 'Composer must not render duplicate legacy About/Team sections.');

foreach ([
    "setting( 'homepage_hero_page', 0 )",
    'homepage_page_reference',
    'post_content',
    'aznet-theme-law01-hero__value',
    'aznet-theme-law01-hero__secondary-action',
    'aznet-theme-law01-hero__trust',
    'Tư vấn rõ ràng',
    'Giải pháp thực tiễn',
    'Bảo mật thông tin',
    'Đồng hành tận tâm',
] as $needle) {
    assert(str_contains($hero, $needle), "Client-ready Law 01 hero contract missing: {$needle}");
}
assert(str_contains($hero, 'if ( $hero instanceof \\WP_Post )'), 'Dedicated Hero Page must remain the preferred source.');
assert(str_contains($hero, "get_bloginfo( 'name' )"), 'Legacy Site Title fallback must preserve existing Hero presentation when the dedicated mapping is absent.');
assert(str_contains($hero, "get_bloginfo( 'description' )"), 'Legacy Site Tagline fallback must preserve the previous Hero slogan when the dedicated mapping is absent.');
assert(str_contains($hero, 'get_the_title( $front_id )'), 'Legacy Front Page title fallback must remain available for backward compatibility.');
assert(! str_contains($hero, "apply_filters( 'the_content'"), 'Hero source body must not re-enter global the_content filters.');
assert(str_contains($hero, 'do_blocks( $body )'), 'Hero Page body should render WordPress blocks without hijacking Front Page content filters.');

assert(! str_contains($hero, 'aznet-theme-law01-button aznet-theme-law01-button--secondary'), 'Hero Services CTA must not share the primary button selector used by retained browser verification.');
assert(str_contains($css, '.aznet-theme-law01-hero__secondary-action'), 'Client-ready Law 01 CSS must style the dedicated hero secondary CTA.');
assert(str_contains($css, '.aznet-theme-law01-hero__value'), 'Law 01 Hero must style the WordPress-native value proposition separately from the site title.');
foreach ([
    'aznet-theme-law01-hero__trust-icon',
    '<svg',
    'viewBox="0 0 24 24"',
] as $needle) {
    assert(str_contains($hero, $needle), "Law 01 demo-aligned trust strip icon contract missing: {$needle}");
}
foreach ([
    'min-height: 4.8rem;',
    'font-size: var(--aznet-theme-text-small);',
    '.aznet-theme-law01-hero__trust-icon {',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 demo-aligned trust strip presentation missing: {$needle}");
}

foreach ([
    '--law01-type-section-title: clamp(1.35rem, 1.55vw, 1.65rem);',
    '--law01-type-card-title: clamp(1.05rem, 1.25vw, 1.2rem);',
    '--law01-type-article-title: clamp(1.15rem, 1.5vw, 1.35rem);',
    '--law01-type-hero-value: clamp(1.45rem, 2vw, 1.9rem);',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 compact heading scale missing: {$needle}");
}


foreach ([
    'grid-template-columns: minmax(0, 48%) minmax(0, 52%);',
    'min-height: clamp(29rem, 32vw, 34rem);',
    '--law01-type-hero-title: clamp(2.7rem, 4.2vw, 3.4rem);',
    'font-size: var(--law01-type-hero-title);',
    'object-position: center 48%;',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 approved hero hierarchy missing: {$needle}");
}

foreach ([
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-section h2 {',
    'max-width: none;',
    'font-size: var(--law01-type-section-title);',
    'white-space: nowrap;',
    'text-wrap: nowrap;',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 homepage section heading system missing: {$needle}");
}

assert(str_contains($css, '.aznet-theme-law01-team .aznet-theme-law01-button--secondary'), 'Burgundy variant must override the higher-specificity Team secondary-button rule.');
assert(str_contains($css, 'color: var(--law01-client-ink);'), 'Burgundy Team secondary CTA must use a readable ink color on the light Team surface.');

assert(str_contains($services, 'aznet-theme-law01-card__badge'), 'Service cards must expose a presentation-only badge hook.');
foreach ([
    'aznet-theme-law01-card__icon',
    '<svg',
    'viewBox="0 0 24 24"',
] as $needle) {
    assert(str_contains($services, $needle), "Law 01 demo-aligned service icon contract missing: {$needle}");
}
assert(! str_contains($services, 'str_pad('), 'Demo-aligned Law 01 service cards must not render numeric presentation badges.');

foreach ([
    'Các dịch vụ pháp lý dành cho bạn',
    'aznet-theme-law01-services__heading',
] as $needle) {
    assert(str_contains($services, $needle), "Law 01 balanced services content hierarchy missing: {$needle}");
}
foreach ([
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-container',
    'width: min(calc(100% - (2 * var(--aznet-theme-gutter))), 96rem);',
    'min-height: 12.25rem;',
    'font-size: var(--law01-type-card-title);',
    'line-height: 1.65;',
    'white-space: nowrap;',
    'width: max-content;',
    'flex: 1 1 auto;',
    '.aznet-theme-law01-section h2.aznet-theme-law01-services__heading',
    'text-wrap: nowrap;',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 demo-aligned services presentation missing: {$needle}");
}

assert(str_contains($profile, 'aznet-theme-law01-profile__about-grid') && str_contains($profile, 'aznet-theme-law01-profile__team-band'), 'Profile band must retain both About and Team presentation in the shared reference band.');
foreach ([
    '.aznet-theme-law01-profile > .aznet-theme-law01-container { display: grid; grid-template-columns: minmax(0, 48%) minmax(0, 52%);',
    '.aznet-theme-law01-profile__about-media { display: none; }',
    '.aznet-theme-law01-profile__team-band {',
    'margin-top: 0;',
    '.aznet-theme-law01-profile__member-image {',
    'aspect-ratio: 4 / 5;',
    'object-position: center top;',
    '.aznet-theme-law01-profile__member {',
    'box-shadow: none;',
] as $needle) {
    assert(str_contains($css, $needle), "Law 01 demo-aligned Profile/Team presentation missing: {$needle}");
}
assert(str_contains($latest, "homepage_latest_posts( (array) setting( 'homepage_knowledge_terms', [] ), 3, homepage_ledger_ids() )"), 'Latest presentation must stay at three client-ready cards.');
assert(str_contains($latest, 'aznet-theme-law01-article-card__media'), 'Latest cards must render featured media when available.');
assert(str_contains($latest, 'get_the_category'), 'Latest cards must expose WordPress-owned taxonomy labels.');
assert(str_contains($latest, "'style' => 'display:block;width:100%;height:auto;'"), 'Latest featured media must constrain intrinsic image width for every Law 01 visual variant.');
assert(! str_contains($latest, '<a class="aznet-theme-law01-article-card__media"'), 'Latest media must not duplicate the canonical article URL.');

foreach (['500+', '95%', '98%', '1.000+', '1000+', 'Nguyễn Văn A', 'Nguyễn Văn Minh', 'Trần Thị Lan', 'Phạm Anh Tuấn', 'Lê Thị Hoa'] as $forbidden) {
    foreach ([$hero, $services, $profile, $latest, $css] as $surface) {
        assert(! str_contains($surface, $forbidden), "Fabricated trust/team content must not be introduced: {$forbidden}");
    }
}

echo "PASS: Law 01 client-ready redesign contract\n";
