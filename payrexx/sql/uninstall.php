<?php
/**
 * Payrexx Payment Gateway.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payrexx_payment_methods`;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

// Remove tab
$sql = new DbQuery();
$sql->select('id_tab')
    ->from('tab')
    ->where('class_name = "AdminPayrexxPaymentMethods"')
    ->limit(1);
$result = Db::getInstance()->executeS($sql);
if (Db::getInstance()->numRows() == 0) {
    return true;
}

$tabId = $result[0]['id_tab'];
$tab = new Tab($tabId);
if (!$tab->delete()) {
    return false;
}
return true;
