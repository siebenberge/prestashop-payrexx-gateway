<?php
/**
 * Payrexx Payment Gateway - upgrade the module
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2024 Payrexx
 * @license   MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_9($module)
{
    $query = 'DELETE FROM `' . _DB_PREFIX_ . 'payrexx_payment_methods` WHERE `pm` = \'sofort\'';
    Db::getInstance()->execute($query);
    return include _PS_MODULE_DIR_ . 'payrexx/sql/install.php';
}
