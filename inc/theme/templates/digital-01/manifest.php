<?php
/** Digital 01 independent agency / technology presentation manifest. */
return [
    'contract_version' => 1,
    'id' => 'digital-01',
    'name' => 'Digital 01',
    'version' => '1.0.0',
    'category' => 'Digital Agency / Công nghệ',
    'description' => 'Mẫu marketing, dịch vụ số, sản phẩm số và tri thức công nghệ.',
    'capabilities' => [ 'homepage', 'woocommerce' ],
    'presentation' => [
        'visual_preset' => 'digital-01',
        'homepage_preset' => 'digital-01',
    ],
    'assets' => [
        'homepage' => [
            [
                'type' => 'style',
                'handle' => 'aznet-theme-homepage-digital-01',
                'path' => '/assets/css/components/homepage-digital-01.css',
                'dependencies' => [ 'aznet-theme-tokens', 'aznet-theme-homepage' ],
            ],
        ],
    ],
    'homepage' => [
        'composition' => [ 'mode' => 'native' ],
        'authoring' => [ 'sections' => [ 'content' ] ],
        'hero' => [
            'enabled' => false,
            'content_owner' => 'wordpress',
            'source_type' => 'page_content',
        ],
    ],
    'provisioning' => [],
];
