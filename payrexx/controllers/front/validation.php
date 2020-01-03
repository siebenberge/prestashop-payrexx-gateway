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

            // Validate current cart
            if ($response->getReferenceId() != $cart->id) {
                $currentStatus = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                    SELECT o.current_state
                    FROM ' . _DB_PREFIX_ . 'cart as c
                    INNER JOIN ' . _DB_PREFIX_ . 'orders as o
                        ON c.id_cart=o.id_cart 
                    WHERE o.id_cart =' . $response->getReferenceId());
                $fopen = fopen(_PS_ROOT_DIR_ . '/log/debug.log', 'w+');
                if ($currentStatus == 2) {
                    fwrite($fopen, $payrexxModule->id);
                    fwrite($fopen, $this->module->currentOrder);
                    fwrite($fopen, $customer->secure_key);
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $response->getReferenceId() .
                        '&id_module=' . $payrexxModule->id .
                        '&id_order=' . $this->module->currentOrder .
                        '&key=' . $customer->secure_key
                    );
                } else {
                    Tools::redirect('index.php?controller=order&step=1');
                }
            }

            $invoices = $response->getInvoices();
            $invoice = $invoices ? end($invoices) : null;
            $transaction = $invoice ? end($invoice['transactions']) : null;
            if ($transaction && in_array($transaction['status'], array('confirmed', 'waiting'))) {
                $payrexxModule->validateOrder(
                    (int)$cart->id,
                    (int)Configuration::get('PS_OS_PAYMENT'),
                    (float)$response->getAmount() / 100,
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
                    '&id_order=' . $this->module->currentOrder .
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
