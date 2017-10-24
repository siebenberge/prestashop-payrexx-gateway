<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <support@payrexx.com>
 * @copyright  2017 Payrexx
 * @license MIT License
 */

class PayrexxValidationModuleFrontController extends ModuleFrontController
{
    public $name = 'payrexx';

    public function initContent()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        if (!isset($cart->id)) {
            Tools::redirect('index.php');
            exit();
        }

        $gatewayId = $context->cookie->paymentId;

        spl_autoload_register(function ($class) {
            $root = __DIR__ . '/payrexx-php-master';
            $classFile = $root . '/lib/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($classFile)) {
                require_once $classFile;
            }
        });

        $instanceName = Configuration::get('PAYREXX_INSTANCE_NAME');
        $secret = Configuration::get('PAYREXX_API_SECRET');
        $payrexx = new \Payrexx\Payrexx($instanceName, $secret);
        $gateway = new \Payrexx\Models\Request\Gateway();

        $gateway->setId($gatewayId);

        $payrexxModule = Module::getInstanceByName('payrexx');
        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
        $customer = new Customer($cart->id_customer);

        try {
            $response = $payrexx->getOne($gateway);
            if ($response->getStatus() === 'confirmed') {
                $payrexxModule->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, 'Payrexx', null, array(), (int)$context->currency->id, false, $customer->secure_key);


                Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$payrexxModule->id.'&key='.$customer->secure_key);
            } else {
                Tools::redirect('index.php?controller=order&step=1');
            }
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }
}
