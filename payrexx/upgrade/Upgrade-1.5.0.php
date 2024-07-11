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

function upgrade_module_1_5_0($module)
{
    $status = include _PS_MODULE_DIR_ . 'payrexx/sql/install.php';

    $paymentMethodSql = new DbQuery();
    $paymentMethodSql->select('pm')
        ->from('payrexx_payment_methods')
        ->where('active = 1')
        ->where('pm = "post-finance-card" OR pm = "post-finance-e-finance"');
    $results = Db::getInstance()->executeS($paymentMethodSql);

    // activate post-finance-pay
    if (!empty($results)) {
        $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'payrexx_payment_methods`
            SET `active` = 1
            WHERE  `pm` = "post-finance-pay"';
        Db::getInstance()->execute($updateSql);
    }

    $deleteSql = 'DELETE FROM `' . _DB_PREFIX_ . 'payrexx_payment_methods`
        WHERE  `pm` = "post-finance-card" OR `pm` = "post-finance-e-finance"';
    Db::getInstance()->execute($deleteSql);

    return $status;
}
