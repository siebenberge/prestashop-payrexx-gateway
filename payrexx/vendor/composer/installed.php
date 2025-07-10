<?php return array(
    'root' => array(
        'name' => 'payrexx/payrexxpaymentgateway',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '3c3eaf591f6629a135b885181c3b257f50232103',
        'type' => 'prestashop-module',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'payrexx/payrexx' => array(
            'pretty_version' => 'v2.0.1',
            'version' => '2.0.1.0',
            'reference' => '09f68c0463b1c240f9b051caf0ec305e80f3f8e3',
            'type' => 'library',
            'install_path' => __DIR__ . '/../payrexx/payrexx',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'payrexx/payrexxpaymentgateway' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '3c3eaf591f6629a135b885181c3b257f50232103',
            'type' => 'prestashop-module',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
