<?php
/**
 * upgrade the module
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
 * @license MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_3()
{
    try {
        $queryStatus = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_payment_methods` (
                `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `active` tinyint DEFAULT NULL,
                `title` varchar(255) DEFAULT NULL,
                `pm` varchar(255) DEFAULT NULL,
                `country` text,
                `currency` text,
                `customer_group` text,
                `position` tinyint DEFAULT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
        if (!$queryStatus) {
            return false;
        }
        // Create new admin tab
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
    } catch (Exception $e) {
        return false;
    }
    return true;
}
