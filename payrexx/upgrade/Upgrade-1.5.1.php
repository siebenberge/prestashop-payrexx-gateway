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

function upgrade_module_1_5_1($module)
{
    return include _PS_MODULE_DIR_ . 'payrexx/sql/install.php';
}
