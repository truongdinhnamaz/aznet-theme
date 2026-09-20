<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');
$brand = (string) file_get_contents($root . '/template-parts/header/brand.php');
$siteHeader = (string) file_get_contents($root . '/template-parts/header/site-header.php');
$mobilePanel = (string) file_get_contents($root . '/template-parts/header/mobile-panel.php');
$law01ConsultationPath = $root . '/template-parts/header/law01-consultation.php';
$law01Consultation = is_file($law01ConsultationPath) ? (string) file_get_contents($law01ConsultationPath) : '';
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$latest = (string) file_get_contents($root . '/template-parts/homepage/law-01/latest.php');
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');
$footer = (string) file_get_contents($root . '/template-parts/footer/site-footer.php');
$footerCss = (string) file_get_contents($root . '/assets/css/components/site-footer.css');

$must = static function (bool $condition, string $message): void {
    assert($condition, $message);
};

// Visual target approved from the supplied law-firm homepage reference:
// balanced light split Hero, compact four-item trust strip, and six-card services row.
$must(
    str_contains($css, 'grid-template-columns: minmax(0, 46%) minmax(0, 54%);'),
    'Law 01 target Hero must use the approved responsive 46/54 desktop split with the media column wider than copy.'
);
$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__content\\s*\\{[^}]*padding-inline-start:\\s*0;[^}]*padding-inline-end:\\s*clamp\\(2rem,\\s*3vw,\\s*3.5rem\\);/s',
        $css
    ),
    'Law 01 Hero copy must start on the shared shell edge and keep breathing room only toward the media seam.'
);
$must(
    str_contains($css, 'min-height: clamp(36rem, 38vw, 40rem);'),
    'Law 01 Hero media must use the approved tall desktop stage from the supplied reference.'
);
$must(
    str_contains($css, 'object-position: center center;'),
    'Law 01 Hero media must keep the supplied office composition centered inside the desktop crop.'
);
$heroImageSize = getimagesize($root . '/assets/starter/law01/hero.webp');
$must(
    false !== $heroImageSize && 1672 === $heroImageSize[0] && 941 === $heroImageSize[1],
    'Law 01 starter Hero must package the approved 1672x941 Tâm Đức office image.'
);
$must(
    str_contains(
        $css,
        '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-container { width: min(calc(100% - (2 * var(--aznet-theme-gutter))), 96rem);'
    ),
    'Law 01 reference composition must use one wide shared inner shell across the homepage.'
);
$must(
    0 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__grid\\s*\\{[^}]*(?:width|max-width)\\s*:/s',
        $css
    ),
    'Law 01 reference Hero surface may be full-width, but its inner grid must inherit the shared shell.'
);

$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__content\\s*\\{[^}]*background:\\s*transparent;/s',
        $css
    ),
    'Law 01 Hero text column must not paint its own boxed background against the full-width section surface.'
);
$must(
    str_contains($css, '--law01-hero-inline-bleed: max(var(--aznet-theme-gutter), calc((100vw - 96rem) / 2));'),
    'Law 01 Hero must derive the viewport bleed from the shared 96rem shell instead of hardcoding a second layout width.'
);
$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__visual\\s*\\{[^}]*width:\\s*calc\\(100% \\+ var\\(--law01-hero-inline-bleed\\)\\);[^}]*margin-inline-end:\\s*calc\\(-1 \\* var\\(--law01-hero-inline-bleed\\)\\);/s',
        $css
    ),
    'Law 01 Hero image surface must bleed to the viewport edge while the content grid stays on the shared shell.'
);

