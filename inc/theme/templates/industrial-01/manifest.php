<?php
/** Industrial 01 reusable presentation manifest. */
return [
    'contract_version' => 1,
    'id'               => 'industrial-01',
    'name'             => 'Industrial 01',
    'version'          => '1.0.0',
    'category'         => 'commerce',
    'description'      => 'Technical B2B industrial presentation.',
    'capabilities'     => [ 'homepage', 'woocommerce' ],
    'presentation'     => [
        'visual_preset'   => 'industrial-01',
        'homepage_preset' => 'industrial-01',
    ],
    'assets'           => [],
    'homepage'         => [
        'hero' => [
            'enabled'        => true,
            'admin_renderer' => 'render_industrial01_hero_editor',
            'content_owner'  => 'theme-legacy-compat',
            'source_type'    => 'presentation_settings',
        ],
    ],
    'provisioning'     => [],
];
