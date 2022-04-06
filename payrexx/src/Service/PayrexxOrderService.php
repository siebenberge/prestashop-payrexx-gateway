<?php

namespace Payrexx\PayrexxPaymentGateway\Service;

use Db;
use Customer;
use Cart;
use Configuration;
use Module;
use Context;
use OrderHistory;

class PayrexxOrderService
{
    public function createOrder($cartId, $prestaStatus, $amount)
    {
        $payrexxModule = Module::getInstanceByName('payrexx');
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);
        $statusId = (int)Configuration::get($prestaStatus);

        $payrexxModule->validateOrder(
            (int)$cart->id,
            $statusId,
            (float)$amount / 100,
            'Payrexx',
            null,
            array(),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );
        $context = Context::getContext();
        $context->cart = $cart;
    }

    public function getPrestaStatusByPayrexxStatus($transactionStatus)
    {
        $prestaStatus = null;
        switch ($transactionStatus) {
            case \Payrexx\Models\Response\Transaction::CANCELLED:
            case \Payrexx\Models\Response\Transaction::DECLINED:
            case \Payrexx\Models\Response\Transaction::ERROR:
            case \Payrexx\Models\Response\Transaction::EXPIRED:
                $prestaStatus = 'PS_OS_ERROR';
                break;
            case \Payrexx\Models\Response\Transaction::REFUNDED:
            case \Payrexx\Models\Response\Transaction::PARTIALLY_REFUNDED:
                $prestaStatus = 'PS_OS_REFUND';
                break;
            case \Payrexx\Models\Response\Transaction::CONFIRMED:
                $prestaStatus = 'PS_OS_PAYMENT';
                break;
            case \Payrexx\Models\Response\Transaction::WAITING:
                $prestaStatus = 'PS_OS_BANKWIRE';
                break;
        }

        return $prestaStatus;
    }

    public function updateOrderStatus($prestaStatus, $order)
    {
        $orderHistory = new OrderHistory();
        $prestaStatusId = Configuration::get($prestaStatus);

        $orderHistory->id_order = (int)$order->id;
        $orderHistory->changeIdOrderState($prestaStatusId, $order, true);
        $orderHistory->addWithemail();
    }

    /**
     * Get Gateway id from the cart id
     *
     * @param int $id_cart cart id
     * @return int
     */
    public function getCartGatewayId($id_cart)
    {
        if (empty($id_cart)) {
            return 0;
        }

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_gateway FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_cart = ' . (int)$id_cart);
    }
}