$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero h1\\s*\\{[^}]*width:\\s*100%;[^}]*max-width:\\s*none;[^}]*font-size:\\s*clamp\\(2\\.55rem,\\s*3\\.6vw,\\s*3\\.4rem\\);[^}]*line-height:\\s*1\\.02;[^}]*letter-spacing:\\s*-\\.025em;[^}]*overflow-wrap:\\s*normal;[^}]*word-break:\\s*normal;[^}]*text-wrap:\\s*balance;/s',
        $css
    ),
    'Law 01 Hero title must use the approved compact responsive typography without an artificial character-width cap.'
);
$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__visual::before\\s*\\{[^}]*width:\\s*clamp\\(5rem,\\s*8vw,\\s*9rem\\);[^}]*linear-gradient\\(90deg,[^}]*var\\(--law01-client-cream\\)[^}]*transparent/s',
        $css
    ),
    'Law 01 Hero image must use a soft cream-to-transparent transition layer so the image edge does not read as a hard seam.'
);
$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-hero__image\\s*\\{[^}]*transform:\\s*scale\\(1\\.02\\);[^}]*transform-origin:\\s*center;/s',
        $css
    ),
    'Law 01 Hero image must use the approved subtle crop polish so the visual reads as part of the scene rather than a pasted rectangle.'
);
$must(
    str_contains(
        $css,
        '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-profile > .aznet-theme-law01-container { display: grid; grid-template-columns: minmax(0, 48%) minmax(0, 52%);'
    ),
    'Law 01 reference Profile band must place About and Team side-by-side on wide desktop.'
);
$must(
    str_contains(
        $css,
        '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-profile__about-media { display: none; }'
    ),
    'Law 01 reference Profile band must keep the About side text-led instead of introducing a second large image panel.'
);
$must(
    1 === preg_match(
        '/\\.aznet-theme-homepage--law-01-burgundy-gold \\.aznet-theme-law01-profile__team-band\\s*\\{[^}]*margin-top:\\s*0;/s',
        $css
    ),
    'Law 01 reference Team band must sit beside About rather than below it.'
);
$must(
    str_contains($css, 'min-height: clamp(29rem, 32vw, 34rem);'),
    'Law 01 target Hero visual must keep the approved desktop image band proportion.'
);
$must(
    str_contains($css, 'min-height: 4.8rem;'),
    'Law 01 target trust strip must stay compact while retaining comfortable icon/text rhythm.'
);
$must(
    str_contains($hero, '$trust_icon_names = [ \'shield\', \'people\', \'scales\', \'handshake\' ];') &&
    str_contains($hero, 'aznet-theme-law01-hero__trust-icon--<?php echo esc_attr( $trust_icon_name ); ?>'),
    'Law 01 trust strip must use the approved shield, people, scales and handshake presentation icon set without changing source messages.'
);
$must(
    str_contains($css, '.aznet-theme-law01-hero__trust-icon--handshake svg'),
    'Law 01 trust strip must include the reference handshake icon sizing hook.'
);
$must(
    str_contains($css, 'grid-template-columns: repeat(6, minmax(0, 1fr));'),
    'Law 01 target services must retain six cards in one desktop row.'
);
$must(
    str_contains($css, 'min-height: 12.25rem;'),
    'Law 01 target service cards must use the approved compact card density.'
);

$must(
    str_contains($brand, 'aznet-theme-site-header__brand-title'),
    'Law 01 reference Header must be able to show the WordPress site title beside a logo mark.'
);
$must(
    str_contains($css, '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-site-header__brand-title'),
    'Law 01 reference Header must style the site-title lockup beside the logo.'
);
$must(
    str_contains($brand, 'aznet-theme-site-header__brand-copy') &&
    str_contains($brand, 'aznet-theme-site-header__brand-kicker'),
    'Law 01 Header brand must support a second presentation line without splitting or duplicating WordPress site identity.'
);
$must(
    str_contains($siteHeader, "get_template_part( 'template-parts/header/law01-consultation'") &&
    str_contains($siteHeader, "get_template_part( 'template-parts/header/utility-navigation'"),
    'Law 01 desktop Header must use a mapped consultation action while retaining generic Header utility navigation outside the Law01 homepage.'
);
$must(
    str_contains($mobilePanel, "get_template_part( 'template-parts/header/law01-consultation'") &&
    str_contains($mobilePanel, "get_template_part( 'template-parts/header/utility-navigation'"),
    'Law 01 mobile Header must preserve the same fail-soft consultation/generic utility split.'
);
$must(
    '' !== $law01Consultation &&
    str_contains($law01Consultation, "setting( 'homepage_contact_page', 0 )") &&
    str_contains($law01Consultation, 'homepage_page_reference') &&
    str_contains($law01Consultation, 'Yêu cầu tư vấn'),
    'Law 01 consultation action must resolve only the mapped public WordPress Contact Page and fail-soft when it is absent.'
);
$must(
    str_contains($profile, '$has_team = $team instanceof \\WP_Post;') &&
    str_contains($profile, 'aznet-theme-law01-profile__container--about-only') &&
    str_contains($profile, 'if ( $team instanceof \\WP_Post ) :') &&
    str_contains($profile, 'if ( [] !== $members ) :'),
    'Law 01 Profile must keep the mapped Team category visible without fabricating member cards when no legitimate members exist.'
);
$must(
    str_contains($css, '.aznet-theme-law01-profile__container--about-only'),
    'Law 01 Profile CSS must expand the inherited About presentation when the Team source is empty.'
);
$must(
    str_contains($css, '.aznet-theme-law01-profile__about-grid::after') &&
    str_contains($css, 'data:image/svg+xml'),
    'Law 01 About presentation must carry the approved decorative legal-scales watermark without introducing client-domain data.'
);

