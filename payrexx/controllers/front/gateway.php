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
        $id_cart = $transaction['invoice']['referenceId'];

        if (empty($id_cart) || !isset($transaction['status'])) {
            return;
        }

        $gateway = $this->getPayrexxGateway((int)$id_cart);
        $status = $gateway->getStatus();
        if (empty($status) || $status !== $transaction['status']) {
            return;
        }

        $payrexxModule = Module::getInstanceByName('payrexx');
        $cart = new Cart((int)$id_cart);
        $customer = new Customer($cart->id_customer);
        if (!in_array($status, array('confirmed', 'waiting'))) {
            die;
        }

        try {
            $payrexxModule->validateOrder(
                (int)$id_cart,
                (int)Configuration::get('PS_OS_PAYMENT'),
                (float)$gateway->getAmount() / 100,
                'Payrexx',
                null,
                array(),
                (int)$cart->id_currency,
                false,
                $customer->secure_key
            );
        } catch (PrestaShopException $e) {
            PrestaShopLoggerCore::addLog('CART ID: ' . $id_cart . ' - ' . $e->getMessage());
        }
        die;
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
            SELECT id_gateway FROM ' . _DB_PREFIX_ . 'payrexx_gateway
            WHERE id_cart = ' . $id_cart);
    }
}
