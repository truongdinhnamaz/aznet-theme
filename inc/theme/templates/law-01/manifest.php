<?php
/** Law 01 reusable presentation manifest. */
return [
    'contract_version' => 1,
    'id'               => 'law-01',
    'name'             => 'Law 01',
    'version'          => '1.0.0',
    'category'         => 'professional',
    'description'      => 'Professional legal/editorial presentation.',
    'capabilities'     => [ 'homepage' ],
    'presentation'     => [
        'homepage_preset' => 'law-01',
    ],
    'assets'           => [],
    'homepage'         => [
        'hero' => [
            'enabled'        => true,
            'admin_renderer' => 'render_homepage_hero_library',
            'content_owner'  => 'wordpress',
            'source_type'    => 'wp_block',
        ],
    ],
    'provisioning'     => [],
];
