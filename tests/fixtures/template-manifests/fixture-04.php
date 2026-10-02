<?php
/** Synthetic D-043 fourth-template fixture. Test-only; never loaded by production bootstrap. */
return [
    'contract_version' => 1,
    'id'               => 'fixture-04',
    'name'             => 'Fixture 04',
    'version'          => '1.0.0',
    'category'         => 'Synthetic',
    'description'      => 'Test-only fourth-template extension proof.',
    'capabilities'     => [ 'homepage' ],
    'presentation'     => [
        'visual_preset'   => 'fixture-04-visual',
        'homepage_preset' => 'fixture-04-home',
    ],
    'assets'           => [
        'homepage' => [
            [
                'type'         => 'style',
                'handle'       => 'fixture-04-home',
                'path'         => '/assets/css/components/homepage.css',
                'dependencies' => [ 'aznet-theme-tokens' ],
            ],
        ],
    ],
    'homepage'         => [
        'authoring' => [
            'sections' => [ 'hero', 'content', 'contact' ],
        ],
        'hero' => [
            'enabled'       => true,
            'content_owner' => 'wordpress',
            'source_type'   => 'wp_block',
        ],
    ],
    'provisioning'     => [
        'blueprint' => 'professional-services-v1',
    ],
];
