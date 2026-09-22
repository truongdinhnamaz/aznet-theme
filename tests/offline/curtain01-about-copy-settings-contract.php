<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$defaults = \AZnet\Theme\settings_defaults();
foreach (['homepage_about_kicker', 'homepage_about_heading', 'homepage_about_quote'] as $key) {
    if (! array_key_exists($key, $defaults) || '' !== $defaults[$key]) {
        fwrite(STDERR, "FAIL: {$key} default must exist and be empty.\n");
        exit(1);
    }
}

$normalized = \AZnet\Theme\normalize_settings([
    'homepage_about_kicker' => '  Hơn hai thập kỷ gìn giữ nghề rèm  ',
    'homepage_about_heading' => 'Hai thế hệ, một nếp nghề',
    'homepage_about_quote' => 'Hãy mang đến cho khách hàng nhiều giá trị hơn số tiền họ bỏ ra.',
]);

if ('Hơn hai thập kỷ gìn giữ nghề rèm' !== $normalized['homepage_about_kicker']) {
    fwrite(STDERR, "FAIL: homepage_about_kicker must normalize trimmed text.\n");
    exit(1);
}
if ('Hai thế hệ, một nếp nghề' !== $normalized['homepage_about_heading']) {
    fwrite(STDERR, "FAIL: homepage_about_heading must normalize text.\n");
    exit(1);
}
if ('Hãy mang đến cho khách hàng nhiều giá trị hơn số tiền họ bỏ ra.' !== $normalized['homepage_about_quote']) {
    fwrite(STDERR, "FAIL: homepage_about_quote must normalize text.\n");
    exit(1);
}

$invalid = \AZnet\Theme\normalize_settings([
    'homepage_about_heading' => ['bad'],
]);

if ('' !== $invalid['homepage_about_heading']) {
    fwrite(STDERR, "FAIL: non-string Homepage About copy must normalize empty.\n");
    exit(1);
}

echo "PASS: Curtain 01 About presentation copy settings contract\n";
