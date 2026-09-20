<?php
declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    function get_theme_file_uri(string $path): string { return 'https://example.test/theme' . $path; }
}

namespace AZnet\Theme {
    require dirname(__DIR__, 2) . '/inc/theme/law01-reference.php';

    $must = static function (bool $ok, string $message): void {
        if (!$ok) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    };

    $must(function_exists(__NAMESPACE__ . '\\law01_reference_art_urls'), 'Law 01 reference art helper must exist');

    $hero = law01_reference_art_urls('hero');
    $team = law01_reference_art_urls('team');
    $articles = law01_reference_art_urls('articles');

    $must(count($hero) === 1, 'Law 01 reference art must expose one generic Hero visual');
    $must(count($team) === 4, 'Law 01 reference art must expose four illustrative Team portraits');
    $must(count($articles) === 3, 'Law 01 reference art must expose three generic article visuals');

    foreach (array_merge($hero, $team, $articles) as $url) {
        $must(str_starts_with($url, 'https://example.test/theme/assets/images/law01-reference/'), 'reference art must stay inside Theme presentation assets');
        $must(str_ends_with($url, '.svg'), 'reference art wrapper must remain a browser-native SVG asset');
    }

    $source = file_get_contents(dirname(__DIR__, 2) . '/template-parts/homepage/law-01/profile.php');
    $must(is_string($source) && str_contains($source, 'law01_reference_art_urls'), 'Team presentation must consume the scoped reference-art helper');
    $must(is_string($source) && str_contains($source, 'Hình ảnh minh họa'), 'AI portrait fallback must visibly disclose that it is illustrative');
    $must(is_string($source) && str_contains($source, '$has_members'), 'real WordPress Team member records must keep precedence');

    echo "PASS: Law 01 generated reference art remains scoped, illustrative and source-safe\n";
}
