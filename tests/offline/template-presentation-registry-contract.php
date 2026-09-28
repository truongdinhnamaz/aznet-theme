<?php
/** Core C1 Template Presentation Registry contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';

foreach ([
    'AZnet\\Theme\\load_local_template_manifests',
    'AZnet\\Theme\\template_presentation_ids',
    'AZnet\\Theme\\visual_preset_ids',
    'AZnet\\Theme\\homepage_preset_ids',
] as $function) {
    if (! function_exists($function)) {
        fwrite(STDERR, "FAIL: missing C1 function {$function}.\n");
        exit(1);
    }
}

echo "PASS: Core C1 Template Presentation Registry contract\n";
