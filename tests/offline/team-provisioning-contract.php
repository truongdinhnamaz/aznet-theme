<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$blueprints = file_get_contents($root . '/inc/theme/provisioning-blueprints.php');
$admin = file_get_contents($root . '/inc/admin/provisioning.php');
$readiness = file_get_contents($root . '/inc/theme/provisioning-readiness.php');

if (! str_contains((string) $blueprints, "'team' => \$page('Đội ngũ của chúng tôi'")) {
    fwrite(STDERR, "FAIL: Law 01 Team parent title not updated\n");
    exit(1);
}

if (! str_contains((string) $admin, "[ 'home','about','services','team','contact' ]")) {
    fwrite(STDERR, "FAIL: Team is not required for Law 01 provisioning\n");
    exit(1);
}

if (! str_contains((string) $readiness, "[ 'services', 'about', 'team', 'contact' ]")) {
    fwrite(STDERR, "FAIL: Team is not required by Law 01 readiness\n");
    exit(1);
}

if (str_contains((string) $readiness, "[ 'team', 'process', 'faq' ]")) {
    fwrite(STDERR, "FAIL: Team is still treated as optional readiness state\n");
    exit(1);
}

foreach (['team_member', 'starter_team_member', 'sample_lawyer'] as $forbidden) {
    if (str_contains((string) $blueprints, $forbidden)) {
        fwrite(STDERR, "FAIL: provisioning fabricates Team people: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Law 01 provisions Team parent only\n";
