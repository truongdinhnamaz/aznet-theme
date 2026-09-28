<?php
/**
 * Built-in presentation template manifests.
 *
 * These manifests describe only Theme presentation capabilities. They do not
 * copy or own WordPress/plugin/provider domain data.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

register_template_manifest(
    [
        'contract_version' => AZNET_THEME_TEMPLATE_MANIFEST_VERSION,
        'id'               => 'law-01',
        'name'             => 'Law 01',
        'version'          => '1.0.0',
        'category'         => 'professional',
        'description'      => 'Mẫu trình bày dịch vụ pháp lý và nội dung chuyên môn.',
        'capabilities'     => [ 'homepage' ],
        'presentation'     => [
            'homepage_preset' => 'law-01',
        ],
        'assets'           => [],
        'homepage'         => [],
        'provisioning'     => [
            'blueprint' => 'law01-v1-2',
        ],
    ]
);

register_template_manifest(
    [
        'contract_version' => AZNET_THEME_TEMPLATE_MANIFEST_VERSION,
        'id'               => 'curtain-01',
        'name'             => 'Rèm 01',
        'version'          => '1.0.0',
        'category'         => 'interior',
        'description'      => 'Mẫu trình bày rèm và giải pháp kiểm soát ánh sáng.',
        'capabilities'     => [ 'homepage', 'woocommerce' ],
        'presentation'     => [
            'visual_preset'   => 'curtain-01',
            'homepage_preset' => 'curtain-01',
        ],
        'assets'           => [],
        'homepage'         => [],
        'provisioning'     => [
            'blueprint' => 'curtain-v1',
        ],
    ]
);
