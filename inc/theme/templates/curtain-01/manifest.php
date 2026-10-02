<?php
/** Rèm 01 reusable presentation manifest. */
return [
    'contract_version' => 1,
    'id'               => 'curtain-01',
    'name'             => 'Rèm 01',
    'version'          => '1.0.0',
    'category'         => 'Rèm / Nội thất',
    'description'      => 'Website rèm và giải pháp kiểm soát ánh sáng.',
    'capabilities'     => [ 'homepage', 'woocommerce' ],
    'presentation'     => [
        'visual_preset'   => 'curtain-01',
        'homepage_preset' => 'curtain-01',
    ],
    'assets'           => [],
    'homepage'         => [
        'hero' => [
            'enabled'        => true,
            'admin_renderer' => 'render_homepage_native_hero_editor',
            'content_owner'  => 'wordpress',
            'source_type'    => 'wp_block',
        ],
    ],
    'provisioning'     => [],
];
