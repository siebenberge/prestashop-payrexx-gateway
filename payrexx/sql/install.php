<?php
/**
 * Payrexx Payment Gateway.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2023 Payrexx
 * @license MIT License
 */

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_gateway` (
        id_cart INT(11) NOT NULL UNIQUE,
        id_gateway INT(11) UNSIGNED DEFAULT "0" NOT NULL,
        PRIMARY KEY (`id_cart`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payrexx_payment_methods` (
    `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `active` tinyint DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `pm` varchar(255) DEFAULT NULL,
    `description` varchar(255) DEFAULT NULL,
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

return true;