$must(
    ! str_contains($footer, 'homepage_composer_active') &&
    ! str_contains($footer, 'homepage_law01_variant') &&
    ! str_contains($footer, '$law01_homepage'),
    'Law 01 Homepage must not control or restyle the independent Theme Footer.'
);

$must(
    str_contains($profile, '$has_about = $about instanceof \\WP_Post;') &&
    str_contains($profile, '$has_team = $team instanceof \\WP_Post;') &&
    str_contains($profile, '$section_label = $has_team') &&
    str_contains($profile, "__( 'Giới thiệu và đội ngũ', 'aznet-theme' )") &&
    str_contains($profile, "__( 'Đội ngũ luật sư', 'aznet-theme' )") &&
    str_contains($profile, "__( 'Giới thiệu', 'aznet-theme' )") &&
    str_contains($profile, 'aria-label="<?php echo esc_attr( $section_label ); ?>"'),
    'Law 01 Profile accessibility label must describe the source-backed About/Team sections even when Team member cards are unavailable.'
);

$membersPos = strpos($profile, 'aznet-theme-law01-profile__members');
$teamCtaPos = strpos($profile, 'aznet-theme-law01-team-more');
$must(
    false !== $membersPos && false !== $teamCtaPos && $teamCtaPos > $membersPos,
    'Law 01 reference Team CTA must follow the portrait row rather than sit in the heading.'
);

$must(
    str_contains($hero, 'aznet-theme-law01-hero__contact-nav') && str_contains($hero, "has_nav_menu( 'header-utility' )"),
    'Law 01 reference Hero must be able to reuse the WordPress-owned phone/hotline menu below its CTAs.'
);
$must(
    str_contains($css, '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-services__intro { display: none; }'),
    'Law 01 reference Services band must visually omit the mapped excerpt in the compact reference composition.'
);
$must(
    str_contains($css, '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-hero__body > :last-child'),
    'Law 01 reference Hero body must support a normal description followed by an emphasized slogan.'
);
$must(
    str_contains($footer, 'aznet-theme-site-footer__brand-title') &&
    str_contains($footerCss, '.aznet-theme-site-footer__brand-title'),
    'Law 01 reference Footer must support a WordPress logo + site-title lockup.'
);

$must(
    str_contains($footer, 'aznet-theme-site-footer__brand-title') &&
    str_contains($footer, 'Thông tin liên hệ') &&
    str_contains($footer, 'Liên kết nhanh'),
    'Law 01 Homepage must inherit the same independent professional Footer structure used by other surfaces.'
);
$must(
    ! str_contains($css, '.aznet-theme-site-footer--law01-'),
    'Law 01 Homepage stylesheet must not contain Footer-specific presentation overrides.'
);
$must(
    str_contains($footer, 'if ( \'\' !== $primary_menu )') &&
    str_contains($footer, 'if ( \'professional\' !== $preset && \'\' !== $contact_menu )') &&
    str_contains($footer, 'if ( \'\' !== $social_menu || \'\' !== $policy_menu )'),
    'Independent Footer must keep empty WordPress menu projections fail-soft with no placeholder data.'
);


$must(
    1 === preg_match(
        '/\\.aznet-theme-site-header--law01-burgundy-gold \\.aznet-theme-site-header__utility-menu a\\s*\\{[^}]*background:\\s*var\\(--law01-client-burgundy\\);[^}]*color:\\s*#fff;/s',
        $css
    ),
    'Law 01 inherited Header utility action must read as the burgundy consultation CTA from the reference while preserving WordPress menu ownership.'
);
$must(
    str_contains(
        $css,
        '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-site-header__search button::before'
    ),
    'Law 01 inherited Header search must use a compact visual search affordance without replacing the semantic WordPress search form.'
);
$must(
    str_contains(
        $css,
        '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-article-card__body'
    ) && str_contains($css, 'min-height: 100%;'),
    'Law 01 inherited Latest cards must retain an equal-height body rhythm while continuing to render WordPress-native posts.'
);
$must(
    str_contains($latest, 'get_option( \'page_for_posts\', 0 )') &&
    str_contains($latest, 'get_post_status( $posts_page_id )') &&
    str_contains($latest, 'Xem tất cả bài viết'),
    'Law 01 Latest heading must expose a fail-soft WordPress Posts Page link instead of inventing an archive URL.'
);
$must(
    str_contains($latest, 'aznet-theme-law01-article-card__media-link'),
    'Law 01 Latest featured media must be part of the source-backed post link surface.'
);

echo "PASS: Law 01 Hero/Trust/Services visual parity contract\n";
