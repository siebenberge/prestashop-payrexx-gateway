<?php
/**
 * Payrexx Payment Gateway - upgrade the module
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_6($module)
{
    $hookName = 'actionFrontControllerSetMedia';
    if (!$module->isRegisteredInHook($hookName)) {
        $module->registerHook($hookName);
    }
    return include _PS_MODULE_DIR_ . 'payrexx/sql/install.php';
}
