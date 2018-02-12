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
    public function initContent()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $gatewayId = $this->context->cookie->paymentId;

        spl_autoload_register(function ($class) {
            $root = _PS_MODULE_DIR_ . '/payrexx/controllers/front/payrexx-php-master';
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
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        try {
            $response = $payrexx->getOne($gateway);
            if ($response->getStatus() === 'confirmed') {
                $payrexxModule->validateOrder(
                    (int)$cart->id,
                    (int)Configuration::get('PS_OS_PAYMENT'),
                    (float)$response->getAmount(),
                    'Payrexx',
                    null,
                    array(),
                    (int)$this->context->currency->id,
                    false,
                    $customer->secure_key
                );

                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart=' . $cart->id .
                    '&id_module=' . $payrexxModule->id .
                    '&id_order='.$this->module->currentOrder.
                    '&key=' . $customer->secure_key
                );
            } else {
                Tools::redirect('index.php?controller=order&step=1');
            }
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }
}
