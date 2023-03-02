<?php
/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
require_once _PS_MODULE_DIR_ . 'payrexx/src/Util/ConfigurationUtil.php';

use Payrexx\PayrexxPaymentGateway\Util\ConfigurationUtil;

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway` (
    id_cart INT(11) NOT NULL UNIQUE,
    id_gateway INT(11) UNSIGNED DEFAULT "0" NOT NULL,
    PRIMARY KEY (`id_cart`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_payment_methods` (
    `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `active` tinyint DEFAULT NULL,
    `pm` varchar(255) DEFAULT NULL,
    `country` text,
    `currency` text,
    `customer_group` text,
    `position` tinyint DEFAULT NULL
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

// Check payment method.
$paymentMethodSql = new DbQuery();
$paymentMethodSql->select('pm')
    ->from('payrexx_payment_methods');
$results = Db::getInstance()->executeS($paymentMethodSql);
$existingPm = [];
foreach ($results as $result) {
    $existingPm[] = $result['pm'];
}
$paymentMethods = array_diff(
    array_keys(ConfigurationUtil::getPaymentMethods()), $existingPm
);
// add payment methods
foreach ($paymentMethods as $paymentMethod) {
    $insertData = [
        'active' => 0,
        'pm' => $paymentMethod,
        'country' => serialize([]),
        'currency' => serialize([]),
        'customer_group' => serialize([]),
        'position' => 0,
    ];
    if (Db::getInstance()->insert('payrexx_payment_methods', $insertData) == false) {
        return false;
    }
}

// Check tab is exist.
$sql = new DbQuery();
$sql->select('id_tab')
    ->from('tab')
    ->where('class_name = "AdminPayrexxPaymentMethods"')
    ->limit(1);
$result = Db::getInstance()->executeS($sql);
if (Db::getInstance()->numRows() > 0) {
    return true;
}
// Create new tab to edit the payment method in backend.
$tab = new Tab();
$tab->id_parent = -1;
$tab->name = [];
foreach (Language::getLanguages(true) as $lang) {
    $tab->name[$lang['id_lang']] = 'Payment Methods';
}
$tab->class_name = 'AdminPayrexxPaymentMethods';
$tab->module = 'payrexx';
$tab->active = 1;

return $tab->add();
