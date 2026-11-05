<?php
/** Wellness 01 — native WordPress homepage presentation for guided movement and wellness studios. */
return [
    'contract_version' => 2,
    'id'               => 'wellness-01',
    'name'             => 'Wellness 01',
    'version'          => '1.0.0',
    'category'         => 'Sức khỏe / Vận động',
    'description'      => 'Studio Yoga, Pilates, Fitness, Dance, Thiền và lớp hướng dẫn.',
    'capabilities'     => [ 'homepage' ],
    'presentation'     => [
        'homepage_preset' => 'wellness-01',
    ],
    'assets'           => [
        'homepage' => [
            [
                'type'         => 'style',
                'handle'       => 'aznet-theme-homepage-wellness-01',
                'path'         => '/assets/css/components/homepage-wellness-01.css',
                'dependencies' => [ 'aznet-theme-tokens', 'aznet-theme-homepage' ],
            ],
        ],
    ],
    'homepage'         => [
        'composition' => [ 'mode' => 'native' ],
        'authoring'   => [ 'sections' => [ 'content' ] ],
        'hero'        => [
            'enabled'       => false,
            'content_owner' => 'wordpress',
            'source_type'   => 'page_content',
        ],
    ],
    'provisioning'     => [],
];
