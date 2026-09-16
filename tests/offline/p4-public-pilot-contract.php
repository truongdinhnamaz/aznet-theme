<?php
$browser_path  = __DIR__ . '/../browser/p4-public-pilot-l4.mjs';
$workflow_path = __DIR__ . '/../../.github/workflows/p4-public-pilot.yml';

$fail = static function ( string $message ): void {
    fwrite( STDERR, "FAIL: {$message}\n" );
    exit( 1 );
};

if ( ! is_file( $browser_path ) ) {
    $fail( 'P4 public pilot browser harness is missing' );
}
if ( ! is_file( $workflow_path ) ) {
    $fail( 'P4 public pilot workflow is missing' );
}

$browser  = file_get_contents( $browser_path );
$workflow = file_get_contents( $workflow_path );

$required_browser_tokens = [
    'P4_BASE_URL',
    'P4_STATE_DIR',
    'wp-json/wp/v2/posts',
    'wp-json/wp/v2/pages',
    'wp-json/wp/v2/categories',
    '1440',
    '1024',
    '390',
    '320',
    '@axe-core/playwright',
    'scrollWidth',
    'clientWidth',
    "page.on('pageerror'",
    "page.on('console'",
    'requestfailed',
    'aznet-theme-profile-surface',
    'summary.json',
    'aznet-theme-article__content',
    'CONTENT_AUTHORED_SEMANTICS',
    'content_accessibility_observations',
];

foreach ( $required_browser_tokens as $token ) {
    if ( false === strpos( $browser, $token ) ) {
        $fail( "browser harness must contain {$token}" );
    }
}

if ( false === strpos( $browser, "violation.id === 'label'" ) ) {
    $fail( 'browser harness must narrowly attribute unlabeled authored-content form controls by axe label rule' );
}

foreach ( [ '/wp-admin', 'wp-login.php', '/ho-so/', '/gioi-thieu/' ] as $forbidden ) {
    if ( false !== strpos( $browser, $forbidden ) ) {
        $fail( "public pilot harness must not hard-code authenticated/private or inferred provider route {$forbidden}" );
    }
}

$required_workflow_tokens = [
    'P4 Public Pilot QA',
    'work/p4-public-pilot-qa',
    'tests/offline/p4-public-pilot-contract.php',
    'tests/browser/p4-public-pilot-l4.mjs',
    'https://tamduchanoi.aznet.vn',
    'actions/upload-artifact@v4',
];

foreach ( $required_workflow_tokens as $token ) {
    if ( false === strpos( $workflow, $token ) ) {
        $fail( "workflow must contain {$token}" );
    }
}

if ( preg_match( '/password|secret|credential|wp-admin|wp-login/i', $workflow ) ) {
    $fail( 'public pilot workflow must not require authenticated credentials or admin access' );
}

echo "PASS: P4 public pilot contract\n";
