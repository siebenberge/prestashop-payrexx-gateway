<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 */
class PayrexxGatewayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $payrexxOrderService = $this->get('payrexx.payrexxpaymentgateway.payrexxorderservice');

        $transaction = Tools::getValue('transaction');
        $cartId = $transaction['invoice']['referenceId'];
        $requestStatus = $transaction['status'];
        $order = Order::getByCartId($cartId);

        if (!$this->validRequest($transaction, $cartId, $requestStatus)) {
            exit;
        }

        if (!$prestaStatus = $payrexxOrderService->getPrestaStatusByPayrexxStatus($requestStatus)) {
            exit;
        }

        // Create order if transaction successful
        if (!$order && in_array($requestStatus, [\Payrexx\Models\Response\Transaction::CONFIRMED, \Payrexx\Models\Response\Transaction::WAITING])) {
            $payrexxOrderService->createOrder($cartId, $prestaStatus, $transaction['amount']);
            exit;
        }

        // Update status if current status is not final
        if ($order && $order->current_state !== 2) {
            $payrexxOrderService->updateOrderStatus($prestaStatus, $order);
            $order->addOrderPayment($transaction['amount'], 'Payrexx', $transaction['id']);
            exit;
        }
        exit;
    }

    private function validRequest($transaction, $cartId, $requestStatus): bool
    {
        // check required data
        if (!$cartId || !$requestStatus || !$transaction['id']) {
            return false;
        }

        $payrexxApiService = $this->get('payrexx.payrexxpaymentgateway.payrexxapiservice');
        $gateway = $payrexxApiService->getPayrexxGateway((int) $transaction['invoice']['paymentRequestId']);

        // Validate request by gateway ID
        if (!$gateway) {
            PrestaShopLoggerCore::addLog('GATEWAY FOR CART ID: ' . $cartId . ' NOT FOUND');
        }

        $transactionObj = $payrexxApiService->getPayrexxTransaction($transaction['id']);

        $payrexxAmount = $transactionObj->getAmount();

        if (empty($payrexxAmount) || $payrexxAmount !== (int) $transaction['amount']) {
            return false;
        }

        $payrexxStatus = $transactionObj->getStatus();
        if (empty($payrexxStatus) || $payrexxStatus !== $requestStatus) {
            return false;
        }

        return true;
    }
}
