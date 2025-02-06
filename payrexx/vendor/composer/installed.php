<?php return array(
    'root' => array(
        'name' => 'payrexx/payrexxpaymentgateway',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'f2692404b910e13b3a709a8703f452fafb8cc48d',
        'type' => 'prestashop-module',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'payrexx/payrexx' => array(
            'pretty_version' => 'v1.8.10',
            'version' => '1.8.10.0',
            'reference' => 'ab932dca32c607cbaa6f8da9b9e1de2efe28021f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../payrexx/payrexx',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'payrexx/payrexxpaymentgateway' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'f2692404b910e13b3a709a8703f452fafb8cc48d',
            'type' => 'prestashop-module',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
