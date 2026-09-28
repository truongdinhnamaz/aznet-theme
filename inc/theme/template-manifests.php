<?php
/**
 * Built-in reusable presentation template manifests.
 *
 * These manifests declare presentation capabilities only. WordPress/plugins/providers
 * remain authoritative for content and domain state.
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
        'category'         => 'Professional',
        'description'      => 'Mẫu trình bày chuyên nghiệp cho website luật và dịch vụ chuyên môn.',
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
        'category'         => 'Nội thất',
        'description'      => 'Mẫu trình bày rèm/nội thất trên dữ liệu WordPress và WooCommerce công khai.',
        'capabilities'     => [ 'homepage', 'woocommerce' ],
        'presentation'     => [
            'visual_preset'   => 'curtain-01',
            'homepage_preset' => 'curtain-01',
        ],
        'assets'           => [],
        'homepage'         => [],
        'provisioning'     => [],
    ]
);
