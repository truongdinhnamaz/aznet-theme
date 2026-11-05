<?php
/**
 * Regression: templates provide Footer content, never Footer presentation.
 * One shared Theme Core component applies to every AZnet site template.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL universal Footer: {$reason}\n");
    exit(1);
};
$source = static function (string $path) use ($root, $fail): string {
    $data = file_get_contents($root . '/' . $path);
    if (! is_string($data)) {
        $fail('missing source ' . $path);
    }
    return $data;
};

$shared = $source('assets/css/components/site-footer.css');
foreach ([
    '.aznet-theme-site-footer {',
    '.aznet-theme-site-footer__inner {',
    '.aznet-theme-site-footer__main {',
    '.aznet-theme-site-footer__bottom {',
    '.aznet-theme-site-footer__brand:has(.aznet-theme-site-footer__logo) {',
    'background: #ffffff;',
] as $needle) {
    if (! str_contains($shared, $needle)) {
        $fail('shared Footer component missing: ' . $needle);
    }
}

$cssFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/assets/css'));
foreach ($cssFiles as $file) {
    if (! $file->isFile() || 'css' !== $file->getExtension()) {
        continue;
    }
    $path = substr($file->getPathname(), strlen($root) + 1);
    if ('assets/css/components/site-footer.css' === $path) {
        continue;
    }
    if (str_contains((string) file_get_contents($file->getPathname()), '.aznet-theme-site-footer')) {
        $fail('template stylesheet still owns Footer visuals: ' . $path);
    }
}

$admin = $source('assets/css/admin/control-center.css');
if (preg_match('/\\.aznet-theme-footer-(?:template-card|live-preview)--(?:minimal|classic|professional|split|centered|compact)\\b/', $admin)) {
    $fail('Footer content profiles still produce different visual previews');
}
$gallery = $source('inc/admin/control-center.php');
if (! str_contains($gallery, 'Footer dùng chung một thiết kế trên mọi mẫu website.')) {
    $fail('Footer administration must state the single-design contract');
}
if (str_contains($gallery, 'Bốn cột rõ ràng cho website dịch vụ và doanh nghiệp.')) {
    $fail('Content profile still described as an industry-specific Footer design');
}

$layout = $source('template-parts/footer/site-footer.php');
if (! str_contains($layout, 'footer_context()')
    || ! str_contains($layout, 'data-aznet-theme-footer-content-preset')
    || str_contains($layout, 'aznet-theme-site-footer--law-01')) {
    $fail('Footer template must be singular, with content-only profile');
}
$document = $source('footer.php');
if (! str_contains($document, "get_template_part( 'template-parts/footer/site-footer' );")) {
    $fail('document Footer must always render the canonical component');
}

echo "PASS: one Theme Core Footer, no template CSS variants, WordPress content retained\n";
