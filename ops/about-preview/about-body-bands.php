<?php
/**
 * Retained About preview body renderer.
 *
 * This is review evidence / recovery source for the WPVibe draft only.
 * It is not loaded automatically by the canonical Theme.
 *
 * Preconditions:
 * - $raw_content contains the current WordPress-authored About Page content.
 * - Hero rendering remains in page-templates/about-preview.php.
 */
$about_blocks   = parse_blocks( $raw_content );
$about_sections = [];

foreach ( $about_blocks as $about_block ) {
    $about_classes = isset( $about_block['attrs']['className'] ) ? (string) $about_block['attrs']['className'] : '';

    if (
        'core/group' === $about_block['blockName']
        && str_contains( $about_classes, 'aznet-theme-page-kit--about' )
    ) {
        $about_sections = $about_block['innerBlocks'];
        break;
    }
}

if ( empty( $about_sections ) ) {
    $about_sections = $about_blocks;
}

$about_variants = [
    'aznet-theme-page-kit__intro'        => 'intro',
    'aznet-theme-page-kit__story'        => 'story',
    'aznet-theme-page-kit__principles'   => 'principles',
    'aznet-theme-page-kit__process'      => 'process',
    'aznet-theme-page-kit__capabilities' => 'capabilities',
    'aznet-theme-page-kit__team-teaser'  => 'team',
    'aznet-theme-page-kit__trust'        => 'trust',
    'aznet-theme-page-kit__cta'          => 'cta',
];
?>
<div class="aznet-theme-page-kit aznet-theme-page-kit--about aznet-theme-about-bands">
    <?php foreach ( $about_sections as $about_section ) : ?>
        <?php
        $section_classes = isset( $about_section['attrs']['className'] ) ? (string) $about_section['attrs']['className'] : '';
        $section_variant = '';

        foreach ( $about_variants as $class_name => $variant_name ) {
            if ( str_contains( $section_classes, $class_name ) ) {
                $section_variant = $variant_name;
                break;
            }
        }

        if ( '' === $section_variant ) {
            echo render_block( $about_section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered WordPress block markup.
            continue;
        }
        ?>
        <section class="aznet-theme-about-band aznet-theme-about-band--<?php echo esc_attr( $section_variant ); ?>">
            <div class="aznet-theme-about-shell aznet-theme-about-band__inner">
                <?php echo render_block( $about_section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered WordPress block markup. ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
