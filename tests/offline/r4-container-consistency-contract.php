<?php
/**
 * Cross-surface container consistency regression.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$tokens = file_get_contents($root . '/assets/css/tokens.css');
$woo = file_get_contents($root . '/assets/css/components/woocommerce-archive.css');

if (false === $tokens || false === $woo) {
    fwrite(STDERR, "FAIL: unable to read container CSS sources\n");
    exit(1);
}

$token_contract = [
    '--aznet-theme-container-shell: 75rem;',
    '--aznet-theme-container-wide: 72rem;',
    '--aznet-theme-gutter: 2.5rem;',
    '--aznet-theme-gutter-mobile: 1.125rem;',
];

foreach ($token_contract as $needle) {
    if (! str_contains($tokens, $needle)) {
        fwrite(STDERR, "FAIL: missing container token contract {$needle}\n");
        exit(2);
    }
}

$woo_contract = [
    'body.woocommerce main#main',
    'width: min(calc(100% - (var(--aznet-theme-gutter) * 2)), var(--aznet-theme-container-shell));',
    'margin-inline: auto;',
    '@media (max-width: 47.999rem)',
    'width: min(calc(100% - (var(--aznet-theme-gutter-mobile) * 2)), var(--aznet-theme-container-shell));',
];

foreach ($woo_contract as $needle) {
    if (! str_contains($woo, $needle)) {
        fwrite(STDERR, "FAIL: Woo archive container contract missing {$needle}\n");
        exit(3);
    }
}

if (str_contains($woo, 'max-width: none')) {
    fwrite(STDERR, "FAIL: Woo archive must not opt out of the shared container\n");
    exit(4);
}

echo "PASS: cross-surface container consistency contract\n";
