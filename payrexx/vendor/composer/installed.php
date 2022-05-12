<?php return array(
    'root' => array(
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'type' => 'prestashop-module',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '78af9ba89a5f38613a739dc5621720edf5bca6af',
        'name' => 'payrexx/payrexxpaymentgateway',
        'dev' => true,
    ),
    'versions' => array(
        'payrexx/payrexx' => array(
            'pretty_version' => 'v1.7.4',
            'version' => '1.7.4.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../payrexx/payrexx',
            'aliases' => array(),
            'reference' => '0cfdafe40e893b12df48d21cd83a5cf3c21b3055',
            'dev_requirement' => false,
        ),
        'payrexx/payrexxpaymentgateway' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'prestashop-module',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '78af9ba89a5f38613a739dc5621720edf5bca6af',
            'dev_requirement' => false,
        ),
    ),
);
