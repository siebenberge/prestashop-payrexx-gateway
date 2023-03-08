<?php
/**
 * Payrexx Validation Module FrontController
 *
 * @author    Payrexx <support@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
use Payrexx\PayrexxPaymentGateway\Config\PayrexxConfig;

class PayrexxValidationModuleFrontController extends ModuleFrontController
{
    const ERROR_CONFIG = 'config';
    const ERROR_CANCEL = 'cancel';
    const ERROR_FAIL = 'fail';

    public function __construct()
    {
        if (Tools::getIsset('payrexxError')) {
            $this->handleError(Tools::getValue('payrexxError'));
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
        if ($order && in_array($order->current_state, [2, 9, 10])) {
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

        $pm = $payrexxDbService->getPaymentMethodByCartId($cartId);
        $paymentMethod = PayrexxConfig::getPaymentMethodNameByIdentifier($pm);

        // Create order
        $prestaStatus = $payrexxOrderService->getPrestaStatusByPayrexxStatus($transaction->getStatus());
        $payrexxOrderService->createOrder(
            $cartId,
            $prestaStatus,
            $transaction->getAmount(),
            $paymentMethod
        );

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
        switch ($payrexxError) {
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

        $this->errors[] = $errMsg;
        $this->redirectWithNotifications('index.php?controller=order&step=1');
    }
}
