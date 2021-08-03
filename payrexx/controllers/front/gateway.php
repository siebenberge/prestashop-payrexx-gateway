<?php
/**
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Payrexx <integration@payrexx.com>
 * @copyright  2019 Payrexx
 * @license MIT License
 */

class PayrexxGatewayModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        $transaction = Tools::getValue('transaction');
        $cartId = $transaction['invoice']['referenceId'];
        $requestStatus = $transaction['status'];

        if (!$this->validRequest($transaction, $cartId, $requestStatus)) {
            die;
        }

        $orderId = Order::getIdByCartId($cartId);
        $prestaStatus = $this->getPrestaStatus($requestStatus);

        if (!$orderId) {
            $this->createOrder($cartId, $prestaStatus, $transaction['amount']);
            die;
        }

        $this->updateOrderStatus($prestaStatus, $orderId);
        die;
    }

    private function validRequest($transaction, $cartId, $requestStatus)
    {

        // check required data
        if (!$cartId || !$requestStatus || !$transaction['id']) {
            return false;
        }

        $gateway = $this->getPayrexxGateway((int)$cartId);

        // Validate request by gateway ID
        if (!$gateway) {
            PrestaShopLoggerCore::addLog('GATEWAY FOR CART ID: ' . $cartId . ' NOT FOUND');
        }

        $transactionObj = $this->getPayrexxTransaction($transaction['id']);

        $payrexxAmount = $transactionObj->getAmount();

        if (empty($payrexxAmount) || $payrexxAmount !== (int)$transaction['amount']) {
            return false;
        }

        $payrexxStatus = $transactionObj->getStatus();
        if (empty($payrexxStatus) || $payrexxStatus !== $requestStatus) {
            return false;
        }

        return true;
    }

    private function createOrder($cartId, $prestaStatus, $amount)
    {
        $payrexxModule = Module::getInstanceByName('payrexx');

        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        $payrexxModule->validateOrder(
            (int)$cartId,
            (int)Configuration::get($prestaStatus),
            (float)$amount / 100,
            'Payrexx',
            null,
            array(),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );
    }

    private function updateOrderStatus($prestaStatus, $orderId = null)
    {
        $objOrder = new Order($orderId);
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $history->changeIdOrderState(Configuration::get($prestaStatus), $objOrder, true);
        $history->addWithemail();

        return;
    }

    private function getPrestaStatus($transactionStatus)
    {
        $prestaStatus = null;
        switch ($transactionStatus) {
            case \Payrexx\Models\Response\Transaction::ERROR:
            case \Payrexx\Models\Response\Transaction::CANCELLED:
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

    /**
     * Get Payrexx Gateway from the cart id
     *
     * @param int $id_cart Cart id
     * @return \Payrexx\Models\Request\Gateway|NULL
     */
    public function getPayrexxGateway($id_cart)
    {
        $id_gateway = static::getCartGatewayId($id_cart);

        if (!$id_gateway) {
            return;
        }
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
        $gateway->setId($id_gateway);

        try {
            return $payrexx->getOne($gateway);
        } catch (\Payrexx\PayrexxException $e) {
            return;
        }
    }
    /**
     * Get Payrexx Gateway from the cart id
     *
     * @param int $id_cart Cart id
     * @return \Payrexx\Models\Request\Gateway|NULL
     */
    public function getPayrexxTransaction($transactionID)
    {
        $instanceName = Configuration::get('PAYREXX_INSTANCE_NAME');
        $secret = Configuration::get('PAYREXX_API_SECRET');

        $payrexx = new \Payrexx\Payrexx($instanceName, $secret);
        $transaction = new \Payrexx\Models\Request\Transaction();
        $transaction->setId($transactionID);

        try {
            return $payrexx->getOne($transaction);
        } catch (\Payrexx\PayrexxException $e) {
            return;
        }
    }

    /**
     * Get Gateway id from the cart id
     *
     * @param int $id_cart cart id
     * @return int
     */
    public static function getCartGatewayId($id_cart)
    {
        if (empty($id_cart)) {
            return 0;
        }

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT id_gateway FROM `' . _DB_PREFIX_ . 'payrexx_gateway`
            WHERE id_cart = ' . (int)$id_cart);
    }
}
