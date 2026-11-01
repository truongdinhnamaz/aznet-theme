<?php
/** Native-menu nested group UX contract: progressive enhancement, no domain state. */
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$fail = static function (bool $good, string $reason): void {
    if (!$good) { fwrite(STDERR, "FAIL: {$reason}\n"); exit(1); }
};
$script = $root . '/assets/js/catalog-navigation.js';
$css = (string) file_get_contents($root . '/assets/css/components/catalog-navigation.css');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$fail(is_file($script), 'optional catalog submenu script must exist');
$js = (string) file_get_contents($script);
foreach (['.aznet-theme-catalog-navigation__menu', '.sub-menu', 'aria-expanded', 'aria-controls', 'Escape', 'hidden', 'addEventListener'] as $token) {
    $fail(str_contains($js, $token), 'submenu progressive enhancement missing: ' . $token);
}
$fail(str_contains($assets, '/assets/js/catalog-navigation.js'), 'feature-only asset must be enqueued');
$fail(str_contains($css, '.aznet-theme-catalog-navigation__submenu-toggle'), 'submenu button must be visible and focusable');
$fail(str_contains($css, '.sub-menu[hidden]'), 'closed nested groups must not remain visible');
$fail(!str_contains($js, 'get_terms(') && !str_contains($js, 'fetch('), 'no private Woo or remote catalog discovery');
// The icon-only disclosure must not expose its visual +/- glyph as visible
// button text while assigning an unrelated action aria-label. Its CSS icon
// must remain decorative and retain the collapsed/expanded affordance.
$fail(str_contains($js, "const icon = document.createElement('span');"), 'submenu needs a dedicated decorative icon');
$fail(str_contains($js, "icon.setAttribute('aria-hidden', 'true');"), 'submenu icon must be hidden from accessible name');
$fail(str_contains($js, 'button.appendChild(icon);'), 'submenu button must contain its icon');
$fail(!preg_match('/button\\.textContent\\s*=/', $js), 'submenu must not inject +/- as visible button text');
$fail(str_contains($css, '.aznet-theme-catalog-navigation__submenu-icon::before'), 'submenu horizontal icon stroke missing');
$fail(str_contains($css, '.aznet-theme-catalog-navigation__submenu-icon::after'), 'submenu vertical icon stroke missing');
$fail(str_contains($css, '[aria-expanded="true"] .aznet-theme-catalog-navigation__submenu-icon::after'), 'expanded submenu must show minus icon');
echo "PASS: accessible nested WordPress catalog groups\n";
