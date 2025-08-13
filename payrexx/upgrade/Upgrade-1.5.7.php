<?php
/**
 * Payrexx Payment Gateway - upgrade the module
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2025 Payrexx
 * @license   MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_5_7($module)
{
    return include _PS_MODULE_DIR_ . 'payrexx/sql/install.php';
}
