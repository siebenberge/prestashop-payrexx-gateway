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
    const ERROR_CONFIG = 'config';
    const ERROR_CANCEL = 'cancel';
    const ERROR_FAIL = 'fail';

    public function __construct()
    {
        if (isset($_GET['payrexxError'])) {
            $this->handleError($_GET['payrexxError']);
        }

        parent::__construct();
    }

    public function initContent()
    {
        $payrexxOrderService = $this->get('payrexx.payrexxpaymentgateway.payrexxorderservice');
        $payrexxApiService = $this->get('payrexx.payrexxpaymentgateway.payrexxapiservice');
        $payrexxDbService = $this->get('payrexx.payrexxpaymentgateway.payrexxdbservice');

        $gatewayId = $this->context->cookie->paymentId;
        $gateway = $payrexxApiService->getPayrexxGateway($gatewayId);
        $cartId = $payrexxDbService->getGatewayCartId($gatewayId);

        // Redirect to success page if successful order already exists
        $order = Order::getByCartId($cartId);
        if ($order && in_array($order->current_state, [2, 10])) {
            Tools::redirect(
                'index.php?controller=order-confirmation&id_cart=' . $cartId .
                '&id_module=' . $this->module->id .
                '&id_order=' . $order->id .
                '&key=' . $order->secure_key
            );
        }

        // handle error if no successful transaction exists
        $transaction = $payrexxApiService->getTransactionByGateway($gateway);
        if (!$transaction || !in_array($transaction->getStatus(), [\Payrexx\Models\Response\Transaction::CONFIRMED, \Payrexx\Models\Response\Transaction::WAITING])) {
            $this->handleError(self::ERROR_FAIL);
            return;
        }

        // Create order
        $prestaStatus = $payrexxOrderService->getPrestaStatusByPayrexxStatus($transaction->getStatus());
        $payrexxOrderService->createOrder($cartId, $prestaStatus, $transaction->getAmount());

        // Redirect to confirmation page if order creation was successful
        if ($order = Order::getByCartId($cartId)) {
            Tools::redirect(
                'index.php?controller=order-confirmation&id_cart=' . $cartId .
                '&id_module=' . $this->module->id .
                '&id_order=' . $this->module->currentOrder .
                '&key=' . $order->secure_key
            );
        }

        $this->handleError(self::ERROR_FAIL);
    }

    private function handleError($payrexxError)
    {
        switch($payrexxError) {
            case self::ERROR_CONFIG:
                $errMsg = 'The connection to the payment provider failed. Please contact the Shop owner';
                break;
            case self::ERROR_CANCEL:
                $errMsg = 'The transaction was cancelled. Please try again';
                break;
            case self::ERROR_FAIL:
            default:
                $errMsg = 'The transaction failed. Please try again';
                break;
        }

        $this->errors[] = Tools::displayError($errMsg);
        $this->redirectWithNotifications('index.php?controller=order&step=1');
    }
}
