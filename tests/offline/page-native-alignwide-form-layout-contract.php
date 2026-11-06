<?php
/**
 * Generic native Page nested alignwide and embedded form presentation guard.
 *
 * Gutenberg and form plugins own content, semantics, data, and submission.
 * Theme owns only the computed layout of their public HTML projection.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$pageCss = file_get_contents($root . '/assets/css/components/page.css');
if (! is_string($pageCss) || '' === $pageCss) {
    fwrite(STDERR, "FAIL: unable to read Page presentation CSS\n");
    exit(1);
}

$fail = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
};

$nestedSelector = '.aznet-theme-page__content-inner .wp-block-group.alignwide > .wp-block-columns.alignwide';
$nestedPattern = '~' . preg_quote($nestedSelector, '~') . '\\s*\\{([^}]*)\\}~';
$nestedMatches = [];
$fail(1 === preg_match($nestedPattern, $pageCss, $nestedMatches),
    'nested native Gutenberg alignwide Columns layout guard missing');
$nestedBody = (string) ($nestedMatches[1] ?? '');
$fail(str_contains($nestedBody, 'transform: none;'),
    'nested wide Columns must not translate a second time');
$fail(! str_contains($nestedBody, '!important') && ! str_contains($nestedBody, 'overflow: hidden'),
    'nested alignwide must not depend on important flags or clipped content');

$formSelector = '.aznet-theme-page__content-inner :where(';
$selectorStart = strpos($pageCss, $formSelector);
$fail(false !== $selectorStart, 'generic Page-embedded form control selector missing');
$brace = strpos($pageCss, '{', $selectorStart);
$end = false === $brace ? false : strpos($pageCss, '}', $brace);
$fail(false !== $brace && false !== $end, 'form presentation CSS rule malformed');
$selectorText = substr($pageCss, $selectorStart, $brace - $selectorStart);
$formBody = substr($pageCss, $brace + 1, $end - $brace - 1);
foreach (['form input:not([type="submit"])', 'form textarea', 'form select'] as $marker) {
    $fail(str_contains($selectorText, $marker), 'native Page form selector missing ' . $marker);
}
foreach (['max-width: 100%;', 'box-sizing: border-box;'] as $marker) {
    $fail(str_contains($formBody, $marker), 'form control box constraint missing ' . $marker);
}
$fail(! str_contains($selectorText, 'wpcf7') && ! str_contains($formBody, '!important'),
    'generic forms must not depend on a provider or important flags');

echo "PASS: generic Page nested-alignwide and form field viewport constraints\n";
