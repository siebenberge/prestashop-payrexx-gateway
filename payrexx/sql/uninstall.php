<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
 * @license MIT License
 */

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway`;';
$sql[] = 'DROP TABLE `' . _DB_PREFIX_ . 'payrexx_payment_methods`;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
