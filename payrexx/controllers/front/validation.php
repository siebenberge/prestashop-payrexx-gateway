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

require_once _PS_MODULE_DIR_ . '/payrexx/Service/PayrexxApiService.php';
class PayrexxValidationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $cart = $this->context->cart;
        $gatewayId = $this->context->cookie->paymentId;

        $payrexxApiService = new \PayrexxPaymentGateway\Service\PayrexxApiService(Configuration::get('PAYREXX_INSTANCE_NAME'), Configuration::get('PAYREXX_API_SECRET'), Configuration::get('PAYREXX_PLATFORM'));

        $payrexxModule = Module::getInstanceByName('payrexx');
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if ($gateway = $payrexxApiService->getPayrexxGateway($gatewayId)) {
            // Validate current cart
            if ($gateway->getReferenceId() != $cart->id) {
                $result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
                    SELECT o.current_state, o.id_order
                    FROM ' . _DB_PREFIX_ . 'cart as c
                    INNER JOIN ' . _DB_PREFIX_ . 'orders as o
                        ON c.id_cart=o.id_cart 
                    WHERE o.id_cart =' . (int)$gateway->getReferenceId());
                if (!empty($result) && $result['current_state'] == 2) {
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $gateway->getReferenceId() .
                        '&id_module=' . $payrexxModule->id .
                        '&id_order=' . $result['id_order'] .
                        '&key=' . $customer->secure_key
                    );
                } else {
                    Tools::redirect('index.php?controller=order&step=1');
                }
            }

            $invoices = $gateway->getInvoices();
            $invoice = $invoices ? end($invoices) : null;
            $transaction = $invoice ? end($invoice['transactions']) : null;
            if ($transaction && in_array($transaction['status'], array('confirmed', 'waiting'))) {
                switch ($transaction['status']) {
                    case \Payrexx\Models\Response\Transaction::CONFIRMED:
                        $prestaStatus = 'PS_OS_PAYMENT';
                        break;
                    case \Payrexx\Models\Response\Transaction::WAITING:
                        $prestaStatus = 'PS_OS_BANKWIRE';
                        break;
                }

                $payrexxModule->validateOrder(
                    (int)$cart->id,
                    (int)Configuration::get($prestaStatus),
                    (float)$gateway->getAmount() / 100,
                    'Payrexx',
                    null,
                    array(),
                    (int)$cart->id_currency,
                    false,
                    $customer->secure_key
                );

                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart=' . $cart->id .
                    '&id_module=' . $payrexxModule->id .
                    '&id_order=' . $this->module->currentOrder .
                    '&key=' . $customer->secure_key
                );
            }
        }

        Tools::redirect('index.php?controller=order&step=1');
    }
}
