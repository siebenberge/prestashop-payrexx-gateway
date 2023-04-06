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

function upgrade_module_1_4_4($module)
{
    return $module->registerHook('actionFrontControllerSetMedia');
}
