<?php
/**
 * Payrexx Payment Gateway.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
 * @license MIT License
 */

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payrexx_payment_methods`;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
