<?php
/** Industrial 01 reusable presentation manifest. */
return [
    'contract_version' => 1,
    'id'               => 'industrial-01',
    'name'             => 'Industrial 01',
    'version'          => '1.0.0',
    'category'         => 'Thiết bị công nghiệp',
    'description'      => 'Website B2B bán thiết bị công nghiệp và phụ kiện.',
    'capabilities'     => [ 'homepage', 'woocommerce' ],
    'presentation'     => [
        'visual_preset'   => 'industrial-01',
        'homepage_preset' => 'industrial-01',
    ],
    'assets'           => [
        'homepage' => [
            [
                'type'         => 'style',
                'handle'       => 'aznet-theme-homepage-industrial-01',
                'path'         => '/assets/css/components/homepage-industrial-01.css',
                'dependencies' => [ 'aznet-theme-tokens', 'aznet-theme-homepage' ],
            ],
        ],
    ],
    'homepage'         => [
        'authoring' => [
            'sections' => [ 'hero', 'about', 'solutions', 'process', 'knowledge', 'contact' ],
        ],
        'hero' => [
            'enabled'        => true,
            'admin_renderer' => 'render_homepage_manifest_settings_hero_editor',
            'content_owner'  => 'theme-legacy-compat',
            'source_type'    => 'presentation_settings',
            'preview'         => [
                'eyebrow'         => 'homepage_industrial01_hero_kicker',
                'title'           => 'homepage_industrial01_hero_title',
                'lead'            => 'homepage_industrial01_hero_lede',
                'primary_label'   => 'homepage_industrial01_hero_primary_label',
                'secondary_label' => 'homepage_industrial01_hero_secondary_label',
                'image'           => 'homepage_industrial01_hero_image',
            ],
            'settings_fields' => [
                [ 'key' => 'homepage_industrial01_hero_kicker', 'type' => 'text', 'label' => 'Dòng giới thiệu nhỏ', 'placeholder' => 'Thiết bị & phụ kiện' ],
                [ 'key' => 'homepage_industrial01_hero_title', 'type' => 'text', 'label' => 'Tiêu đề Hero' ],
                [ 'key' => 'homepage_industrial01_hero_lede', 'type' => 'textarea', 'label' => 'Mô tả Hero' ],
                [ 'key' => 'homepage_industrial01_hero_primary_label', 'type' => 'text', 'label' => 'Nút chính', 'placeholder' => 'Danh mục thiết bị' ],
                [ 'key' => 'homepage_industrial01_hero_primary_url', 'type' => 'url', 'label' => 'Liên kết nút chính' ],
                [ 'key' => 'homepage_industrial01_hero_secondary_label', 'type' => 'text', 'label' => 'Nút phụ', 'placeholder' => 'Yêu cầu báo giá' ],
                [ 'key' => 'homepage_industrial01_hero_secondary_url', 'type' => 'text', 'label' => 'Liên kết nút phụ', 'placeholder' => '#aznet-industrial01-quote' ],
                [ 'key' => 'homepage_industrial01_hero_image', 'type' => 'image', 'label' => 'Ảnh Hero cột phải' ],
                [ 'key' => 'homepage_industrial01_hero_background', 'type' => 'image', 'label' => 'Ảnh nền Hero', 'help' => 'Khuyến nghị ảnh ngang khoảng 1920×960, không có chữ và chừa vùng an toàn cho nội dung.' ],
            ],
        ],
    ],
    'provisioning'     => [],
];
