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

class PayrexxPayrexxModuleFrontController extends ModuleFrontController
{
    const MODULE_NAME = 'payrexx';

    public function postProcess()
    {
        $context = Context::getContext();
        $cart = $context->cart;

        $productNames = array();
        $products = $cart->getProducts();
        foreach ($products as $product) {
            $quantity = $product['cart_quantity'] > 1 ? $product['cart_quantity'] . 'x ' : '';
            $productNames[] = $quantity . $product['name'];
        }

        $customer = $context->customer;
        $address = new Address($cart->id_address_delivery);

        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
        $currency = $context->currency->iso_code;

        $successRedirectUrl = Context::getContext()->link
            ->getModuleLink(self::MODULE_NAME, 'validation', array(), true);
        $failedRedirectUrl = Tools::getShopDomain(true, true) . '/index.php?controller=order&step=1';

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

        $currencyIsoCode = !empty($currency) ? $currency : 'USD';
        $iso = Country::getIsoById($address->id_country);

        $gateway->setPurpose(implode(', ', $productNames));
        $gateway->setAmount($total * 100);
        $gateway->setCurrency($currencyIsoCode);
        $gateway->setSuccessRedirectUrl($successRedirectUrl);
        $gateway->setFailedRedirectUrl($failedRedirectUrl);
        $gateway->setPsp(array());
        $gateway->setReferenceId($cart->id);
        $gateway->setSkipResultPage(true);

        $gateway->addField('title', '');
        $gateway->addField('forename', $customer->firstname);
        $gateway->addField('surname', $customer->lastname);
        $gateway->addField('company', $customer->company);
        $gateway->addField('street', $address->address1);
        $gateway->addField('postcode', $address->postcode);
        $gateway->addField('place', $address->city);
        $gateway->addField('country', $iso);
        $gateway->addField('phone', $address->phone);
        $gateway->addField('email', $customer->email);
        $gateway->addField('custom_field_1', $cart->id, 'Prestashop ID');

        try {
            $response = $payrexx->create($gateway);
            $context->cookie->paymentId = $response->getId();
            static::insertCartGatewayId($cart->id, $response->getId());
            $lang = Language::getIsoById($context->cookie->id_lang);
            $gatewayUrl = 'https://' . $instanceName . '.payrexx.com/' . $lang . '/?payment=' . $response->getHash();

            if ((bool)Configuration::get('PAYREXX_USE_MODAL')) {
                if (empty($_SESSION)) {
                    session_start();
                }
                $_SESSION['payrexx_gateway_url'] = $gatewayUrl;
                Tools::redirect('index.php?controller=order&step=1');
            } else {
                Tools::redirect($gatewayUrl);
            }
        } catch (\Payrexx\PayrexxException $e) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    /**
     * Insert Gateway id
     *
     * @param int $id_cart    cart id
     * @param int $id_gateway gateway id
     * @return boolean
     */
    public static function insertCartGatewayId($id_cart, $id_gateway)
    {
        if (empty($id_cart) || empty($id_gateway)) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'payrexx_gateway` (`id_cart`, `id_gateway`)
            VALUES (' . $id_cart . ',' . $id_gateway . ')'
            . 'ON DUPLICATE KEY UPDATE id_gateway = ' . $id_gateway . '
        ');
    }
}
