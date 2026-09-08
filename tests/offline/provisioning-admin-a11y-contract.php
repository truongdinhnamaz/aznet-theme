<?php
$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/provisioning.php');
if (false === $admin) { throw new RuntimeException('provisioning admin source unreadable'); }
foreach ([
    '<fieldset class="aznet-theme-provision-row"><legend>',
    'class="screen-reader-text" for="\' . esc_attr( $action_id )',
    'class="screen-reader-text" for="\' . esc_attr( $object_id )',
    'id="\' . esc_attr( $action_id )',
    'id="\' . esc_attr( $object_id )',
    'for="aznet-theme-provision-menu-action"',
    'id="aznet-theme-provision-menu-action"',
    'for="aznet-theme-provision-menu-id"',
    'id="aznet-theme-provision-menu-id"',
] as $needle) {
    if (!str_contains($admin, $needle)) { throw new RuntimeException('missing accessible provisioning control marker: ' . $needle); }
}
echo "PASS: Provisioning wizard controls have explicit accessible labels\n";
