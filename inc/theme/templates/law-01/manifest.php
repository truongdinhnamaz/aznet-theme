<?php
/** Law 01 reusable presentation manifest. */
return [
    'contract_version' => 1,
    'id'               => 'law-01',
    'name'             => 'Luật 01',
    'version'          => '1.0.0',
    'category'         => 'Luật',
    'description'      => 'Website dịch vụ pháp lý kết hợp nội dung chuyên môn.',
    'capabilities'     => [ 'homepage' ],
    'presentation'     => [
        'homepage_preset' => 'law-01',
    ],
    'assets'           => [
        'homepage' => [
            [
                'type'         => 'style',
                'handle'       => 'aznet-theme-team-card',
                'path'         => '/assets/css/components/team-card.css',
                'dependencies' => [ 'aznet-theme-tokens' ],
            ],
            [
                'type'         => 'style',
                'handle'       => 'aznet-theme-homepage-law-01',
                'path'         => '/assets/css/components/homepage-law-01.css',
                'dependencies' => [ 'aznet-theme-tokens', 'aznet-theme-team-card' ],
            ],
            [
                'type'         => 'style',
                'handle'       => 'aznet-theme-homepage-law-01-variants',
                'path'         => '/assets/css/components/homepage-law-01-variants.css',
                'dependencies' => [ 'aznet-theme-homepage-law-01' ],
            ],
        ],
    ],
    'homepage'         => [
        'authoring' => [
            'sections' => [ 'hero', 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact' ],
        ],
        'hero' => [
            'enabled'        => true,
            'admin_renderer' => 'render_homepage_hero_library',
            'content_owner'  => 'wordpress',
            'source_type'    => 'wp_block',
        ],
    ],
    'provisioning'     => [
        'blueprint' => 'law01-v1-2',
    ],
];
