<?php
/**
 * Wellness 01 presentation-only pattern regression, PHP 8.1+ (no WordPress runtime).
 * RED when pattern/assets are absent; GREEN on source-level contracts only.
 */
$root = dirname(__DIR__, 2);
$path = $root . '/patterns/homepage-wellness-01.php';
if (!is_file($path)) {
    fwrite(STDERR, "RED: missing Wellness 01 pattern\n");
    exit(1);
}
$source = file_get_contents($path);
$checks = [
    'native slug' => str_contains($source, 'Slug: aznet-theme/homepage-wellness-01'),
    'native category' => str_contains($source, 'Categories: aznet-theme-pages'),
    'root is Gutenberg group' => preg_match('/\?>\s*<!-- wp:group\b/s', $source) === 1,
    'one H1' => preg_match_all('/<h1\b/', $source) === 1,
    'program anchor' => str_contains($source, 'id="aznet-theme-wellness-programs"'),
    'contact anchor' => str_contains($source, 'id="aznet-theme-wellness-contact"'),
    'native editorial query and FAQ' => str_contains($source, '<!-- wp:query ') && substr_count($source, '<!-- wp:details') >= 2,
    'seven native image blocks' => substr_count($source, '<!-- wp:image ') === 7 && substr_count($source, '<!-- /wp:image -->') === 7,
    'seven public Theme file references' => substr_count($source, 'get_theme_file_uri(') === 7 && substr_count($source, 'esc_url( get_theme_file_uri(') === 7,
    'visible AI photo disclaimer' => str_contains($source, 'Ảnh minh họa được tạo bằng AI; không đại diện cho cơ sở'),
    'no external scripts or iframe' => !preg_match('~<script\b|<iframe\b~i', $source),
    'no private identity/commerce/booking state' => !preg_match('/get_option\s*\(|get_post_meta\s*\(|update_post_meta\s*\(|\$wpdb|_rootprofile_|_choiceguide_|appointment_status|booking_capacity|wc_get_product|WC\(/i', $source),
    'no route takeover/hook registration' => !preg_match('/template_redirect|template_include|add_action\s*\(|add_filter\s*\(/i', $source),
    'no hardcoded external hotlink' => !preg_match('~(?:https?:)?//[^\s\"\x27]+\.(?:jpe?g|webp|png)~i', $source),
];
$matches = [];
preg_match_all('/<!--\s*(\/?)wp:([a-z][a-z0-9\/-]*)(.*?)-->/s', $source, $matches, PREG_SET_ORDER);
$stack = [];
$valid = true;
foreach ($matches as $m) {
    $closing = '/' === $m[1];
    $attrs = trim($m[3]);
    $self = !$closing && str_ends_with($attrs, '/');
    if ($self) {
        $attrs = trim(substr($attrs, 0, -1));
    }
    if ('' !== $attrs) {
        json_decode($attrs, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $valid = false;
            break;
        }
    }
    if ($closing) {
        if (array_pop($stack) !== $m[2]) {
            $valid = false;
            break;
        }
    } elseif (!$self) {
        $stack[] = $m[2];
    }
}
$checks['balanced Gutenberg markers/JSON'] = $valid && !$stack && count($matches) > 100;
$markers = [];
preg_match_all('/<!--\s*\/?wp:([a-z][a-z0-9\/-]*)/', $source, $markers);
$checks['only Core WordPress blocks'] = count(array_filter($markers[1], static fn($name) => str_contains($name, '/'))) === 0;
$ids = [];
$hrefs = [];
preg_match_all('/\bid="([^"]+)"/', $source, $ids);
preg_match_all('/\bhref="#([^"]+)"/', $source, $hrefs);
$checks['unique IDs and resolved CTA anchors'] = count($ids[1]) === count(array_unique($ids[1])) && !array_diff($hrefs[1], $ids[1]);
$relative = [];
preg_match_all("~get_theme_file_uri\( '(/assets/starter/wellness01/[a-z-]+\\.webp)' \)~", $source, $relative);
$expected = ['group', 'guide', 'meditation', 'mobility'];
$found = [];
foreach ($relative[1] as $ref) {
    $name = basename($ref, '.webp');
    $found[] = $name;
    $asset = $root . $ref;
    if (!is_file($asset) || filesize($asset) < 10000 || filesize($asset) > 200000) {
        $checks['all four optimized local assets'] = false;
    }
    $fp = is_file($asset) ? fopen($asset, 'rb') : false;
    if ($fp) {
        $head = fread($fp, 12);
        fclose($fp);
        if (substr($head, 0, 4) !== 'RIFF' || substr($head, 8, 4) !== 'WEBP') {
            $checks['valid WebP headers'] = false;
        }
    }
}
$checks['only four curated photo assets referenced'] = count($found) === 7 && array_diff(array_unique($found), $expected) === [] && count(array_unique($found)) === 4;
$checks['all four optimized local assets'] = $checks['all four optimized local assets'] ?? true;
$checks['valid WebP headers'] = $checks['valid WebP headers'] ?? true;
$checks['no fabricated staff identification'] = !preg_match('/(Chuyên gia|HLV)\s+[A-Z][a-z]+\s+[A-Z][a-z]+/', $source);
foreach ($checks as $name => $ok) {
    if (!$ok) {
        fwrite(STDERR, "FAIL: $name\n");
        exit(1);
    }
}
echo 'PASS: Wellness 01 source/asset contract (' . count($checks) . " checks)\n";
